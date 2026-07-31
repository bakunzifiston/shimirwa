<?php

namespace App\Models;

use App\Support\Inventory\MillingItemUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Milling extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'items',               // JSON: [{type, source, stock_id, quantity}, ...]
        'total_mixed_quantity',
        'output_flour',
        'loss',
        'batch_number',
        'employee_id',
    ];

    protected $casts = [
        'date' => 'date',
        'items' => 'array',
        'total_mixed_quantity' => 'float',
        'output_flour' => 'float',
        'loss' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Map of catalog item name => excludes_from_milled_weight, for the given items.
     * Only 'raw' (direct-to-milling) items ever consult this — roasting/sorting
     * items always count toward the milled weight since they represent actual
     * milled grain.
     */
    public static function excludesWeightFlags(array $items): array
    {
        $names = collect($items)->where('source', 'raw')->pluck('type')->filter()->unique();
        if ($names->isEmpty()) return [];

        return ProductCatalog::whereIn('name', $names)->pluck('excludes_from_milled_weight', 'name')->all();
    }

    public static function itemCountsTowardMilledWeight(array $item, array $flags): bool
    {
        if (($item['source'] ?? '') !== 'raw') return true;

        return empty($flags[$item['type'] ?? '']);
    }

    /**
     * Total kg of direct-to-milling additives (e.g. sugar) mixed into this batch
     * whose catalog entry is flagged "excludes_from_milled_weight". These are
     * deducted from stock like any other ingredient but excluded from
     * total_mixed_quantity / output_flour — they were never milled.
     */
    public function additivesTotal(): float
    {
        $items = $this->items ?? [];
        $flags = self::excludesWeightFlags($items);

        return (float) collect($items)
            ->reject(fn ($item) => self::itemCountsTowardMilledWeight($item, $flags))
            ->sum(fn ($item) => (float) ($item['quantity'] ?? 0));
    }

    /**
     * Resolve each item in the JSON to its actual Roasting or Sorting model,
     * eager-loading related records so the show page can display full trace info.
     * Returns a collection of arrays: item data + resolved 'batch' model.
     */
    public function resolvedIngredients(): \Illuminate\Support\Collection
    {
        $items = $this->items ?? [];
        if (empty($items)) return collect();

        // Collect IDs grouped by source to bulk-load
        $roastingIds = collect($items)->where('source', 'roasting')->pluck('stock_id')->map(fn($id) => (int)$id)->unique();
        $sortingIds  = collect($items)->where('source', 'sorting')->pluck('stock_id')->map(fn($id) => (int)$id)->unique();
        $rawIds      = collect($items)->where('source', 'raw')->pluck('stock_id')->map(fn($id) => (int)$id)->unique();

        $roastings = Roasting::with(['rawMaterialStock', 'sorting.rawMaterialStock', 'chef'])
            ->whereIn('id', $roastingIds)->get()->keyBy('id');
        $sortings  = Sorting::with(['rawMaterialStock', 'employee'])
            ->whereIn('id', $sortingIds)->get()->keyBy('id');
        $raws      = RawMaterialStock::with('client')
            ->whereIn('id', $rawIds)->get()->keyBy('id');
        $flags     = self::excludesWeightFlags($items);

        return collect($items)->map(function ($item) use ($roastings, $sortings, $raws, $flags) {
            $source  = $item['source']   ?? '';
            $stockId = (int) ($item['stock_id'] ?? 0);
            $batch   = match($source) {
                'roasting' => $roastings[$stockId] ?? null,
                'sorting'  => $sortings[$stockId]  ?? null,
                'raw'      => $raws[$stockId]      ?? null,
                default    => null,
            };

            $itemName = $batch
                ? match($source) {
                    'roasting' => $batch->rawMaterialStock?->item ?? $batch->sorting?->rawMaterialStock?->item ?? '—',
                    'sorting'  => $batch->rawMaterialStock?->item ?? '—',
                    'raw'      => $batch->item ?? '—',
                    default    => '—',
                }
                : ($item['type'] ?? '—');

            $batchRef = $batch
                ? match($source) {
                    'roasting' => $batch->batch ?? "Roasting #{$stockId}",
                    'sorting'  => $batch->rawMaterialStock?->batch_number ?? "Sorting #{$stockId}",
                    'raw'      => $batch->batch_number ?? "Raw #{$stockId}",
                    default    => "#{$stockId}",
                }
                : "#{$stockId}";

            return [
                'type'      => $item['type']     ?? '—',
                'source'    => $source,
                'stock_id'  => $stockId,
                'quantity'  => (float) ($item['quantity'] ?? 0),
                'item_name' => $itemName,
                'batch_ref' => $batchRef,
                'batch'     => $batch,
                'excluded_from_weight' => !self::itemCountsTowardMilledWeight($item, $flags),
            ];
        });
    }

    protected static function normalizeMillingItems(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected static function booted()
    {
        static::creating(function ($milling) {
            $items = $milling->items ?? [];
            $flags = self::excludesWeightFlags($items);
            $total = 0;
            foreach ($items as $item) {
                $qty     = floatval($item['quantity'] ?? 0);
                $source  = $item['source']   ?? '';
                $stockId = (int) ($item['stock_id'] ?? 0);
                if (!$qty || !$source || !$stockId) continue;
                $batch = self::resolveBatch($source, $stockId);
                $avail = $batch->remainingUsable();
                if ($avail < $qty) throw new \Exception("Not enough stock in batch. Available: {$avail} kg.");
                // Additives flagged "excludes_from_milled_weight" (e.g. sugar) are mixed
                // in and deducted from stock like any other ingredient, but they were
                // never milled — they don't count toward total_mixed_quantity / output_flour.
                if (self::itemCountsTowardMilledWeight($item, $flags)) {
                    $total += $qty;
                }
            }

            $loss = (float) ($milling->loss ?? 0);
            if ($loss > $total) {
                throw new \Exception('Loss cannot exceed mixed quantity.');
            }

            $milling->total_mixed_quantity = $total;
            if ($milling->output_flour === null) {
                $milling->output_flour = max($total - $loss, 0);
            }
        });

        static::created(function ($milling) {
            foreach ($milling->items ?? [] as $item) {
                $qty     = floatval($item['quantity'] ?? 0);
                $source  = $item['source']   ?? '';
                $stockId = (int) ($item['stock_id'] ?? 0);
                if (!$qty || !$source || !$stockId) continue;
                $batch = self::resolveBatch($source, $stockId);
                if ($source === 'raw') {
                    RawMaterialStock::withoutEvents(function () use ($batch, $qty) {
                        $batch->quantity_in = max($batch->remainingUsable() - $qty, 0);
                        $batch->save();
                    });
                } else {
                    $batch::withoutEvents(function () use ($batch, $qty) {
                        $batch->quantity_remaining = max($batch->remainingUsable() - $qty, 0);
                        $batch->save();
                    });
                }
            }
        });

        static::updating(function ($milling) {
            if (! $milling->isDirty('items') && ! $milling->isDirty('loss')) {
                return;
            }

            $items = self::normalizeMillingItems($milling->items);
            // Additives flagged excludes_from_milled_weight (e.g. sugar) are excluded
            // from the milled weight — see the note in the creating() event above.
            $flags = self::excludesWeightFlags($items);
            $total = array_sum(array_map(
                fn ($item) => self::itemCountsTowardMilledWeight($item, $flags) ? (float) ($item['quantity'] ?? 0) : 0,
                $items
            ));
            $loss = (float) ($milling->loss ?? 0);

            if ($loss > $total) {
                throw new \Exception("Loss cannot exceed total mixed quantity ({$total} kg).");
            }

            $milling->total_mixed_quantity = $total;

            if ($milling->isDirty('loss') || $milling->isDirty('items')) {
                $milling->output_flour = max($total - $loss, 0);
            }
        });

        static::updated(function ($milling) {
            if (!$milling->wasChanged('items')) return;
            DB::transaction(function () use ($milling) {
                self::applyItemDiff(
                    self::normalizeMillingItems($milling->getOriginal('items')),
                    self::normalizeMillingItems($milling->items)
                );
            });
        });

        static::deleted(function ($milling) {
            DB::transaction(function () use ($milling) {
                self::applyItemDiff(self::normalizeMillingItems($milling->items), []);
            });
        });
    }

    private static function resolveBatch(string $source, int $stockId): Roasting|Sorting|RawMaterialStock
    {
        return match($source) {
            'roasting' => Roasting::findOrFail($stockId),
            'sorting'  => Sorting::findOrFail($stockId),
            default    => RawMaterialStock::findOrFail($stockId),
        };
    }

    private static function resolveBatchFromItem(array $item, bool $lock = false): Roasting|Sorting|RawMaterialStock|null
    {
        $source  = $item['source']   ?? '';
        $stockId = (int) ($item['stock_id'] ?? 0);

        if (! $source || ! $stockId) {
            return null;
        }

        $query = match($source) {
            'roasting' => Roasting::where('id', $stockId),
            'sorting'  => Sorting::where('id', $stockId),
            default    => RawMaterialStock::where('id', $stockId),
        };

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private static function itemKey(array $item): string
    {
        return ($item['source'] ?? '') . ':' . ($item['stock_id'] ?? '');
    }

    /**
     * @param  array<int, array<string, mixed>>  $oldItems
     * @param  array<int, array<string, mixed>>  $newItems
     */
    private static function applyItemDiff(array $oldItems, array $newItems): void
    {
        $buildMap = function (array $items): array {
            $map = [];
            foreach ($items as $item) {
                $key = self::itemKey($item);
                if (! str_contains($key, ':') || str_ends_with($key, ':')) {
                    continue;
                }
                $map[$key] = ($map[$key] ?? 0) + (float) ($item['quantity'] ?? 0);
            }

            return $map;
        };

        $oldMap = $buildMap($oldItems);
        $newMap = $buildMap($newItems);
        $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

        foreach ($allKeys as $key) {
            [$source, $stockId] = explode(':', $key, 2);
            if (! $source || ! $stockId) {
                continue;
            }

            $diff = ($newMap[$key] ?? 0) - ($oldMap[$key] ?? 0);
            if ($diff == 0) {
                continue;
            }

            $batch = self::resolveBatchFromItem([
                'source' => $source,
                'stock_id' => (int) $stockId,
            ], lock: true);

            if (! $batch) {
                throw new \Exception('Selected batch does not exist.');
             }

            if ($diff > 0 && $batch->remainingUsable() < $diff) {
                $label = $batch instanceof Roasting
                    ? $batch->batch
                    : ($batch instanceof RawMaterialStock
                        ? ($batch->batch_number ?? "Raw #{$batch->id}")
                        : ($batch->rawMaterialStock?->batch_number ?? "#{$batch->id}"));
                throw new \Exception("Not enough stock in batch {$label}. Available: {$batch->remainingUsable()} kg.");
            }

            if ($source === 'raw') {
                RawMaterialStock::withoutEvents(function () use ($batch, $diff) {
                    $batch->quantity_in = max($batch->remainingUsable() - $diff, 0);
                    $batch->save();
                });
            } else {
                $batch::withoutEvents(function () use ($batch, $diff) {
                    $batch->quantity_remaining = max($batch->remainingUsable() - $diff, 0);
                    $batch->save();
                });
            }
        }
    }
}
