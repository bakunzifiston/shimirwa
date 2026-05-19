<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sale\StoreSaleRequest;
use App\Http\Requests\Admin\Sale\UpdateSaleRequest;
use App\Models\Client;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $sales = Sale::query()
            ->with(['client', 'employee'])
            ->when($search, fn ($q) => $q->where('item', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.index', compact('sales', 'search'));
    }

    public function create(): View
    {
        return view('admin.sales.create', $this->formData(new Sale));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        Sale::create($request->validated());

        return redirect()->route('admin.sales.index')->with('success', 'Sale recorded.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['client', 'employee']);

        return view('admin.sales.show', compact('sale'));
    }

    public function edit(Sale $sale): View
    {
        return view('admin.sales.edit', $this->formData($sale));
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $sale->update($request->validated());

        return redirect()->route('admin.sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted.');
    }

    protected function formData(Sale $sale): array
    {
        return [
            'sale' => $sale,
            'clients' => Client::where('role', 'client')->orderBy('full_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'emballages' => Emballage::with('milling')
                ->where('item', '>', 0)
                ->orderByDesc('date')
                ->get(),
        ];
    }
}
