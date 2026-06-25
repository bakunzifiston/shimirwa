<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\PackagingCatalog;
use App\Models\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $tab    = $request->input('tab', 'product');
        $search = $request->string('search')->trim()->toString();

        $productItems = ProductCatalog::query()
            ->when($search && $tab === 'product', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sub_category', 'like', "%{$search}%"))
            ->orderBy('category')->orderBy('sort_order')->orderBy('name')
            ->paginate(20, ['*'], 'product_page')
            ->withQueryString();

        $packagingItems = PackagingCatalog::query()
            ->with('innerUnitCatalog')
            ->when($search && $tab === 'packaging', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(20, ['*'], 'pkg_page')
            ->withQueryString();

        return view('admin.settings.catalogs', compact('tab', 'search', 'productItems', 'packagingItems'));
    }
}
