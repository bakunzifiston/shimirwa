<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emballage\StoreEmballageRequest;
use App\Http\Requests\Admin\Emballage\UpdateEmballageRequest;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Milling;
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

        return view('admin.emballages.index', compact('emballages', 'search'));
    }

    public function create(): View
    {
        return view('admin.emballages.create', $this->formData(new Emballage));
    }

    public function store(StoreEmballageRequest $request): RedirectResponse
    {
        Emballage::create($request->validated());

        return redirect()->route('admin.emballages.index')->with('success', 'Packaging recorded.');
    }

    public function show(Emballage $emballage): View
    {
        $emballage->load(['milling', 'employee', 'rawMaterialStock', 'envelopeStock']);

        return view('admin.emballages.show', compact('emballage'));
    }

    public function edit(Emballage $emballage): View
    {
        return view('admin.emballages.edit', $this->formData($emballage));
    }

    public function update(UpdateEmballageRequest $request, Emballage $emballage): RedirectResponse
    {
        $emballage->update($request->validated());

        return redirect()->route('admin.emballages.show', $emballage)->with('success', 'Packaging updated.');
    }

    public function destroy(Emballage $emballage): RedirectResponse
    {
        $emballage->delete();

        return redirect()->route('admin.emballages.index')->with('success', 'Packaging deleted.');
    }

    protected function formData(Emballage $emballage): array
    {
        return [
            'emballage' => $emballage,
            'packagingStocks' => RawMaterialStock::packagingStaff()->where('quantity_in', '>', 0)->orderByDesc('date')->get(),
            'millings' => Milling::where('output_flour', '>', 0)->orderByDesc('date')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'packagingTypes' => [
                'box' => 'Box (12 kg flour per box)',
                '1kg' => '1kg package',
                '5kg' => '5kg package',
                'sack' => 'Sack (manual weight)',
            ],
        ];
    }
}
