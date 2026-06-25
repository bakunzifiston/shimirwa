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

        $today        = Sale::whereDate('date', today())->count();
        $thisMonth    = Sale::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = Sale::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalRev     = (float) Sale::sum('total_price');
        $monthRev     = (float) Sale::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_price');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total sales',  'value' => Sale::count(), 'icon' => 'cart', 'color' => 'blue',   'delta' => null],
            ['label' => 'Total revenue','value' => number_format($totalRev, 0).' RWF', 'icon' => 'chart', 'color' => 'green',  'delta' => null],
            ['label' => 'Month revenue','value' => number_format($monthRev, 0).' RWF', 'icon' => 'calendar', 'color' => 'sky',    'delta' => null],
            ['label' => 'This month',   'value' => $thisMonth.' sales', 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.sales.index', compact('sales', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.sales.create', $this->formData(new Sale));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            Sale::create($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

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
        try {
            $sale->update($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        try {
            $sale->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted.');
    }

    protected function formData(Sale $sale): array
    {
        $includeEmballageIds = collect($sale->batches ?? [])
            ->pluck('emballage_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return [
            'sale' => $sale,
            'clients' => Client::where('role', 'client')->orderBy('full_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'emballages' => Emballage::with('milling')
                ->where(function ($query) use ($includeEmballageIds) {
                    $query->where('item', '>', 0);
                    if ($includeEmballageIds !== []) {
                        $query->orWhereIn('id', $includeEmballageIds);
                    }
                })
                ->orderByDesc('date')
                ->get(),
        ];
    }
}
