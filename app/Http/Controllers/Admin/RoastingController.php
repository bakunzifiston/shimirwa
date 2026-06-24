<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roasting\StoreRoastingRequest;
use App\Http\Requests\Admin\Roasting\UpdateRoastingRequest;
use App\Models\Employee;
use App\Models\RawMaterialStock;
use App\Models\Roasting;
use App\Models\Sorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoastingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $roastings = Roasting::query()
            ->with(['chef', 'supervisor', 'rawMaterialStock', 'sorting.rawMaterialStock'])
            ->when($search, fn ($q) => $q->where('batch', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summaryStats = [
            [
                'label' => 'Total batches',
                'value' => Roasting::count(),
                'icon' => 'box',
            ],
            [
                'label' => 'In stock',
                'value' => Roasting::where('quantity_remaining', '>=', 0.01)->count(),
                'icon' => 'chart',
                'valueAccent' => true,
            ],
            [
                'label' => 'Kg processed',
                'value' => number_format((float) Roasting::sum('quantity_in'), 0).' kg',
                'icon' => 'fire',
            ],
            [
                'label' => 'Kg remaining',
                'value' => number_format((float) Roasting::sum('quantity_remaining'), 0).' kg',
                'icon' => 'package',
            ],
        ];

        return view('admin.roastings.index', compact('roastings', 'search', 'summaryStats'));
    }

    public function create(): View
    {
        return view('admin.roastings.create', $this->formData(new Roasting));
    }

    public function store(StoreRoastingRequest $request): RedirectResponse
    {
        try {
            Roasting::create($request->persistedAttributes());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.roastings.index')->with('success', 'Roasting recorded.');
    }

    public function show(Roasting $roasting): View
    {
        $roasting->load(['chef', 'supervisor', 'rawMaterialStock', 'sorting.rawMaterialStock']);

        return view('admin.roastings.show', compact('roasting'));
    }

    public function edit(Roasting $roasting): View
    {
        return view('admin.roastings.edit', $this->formData($roasting));
    }

    public function update(UpdateRoastingRequest $request, Roasting $roasting): RedirectResponse
    {
        try {
            $roasting->update($request->persistedAttributes());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.roastings.show', $roasting)->with('success', 'Roasting updated.');
    }

    public function destroy(Roasting $roasting): RedirectResponse
    {
        try {
            $roasting->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()->route('admin.roastings.index')->with('success', 'Roasting deleted.');
    }

    protected function formData(Roasting $roasting): array
    {
        $sourceType = old('source_type', $roasting->raw_material_stock_id ? 'raw' : ($roasting->sorting_id ? 'sorting' : 'raw'));

        return [
            'roasting' => $roasting,
            'sourceType' => $sourceType,
            'rawStocks' => $this->availableRawStocks($roasting->raw_material_stock_id),
            'sortingStocks' => $this->availableSortingStocks($roasting->sorting_id),
            'employees' => Employee::orderBy('full_name')->get(),
        ];
    }

    protected function availableRawStocks(?int $includeId = null)
    {
        return RawMaterialStock::query()
            ->rawMaterialKg()
            ->where(function ($query) use ($includeId) {
                $query->where('quantity_in', '>', 0);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderByDesc('date')
            ->get();
    }

    protected function availableSortingStocks(?int $includeId = null)
    {
        return Sorting::query()
            ->with('rawMaterialStock')
            ->where(function ($query) use ($includeId) {
                $query->where('quantity_remaining', '>', 0);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderByDesc('date')
            ->get();
    }

}
