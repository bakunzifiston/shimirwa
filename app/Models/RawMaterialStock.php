<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialStock extends Model
{
    use HasFactory;

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

    protected static function booted()
    {
        // Only calculate quantity_in when creating a new stock record
        static::creating(function ($stock) {
            $stock->quantity_in = $stock->received - $stock->rejected;
        });
    }
}
