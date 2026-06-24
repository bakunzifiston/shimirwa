<?php

namespace App\Models;

use App\Support\Inventory\MillingItemUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Concerns\ManagesPipelineBatch;

/**
 * Roasting production batch.
 *
 * On delete: restores quantity_in to the source batch
 * (raw_material_stocks.quantity_in or sortings.quantity_remaining).
 * Blocked when referenced by milling.
 */
class Roasting extends Model
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
        'quantity_in',
        'quantity_remaining',
        'loss',
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

    public function hasDownstreamUsage(): bool
    {
        return MillingItemUsage::roastingReferenced($this->id);
    }

    protected static function booted()
    {
        static::creating(function (Roasting $roasting) {
            $gross = (float) $roasting->quantity_in;
            $stock = self::findSource($roasting->raw_material_stock_id, $roasting->sorting_id);
            $available = $roasting->sorting_id
                ? $stock->remainingUsable()
                : $stock->remainingQuantity();

            if ($available < $gross) {
                throw new \Exception('Not enough stock available for this roasting.');
            }

            $roasting->initializePipelineBatchBalances();
        });

        static::created(function (Roasting $roasting) {
            DB::transaction(function () use ($roasting) {
                self::adjustSourceStock(
                    $roasting->raw_material_stock_id,
                    $roasting->sorting_id,
                    -(float) $roasting->quantity_in
                );
            });
        });

        static::updating(function (Roasting $roasting) {
            if ($roasting->hasDownstreamUsage()) {
                foreach (['quantity_in', 'quantity_remaining', 'loss', 'raw_material_stock_id', 'sorting_id'] as $field) {
                    if ($roasting->isDirty($field)) {
                        throw new \Exception('Cannot change stock quantities: this roasting batch is used in milling.');
                    }
                }

                return;
            }

            if (! $roasting->isDirty(['quantity_in', 'loss', 'raw_material_stock_id', 'sorting_id'])) {
                return;
            }

            $oldGross = (float) $roasting->getOriginal('quantity_in');
            $newGross = (float) $roasting->quantity_in;

            DB::transaction(function () use ($roasting, $oldGross, $newGross) {
                self::adjustSourceStock(
                    $roasting->getOriginal('raw_material_stock_id'),
                    $roasting->getOriginal('sorting_id'),
                    $oldGross
                );

                $stock = self::findSource($roasting->raw_material_stock_id, $roasting->sorting_id, true);
                $available = $roasting->sorting_id
                    ? $stock->remainingUsable()
                    : $stock->remainingQuantity();

                if ($available < $newGross) {
                    self::adjustSourceStock(
                        $roasting->getOriginal('raw_material_stock_id'),
                        $roasting->getOriginal('sorting_id'),
                        -$oldGross
                    );
                    throw new \Exception('Not enough stock available for this roasting.');
                }

                self::adjustSourceStock(
                    $roasting->raw_material_stock_id,
                    $roasting->sorting_id,
                    -$newGross
                );

                $roasting->refreshPipelineBatchRemaining();
            });
        });

        static::deleting(function (Roasting $roasting) {
            if ($roasting->hasDownstreamUsage()) {
                throw new \Exception('Cannot delete roasting: it is referenced by milling records.');
            }

            DB::transaction(function () use ($roasting) {
                self::restoreSourceStock($roasting);
            });
        });
    }

    private static function restoreSourceStock(Roasting $roasting): void
    {
        self::adjustSourceStock(
            $roasting->raw_material_stock_id,
            $roasting->sorting_id,
            (float) $roasting->quantity_in
        );
    }

    private static function findSource(?int $rawMaterialStockId, ?int $sortingId, bool $lock = false): RawMaterialStock|Sorting
    {
        if ($rawMaterialStockId) {
            $query = RawMaterialStock::query();
            $stock = $lock ? $query->lockForUpdate()->find($rawMaterialStockId) : $query->find($rawMaterialStockId);
        } elseif ($sortingId) {
            $query = Sorting::query();
            $stock = $lock ? $query->lockForUpdate()->find($sortingId) : $query->find($sortingId);
        } else {
            throw new \Exception('Roasting must have a source stock (raw material or sorting).');
        }

        if (! $stock) {
            throw new \Exception('No matching stock found.');
        }

        return $stock;
    }

    private static function adjustSourceStock(?int $rawMaterialStockId, ?int $sortingId, float $delta): void
    {
        if ($rawMaterialStockId) {
            RawMaterialStock::query()->lockForUpdate()->find($rawMaterialStockId)?->increment('quantity_in', $delta);

            return;
        }

        if ($sortingId) {
            Sorting::query()->lockForUpdate()->find($sortingId)?->increment('quantity_remaining', $delta);
        }
    }
}
