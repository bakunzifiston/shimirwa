<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'stats' => config('site.stats'),
            'testimonials' => config('site.testimonials'),
            'featuredProducts' => Product::query()
                ->active()
                ->with('images')
                ->orderByDesc('created_at')
                ->limit(4)
                ->get(),
        ]);
    }
}
