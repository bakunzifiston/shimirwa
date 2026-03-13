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
    }
}
