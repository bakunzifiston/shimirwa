<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sorting\StoreSortingRequest;
use App\Http\Requests\Admin\Sorting\UpdateSortingRequest;
use App\Models\Employee;
use App\Models\ProductCatalog;
use App\Models\RawMaterialStock;
use App\Models\Sorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SortingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $sortings = Sorting::query()
            ->with(['rawMaterialStock', 'employee'])
            ->when($search, fn ($q) => $q->whereHas('rawMaterialStock', function ($s) use ($search) {
                $s->where('item', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $today        = Sorting::whereDate('date', today())->count();
        $thisMonth    = Sorting::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = Sorting::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalKgIn    = (float) Sorting::sum('quantity_in');
        $totalLoss    = (float) Sorting::sum('loss');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total batches', 'value' => Sorting::count(), 'icon' => 'list', 'color' => 'blue',   'delta' => null],
            ['label' => 'Total sorted',  'value' => number_format($totalKgIn, 1).' kg', 'icon' => 'scale', 'color' => 'sky',    'delta' => null],
            ['label' => 'Total loss',    'value' => number_format($totalLoss, 1).' kg', 'icon' => 'alert', 'color' => 'red',    'delta' => null],
            ['label' => 'This month',    'value' => $thisMonth, 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.sortings.index', compact('sortings', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.sortings.create', [
            'sorting' => new Sorting,
            'stocks' => $this->availableStocks(),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function store(StoreSortingRequest $request): RedirectResponse
    {
        $allocations = $request->input('allocations', []);

        // If only one batch (no split), wrap it in the allocations format
        if (empty($allocations)) {
            $allocations = [[
                'raw_material_stock_id' => $request->input('raw_material_stock_id'),
                'quantity_in'           => $request->input('quantity_in'),
            ]];
        }

        $totalQty  = array_sum(array_column($allocations, 'quantity_in'));
        $loss      = (float) $request->input('loss', 0);
        $created   = [];

        try {
            \DB::transaction(function () use ($request, $allocations, $totalQty, $loss, &$created) {
                foreach ($allocations as $alloc) {
                    $qty = (float) $alloc['quantity_in'];
                    // Distribute loss proportionally across batches
                    $batchLoss = $totalQty > 0 ? round($loss * ($qty / $totalQty), 4) : 0;

                    $created[] = Sorting::create([
                        'date'                  => $request->input('date'),
                        'raw_material_stock_id' => $alloc['raw_material_stock_id'],
                        'quantity_in'           => $qty,
                        'loss'                  => $batchLoss,
                        'employee_id'           => $request->input('employee_id'),
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['quantity_in' => $e->getMessage()]);
        }

        $count = count($created);
        $msg   = $count > 1
            ? "Sorting recorded across {$count} batches."
            : 'Sorting recorded.';

        return redirect()->route('admin.sortings.index')->with('success', $msg);
    }

    public function show(Sorting $sorting): View
    {
        $sorting->load(['rawMaterialStock', 'employee']);

        return view('admin.sortings.show', compact('sorting'));
    }

    public function edit(Sorting $sorting): View
    {
        return view('admin.sortings.edit', [
            'sorting' => $sorting,
            'stocks' => $this->availableStocks(),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function update(UpdateSortingRequest $request, Sorting $sorting): RedirectResponse
    {
        try {
            $sorting->update($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['raw_material_stock_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.sortings.show', $sorting)->with('success', 'Sorting updated.');
    }

    public function destroy(Sorting $sorting): RedirectResponse
    {
        $sorting->delete();

        return redirect()->route('admin.sortings.index')->with('success', 'Sorting deleted.');
    }

    protected function availableStocks()
    {
        // Only show batches whose item is flagged as requires_sorting in the catalog.
        // If no catalog items are flagged yet, fall back to all available stocks.
        $sortableItems = ProductCatalog::active()->production()->requiresSorting()
            ->pluck('name');

        return RawMaterialStock::query()
            ->where('quantity_in', '>', 0)
            ->when($sortableItems->isNotEmpty(), fn ($q) => $q->whereIn('item', $sortableItems))
            ->orderByDesc('date')
            ->get();
    }
}
