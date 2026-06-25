<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCatalog extends Model
{
    protected $table = 'product_catalog';

    protected $fillable = [
        'name',
        'category',
        'sub_category',
        'unit',
        'description',
        'is_active',
        'requires_sorting',
        'requires_roasting',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'          => 'boolean',
            'requires_sorting'   => 'boolean',
            'requires_roasting'  => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProduction($query)
    {
        return $query->where('category', 'production');
    }

    public function scopeEcommerce($query)
    {
        return $query->where('category', 'ecommerce');
    }

    public function scopeBySubCategory($query, string $sub)
    {
        return $query->where('sub_category', $sub);
    }

    public function scopeRequiresSorting($query)
    {
        return $query->where('requires_sorting', true);
    }

    public function scopeRequiresRoasting($query)
    {
        return $query->where('requires_roasting', true);
    }
}
