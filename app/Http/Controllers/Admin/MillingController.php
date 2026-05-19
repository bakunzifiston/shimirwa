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

        return view('admin.millings.index', compact('millings', 'search'));
    }

    public function create(): View
    {
        return view('admin.millings.create', $this->formData(new Milling));
    }

    public function store(StoreMillingRequest $request): RedirectResponse
    {
        Milling::create($request->validated());

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
        $milling->update($request->validated());

        return redirect()->route('admin.millings.show', $milling)->with('success', 'Milling updated.');
    }

    public function destroy(Milling $milling): RedirectResponse
    {
        $milling->delete();

        return redirect()->route('admin.millings.index')->with('success', 'Milling deleted.');
    }

    protected function formData(Milling $milling): array
    {
        return [
            'milling' => $milling,
            'employees' => Employee::orderBy('full_name')->get(),
            'roastingOptions' => Roasting::where('quantity_in', '>', 0)->orderByDesc('date')->get(),
            'sortingOptions' => Sorting::with('rawMaterialStock')->where('quantity_in', '>', 0)->orderByDesc('date')->get(),
        ];
    }
}
