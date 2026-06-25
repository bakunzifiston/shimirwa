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
        'date'             => 'date',
        'expiry_date'      => 'date',
        'milling_overflow' => 'array',
    ];

    protected $fillable = [
        'date',
        'packaging_batch_id',
        'milling_id',
        'milling_overflow',        // [{milling_id, quantity}, ...] overflow draws
        'packaging_catalog_id',   // FK → packaging_catalogs
        'raw_material_stock_id',
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

            $overflow   = $emballage->milling_overflow ?? [];
            $ovTotal    = array_sum(array_column($overflow, 'quantity'));
            $primaryQty = max(round((float) ($emballage->quantity ?? 0) - $ovTotal, 4), 0);

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

            // Store the adjusted primary quantity (total minus overflow)
            $emballage->quantity = (float) ($emballage->quantity ?? 0);
        });

        static::created(function (Emballage $emballage) {
            $item = (float) ($emballage->item ?? 0);

            // Deduct packaging materials
            if ($emballage->rawMaterialStock) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->raw_material_stock_id)
                    ->decrement('quantity_in', $item);
            }
            // Deduct flour from primary milling batch
            if ($emballage->milling_id && (float) $emballage->quantity > 0) {
                \DB::table('millings')
                    ->where('id', $emballage->milling_id)
                    ->decrement('output_flour', $emballage->quantity);
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
            $diffItem = $item - $oldItem;

            $oldMillingId = $emballage->getOriginal('milling_id');
            $newMillingId = $emballage->milling_id;

            // Flour availability check
            if ($newQty > 0 && $newMillingId) {
                if ($emballage->isDirty('milling_id')) {
                    $next = Milling::find($newMillingId);
                    if ($next && (float) $next->output_flour < $newQty) {
                        self::failMillingFlourAvailability(sprintf(
                            'Milling batch %s has %s kg available but needs %s kg.',
                            $next->batch_number, self::fmtKg((float) $next->output_flour), self::fmtKg($newQty)
                        ));
                    }
                } else {
                    $diffQty = $newQty - $oldQty;
                    if ($diffQty > 0) {
                        $m = Milling::find($newMillingId);
                        if ($m && (float) $m->output_flour < $diffQty) {
                            self::failMillingFlourAvailability(sprintf(
                                'Milling batch %s has only %s kg free; you need %s kg more.',
                                $m->batch_number, self::fmtKg((float) $m->output_flour), self::fmtKg($diffQty)
                            ));
                        }
                    }
                }
            }

            // Adjust packaging material stocks
            if ($emballage->raw_material_stock_id && $diffItem != 0) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->raw_material_stock_id)
                    ->decrement('quantity_in', $diffItem);
            }
            // Adjust flour — primary batch
            if ($emballage->isDirty('milling_id')) {
                if ($oldMillingId && $oldQty > 0) {
                    \DB::table('millings')->where('id', $oldMillingId)->increment('output_flour', $oldQty);
                }
                if ($newMillingId && $newQty > 0) {
                    \DB::table('millings')->where('id', $newMillingId)->decrement('output_flour', $newQty);
                }
                if ($emballage->milling) {
                    $emballage->batch = $emballage->milling->batch_number;
                }
            } else {
                $diffQty = $newQty - $oldQty;
                if ($diffQty != 0 && $newMillingId) {
                    \DB::table('millings')->where('id', $newMillingId)->decrement('output_flour', $diffQty);
                }
            }

            // Adjust flour — overflow batches: restore old, deduct new
            if ($emballage->isDirty('milling_overflow')) {
                $oldOverflow = $emballage->getOriginal('milling_overflow') ?? [];
                if (is_string($oldOverflow)) $oldOverflow = json_decode($oldOverflow, true) ?? [];
                foreach ($oldOverflow as $ov) {
                    if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                        \DB::table('millings')->where('id', $ov['milling_id'])->increment('output_flour', (float) $ov['quantity']);
                    }
                }
                foreach ($emballage->milling_overflow ?? [] as $ov) {
                    if (!empty($ov['milling_id']) && (float) ($ov['quantity'] ?? 0) > 0) {
                        \DB::table('millings')->where('id', $ov['milling_id'])->decrement('output_flour', (float) $ov['quantity']);
                    }
                }
            }
        });

        // ---- DELETE ----
        static::deleted(function (Emballage $emballage) {
            $item = (float) ($emballage->item ?? 0);

            if ($emballage->raw_material_stock_id && $item > 0) {
                \DB::table('raw_material_stocks')
                    ->where('id', $emballage->raw_material_stock_id)
                    ->increment('quantity_in', $item);
            }
            // Restore flour to primary milling batch
            if ($emballage->milling_id && (float) $emballage->quantity > 0) {
                \DB::table('millings')
                    ->where('id', $emballage->milling_id)
                    ->increment('output_flour', $emballage->quantity);
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
