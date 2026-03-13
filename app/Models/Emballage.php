<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emballage extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'milling_id',
        'packaging_batch_id',
        'item',
        'packaging_type',
        'quantity', // auto = item × packaging
        'damaged',
        'expiry_date',
        'batch',
        'comment',
        'employee_id',
        'raw_material_stock_id',
    ];

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

    protected static function boot()
    {
        parent::boot();

        // ------------------- CREATE -------------------
        static::creating(function ($emballage) {

            // Auto-calc quantity = item × packaging numeric part
            $item = is_numeric($emballage->item) ? (float)$emballage->item : 0;
            $pack = preg_replace('/[^0-9]/', '', $emballage->packaging_type);
            $packValue = $pack && is_numeric($pack) ? (float)$pack : 1;

            $emballage->quantity = $item * $packValue;

            // Set batch number
            if ($emballage->rawMaterialStock) {
                $emballage->batch = $emballage->rawMaterialStock->batch_number;
            }
            if ($emballage->milling) {
                $emballage->batch = $emballage->milling->batch_number;
            }
        });

        static::created(function ($emballage) {

            // Reduce raw material
            $itemValue = (float)$emballage->item;
            if ($emballage->rawMaterialStock) {
                $emballage->rawMaterialStock->decrement('quantity_in', $itemValue);
            }

            // Reduce milling by QUANTITY ONLY
            if ($emballage->milling) {

                $reduceMilling = (float)$emballage->quantity;

                $emballage->milling->decrement('output_flour', $reduceMilling);

                if ($emballage->milling->output_flour < 0) {
                    $emballage->milling->output_flour = 0;
                }

                $emballage->milling->save();
            }
        });

        // ------------------- UPDATE -------------------
        static::updating(function ($emballage) {

            // Recalculate quantity
            $item = is_numeric($emballage->item) ? (float)$emballage->item : 0;
            $pack = preg_replace('/[^0-9]/', '', $emballage->packaging_type);
            $packValue = $pack && is_numeric($pack) ? (float)$pack : 1;

            $emballage->quantity = $item * $packValue;

            // RAW MATERIAL: adjust difference
            $oldItem = (float)$emballage->getOriginal('item');
            $diffRaw = $item - $oldItem;

            if ($emballage->rawMaterialStock) {
                $emballage->rawMaterialStock->decrement('quantity_in', $diffRaw);
            }

            // MILLING: adjust using QUANTITY ONLY
            $oldQty = (float)$emballage->getOriginal('quantity');
            $newQty = (float)$emballage->quantity;

            $diffMilling = $newQty - $oldQty;

            if ($emballage->milling) {
                $emballage->milling->decrement('output_flour', $diffMilling);

                if ($emballage->milling->output_flour < 0) {
                    $emballage->milling->output_flour = 0;
                }

                $emballage->milling->save();
            }

            // Update batch if milling changed
            if ($emballage->isDirty('milling_id') && $emballage->milling) {
                $emballage->batch = $emballage->milling->batch_number;
            }
        });

        // ------------------- DELETE -------------------
        static::deleted(function ($emballage) {

            // Restore RAW MATERIAL
            if ($emballage->rawMaterialStock) {
                $emballage->rawMaterialStock->increment('quantity_in', (float)$emballage->item);
            }

            // Restore MILLING using QUANTITY ONLY
            if ($emballage->milling) {

                $emballage->milling->increment('output_flour', (float)$emballage->quantity);

            }
        });
    }
}
