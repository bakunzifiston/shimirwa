<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sorting\StoreSortingRequest;
use App\Http\Requests\Admin\Sorting\UpdateSortingRequest;
use App\Models\Employee;
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

        $summaryStats = [
            [
                'label' => 'Total batches',
                'value' => Sorting::count(),
                'icon' => 'box',
            ],
            [
                'label' => 'In stock',
                'value' => Sorting::where('quantity_remaining', '>=', 0.01)->count(),
                'icon' => 'chart',
                'valueAccent' => true,
            ],
            [
                'label' => 'Kg processed',
                'value' => number_format((float) Sorting::sum('quantity_in'), 0).' kg',
                'icon' => 'filter',
            ],
            [
                'label' => 'Kg remaining',
                'value' => number_format((float) Sorting::sum('quantity_remaining'), 0).' kg',
                'icon' => 'package',
            ],
        ];

        return view('admin.sortings.index', compact('sortings', 'search', 'summaryStats'));
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
        try {
            Sorting::create($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.sortings.index')->with('success', 'Sorting recorded.');
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
            'stocks' => $this->availableStocks($sorting->raw_material_stock_id),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function update(UpdateSortingRequest $request, Sorting $sorting): RedirectResponse
    {
        try {
            $sorting->update($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.sortings.show', $sorting)->with('success', 'Sorting updated.');
    }

    public function destroy(Sorting $sorting): RedirectResponse
    {
        try {
            $sorting->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()->route('admin.sortings.index')->with('success', 'Sorting deleted.');
    }

    protected function availableStocks(?int $includeId = null)
    {
        return RawMaterialStock::query()
            ->availableForSorting($includeId)
            ->orderByDesc('date')
            ->get()
            ->filter(fn (RawMaterialStock $stock) => $stock->hasAvailableStock()
                || ($includeId && (int) $stock->id === (int) $includeId))
            ->values();
    }
}
