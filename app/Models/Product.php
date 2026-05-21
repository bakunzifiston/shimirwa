<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function effectivePrice(): float
    {
        if ($this->discount_price !== null && (float) $this->discount_price > 0 && (float) $this->discount_price < (float) $this->price) {
            return (float) $this->discount_price;
        }

        return (float) $this->price;
    }

    public function primaryImageUrl(): ?string
    {
        $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $image?->url();
    }

    public function shortDescription(int $length = 120): string
    {
        return Str::limit(strip_tags((string) $this->description), $length);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
