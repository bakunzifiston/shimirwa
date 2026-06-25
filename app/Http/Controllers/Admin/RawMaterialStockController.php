<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RawMaterialStock\StoreRawMaterialStockRequest;
use App\Http\Requests\Admin\RawMaterialStock\UpdateRawMaterialStockRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\PackagingCatalog;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawMaterialStockController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $type = $request->string('type')->toString();

        $stocks = RawMaterialStock::query()
            ->with(['client', 'employee'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('item', 'like', "%{$search}%")
                        ->orWhere('batch_number', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $today        = RawMaterialStock::whereDate('date', today())->count();
        $thisMonth    = RawMaterialStock::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = RawMaterialStock::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalKg      = (float) RawMaterialStock::sum('quantity_in');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total batches',    'value' => RawMaterialStock::count(), 'icon' => 'box', 'color' => 'blue',   'delta' => null],
            ['label' => 'Total kg received','value' => number_format($totalKg, 1).' kg', 'icon' => 'scale', 'color' => 'sky',    'delta' => null],
            ['label' => 'Today',            'value' => $today,    'icon' => 'calendar', 'color' => 'green',  'delta' => null],
            ['label' => 'This month',       'value' => $thisMonth, 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        // Per-item totals grouped by item name (all batches, not just current page)
        $itemTotals = RawMaterialStock::query()
            ->selectRaw('item, type, COUNT(*) as batches, SUM(received) as total_received, SUM(quantity_in) as total_remaining')
            ->groupBy('item', 'type')
            ->orderBy('type')
            ->orderBy('item')
            ->get();

        return view('admin.raw-material-stocks.index', [
            'stocks'      => $stocks,
            'search'      => $search,
            'type'        => $type,
            'types'       => config('raw_material_stock.types'),
            'pageStats'   => $pageStats,
            'itemTotals'  => $itemTotals,
        ]);
    }

    public function create(): View
    {
        return view('admin.raw-material-stocks.create', [
            'stock' => new RawMaterialStock,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreRawMaterialStockRequest $request): RedirectResponse
    {
        RawMaterialStock::create($request->validated());

        return redirect()
            ->route('admin.raw-material-stocks.index')
            ->with('success', 'Material reception recorded successfully.');
    }

    public function show(RawMaterialStock $rawMaterialStock): View
    {
        $rawMaterialStock->load([
            'client',
            'employee',
            'sortings.employee',
            'roastings.chef',
            'roastings.supervisor',
        ]);

        // Roastings sourced from sortings of this batch (indirect roastings)
        $sortingIds = $rawMaterialStock->sortings->pluck('id');
        $roastingsFromSortings = \App\Models\Roasting::with(['chef', 'supervisor', 'sorting'])
            ->whereIn('sorting_id', $sortingIds)
            ->orderBy('date')
            ->get();

        return view('admin.raw-material-stocks.show', [
            'stock'                 => $rawMaterialStock,
            'roastingsFromSortings' => $roastingsFromSortings,
        ]);
    }

    public function edit(RawMaterialStock $rawMaterialStock): View
    {
        return view('admin.raw-material-stocks.edit', [
            'stock' => $rawMaterialStock,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateRawMaterialStockRequest $request, RawMaterialStock $rawMaterialStock): RedirectResponse
    {
        $rawMaterialStock->update($request->validated());

        return redirect()
            ->route('admin.raw-material-stocks.show', $rawMaterialStock)
            ->with('success', 'Record updated successfully.');
    }

    public function destroy(RawMaterialStock $rawMaterialStock): RedirectResponse
    {
        try {
            $rawMaterialStock->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.raw-material-stocks.index')
            ->with('success', 'Record deleted successfully.');
    }

    protected function formOptions(): array
    {
        $configDefaults = config('raw_material_stock.items_by_type', []);

        // ── Raw Material: from ProductCatalog (production, Raw Material sub-category) ──
        $rawCatalog = ProductCatalog::active()->production()
            ->where('sub_category', 'Raw Material')
            ->orderBy('sort_order')->orderBy('name')
            ->pluck('name', 'name');

        // ── Packaging Material: from PackagingCatalog (the one users manage in Settings) ──
        $pkgCatalog = PackagingCatalog::active()
            ->orderBy('sort_order')->orderBy('name')
            ->pluck('name', 'name');

        $itemsByType = $configDefaults;

        // Override with live catalog data when entries exist; keep config fallback otherwise
        if ($rawCatalog->isNotEmpty()) {
            $itemsByType['Raw Material'] = $rawCatalog->toArray();
        }
        if ($pkgCatalog->isNotEmpty()) {
            $itemsByType['Packaging Material'] = $pkgCatalog->toArray();
        }

        // Also include any other ProductCatalog groups not covered above
        $otherCatalog = ProductCatalog::active()->production()
            ->whereNotIn('sub_category', ['Raw Material'])
            ->orderBy('sort_order')->orderBy('name')
            ->get(['name', 'sub_category']);

        foreach ($otherCatalog as $ci) {
            $group = $ci->sub_category ?: 'Other';
            $itemsByType[$group][$ci->name] = $ci->name;
        }

        // Types = all groups that have items, always include Other for free-text
        $types = array_combine(array_keys($itemsByType), array_keys($itemsByType));
        $types['Other'] = 'Other';

        // Employees for reception: those with 'reception' specialty first, then all
        $employees = Employee::query()
            ->orderByRaw("CASE WHEN specialties LIKE '%reception%' THEN 0 ELSE 1 END")
            ->orderBy('full_name')
            ->get();

        return [
            'suppliers'   => Client::where('role', 'supplier')->orderBy('full_name')->get(),
            'employees'   => $employees,
            'types'       => $types,
            'itemsByType' => $itemsByType,
        ];
    }
}
