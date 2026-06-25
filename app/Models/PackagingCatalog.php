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
    ];

    protected $casts = [
        'kg_per_unit'   => 'float',
        'manual_weight' => 'boolean',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function emballages()
    {
        return $this->hasMany(Emballage::class);
    }
}
