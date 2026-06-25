<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;
use App\Models\Sorting;

class Roasting extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
    ];

    protected $fillable = [
        'date',
        'quantity_in',       // full gross input taken from stock
        'loss',              // waste during roasting
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

    // Usable output after loss — used by Milling to check available stock
    public function getQuantityOutAttribute(): float
    {
        return max((float) $this->quantity_in - (float) ($this->loss ?? 0), 0);
    }

    protected static function booted()
    {
        static::creating(function ($roasting) {
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

            // Resolve the item name: raw stock has 'item', sorting goes through its rawMaterialStock
            $itemName = $stock instanceof Sorting
                ? $stock->rawMaterialStock?->item
                : $stock->item;

            // Enforce catalog flag: item must be marked as requires_roasting
            if ($itemName) {
                $catalogEntry = ProductCatalog::where('name', $itemName)
                    ->where('category', 'production')
                    ->first();

                if ($catalogEntry && ! $catalogEntry->requires_roasting) {
                    throw new \Exception("\"{$itemName}\" is not configured for roasting. Enable \"Requires roasting\" in Settings → Product Catalog.");
                }
            }

            // Check against the available quantity (quantity_out for Sorting, quantity_in for RawMaterialStock)
            $available = $stock instanceof Sorting ? $stock->quantity_out : $stock->quantity_in;

            if ($available < $roasting->quantity_in) {
                throw new \Exception('Not enough stock available for this roasting.');
            }

            if (!is_null($roasting->loss) && $roasting->loss > $roasting->quantity_in) {
                throw new \Exception('Loss cannot exceed quantity in.');
            }
        });

        static::created(function ($roasting) {
            if ($roasting->raw_material_stock_id) {
                DB::table('raw_material_stocks')
                    ->where('id', $roasting->raw_material_stock_id)
                    ->decrement('quantity_in', $roasting->quantity_in);
            } elseif ($roasting->sorting_id) {
                DB::table('sortings')
                    ->where('id', $roasting->sorting_id)
                    ->decrement('quantity_in', $roasting->quantity_in);
            }
        });

        static::deleted(function ($roasting) {
            if ($roasting->raw_material_stock_id) {
                DB::table('raw_material_stocks')
                    ->where('id', $roasting->raw_material_stock_id)
                    ->increment('quantity_in', $roasting->quantity_in);
            } elseif ($roasting->sorting_id) {
                DB::table('sortings')
                    ->where('id', $roasting->sorting_id)
                    ->increment('quantity_in', $roasting->quantity_in);
            }
        });
    }
}
