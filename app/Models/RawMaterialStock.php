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

    public function sortings()
    {
        return $this->hasMany(Sorting::class, 'raw_material_stock_id');
    }

    public function roastings()
    {
        return $this->hasMany(Roasting::class, 'raw_material_stock_id');
    }

    public function scopePackagingStaff($query)
    {
        // Match both the legacy 'Packaging Staff' type and the catalog-driven sub_category 'Packaging Material'
        return $query->where(function ($q) {
            $q->whereIn('type', ['Packaging Material', 'Packaging Staff', 'packaging staff', 'packaging material']);
        });
    }

    protected static function booted()
    {
        // On creation: derive initial quantity_in from received minus rejected.
        static::creating(function (RawMaterialStock $stock): void {
            $stock->quantity_in = max((float) $stock->received - (float) $stock->rejected, 0);
        });

        // On update: only recalculate when the user actually changed received/rejected.
        // Sorting/Roasting/Milling deductions write quantity_in directly via saveQuietly(),
        // so they never reach this hook — but a plain save() from reception edits still does.
        static::updating(function (RawMaterialStock $stock): void {
            if ($stock->isDirty('received') || $stock->isDirty('rejected')) {
                $consumed = (float) \DB::table('sortings')->where('raw_material_stock_id', $stock->id)->sum('quantity_in')
                          + (float) \DB::table('roastings')->where('raw_material_stock_id', $stock->id)->sum('quantity_in');
                $base     = max((float) $stock->received - (float) $stock->rejected, 0);
                $stock->quantity_in = max($base - $consumed, 0);
            }
        });
    }
}
