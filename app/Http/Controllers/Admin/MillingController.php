<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Milling\StoreMillingRequest;
use App\Http\Requests\Admin\Milling\UpdateMillingRequest;
use App\Models\Employee;
use App\Models\Milling;
use App\Models\ProductCatalog;
use App\Models\Roasting;
use App\Models\Sorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MillingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $millings = Milling::query()
            ->with('employee')
            ->when($search, fn ($q) => $q->where('batch_number', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $today        = Milling::whereDate('date', today())->count();
        $thisMonth    = Milling::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = Milling::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalOutput  = (float) Milling::sum('output_flour');
        $totalLoss    = (float) Milling::sum('loss');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total runs',    'value' => Milling::count(), 'icon' => 'cog', 'color' => 'blue',   'delta' => null],
            ['label' => 'Flour output',  'value' => number_format($totalOutput, 1).' kg', 'icon' => 'scale', 'color' => 'sky',    'delta' => null],
            ['label' => 'Total loss',    'value' => number_format($totalLoss, 1).' kg',   'icon' => 'alert', 'color' => 'red',    'delta' => null],
            ['label' => 'This month',    'value' => $thisMonth, 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.millings.index', compact('millings', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.millings.create', $this->formData(new Milling));
    }

    public function store(StoreMillingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Expand overflow allocations: each item may draw from multiple batches
        $expanded = [];
        foreach ($validated['items'] as $item) {
            $overflow    = $item['overflow'] ?? [];
            $primaryQty  = (float) $item['quantity'];
            if (!empty($overflow)) {
                $overflowQty = array_sum(array_column($overflow, 'quantity'));
                $primaryQty  = max(round($primaryQty - $overflowQty, 4), 0);
            }

            if ($primaryQty > 0) {
                $expanded[] = [
                    'type'     => $item['type'],
                    'source'   => $item['source'],
                    'stock_id' => $item['stock_id'],
                    'quantity' => $primaryQty,
                ];
            }

            foreach ($overflow as $ov) {
                if ((float) ($ov['quantity'] ?? 0) > 0) {
                    $expanded[] = [
                        'type'     => $item['type'],
                        'source'   => $item['source'],
                        'stock_id' => $ov['stock_id'],
                        'quantity' => (float) $ov['quantity'],
                    ];
                }
            }
        }

        $validated['items'] = $expanded;
        Milling::create($validated);

        return redirect()->route('admin.millings.index')->with('success', 'Milling recorded.');
    }

    public function show(Milling $milling): View
    {
        $milling->load('employee');

        return view('admin.millings.show', [
            'milling'     => $milling,
            'ingredients' => $milling->resolvedIngredients(),
        ]);
    }

    public function edit(Milling $milling): View
    {
        return view('admin.millings.edit', $this->formData($milling));
    }

    public function update(UpdateMillingRequest $request, Milling $milling): RedirectResponse
    {
        try {
            $milling->update($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['update' => $e->getMessage()]);
        }

        return redirect()->route('admin.millings.show', $milling)->with('success', 'Milling updated.');
    }

    public function destroy(Milling $milling): RedirectResponse
    {
        try {
            $milling->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()->route('admin.millings.index')->with('success', 'Milling deleted.');
    }

    protected function formData(Milling $milling): array
    {
        // Items that go through roasting before milling
        $roastableItems = ProductCatalog::active()->production()->requiresRoasting()->pluck('name');
        // Items that only go through sorting (no roasting) before milling
        $sortOnlyItems  = ProductCatalog::active()->production()
            ->requiresSorting()
            ->where('requires_roasting', false)
            ->pluck('name');

        // Roasting batches with usable output (quantity_in - loss > 0)
        $roastingOptions = Roasting::with(['rawMaterialStock', 'sorting.rawMaterialStock'])
            ->whereRaw('quantity_in - COALESCE(loss, 0) > 0')
            ->when($roastableItems->isNotEmpty(), fn ($q) => $q->where(function ($q2) use ($roastableItems) {
                $q2->whereHas('rawMaterialStock', fn ($s) => $s->whereIn('item', $roastableItems))
                   ->orWhereHas('sorting.rawMaterialStock', fn ($s) => $s->whereIn('item', $roastableItems));
            }))
            ->orderByDesc('date')->get();

        // Sorting batches for items that skip roasting
        $sortingOptions = Sorting::with('rawMaterialStock')
            ->where('quantity_in', '>', 0)
            ->when($sortOnlyItems->isNotEmpty(), fn ($q) => $q->whereHas(
                'rawMaterialStock', fn ($s) => $s->whereIn('item', $sortOnlyItems)
            ))
            ->orderByDesc('date')->get();

        // Only raw-material catalog items for the ingredient dropdown (exclude packaging)
        $catalogItems = ProductCatalog::active()->production()
            ->whereNotIn('sub_category', ['Packaging Material', 'packaging material', 'Packaging Staff', 'packaging staff'])
            ->orderBy('sort_order')->orderBy('name')->get(['name', 'requires_roasting', 'requires_sorting']);

        return [
            'milling'         => $milling,
            'employees'       => Employee::orderBy('full_name')->get(),
            'roastingOptions' => $roastingOptions,
            'sortingOptions'  => $sortingOptions,
            'catalogItems'    => $catalogItems,
        ];
    }

    protected function availableRoastings(array $includeIds = [])
    {
        return Roasting::query()
            ->where(function ($query) use ($includeIds) {
                $query->where('quantity_remaining', '>', 0);
                if ($includeIds !== []) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->orderByDesc('date')
            ->get();
    }

    protected function availableSortings(array $includeIds = [])
    {
        return Sorting::query()
            ->with('rawMaterialStock')
            ->where(function ($query) use ($includeIds) {
                $query->where('quantity_remaining', '>', 0);
                if ($includeIds !== []) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->orderByDesc('date')
            ->get();
    }
}
