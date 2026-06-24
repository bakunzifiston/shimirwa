<?php

namespace App\Models;

use App\Support\Inventory\MillingItemUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Concerns\ManagesPipelineBatch;

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

    protected $afterCommit = true;

    protected $casts = [
        'date' => 'date',
        'quantity_in' => 'float',
        'quantity_remaining' => 'float',
        'loss' => 'float',
    ];

    protected $fillable = [
        'date',
        'raw_material_stock_id',
        'quantity_in',
        'quantity_remaining',
        'loss',
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

    public function hasDownstreamUsage(): bool
    {
        return MillingItemUsage::sortingReferencedInRoasting($this->id)
            || MillingItemUsage::sortingReferenced($this->id);
    }

    protected static function booted()
    {
        static::creating(function (Sorting $sorting) {
            $gross = (float) $sorting->quantity_in;

            $stock = RawMaterialStock::query()->find($sorting->raw_material_stock_id);
            if (! $stock) {
                throw new \Exception('No matching stock found for this batch.');
            }

            if ($stock->remainingQuantity() < $gross) {
                throw new \Exception('Not enough stock available for this sorting.');
            }

            $sorting->initializePipelineBatchBalances();
        });

        static::created(function (Sorting $sorting) {
            DB::transaction(function () use ($sorting) {
                $stock = RawMaterialStock::query()->lockForUpdate()->find($sorting->raw_material_stock_id);
                if ($stock) {
                    $stock->decrement('quantity_in', (float) $sorting->quantity_in);
                }
            });
        });

        static::updating(function (Sorting $sorting) {
            if ($sorting->hasDownstreamUsage()) {
                foreach (['quantity_in', 'quantity_remaining', 'loss', 'raw_material_stock_id'] as $field) {
                    if ($sorting->isDirty($field)) {
                        throw new \Exception('Cannot change stock quantities: this sorting batch is used in roasting or milling.');
                    }
                }

                return;
            }

            if (! $sorting->isDirty(['quantity_in', 'loss', 'raw_material_stock_id'])) {
                return;
            }

            $oldGross = (float) $sorting->getOriginal('quantity_in');
            $newGross = (float) $sorting->quantity_in;
            $oldStockId = (int) $sorting->getOriginal('raw_material_stock_id');
            $newStockId = (int) $sorting->raw_material_stock_id;

            DB::transaction(function () use ($sorting, $oldGross, $newGross, $oldStockId, $newStockId) {
                RawMaterialStock::query()->lockForUpdate()->find($oldStockId)?->increment('quantity_in', $oldGross);

                $newStock = RawMaterialStock::query()->lockForUpdate()->find($newStockId);
                if (! $newStock) {
                    throw new \Exception('No matching stock found for this batch.');
                }

                if ($newStock->remainingQuantity() < $newGross) {
                    RawMaterialStock::query()->lockForUpdate()->find($oldStockId)?->decrement('quantity_in', $oldGross);
                    throw new \Exception('Not enough stock available for this sorting.');
                }

                $newStock->decrement('quantity_in', $newGross);
                $sorting->refreshPipelineBatchRemaining();
            });
        });

        static::deleting(function (Sorting $sorting) {
            if ($sorting->hasDownstreamUsage()) {
                throw new \Exception('Cannot delete sorting: it is referenced by roasting or milling records.');
            }

            DB::transaction(function () use ($sorting) {
                RawMaterialStock::query()
                    ->lockForUpdate()
                    ->find($sorting->raw_material_stock_id)
                    ?->increment('quantity_in', (float) $sorting->quantity_in);
            });
        });
    }
}
