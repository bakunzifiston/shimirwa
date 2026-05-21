<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $stock = $request->string('stock')->toString();

        $products = Product::query()
            ->active()
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($stock === 'in_stock', fn ($q) => $q->where('stock_quantity', '>', 0))
            ->when($stock === 'out_of_stock', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('pages.shop.index', compact('products', 'search', 'stock'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load('images');

        $related = Product::query()
            ->active()
            ->where('id', '!=', $product->id)
            ->inStock()
            ->with('images')
            ->limit(3)
            ->get();

        return view('pages.shop.show', compact('product', 'related'));
    }
}
