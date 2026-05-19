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
        'items', // JSON column
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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * `items` is cast to array, so getOriginal('items') may be an array or (from DB) a JSON string.
     *
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

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS - VALIDATION + DEDUCTION
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /*
        |--------------------------------------------------------------------------
        | Before Create → Validate quantities
        |--------------------------------------------------------------------------
        */
        static::creating(function ($milling) {

            $total = 0;

            foreach ($milling->items ?? [] as $item) {

                $qty = floatval($item['quantity'] ?? 0);
                $type = $item['type'] ?? null;
                $stockId = $item['stock_id'] ?? null;

                if (!$qty || !$type || !$stockId) continue;

                // Add quantity to total
                $total += $qty;

                // Get correct stock model
                $batch = in_array($type, ['soy', 'maize'])
                    ? Roasting::find($stockId)
                    : Sorting::find($stockId);

                if (!$batch) {
                    throw new \Exception("Selected batch does not exist.");
                }

                // Validate stock
                if ($batch->quantity_in < $qty) {
                    throw new \Exception("Not enough stock in batch {$batch->batch}. Available: {$batch->quantity_in} kg.");
                }
            }

            // Save totals
            $milling->total_mixed_quantity = $total;
            $milling->output_flour = max($total - ($milling->loss ?? 0), 0);

            // Loss validation
            if ($milling->loss > $total) {
                throw new \Exception("Loss cannot exceed mixed quantity.");
            }
        });

        /*
        |--------------------------------------------------------------------------
        | After Create → Deduct stock
        |--------------------------------------------------------------------------
        */
        static::created(function ($milling) {

            foreach ($milling->items ?? [] as $item) {

                $qty = floatval($item['quantity'] ?? 0);
                $type = $item['type'] ?? null;
                $stockId = $item['stock_id'] ?? null;

                if (!$qty || !$type || !$stockId) continue;

                $batch = in_array($type, ['soy', 'maize'])
                    ? Roasting::find($stockId)
                    : Sorting::find($stockId);

                if ($batch) {
                    $batch->quantity_in -= $qty;
                    if ($batch->quantity_in < 0) $batch->quantity_in = 0;
                    $batch->save();
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Before Update → Recalculate totals
        |--------------------------------------------------------------------------
        */
        static::updating(function ($milling) {

            if (!$milling->isDirty('items') && !$milling->isDirty('loss')) {
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
            $milling->output_flour         = max($total - $loss, 0);
        });

        /*
        |--------------------------------------------------------------------------
        | After Update → Adjust stock deductions if items changed
        |--------------------------------------------------------------------------
        */
        static::updated(function ($milling) {

            if (!$milling->wasChanged('items')) {
                return;
            }

            $oldItems = self::normalizeMillingItems($milling->getOriginal('items'));
            $newItems = self::normalizeMillingItems($milling->items);

            // Build maps: [stock_type:stock_id => qty]
            $buildMap = function (array $items): array {
                $map = [];
                foreach ($items as $item) {
                    $key = ($item['type'] ?? '') . ':' . ($item['stock_id'] ?? '');
                    $map[$key] = ($map[$key] ?? 0) + floatval($item['quantity'] ?? 0);
                }
                return $map;
            };

            $oldMap = $buildMap($oldItems);
            $newMap = $buildMap($newItems);
            $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

            foreach ($allKeys as $key) {
                [$type, $stockId] = explode(':', $key, 2);
                if (!$type || !$stockId) continue;

                $diff = ($newMap[$key] ?? 0) - ($oldMap[$key] ?? 0);
                if ($diff == 0) continue;

                $batch = in_array($type, ['soy', 'maize'])
                    ? Roasting::find($stockId)
                    : Sorting::find($stockId);

                if ($batch) {
                    $batch->quantity_in -= $diff;
                    if ($batch->quantity_in < 0) $batch->quantity_in = 0;
                    $batch->save();
                }
            }
        });
    }
}
