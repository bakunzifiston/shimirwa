<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * Serve product images from storage when public/storage symlink is missing (common on shared hosting).
     */
    public function show(string $path): BinaryFileResponse|Response
    {
        if (! preg_match('#^products/[a-zA-Z0-9._-]+$#', $path)) {
            abort(404);
        }

        $fullPath = storage_path('app/public/'.$path);

        if (! File::isFile($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
