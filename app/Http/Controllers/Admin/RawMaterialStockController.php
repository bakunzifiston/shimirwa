<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RawMaterialStock\StoreRawMaterialStockRequest;
use App\Http\Requests\Admin\RawMaterialStock\UpdateRawMaterialStockRequest;
use App\Models\Client;
use App\Models\Employee;
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

        return view('admin.raw-material-stocks.index', [
            'stocks' => $stocks,
            'search' => $search,
            'type' => $type,
            'types' => config('raw_material_stock.types'),
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
        $rawMaterialStock->load(['client', 'employee']);

        return view('admin.raw-material-stocks.show', [
            'stock' => $rawMaterialStock,
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
        $rawMaterialStock->delete();

        return redirect()
            ->route('admin.raw-material-stocks.index')
            ->with('success', 'Record deleted successfully.');
    }

  /**
   * @return array{suppliers: \Illuminate\Support\Collection, employees: \Illuminate\Support\Collection, types: array, itemsByType: array}
   */
    protected function formOptions(): array
    {
        return [
            'suppliers' => Client::query()
                ->where('role', 'supplier')
                ->orderBy('full_name')
                ->get(),
            'employees' => Employee::query()->orderBy('full_name')->get(),
            'types' => config('raw_material_stock.types'),
            'itemsByType' => config('raw_material_stock.items_by_type'),
        ];
    }
}
