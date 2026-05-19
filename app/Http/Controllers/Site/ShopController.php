<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = collect(config('site.products'));
        $search = $request->string('q')->trim()->toString();
        $category = $request->string('category')->toString();

        if ($search !== '') {
            $products = $products->filter(fn ($p) => str_contains(strtolower($p['name'].' '.$p['short']), strtolower($search)));
        }

        if ($category !== '' && $category !== 'all') {
            $products = $products->where('category', $category);
        }

        return view('pages.shop.index', [
            'products' => $products->values(),
            'search' => $search,
            'category' => $category ?: 'all',
            'categories' => config('site.product_categories'),
        ]);
    }

    public function show(string $slug): View
    {
        $product = collect(config('site.products'))->firstWhere('slug', $slug);

        abort_unless($product, 404);

        $related = collect(config('site.products'))
            ->where('slug', '!=', $slug)
            ->where('category', $product['category'])
            ->take(3)
            ->values();

        return view('pages.shop.show', compact('product', 'related'));
    }
}
