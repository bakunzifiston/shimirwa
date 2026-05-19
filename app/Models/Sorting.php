<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterialStock;
use App\Models\Employee;

class Sorting extends Model
{
    use HasFactory;

    // Ensure events are fired after database commit
    protected $afterCommit = true;

    protected $casts = [
        'date' => 'date',
    ];

    protected $fillable = [
        'date',
        'raw_material_stock_id', // FK to stock batch
        'quantity_in',           // usable quantity after loss
        'loss',                  // waste/loss during sorting
        'employee_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function rawMaterialStock()
    {
        return $this->belongsTo(RawMaterialStock::class, 'raw_material_stock_id');
    }

    protected static function booted()
    {
        // Before creating: check stock availability and adjust stored quantity
        static::creating(function ($sorting) {
            $stock = RawMaterialStock::find($sorting->raw_material_stock_id);

            if (!$stock) {
                throw new \Exception('No matching stock found for this batch.');
            }

            // Check stock against full input amount (before loss)
            $fullInput = $sorting->quantity_in;
            if ($stock->quantity_in < $fullInput) {
                throw new \Exception('Not enough stock available for this sorting.');
            }

            // Adjust quantity_in to store usable amount (input - loss)
            if (!is_null($sorting->loss) && $sorting->loss > 0) {
                if ($sorting->loss > $fullInput) {
                    throw new \Exception('Loss cannot exceed quantity in.');
                }
                $sorting->quantity_in = $fullInput - $sorting->loss;
            }
        });

        // After creating: always deduct full input amount from stock
        static::created(function ($sorting) {
            $fullInput = $sorting->quantity_in + $sorting->loss;

            $stock = RawMaterialStock::find($sorting->raw_material_stock_id);
            if ($stock) {
                $stock->quantity_in -= $fullInput;
                $stock->save();
            }
        });
    }
}
