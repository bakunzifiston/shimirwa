<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Emballage extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $fillable = [
        'date',
        'packaging_batch_id',
        'milling_id',
        'raw_material_stock_id',  // box stock / 1kg envelope stock / sack stock
        'envelope_stock_id',      // only for Box: the 1kg envelope batch to deduct from
        'item',                   // number of units (boxes / envelopes / sacks)
        'packaging_type',         // 'box' | '1kg' | 'sack'
        'quantity',               // total flour kg = item × packagingKg()
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rawMaterialStock()
    {
        return $this->belongsTo(RawMaterialStock::class);
    }

    public function envelopeStock()
    {
        return $this->belongsTo(RawMaterialStock::class, 'envelope_stock_id');
    }

    // -----------------------------------------------------------------------
    // Helper: flour kg per packaging unit
    // -----------------------------------------------------------------------

    public static function packagingKg(string $type): float
    {
        return match (strtolower(trim($type))) {
            'box'  => 12,
            '5kg'  => 5,
            '1kg'  => 1,
            'sack' => 0,    // flexible: user enters weight manually
            default => 1,
        };
    }

    /**
     * Filament resource forms use state path `data`, so errors attach to the milling field in the UI.
     */
    protected static function failMillingFlourAvailability(string $message): never
    {
        throw ValidationException::withMessages([
            'milling_id' => $message,
        ]);
    }

    protected static function formatKg(float $kg): string
    {
        $rounded = round($kg, 4);

        return abs($rounded - round($rounded)) < 0.0001
            ? (string) (int) round($rounded)
            : rtrim(rtrim(number_format($rounded, 2, '.', ''), '0'), '.');
    }

    protected static function formatCount(float $n): string
    {
        return (string) (int) $n;
    }

    // -----------------------------------------------------------------------
    // Model Events
    // -----------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        // ------------------- CREATE -------------------
        static::creating(function ($emballage) {
            $item = (float) ($emballage->item ?? 0);
            $type = strtolower(trim($emballage->packaging_type ?? '1kg'));

            // Sack: user enters quantity (kg) manually — do not overwrite
            if ($type !== 'sack') {
                $emballage->quantity = $item * self::packagingKg($type);
            }

            // Set batch from milling
            if ($emballage->milling) {
                $emballage->batch = $emballage->milling->batch_number;
            }

            // Milling (flour): must have enough output_flour for this packaging
            $kgNeeded = (float) ($emballage->quantity ?? 0);
            if ($kgNeeded > 0 && $emballage->milling_id) {
                $milling = Milling::query()->find($emballage->milling_id);
                if ($milling && (float) $milling->output_flour < $kgNeeded) {
                    $available = (float) $milling->output_flour;
                    $batch = (string) $milling->batch_number;
                    $per = self::packagingKg($type);

                    $message = $per > 0
                        ? sprintf(
                            'Milling batch %s has %s kg of flour available. This packaging needs %s kg (%s units × %s kg each). Use fewer units or choose another batch.',
                            $batch,
                            self::formatKg($available),
                            self::formatKg($kgNeeded),
                            self::formatCount($item),
                            self::formatKg($per),
                        )
                        : sprintf(
                            'Milling batch %s has %s kg of flour available, but this packaging needs %s kg. Reduce the total flour or choose another batch.',
                            $batch,
                            self::formatKg($available),
                            self::formatKg($kgNeeded),
                        );

                    self::failMillingFlourAvailability($message);
                }
            }
        });

        static::created(function ($emballage) {
            $item = (float) ($emballage->item ?? 0);
            $type = strtolower(trim($emballage->packaging_type ?? '1kg'));

            // Deduct packaging materials from raw material stocks
            if ($type === 'box') {
                // Deduct box units from box stock
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->decrement('quantity_in', $item);
                }
                // Deduct 12 envelopes per box from envelope stock
                if ($emballage->envelopeStock) {
                    $emballage->envelopeStock->decrement('quantity_in', $item * 12);
                }
            } else {
                // 1kg -> deduct envelopes; sack -> deduct sack bags
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->decrement('quantity_in', $item);
                }
            }

            // Deduct flour (kg) from milling output (validated in creating)
            if ($emballage->milling && (float) $emballage->quantity > 0) {
                $emballage->milling->decrement('output_flour', $emballage->quantity);
            }
        });

        // ------------------- UPDATE -------------------
        static::updating(function ($emballage) {
            $item = (float) ($emballage->item ?? 0);
            $type = strtolower(trim($emballage->packaging_type ?? '1kg'));

            // Sack: user manages quantity manually — do not overwrite
            if ($type !== 'sack') {
                $emballage->quantity = $item * self::packagingKg($type);
            }

            $oldItem = (float) $emballage->getOriginal('item');
            $oldType = strtolower(trim($emballage->getOriginal('packaging_type') ?? '1kg'));
            $oldQty  = $oldType === 'sack'
                ? (float) $emballage->getOriginal('quantity')
                : $oldItem * self::packagingKg($oldType);
            $diffItem = $item - $oldItem;

            // Milling flour: block save if the selected batch cannot cover the change
            $oldMillingId = $emballage->getOriginal('milling_id');
            $newMillingId = $emballage->milling_id;
            $newQty = (float) $emballage->quantity;

            if ($newQty > 0 && $newMillingId) {
                if ($emballage->isDirty('milling_id')) {
                    $nextMilling = Milling::query()->find($newMillingId);
                    if ($nextMilling && (float) $nextMilling->output_flour < $newQty) {
                        $message = sprintf(
                            'Milling batch %s has %s kg available, but this line needs %s kg. Choose a batch with enough flour or reduce packaging.',
                            (string) $nextMilling->batch_number,
                            self::formatKg((float) $nextMilling->output_flour),
                            self::formatKg($newQty),
                        );
                        self::failMillingFlourAvailability($message);
                    }
                } else {
                    $diffQty = $newQty - $oldQty;
                    if ($diffQty > 0) {
                        $m = Milling::query()->find($emballage->milling_id);
                        if ($m && (float) $m->output_flour < $diffQty) {
                            $message = sprintf(
                                'Milling batch %s has %s kg free for this change, but you need %s kg more flour. Reduce units or pick another batch.',
                                (string) $m->batch_number,
                                self::formatKg((float) $m->output_flour),
                                self::formatKg($diffQty),
                            );
                            self::failMillingFlourAvailability($message);
                        }
                    }
                }
            }

            // Adjust raw material stocks
            if ($type === 'box') {
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->decrement('quantity_in', $diffItem);
                }
                if ($emballage->envelopeStock) {
                    $emballage->envelopeStock->decrement('quantity_in', $diffItem * 12);
                }
            } else {
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->decrement('quantity_in', $diffItem);
                }
            }

            // Adjust milling flour: follow the selected milling batch
            if ($emballage->isDirty('milling_id')) {
                if ($oldMillingId) {
                    $previousMilling = Milling::query()->find($oldMillingId);
                    if ($previousMilling && $oldQty > 0) {
                        $previousMilling->increment('output_flour', $oldQty);
                    }
                }
                if ($newMillingId) {
                    $nextMilling = Milling::query()->find($newMillingId);
                    if ($nextMilling && (float) $emballage->quantity > 0) {
                        $nextMilling->decrement('output_flour', $emballage->quantity);
                    }
                }
            } else {
                $diffQty = $emballage->quantity - $oldQty;
                if ($emballage->milling && $diffQty != 0) {
                    $emballage->milling->decrement('output_flour', $diffQty);
                }
            }

            if ($emballage->isDirty('milling_id') && $emballage->milling) {
                $emballage->batch = $emballage->milling->batch_number;
            }
        });

        // ------------------- DELETE -------------------
        static::deleted(function ($emballage) {
            $item = (float) ($emballage->item ?? 0);
            $type = strtolower(trim($emballage->packaging_type ?? '1kg'));

            // Restore raw material stocks
            if ($type === 'box') {
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->increment('quantity_in', $item);
                }
                if ($emballage->envelopeStock) {
                    $emballage->envelopeStock->increment('quantity_in', $item * 12);
                }
            } else {
                if ($emballage->rawMaterialStock) {
                    $emballage->rawMaterialStock->increment('quantity_in', $item);
                }
            }

            // Restore milling flour
            if ($emballage->milling) {
                $emballage->milling->increment('output_flour', $emballage->quantity);
            }
        });
    }
}
