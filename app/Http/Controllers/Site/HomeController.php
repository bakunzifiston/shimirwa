<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'stats' => config('site.stats'),
            'testimonials' => config('site.testimonials'),
            'featuredProducts' => collect(config('site.products'))->take(4),
        ]);
    }
}
