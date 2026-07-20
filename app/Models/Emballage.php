<?php

namespace App\Models;

use App\Support\Inventory\InventoryReferences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Emballage extends Model
{
    use HasFactory;

    protected $casts = [
        'date'               => 'date',
        'expiry_date'        => 'date',
        'milling_overflow'   => 'array',
        'packaging_overflow' => 'array',
    ];

    protected $fillable = [
        'date',
        'packaging_batch_id',
        'milling_id',
        'milling_overflow',        // [{milling_id, quantity}, ...] overflow draws
        'packaging_catalog_id',   // FK → packaging_catalogs
        'raw_material_stock_id',
        'packaging_overflow',      // [{stock_id, units}, ...] extra packaging-material draws
        'inner_stock_id',          // FK → raw_material_stocks (inner units, e.g. bags inside a box)
        'item',
        'packaging_type',          // kept for legacy display; new records use catalog
        'quantity',
        'damaged',
        'unit_price',
        'total_price',
        'expiry_date',
        'batch',
        'comment',
        'employee_id',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function milling()
    {
        return $this->belongsTo(Milling::class);
    }

    public function packagingCatalog()
    {
        return $this->belongsTo(PackagingCatalog::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rawMaterialStock()
    {
        return $this->belongsTo(RawMaterialStock::class);
    }

    public function innerStock()
    {
        return $this->belongsTo(RawMaterialStock::class, 'inner_stock_id');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Resolve kg-per-unit from the catalog record (or fall back to legacy hardcoded types).
     */
    public function resolveKgPerUnit(): float
    {
        if ($this->packaging_catalog_id && $this->packagingCatalog) {
            return (float) $this->packagingCatalog->kg_per_unit;
        }
        // Legacy fallback for old records
        return self::legacyPackagingKg($this->packaging_type ?? '');
    }

    public function isManualWeight(): bool
    {
        if ($this->packaging_catalog_id && $this->packagingCatalog) {
            return (bool) $this->packagingCatalog->manual_weight;
        }
        return strtolower(trim($this->packaging_type ?? '')) === 'sack';
    }

    public static function legacyPackagingKg(string $type): float
    {
        return match (strtolower(trim($type))) {
            'box'  => 12,
            '5kg'  => 5,
            '1kg'  => 1,
            'sack' => 0,
            default => 1,
        };
    }

    /**
     * How many inner units to deduct from inner_stock when this emballage is created.
     * e.g. 10 boxes × 12 bags/box = 120 bags
     */
    public function innerUnitsTotal(): int
    {
        if (!$this->packaging_catalog_id) return 0;
        $catalog = $this->relationLoaded('packagingCatalog')
            ? $this->packagingCatalog
            : PackagingCatalog::find($this->packaging_catalog_id);
        if (!$catalog || !$catalog->hasInnerUnits()) return 0;
        return (int) $this->item * (int) $catalog->inner_units_per_package;
    }

    /**
     * Flour kg drawn from extra milling batches (overflow draws).
     */
    public function millingOverflowKg(): float
    {
        return (float) array_sum(array_column($this->milling_overflow ?? [], 'quantity'));
    }

    /**
     * Flour kg drawn from the primary milling batch (total minus overflow).
     */
    public function primaryFlourKg(): float
    {
        return max((float) ($this->quantity ?? 0) - $this->millingOverflowKg(), 0);
    }

    /**
     * Units drawn from extra packaging-material batches (overflow draws).
     */
    public function packagingOverflowUnits(): float
    {
        return (float) array_sum(array_column($this->packaging_overflow ?? [], 'units'));
    }

    /**
     * Units drawn from the primary packaging-material batch (total minus overflow).
     */
    public function primaryPackagingUnits(): float
    {
        return max((float) ($this->item ?? 0) - $this->packagingOverflowUnits(), 0);
    }

    protected static function failMillingFlourAvailability(string $message): never
    {
        throw ValidationException::withMessages(['milling_id' => $message]);
    }

    protected static function fmtKg(float $kg): string
    {
        $r = round($kg, 4);
        return abs($r - round($r)) < 0.0001
            ? (string) (int) round($r)
            : rtrim(rtrim(number_format($r, 3, '.', ''), '0'), '.');
    }

    // -----------------------------------------------------------------------
    // Model Events
    // -----------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        // ---- CREATE ----
        static::creating(function (Emballage $emballage) {
            // Eager-load catalog for this event
            if ($emballage->packaging_catalog_id) {
                $emballage->setRelation('packagingCatalog', PackagingCatalog::find($emballage->packaging_catalog_id));
            }

            $item    = (float) ($emballage->item ?? 0);
            $isManual = $emballage->isManualWeight();
            $kgPerUnit = $emballage->resolveKgPerUnit();

            if (!$isManual) {
                $emballage->quantity = $item * $kgPerUnit;
            }

            if ($emballage->milling_id && !$emballage->batch) {
                $milling = Milling::find($emballage->milling_id);
                if ($milling) $emballage->batch = $milling->batch_number;
            }

            // quantity holds the TOTAL flour; the primary batch supplies total minus overflow
            $primaryQty = round($emballage->primaryFlourKg(), 4);

            // Validate primary batch has enough for its share
            if ($primaryQty > 0 && $emballage->milling_id) {
                $milling = Milling::find($emballage->milling_id);
                if ($milling && (float) $milling->output_flour < $primaryQty) {
                    $avail = (float) $milling->output_flour;
                    $msg = sprintf('Milling batch %s has %s kg available but needs %s kg.',
                        $milling->batch_number, self::fmtKg($avail), self::fmtKg($primaryQty));
                    self::failMillingFlourAvailability($msg);
                }
            }

            $emballage->quantity = (float) ($emballage->quantity ?? 0);
        });

        static::created(function (Emballage $emballage) {
            // Deduct packaging materials (outer: e.g. boxes) — primary batch gets
            // its share (total units minus overflow draws)
            $primaryUnits = $emballage->primaryPackagingUnits();
            if ($emballage->raw_material_stock_id && $primaryUnits > 0) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->raw_material_stock_id)
                    ->decrement('quantity_in', $primaryUnits);
            }
            // Deduct overflow packaging-material batches
            foreach ($emballage->packaging_overflow ?? [] as $ov) {
                if (!empty($ov['stock_id']) && (float) ($ov['units'] ?? 0) > 0) {
                    \DB::table('raw_material_stocks')
                        ->where('id', $ov['stock_id'])
                        ->decrement('quantity_in', (float) $ov['units']);
                }
            }
            // Deduct inner units (e.g. bags inside boxes)
            $innerUnits = $emballage->innerUnitsTotal();
            if ($innerUnits > 0 && $emballage->inner_stock_id) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->inner_stock_id)
                    ->decrement('quantity_in', $innerUnits);
            }
            // Deduct flour from primary milling batch (its share = total minus overflow)
            $primaryFlour = $emballage->primaryFlourKg();
            if ($emballage->milling_id && $primaryFlour > 0) {
                \DB::table('millings')
                    ->where('id', $emballage->milling_id)
                    ->decrement('output_flour', $primaryFlour);
            }

            // Deduct flour from overflow milling batches
            foreach ($emballage->milling_overflow ?? [] as $ov) {
                if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                    \DB::table('millings')
                        ->where('id', $ov['milling_id'])
                        ->decrement('output_flour', (float) $ov['quantity']);
                }
            }
        });

        // ---- UPDATE ----
        static::updating(function (Emballage $emballage) {
            if ($emballage->packaging_catalog_id) {
                $emballage->setRelation('packagingCatalog', PackagingCatalog::find($emballage->packaging_catalog_id));
            }

            $item      = (float) ($emballage->item ?? 0);
            $isManual  = $emballage->isManualWeight();
            $kgPerUnit = $emballage->resolveKgPerUnit();

            if (!$isManual) {
                $emballage->quantity = $item * $kgPerUnit;
            }

            $oldItem = (float) $emballage->getOriginal('item');
            $oldQty  = (float) $emballage->getOriginal('quantity');
            $newQty  = (float) $emballage->quantity;

            $oldMillingId = $emballage->getOriginal('milling_id');
            $newMillingId = $emballage->milling_id;

            // Flour: quantities are TOTALS; the primary batch's share = total minus overflow
            $oldOvFlour = $emballage->getOriginal('milling_overflow') ?? [];
            if (is_string($oldOvFlour)) $oldOvFlour = json_decode($oldOvFlour, true) ?? [];
            $oldOvKg      = (float) array_sum(array_column($oldOvFlour, 'quantity'));
            $oldPrimaryKg = max($oldQty - $oldOvKg, 0);
            $newPrimaryKg = round($emballage->primaryFlourKg(), 4);

            // Availability check for the new primary share (add back what this record already holds there)
            if ($newPrimaryKg > 0 && $newMillingId) {
                $m = Milling::find($newMillingId);
                if ($m) {
                    $avail = (float) $m->output_flour;
                    if ($oldMillingId == $newMillingId) {
                        $avail += $oldPrimaryKg;
                    }
                    foreach ($oldOvFlour as $ov) {
                        if (($ov['milling_id'] ?? null) == $newMillingId) {
                            $avail += (float) ($ov['quantity'] ?? 0);
                        }
                    }
                    if ($newPrimaryKg > $avail) {
                        self::failMillingFlourAvailability(sprintf(
                            'Milling batch %s only has %s kg available (needs %s kg).',
                            $m->batch_number, self::fmtKg($avail), self::fmtKg($newPrimaryKg)
                        ));
                    }
                }
            }

            // Adjust packaging material stocks (outer): restore all old draws, apply new
            $outerDirty = $emballage->isDirty('raw_material_stock_id')
                || $emballage->isDirty('item')
                || $emballage->isDirty('packaging_overflow');
            if ($outerDirty) {
                $oldPkgOverflow = $emballage->getOriginal('packaging_overflow') ?? [];
                if (is_string($oldPkgOverflow)) $oldPkgOverflow = json_decode($oldPkgOverflow, true) ?? [];
                $oldOvUnits = (float) array_sum(array_column($oldPkgOverflow, 'units'));
                $oldPrimary = max($oldItem - $oldOvUnits, 0);
                $oldStockId = $emballage->getOriginal('raw_material_stock_id');

                if ($oldStockId && $oldPrimary > 0) {
                    \DB::table('raw_material_stocks')->where('id', $oldStockId)->increment('quantity_in', $oldPrimary);
                }
                foreach ($oldPkgOverflow as $ov) {
                    if (!empty($ov['stock_id']) && (float) ($ov['units'] ?? 0) > 0) {
                        \DB::table('raw_material_stocks')->where('id', $ov['stock_id'])->increment('quantity_in', (float) $ov['units']);
                    }
                }

                $newPrimary = $emballage->primaryPackagingUnits();
                if ($emballage->raw_material_stock_id && $newPrimary > 0) {
                    \DB::table('raw_material_stocks')->where('id', $emballage->raw_material_stock_id)->decrement('quantity_in', $newPrimary);
                }
                foreach ($emballage->packaging_overflow ?? [] as $ov) {
                    if (!empty($ov['stock_id']) && (float) ($ov['units'] ?? 0) > 0) {
                        \DB::table('raw_material_stocks')->where('id', $ov['stock_id'])->decrement('quantity_in', (float) $ov['units']);
                    }
                }
            }

            // Adjust inner units when item count or inner_stock_id changes
            $oldInnerStockId = $emballage->getOriginal('inner_stock_id');
            $newInnerStockId = $emballage->inner_stock_id;
            $oldItem         = (float) $emballage->getOriginal('item');
            $innerUnitsOld   = 0;
            $innerUnitsNew   = 0;

            // Compute old inner units
            if ($oldInnerStockId) {
                $oldCatalogId = $emballage->getOriginal('packaging_catalog_id');
                $oldCatalog   = PackagingCatalog::find($oldCatalogId);
                if ($oldCatalog && $oldCatalog->hasInnerUnits()) {
                    $innerUnitsOld = (int) $oldItem * (int) $oldCatalog->inner_units_per_package;
                }
            }
            // Compute new inner units
            if ($newInnerStockId) {
                $innerUnitsNew = $emballage->innerUnitsTotal();
            }

            if ($emballage->isDirty('inner_stock_id') && $oldInnerStockId !== $newInnerStockId) {
                // Stock changed — restore old, deduct new
                if ($oldInnerStockId && $innerUnitsOld > 0) {
                    \DB::table('raw_material_stocks')->where('id', $oldInnerStockId)->increment('quantity_in', $innerUnitsOld);
                }
                if ($newInnerStockId && $innerUnitsNew > 0) {
                    \DB::table('raw_material_stocks')->where('id', $newInnerStockId)->decrement('quantity_in', $innerUnitsNew);
                }
            } elseif ($newInnerStockId && $innerUnitsNew !== $innerUnitsOld) {
                // Same stock, count changed
                $diff = $innerUnitsNew - $innerUnitsOld;
                if ($diff != 0) {
                    \DB::table('raw_material_stocks')->where('id', $newInnerStockId)->decrement('quantity_in', $diff);
                }
            }
            // Adjust flour — restore all old draws, apply new (primary share + overflow)
            $flourDirty = $emballage->isDirty('milling_id')
                || $emballage->isDirty('quantity')
                || $emballage->isDirty('milling_overflow');
            if ($flourDirty) {
                if ($oldMillingId && $oldPrimaryKg > 0) {
                    \DB::table('millings')->where('id', $oldMillingId)->increment('output_flour', $oldPrimaryKg);
                }
                foreach ($oldOvFlour as $ov) {
                    if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                        \DB::table('millings')->where('id', $ov['milling_id'])->increment('output_flour', (float) $ov['quantity']);
                    }
                }
                if ($newMillingId && $newPrimaryKg > 0) {
                    \DB::table('millings')->where('id', $newMillingId)->decrement('output_flour', $newPrimaryKg);
                }
                foreach ($emballage->milling_overflow ?? [] as $ov) {
                    if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                        \DB::table('millings')->where('id', $ov['milling_id'])->decrement('output_flour', (float) $ov['quantity']);
                    }
                }
                if ($emballage->isDirty('milling_id') && $emballage->milling) {
                    $emballage->batch = $emballage->milling->batch_number;
                }
            }
        });

        // ---- DELETE ----
        static::deleted(function (Emballage $emballage) {
            // Restore outer packaging material — primary batch share
            $primaryUnits = $emballage->primaryPackagingUnits();
            if ($emballage->raw_material_stock_id && $primaryUnits > 0) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->raw_material_stock_id)
                    ->increment('quantity_in', $primaryUnits);
            }
            // Restore overflow packaging-material batches
            foreach ($emballage->packaging_overflow ?? [] as $ov) {
                if (!empty($ov['stock_id']) && (float) ($ov['units'] ?? 0) > 0) {
                    \DB::table('raw_material_stocks')
                        ->where('id', $ov['stock_id'])
                        ->increment('quantity_in', (float) $ov['units']);
                }
            }
            // Restore inner units (e.g. bags that were inside boxes)
            $innerUnits = $emballage->innerUnitsTotal();
            if ($innerUnits > 0 && $emballage->inner_stock_id) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->inner_stock_id)
                    ->increment('quantity_in', $innerUnits);
            }
            // Restore flour to primary milling batch (its share = total minus overflow)
            $primaryFlour = $emballage->primaryFlourKg();
            if ($emballage->milling_id && $primaryFlour > 0) {
                \DB::table('millings')
                    ->where('id', $emballage->milling_id)
                    ->increment('output_flour', $primaryFlour);
            }

            // Restore flour to overflow milling batches
            foreach ($emballage->milling_overflow ?? [] as $ov) {
                if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                    \DB::table('millings')
                        ->where('id', $ov['milling_id'])
                        ->increment('output_flour', (float) $ov['quantity']);
                }
            }
        });
    }
}
