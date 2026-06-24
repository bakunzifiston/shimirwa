<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Milling\StoreMillingRequest;
use App\Http\Requests\Admin\Milling\UpdateMillingRequest;
use App\Models\Employee;
use App\Models\Milling;
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

        $summaryStats = [
            [
                'label' => 'Total batches',
                'value' => Milling::count(),
                'icon' => 'box',
            ],
            [
                'label' => 'Flour output',
                'value' => number_format((float) Milling::sum('output_flour'), 0).' kg',
                'icon' => 'chart',
                'valueAccent' => true,
            ],
            [
                'label' => 'Kg mixed',
                'value' => number_format((float) Milling::sum('total_mixed_quantity'), 0).' kg',
                'icon' => 'cog',
            ],
            [
                'label' => 'Total loss',
                'value' => number_format((float) Milling::sum('loss'), 0).' kg',
                'icon' => 'alert',
            ],
        ];

        return view('admin.millings.index', compact('millings', 'search', 'summaryStats'));
    }

    public function create(): View
    {
        return view('admin.millings.create', $this->formData(new Milling));
    }

    public function store(StoreMillingRequest $request): RedirectResponse
    {
        try {
            Milling::create($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.millings.index')->with('success', 'Milling recorded.');
    }

    public function show(Milling $milling): View
    {
        $milling->load('employee');

        return view('admin.millings.show', compact('milling'));
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
        $selectedRoastingIds = collect($milling->items ?? [])
            ->filter(fn ($item) => in_array($item['type'] ?? '', ['soy', 'maize'], true))
            ->pluck('stock_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedSortingIds = collect($milling->items ?? [])
            ->reject(fn ($item) => in_array($item['type'] ?? '', ['soy', 'maize'], true))
            ->pluck('stock_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return [
            'milling' => $milling,
            'employees' => Employee::orderBy('full_name')->get(),
            'roastingOptions' => $this->availableRoastings($selectedRoastingIds),
            'sortingOptions' => $this->availableSortings($selectedSortingIds),
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
