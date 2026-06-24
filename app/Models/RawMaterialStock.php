<?php

namespace App\Models;

use App\Support\Inventory\InventoryReferences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialStock extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
        'received' => 'float',
        'rejected' => 'float',
        'quantity_in' => 'float',
    ];

    protected $fillable = [
        'date',
        'client_id',
        'item',
        'type',
        'received',
        'rejected',
        'comment',
        'batch_number',
        'employee_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopePackagingStaff($query)
    {
        return $query->whereIn('type', ['Packaging Staff', 'packaging staff']);
    }

    public function scopeRawMaterialKg($query)
    {
        return $query->where('type', 'Raw Material');
    }

    /**
     * Raw-material batches with usable stock for sorting (and similar pickers).
     * Optionally include a linked batch on edit even when depleted.
     */
    public function scopeAvailableForSorting($query, ?int $includeId = null)
    {
        return $query
            ->rawMaterialKg()
            ->where(function ($q) use ($includeId) {
                $q->where('quantity_in', '>=', 0.01);
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            });
    }

    public function hasAvailableStock(): bool
    {
        return $this->remainingQuantity() >= 0.01;
    }

    public function remainingQuantity(): float
    {
        return max((float) $this->quantity_in, 0);
    }

    public function initialNetQuantity(): float
    {
        return max((float) $this->received - (float) $this->rejected, 0);
    }

    protected static function booted()
    {
        static::creating(function (RawMaterialStock $stock) {
            $stock->quantity_in = $stock->initialNetQuantity();
        });

        static::updating(function (RawMaterialStock $stock) {
            if ($stock->isDirty(['received', 'rejected'])) {
                $oldNet = max((float) $stock->getOriginal('received') - (float) $stock->getOriginal('rejected'), 0);
                $newNet = max((float) $stock->received - (float) $stock->rejected, 0);
                $stock->quantity_in = max((float) $stock->quantity_in + ($newNet - $oldNet), 0);
            }
        });

        static::deleting(function (RawMaterialStock $stock) {
            $reason = InventoryReferences::rawMaterialStockUsageReason((int) $stock->id);
            if ($reason) {
                throw new \Exception($reason);
            }
        });
    }
}
