<?php

namespace App\Models;

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
    'type',          // <-- added this line
    'received',
    'rejected',
    'quantity_in',
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

    protected static function booted()
    {
        $recalculateNet = function (RawMaterialStock $stock): void {
            $stock->quantity_in = max((float) $stock->received - (float) $stock->rejected, 0);
        };

        static::creating($recalculateNet);
        static::updating($recalculateNet);
    }
}
