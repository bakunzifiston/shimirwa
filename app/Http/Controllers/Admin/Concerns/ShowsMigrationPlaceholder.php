<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\View\View;

trait ShowsMigrationPlaceholder
{
    protected function placeholder(string $title, string $description, ?string $filamentResource = null): View
    {
        return view('admin.modules.placeholder', compact('title', 'description', 'filamentResource'));
    }
}
