<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Training extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const CATEGORIES = [
        'general'   => 'General',
        'farming'   => 'Farming & Sourcing',
        'nutrition' => 'Nutrition & Health',
        'baking'    => 'Baking & Cooking',
        'business'  => 'Business & Retail',
    ];

    protected $fillable = [
        'title',
        'slug',
        'category',
        'cover_image',
        'excerpt',
        'body',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Training $training) {
            if (empty($training->slug)) {
                $training->slug = Str::slug($training->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image ? asset($this->cover_image) : null;
    }

    public function media(): HasMany
    {
        return $this->hasMany(TrainingMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TrainingMedia::class)->where('type', TrainingMedia::TYPE_IMAGE)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TrainingMedia::class)->where('type', TrainingMedia::TYPE_VIDEO)->orderBy('sort_order');
    }
}
