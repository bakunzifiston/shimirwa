<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateProductImagesToPublic extends Command
{
    protected $signature = 'products:migrate-images-to-public';

    protected $description = 'Copy legacy storage/app/public product images into public/uploads/products (for shared hosting)';

    public function handle(): int
    {
        $directory = public_path('uploads/products');
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $images = ProductImage::query()
            ->where('path', 'not like', 'uploads/%')
            ->get();

        if ($images->isEmpty()) {
            $this->info('No legacy product images to migrate.');

            return self::SUCCESS;
        }

        $migrated = 0;

        foreach ($images as $image) {
            if (! Storage::disk('public')->exists($image->path)) {
                $this->warn("Missing file for image #{$image->id}: {$image->path}");

                continue;
            }

            $extension = pathinfo($image->path, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = Str::uuid().'.'.strtolower($extension);
            $newPath = 'uploads/products/'.$filename;

            File::copy(
                Storage::disk('public')->path($image->path),
                public_path($newPath)
            );

            $image->update(['path' => $newPath]);
            $migrated++;
        }

        $this->info("Migrated {$migrated} image(s) to public/uploads/products.");

        return self::SUCCESS;
    }
}
