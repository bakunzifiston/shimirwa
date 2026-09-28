<?php

namespace App\Models;

use App\Models\Concerns\ManagesPipelineBatch;
use App\Support\Inventory\MillingItemUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;
use App\Models\Sorting;

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

    protected $casts = [
        'date' => 'date',
        'quantity_in' => 'float',
        'quantity_remaining' => 'float',
        'loss' => 'float',
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

    public function hasDownstreamUsage(): bool
    {
        return Milling::whereJsonContains('items', [
            'source'   => 'roasting',
            'stock_id' => $this->id,
        ])->exists();
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

            // Check against the available quantity — remainingUsable() for Sorting (what's
            // actually still left, consistent with how Milling also draws from Sorting),
            // quantity_in for RawMaterialStock (its own live balance).
            $available = $stock instanceof Sorting ? $stock->remainingUsable() : $stock->quantity_in;

            if ($available < $roasting->quantity_in) {
                throw new \Exception('Not enough stock available for this roasting.');
            }

            if (!is_null($roasting->loss) && $roasting->loss > $roasting->quantity_in) {
                throw new \Exception('Loss cannot exceed quantity in.');
            }

            $roasting->initializePipelineBatchBalances();
        });

        static::updating(function ($roasting) {
            if ($roasting->isDirty('quantity_in') || $roasting->isDirty('loss')) {
                $roasting->refreshPipelineBatchRemaining();
            }
        });

        static::created(function ($roasting) {
            if ($roasting->raw_material_stock_id) {
                DB::table('raw_material_stocks')
                    ->where('id', $roasting->raw_material_stock_id)
                    ->decrement('quantity_in', $roasting->quantity_in);
            } elseif ($roasting->sorting_id) {
                self::drawFromSorting($roasting->sorting_id, $roasting->quantity_in);
            }
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
            // $delta > 0 here means "restore this much" (e.g. roasting deleted),
            // i.e. the inverse of drawing it.
            self::drawFromSorting($sortingId, -$delta);
        }
    }

    /**
     * Draw from (positive $qty) or restore to (negative $qty) a Sorting
     * batch's remaining balance via quantity_remaining — the same mechanism
     * Milling uses when it sources from a Sorting batch, so both consumers
     * of a Sorting batch stay consistent with each other.
     */
    private static function drawFromSorting(int $sortingId, float $qty): void
    {
        Sorting::withoutEvents(function () use ($sortingId, $qty) {
            $sorting = Sorting::query()->lockForUpdate()->find($sortingId);
            if (! $sorting) {
                return;
            }
            $sorting->quantity_remaining = max($sorting->remainingUsable() - $qty, 0);
            $sorting->save();
        });
    }
}
