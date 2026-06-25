<?php

namespace App\Models;

use App\Support\Inventory\MillingItemUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;

/**
 * Sorting production batch.
 *
 * quantity_in        — gross input taken from raw material (kg)
 * quantity_out       — computed usable output (quantity_in - loss)
 * quantity_remaining — balance available for roasting/milling
 *
 * On delete: restores quantity_in to raw_material_stocks.quantity_in
 * (blocked when referenced by roasting or milling).
 */
class Sorting extends Model
{
    use HasFactory;
    use ManagesPipelineBatch;

    protected $casts = [
        'date' => 'date',
        'quantity_in' => 'float',
        'quantity_remaining' => 'float',
        'loss' => 'float',
    ];

    protected $fillable = [
        'date',
        'raw_material_stock_id',
        'quantity_in',           // full gross input taken from stock
        'loss',                  // waste during sorting
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

    // Usable output after loss — used by Milling to check available stock
    public function getQuantityOutAttribute(): float
    {
        return max((float) $this->quantity_in - (float) ($this->loss ?? 0), 0);
    }

    protected static function booted()
    {
        static::creating(function ($sorting) {
            $stock = RawMaterialStock::find($sorting->raw_material_stock_id);

            $stock = RawMaterialStock::query()->find($sorting->raw_material_stock_id);
            if (! $stock) {
                throw new \Exception('No matching stock found for this batch.');
            }

            // Enforce catalog flag: item must be marked as requires_sorting
            $catalogEntry = ProductCatalog::where('name', $stock->item)
                ->where('category', 'production')
                ->first();

            if ($catalogEntry && ! $catalogEntry->requires_sorting) {
                throw new \Exception("\"{$stock->item}\" is not configured for sorting. Enable \"Requires sorting\" in Settings → Product Catalog.");
            }

            if ($stock->quantity_in < $sorting->quantity_in) {
                throw new \Exception('Not enough stock available for this sorting.');
            }

            if (!is_null($sorting->loss) && $sorting->loss > $sorting->quantity_in) {
                throw new \Exception('Loss cannot exceed quantity in.');
            }
        });

        static::created(function ($sorting) {
            DB::table('raw_material_stocks')
                ->where('id', $sorting->raw_material_stock_id)
                ->decrement('quantity_in', $sorting->quantity_in);
        });

        static::deleted(function ($sorting) {
            DB::table('raw_material_stocks')
                ->where('id', $sorting->raw_material_stock_id)
                ->increment('quantity_in', $sorting->quantity_in);
        });
    }
}
