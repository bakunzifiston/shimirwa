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

        return view('admin.roastings.index', compact('roastings', 'search'));
    }

    public function create(): View
    {
        return view('admin.roastings.create', $this->formData(new Roasting));
    }

    public function store(StoreRoastingRequest $request): RedirectResponse
    {
        Roasting::create($this->mapPayload($request->validated()));

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
        $roasting->update($this->mapPayload($request->validated()));

        return redirect()->route('admin.roastings.show', $roasting)->with('success', 'Roasting updated.');
    }

    public function destroy(Roasting $roasting): RedirectResponse
    {
        $roasting->delete();

        return redirect()->route('admin.roastings.index')->with('success', 'Roasting deleted.');
    }

    protected function formData(Roasting $roasting): array
    {
        $sourceType = old('source_type', $roasting->raw_material_stock_id ? 'raw' : ($roasting->sorting_id ? 'sorting' : 'raw'));

        return [
            'roasting' => $roasting,
            'sourceType' => $sourceType,
            'rawStocks' => RawMaterialStock::where('quantity_in', '>', 0)->orderByDesc('date')->get(),
            'sortingStocks' => Sorting::with('rawMaterialStock')->where('quantity_in', '>', 0)->orderByDesc('date')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
        ];
    }

    protected function mapPayload(array $data): array
    {
        $source = $data['source_type'] ?? 'raw';
        unset($data['source_type']);

        if ($source === 'raw') {
            $data['sorting_id'] = null;
        } else {
            $data['raw_material_stock_id'] = null;
        }

        return $data;
    }
}
