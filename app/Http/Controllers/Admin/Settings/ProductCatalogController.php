<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    private const CATEGORIES = [
        'production' => 'Production',
        'ecommerce'  => 'E-commerce',
    ];

    private const SUB_CATEGORIES = [
        'production' => ['Raw Material', 'Packaging Material', 'Finished Good', 'Other'],
        'ecommerce'  => ['Flour Product', 'Grain Product', 'Bundle', 'Other'],
    ];

    // Sub-categories that represent packaging materials — used by reception form + emballage scope
    public const PACKAGING_SUB_CATEGORIES = ['Packaging Material'];

    private const UNITS = ['kg', 'g', 'units', 'pcs', 'bags', 'boxes', 'litres'];

    public function index(Request $request): View
    {
        $search   = $request->string('search')->trim()->toString();
        $category = $request->string('category')->toString();

        $items = ProductCatalog::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sub_category', 'like', "%{$search}%"))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->orderBy('category')->orderBy('sort_order')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $total      = ProductCatalog::count();
        $production = ProductCatalog::where('category', 'production')->count();
        $ecommerce  = ProductCatalog::where('category', 'ecommerce')->count();
        $active     = ProductCatalog::where('is_active', true)->count();

        $pageStats = [
            ['label' => 'Total items',   'value' => $total,      'icon' => 'list',    'color' => 'blue'],
            ['label' => 'Production',    'value' => $production,  'icon' => 'cog',     'color' => 'amber'],
            ['label' => 'E-commerce',    'value' => $ecommerce,   'icon' => 'package', 'color' => 'green'],
            ['label' => 'Active',        'value' => $active,      'icon' => 'check',   'color' => 'sky'],
        ];

        return view('admin.settings.product-catalog.index', compact(
            'items', 'search', 'category', 'pageStats'
        ));
    }

    public function create(): View
    {
        return view('admin.settings.product-catalog.create', $this->formData(new ProductCatalog));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        ProductCatalog::create($data);

        return redirect()->route('admin.settings.product-catalog.index')
            ->with('success', 'Item added to catalog.');
    }

    public function show(ProductCatalog $productCatalog): View
    {
        return view('admin.settings.product-catalog.show', compact('productCatalog'));
    }

    public function edit(ProductCatalog $productCatalog): View
    {
        return view('admin.settings.product-catalog.edit', array_merge(
            $this->formData($productCatalog),
            ['productCatalog' => $productCatalog]
        ));
    }

    public function update(Request $request, ProductCatalog $productCatalog): RedirectResponse
    {
        $data = $this->validated($request, $productCatalog->id);
        $productCatalog->update($data);

        return redirect()->route('admin.settings.product-catalog.show', $productCatalog)
            ->with('success', 'Catalog item updated.');
    }

    public function destroy(ProductCatalog $productCatalog): RedirectResponse
    {
        $productCatalog->delete();

        return redirect()->route('admin.settings.product-catalog.index')
            ->with('success', 'Catalog item deleted.');
    }

    private function formData(ProductCatalog $item): array
    {
        return [
            'item'         => $item,
            'categories'   => self::CATEGORIES,
            'subCategories'=> self::SUB_CATEGORIES,
            'units'        => self::UNITS,
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category'          => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'sub_category'      => ['nullable', 'string', 'max:60'],
            'unit'              => ['required', 'string', 'max:30'],
            'description'       => ['nullable', 'string'],
            'is_active'         => ['boolean'],
            'requires_sorting'  => ['boolean'],
            'requires_roasting'  => ['boolean'],
            'direct_to_milling'  => ['boolean'],
            'sort_order'         => ['integer', 'min:0'],
        ]);
    }
}
