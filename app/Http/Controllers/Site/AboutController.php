<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::query()
            ->active()
            ->with('images')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('pages.about', compact('products'));
    }
}
