<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\PackagingCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackagingCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $items = PackagingCatalog::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $total  = PackagingCatalog::count();
        $active = PackagingCatalog::where('is_active', true)->count();

        $pageStats = [
            ['label' => 'Total types',  'value' => $total,  'icon' => 'package', 'color' => 'blue'],
            ['label' => 'Active',       'value' => $active, 'icon' => 'check',   'color' => 'green'],
            ['label' => 'Inactive',     'value' => $total - $active, 'icon' => 'alert', 'color' => 'amber'],
        ];

        return view('admin.settings.packaging-catalog.index', compact('items', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.settings.packaging-catalog.create', ['item' => new PackagingCatalog]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        PackagingCatalog::create($data);

        return redirect()->route('admin.settings.packaging-catalog.index')
            ->with('success', 'Packaging type added.');
    }

    public function show(PackagingCatalog $packagingCatalog): View
    {
        $packagingCatalog->loadCount('emballages');
        return view('admin.settings.packaging-catalog.show', ['item' => $packagingCatalog]);
    }

    public function edit(PackagingCatalog $packagingCatalog): View
    {
        return view('admin.settings.packaging-catalog.edit', ['item' => $packagingCatalog]);
    }

    public function update(Request $request, PackagingCatalog $packagingCatalog): RedirectResponse
    {
        $data = $this->validated($request, $packagingCatalog->id);
        $packagingCatalog->update($data);

        return redirect()->route('admin.settings.packaging-catalog.show', $packagingCatalog)
            ->with('success', 'Packaging type updated.');
    }

    public function destroy(PackagingCatalog $packagingCatalog): RedirectResponse
    {
        $packagingCatalog->delete();

        return redirect()->route('admin.settings.packaging-catalog.index')
            ->with('success', 'Packaging type deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'kg_per_unit'   => ['required', 'numeric', 'min:0'],
            'manual_weight' => ['boolean'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
            'description'   => ['nullable', 'string', 'max:500'],
        ]);
    }
}
