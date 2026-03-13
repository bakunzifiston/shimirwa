<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterialStock;
use App\Models\Sorting;
use App\Models\Employee;

class Roasting extends Model
{
    use HasFactory;

    protected $afterCommit = true;

    protected $fillable = [
        'date',
        'quantity_in',       // total quantity taken from stock
        'loss',              // how much is wasted
        'batch',
        'chef_id',
        'supervisor_id',
        'raw_material_stock_id',
        'sorting_id',
    ];

    public function chef()
    {
        return $this->belongsTo(Employee::class, 'chef_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function rawMaterialStock()
    {
        return $this->belongsTo(RawMaterialStock::class, 'raw_material_stock_id');
    }

    public function sorting()
    {
        return $this->belongsTo(Sorting::class, 'sorting_id');
    }

    protected static function booted()
    {
        // Before creating: check stock availability and adjust stored quantity
        static::creating(function ($roasting) {
            // Identify source stock (raw material or sorting)
            if ($roasting->raw_material_stock_id) {
                $stock = RawMaterialStock::find($roasting->raw_material_stock_id);
            } elseif ($roasting->sorting_id) {
                $stock = Sorting::find($roasting->sorting_id);
            } else {
                throw new \Exception('Roasting must have a source stock (raw material or sorting).');
            }

            if (!$stock) {
                throw new \Exception('No matching stock found.');
            }

            // Check if enough stock exists to take full quantity_in
            if ($stock->quantity_in < $roasting->quantity_in) {
                throw new \Exception('Not enough stock available for this roasting.');
            }

            // Store usable quantity (quantity_in - loss)
            if (!is_null($roasting->loss) && $roasting->loss > 0) {
                if ($roasting->loss > $roasting->quantity_in) {
                    throw new \Exception('Loss cannot exceed quantity in.');
                }
                // overwrite quantity_in with usable amount
                $roasting->quantity_in = $roasting->quantity_in - $roasting->loss;
            }
        });

        // After creating: always deduct full input quantity (before loss) from stock
        static::created(function ($roasting) {
            $takenAmount = $roasting->quantity_in + $roasting->loss; // full quantity taken from stock

            if ($roasting->raw_material_stock_id) {
                $stock = RawMaterialStock::find($roasting->raw_material_stock_id);
            } elseif ($roasting->sorting_id) {
                $stock = Sorting::find($roasting->sorting_id);
            }

            if ($stock) {
                $stock->quantity_in -= $takenAmount;
                $stock->save();
            }
        });
    }
}
