<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        if (empty($this->path)) {
            return '';
        }

        // New uploads: stored directly under public/ (works without storage:link)
        if (str_starts_with($this->path, 'uploads/')) {
            $publicFile = public_path($this->path);

            return File::isFile($publicFile)
                ? asset($this->path)
                : '';
        }

        // Legacy: storage/app/public/products/… via symlink
        $symlinkFile = public_path('storage/'.$this->path);
        if (File::isFile($symlinkFile)) {
            return asset('storage/'.$this->path);
        }

        // Legacy: serve through Laravel when symlink is missing on the host
        if (Storage::disk('public')->exists($this->path)) {
            return route('media.show', ['path' => $this->path], absolute: false);
        }

        return '';
    }
}
