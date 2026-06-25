<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kg_per_unit',
        'manual_weight',
        'is_active',
        'sort_order',
        'description',
        'inner_unit_catalog_id',
        'inner_units_per_package',
    ];

    protected $casts = [
        'kg_per_unit'            => 'float',
        'manual_weight'          => 'boolean',
        'is_active'              => 'boolean',
        'sort_order'             => 'integer',
        'inner_units_per_package'=> 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function emballages()
    {
        return $this->hasMany(Emballage::class);
    }

    // The inner packaging type (e.g. for a Box → the "1kg bag" catalog entry)
    public function innerUnitCatalog()
    {
        return $this->belongsTo(PackagingCatalog::class, 'inner_unit_catalog_id');
    }

    public function hasInnerUnits(): bool
    {
        return $this->inner_unit_catalog_id && $this->inner_units_per_package > 0;
    }
}
