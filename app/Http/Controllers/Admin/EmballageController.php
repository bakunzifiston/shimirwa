<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emballage\StoreEmballageRequest;
use App\Http\Requests\Admin\Emballage\UpdateEmballageRequest;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Milling;
use App\Models\PackagingCatalog;
use App\Models\RawMaterialStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmballageController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $emballages = Emballage::query()
            ->with(['milling', 'employee', 'rawMaterialStock'])
            ->when($search, fn ($q) => $q->where('packaging_batch_id', 'like', "%{$search}%")
                ->orWhere('packaging_type', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $today        = Emballage::whereDate('date', today())->count();
        $thisMonth    = Emballage::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = Emballage::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalUnits   = (float) Emballage::sum('item');
        $totalDmg     = (float) Emballage::sum('damaged');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total runs',   'value' => Emballage::count(), 'icon' => 'package', 'color' => 'blue',   'delta' => null],
            ['label' => 'Units packed', 'value' => number_format($totalUnits, 0), 'icon' => 'box', 'color' => 'green',  'delta' => null],
            ['label' => 'Damaged',      'value' => number_format($totalDmg, 0),   'icon' => 'alert', 'color' => 'red',    'delta' => null],
            ['label' => 'This month',   'value' => $thisMonth, 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.emballages.index', compact('emballages', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.emballages.create', $this->formData(new Emballage));
    }

    public function store(StoreEmballageRequest $request): RedirectResponse
    {
        try {
            Emballage::create($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.emballages.index')->with('success', 'Packaging recorded.');
    }

    public function show(Emballage $emballage): View
    {
        $emballage->load(['milling', 'employee', 'rawMaterialStock', 'packagingCatalog']);

        return view('admin.emballages.show', compact('emballage'));
    }

    public function edit(Emballage $emballage): View
    {
        return view('admin.emballages.edit', $this->formData($emballage));
    }

    public function update(UpdateEmballageRequest $request, Emballage $emballage): RedirectResponse
    {
        try {
            $emballage->update($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.emballages.show', $emballage)->with('success', 'Packaging updated.');
    }

    public function destroy(Emballage $emballage): RedirectResponse
    {
        try {
            $emballage->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()->route('admin.emballages.index')->with('success', 'Packaging deleted.');
    }

    protected function formData(Emballage $emballage): array
    {
        // For edit: include the current primary + all overflow milling batches even if flour is 0
        $linkedIds = [];
        if ($emballage->exists && $emballage->milling_id) {
            $linkedIds[] = $emballage->milling_id;
        }
        foreach ($emballage->milling_overflow ?? [] as $ov) {
            if (!empty($ov['milling_id'])) $linkedIds[] = $ov['milling_id'];
        }

        $millings = Milling::where(function ($q) use ($linkedIds) {
            $q->where('output_flour', '>', 0);
            if (!empty($linkedIds)) {
                $q->orWhereIn('id', $linkedIds);
            }
        })->orderByDesc('date')->get();

        return [
            'emballage'        => $emballage,
            'packagingStocks'  => RawMaterialStock::packagingStaff()->where('quantity_in', '>', 0)->orderByDesc('date')->get(),
            'millings'         => $millings,
            'employees'        => Employee::orderBy('full_name')->get(),
            'packagingCatalogs'=> PackagingCatalog::active()->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }
}
