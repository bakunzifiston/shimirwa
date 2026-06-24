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
        'items',
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
     * @return array<int, array<string, mixed>>
     */
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
        static::creating(function (Milling $milling) {
            $total = 0;

            foreach ($milling->items ?? [] as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $type = $item['type'] ?? null;
                $stockId = $item['stock_id'] ?? null;

                if (! $qty || ! $type || ! $stockId) {
                    continue;
                }

                $total += $qty;
                $batch = self::resolveBatch($type, (int) $stockId);

                if ($batch->remainingUsable() < $qty) {
                    $label = $batch instanceof Roasting ? $batch->batch : ($batch->rawMaterialStock?->batch_number ?? "#{$batch->id}");
                    throw new \Exception("Not enough stock in batch {$label}. Available: {$batch->remainingUsable()} kg.");
                }
            }

            $milling->total_mixed_quantity = $total;
            $milling->output_flour = max($total - ($milling->loss ?? 0), 0);

            if (($milling->loss ?? 0) > $total) {
                throw new \Exception('Loss cannot exceed mixed quantity.');
            }
        });

        static::created(function (Milling $milling) {
            DB::transaction(function () use ($milling) {
                self::deductItemQuantities($milling->items ?? []);
            });
        });

        static::updating(function (Milling $milling) {
            if ($milling->isDirty(['items', 'loss'])
                && MillingItemUsage::millingReferencedInEmballage($milling->id)) {
                throw new \Exception('Cannot change ingredients or loss: packaging records exist for this milling batch.');
            }

            if (! $milling->isDirty('items') && ! $milling->isDirty('loss')) {
                return;
            }

            $total = 0;
            foreach ($milling->items ?? [] as $item) {
                $total += floatval($item['quantity'] ?? 0);
            }

            $loss = floatval($milling->loss ?? 0);

            if ($loss > $total) {
                throw new \Exception("Loss cannot exceed total mixed quantity ({$total} kg).");
            }

            $milling->total_mixed_quantity = $total;
            $milling->output_flour = max($total - $loss, 0);
        });

        static::updated(function (Milling $milling) {
            if (! $milling->wasChanged('items')) {
                return;
            }

            DB::transaction(function () use ($milling) {
                $oldItems = self::normalizeMillingItems($milling->getOriginal('items'));
                $newItems = self::normalizeMillingItems($milling->items);
                self::applyItemDiff($oldItems, $newItems);
            });
        });

        static::deleting(function (Milling $milling) {
            if (MillingItemUsage::millingReferencedInEmballage($milling->id)) {
                throw new \Exception('Cannot delete milling: packaging records exist. Delete packaging first.');
            }

            DB::transaction(function () use ($milling) {
                self::restoreItemQuantities(self::normalizeMillingItems($milling->items));
            });
        });
    }

    /**
     * Deduct ingredient quantities from sorting/roasting batches on create.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private static function deductItemQuantities(array $items): void
    {
        self::applyItemDiff([], $items);
    }

    /**
     * Restore ingredient quantities to sorting/roasting batches on delete.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private static function restoreItemQuantities(array $items): void
    {
        self::applyItemDiff($items, []);
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
                $key = ($item['type'] ?? '').':'.($item['stock_id'] ?? '');
                $map[$key] = ($map[$key] ?? 0) + floatval($item['quantity'] ?? 0);
            }

            return $map;
        };

        $oldMap = $buildMap($oldItems);
        $newMap = $buildMap($newItems);
        $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

        foreach ($allKeys as $key) {
            [$type, $stockId] = explode(':', $key, 2);
            if (! $type || ! $stockId) {
                continue;
            }

            $diff = ($newMap[$key] ?? 0) - ($oldMap[$key] ?? 0);
            if ($diff == 0) {
                continue;
            }

            $batch = self::resolveBatch($type, (int) $stockId);

            if ($diff > 0 && $batch->remainingUsable() < $diff) {
                $label = $batch instanceof Roasting ? $batch->batch : ($batch->rawMaterialStock?->batch_number ?? "#{$batch->id}");
                throw new \Exception("Not enough stock in batch {$label}. Available: {$batch->remainingUsable()} kg.");
            }

            $batch::withoutEvents(function () use ($batch, $diff) {
                $batch->quantity_remaining = max($batch->remainingUsable() - $diff, 0);
                $batch->save();
            });
        }
    }

    private static function resolveBatch(string $type, int $stockId): Roasting|Sorting
    {
        $batch = in_array($type, ['soy', 'maize'], true)
            ? Roasting::query()->lockForUpdate()->find($stockId)
            : Sorting::query()->lockForUpdate()->with('rawMaterialStock')->find($stockId);

        if (! $batch) {
            throw new \Exception('Selected batch does not exist.');
        }

        return $batch;
    }
}
