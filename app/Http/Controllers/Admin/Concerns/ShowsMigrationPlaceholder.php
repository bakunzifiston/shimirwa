<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\View\View;

trait ShowsMigrationPlaceholder
{
    protected function placeholder(string $title, string $description): View
    {
        return view('admin.modules.placeholder', compact('title', 'description'));
    }
}
