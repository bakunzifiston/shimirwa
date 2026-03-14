<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emballage extends Model
{
    use HasFactory;

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

            // Deduct flour (kg) from milling output
            if ($emballage->milling) {
                $emballage->milling->decrement('output_flour', $emballage->quantity);
                if ($emballage->milling->output_flour < 0) {
                    $emballage->milling->update(['output_flour' => 0]);
                }
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

            // Adjust milling flour
            $diffQty = $emballage->quantity - $oldQty;
            if ($emballage->milling && $diffQty != 0) {
                $emballage->milling->decrement('output_flour', $diffQty);
                if ($emballage->milling->output_flour < 0) {
                    $emballage->milling->update(['output_flour' => 0]);
                }
            }

            // Update batch if milling changed
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
