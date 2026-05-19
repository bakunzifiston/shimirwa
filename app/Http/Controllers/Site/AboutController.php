<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.about', [
            'values' => config('site.values'),
            'team' => config('site.team'),
            'milestones' => config('site.milestones'),
        ]);
    }
}
