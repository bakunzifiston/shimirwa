<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Roasting;
use App\Models\Sorting;
use App\Models\Employee;

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
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
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

        $roastings = Roasting::with(['rawMaterialStock', 'sorting.rawMaterialStock', 'chef'])
            ->whereIn('id', $roastingIds)->get()->keyBy('id');
        $sortings  = Sorting::with(['rawMaterialStock', 'employee'])
            ->whereIn('id', $sortingIds)->get()->keyBy('id');

        return collect($items)->map(function ($item) use ($roastings, $sortings) {
            $source  = $item['source']   ?? '';
            $stockId = (int) ($item['stock_id'] ?? 0);
            $batch   = $source === 'roasting' ? ($roastings[$stockId] ?? null) : ($sortings[$stockId] ?? null);

            $itemName = $batch
                ? ($source === 'roasting'
                    ? ($batch->rawMaterialStock?->item ?? $batch->sorting?->rawMaterialStock?->item ?? '—')
                    : ($batch->rawMaterialStock?->item ?? '—'))
                : ($item['type'] ?? '—');

            $batchRef = $batch
                ? ($source === 'roasting'
                    ? ($batch->batch ?? "Roasting #{$stockId}")
                    : ($batch->rawMaterialStock?->batch_number ?? "Sorting #{$stockId}"))
                : "#{$stockId}";

            return [
                'type'      => $item['type']     ?? '—',
                'source'    => $source,
                'stock_id'  => $stockId,
                'quantity'  => (float) ($item['quantity'] ?? 0),
                'item_name' => $itemName,
                'batch_ref' => $batchRef,
                'batch'     => $batch,
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

    protected static function resolveBatch(array $item): Roasting|Sorting|null
    {
        $source  = $item['source']   ?? null;
        $stockId = $item['stock_id'] ?? null;
        if (!$source || !$stockId) return null;
        return $source === 'roasting' ? Roasting::find($stockId) : Sorting::find($stockId);
    }

    protected static function booted()
    {
        static::creating(function ($milling) {
            $total = 0;
            foreach ($milling->items ?? [] as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                if (!$qty) continue;
                $batch = self::resolveBatch($item);
                if (!$batch) throw new \Exception("Selected batch does not exist.");
                $avail = $batch->quantity_out; // net output after loss, same accessor on both Roasting and Sorting
                if ($avail < $qty) throw new \Exception("Not enough stock in batch. Available: {$avail} kg.");
                $total += $qty;
            }
            $milling->total_mixed_quantity = $total;
            if (($milling->loss ?? 0) > $total) throw new \Exception("Loss cannot exceed mixed quantity.");
        });

        static::created(function ($milling) {
            foreach ($milling->items ?? [] as $item) {
                $qty   = floatval($item['quantity'] ?? 0);
                $batch = self::resolveBatch($item);
                if ($batch && $qty) {
                    \DB::table($item['source'] === 'roasting' ? 'roastings' : 'sortings')
                        ->where('id', $item['stock_id'])
                        ->decrement('quantity_in', $qty);
                }
            }
        });

        static::updating(function ($milling) {
            if (!$milling->isDirty('items') && !$milling->isDirty('loss')) return;
            $total = array_sum(array_column($milling->items ?? [], 'quantity'));
            $loss  = floatval($milling->loss ?? 0);
            if ($loss > $total) throw new \Exception("Loss cannot exceed total mixed quantity ({$total} kg).");
            $milling->total_mixed_quantity = $total;
        });

        static::updated(function ($milling) {
            if (!$milling->wasChanged('items')) return;

            $buildMap = fn (array $items) => array_reduce($items, function ($map, $item) {
                $key = ($item['source'] ?? '') . ':' . ($item['stock_id'] ?? '');
                $map[$key] = ($map[$key] ?? 0) + floatval($item['quantity'] ?? 0);
                return $map;
            }, []);

            $oldMap  = $buildMap(self::normalizeMillingItems($milling->getOriginal('items')));
            $newMap  = $buildMap(self::normalizeMillingItems($milling->items));
            $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

            foreach ($allKeys as $key) {
                [$source, $stockId] = explode(':', $key, 2);
                if (!$source || !$stockId) continue;
                $diff = ($newMap[$key] ?? 0) - ($oldMap[$key] ?? 0);
                if ($diff == 0) continue;
                $table = $source === 'roasting' ? 'roastings' : 'sortings';
                if ($diff > 0) {
                    \DB::table($table)->where('id', $stockId)->decrement('quantity_in', $diff);
                } else {
                    \DB::table($table)->where('id', $stockId)->increment('quantity_in', abs($diff));
                }
            }
        });

        static::deleted(function ($milling) {
            foreach ($milling->items ?? [] as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                if (!$qty || empty($item['stock_id'])) continue;
                $table = ($item['source'] ?? '') === 'roasting' ? 'roastings' : 'sortings';
                \DB::table($table)->where('id', $item['stock_id'])->increment('quantity_in', $qty);
            }
        });
    }
}
