<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roasting\StoreRoastingRequest;
use App\Http\Requests\Admin\Roasting\UpdateRoastingRequest;
use App\Models\Employee;
use App\Models\ProductCatalog;
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

        $today        = Roasting::whereDate('date', today())->count();
        $thisMonth    = Roasting::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $lastMonth    = Roasting::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->count();
        $totalKgIn    = (float) Roasting::sum('quantity_in');
        $totalLoss    = (float) Roasting::sum('loss');
        $delta        = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total batches', 'value' => Roasting::count(), 'icon' => 'fire', 'color' => 'blue',   'delta' => null],
            ['label' => 'Total roasted', 'value' => number_format($totalKgIn, 1).' kg', 'icon' => 'scale', 'color' => 'sky',    'delta' => null],
            ['label' => 'Total loss',    'value' => number_format($totalLoss, 1).' kg', 'icon' => 'alert', 'color' => 'red',    'delta' => null],
            ['label' => 'This month',    'value' => $thisMonth, 'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.roastings.index', compact('roastings', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.roastings.create', $this->formData(new Roasting));
    }

    public function store(StoreRoastingRequest $request): RedirectResponse
    {
        $allocations = $request->input('allocations', []);

        // Single-batch fallback (no overflow triggered)
        if (empty($allocations)) {
            $allocations = [[
                'source_batch' => $request->input('source_batch_single', $request->input('_source_batch_hint')),
                'quantity_in'  => $request->input('quantity_in'),
            ]];
        }

        $totalQty = array_sum(array_column($allocations, 'quantity_in'));
        $loss     = (float) $request->input('loss', 0);
        $created  = [];

        try {
            \DB::transaction(function () use ($request, $allocations, $totalQty, $loss, &$created) {
                foreach ($allocations as $alloc) {
                    $qty       = (float) $alloc['quantity_in'];
                    $batchLoss = $totalQty > 0 ? round($loss * ($qty / $totalQty), 4) : 0;
                    $payload   = $this->mapPayload(array_merge($request->validated(), [
                        'source_batch' => $alloc['source_batch'],
                        'quantity_in'  => $qty,
                        'loss'         => $batchLoss,
                    ]));
                    $created[] = Roasting::create($payload);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['quantity_in' => $e->getMessage()]);
        }

        $count = count($created);
        $msg   = $count > 1 ? "Roasting recorded across {$count} batches." : 'Roasting recorded.';

        return redirect()->route('admin.roastings.index')->with('success', $msg);
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
            $roasting->update($this->mapPayload($request->validated()));
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['raw_material_stock_id' => $e->getMessage()]);
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
        $roastableItems = ProductCatalog::active()->production()->requiresRoasting()->pluck('name');

        // Raw material batches that require roasting and haven't been fully sorted first
        // (items NOT marked requires_sorting go straight from reception to roasting)
        $sortOnlyFirst = ProductCatalog::active()->production()->requiresSorting()->pluck('name');

        // The batch currently selected on this roasting (edit mode) is always kept in
        // its list, bypassing every other filter below, so the form never loses its
        // own selection — even if the batch is now exhausted or its item's catalog
        // flags have since changed.
        $rawStocks = RawMaterialStock::query()
            ->where(function ($q) use ($roasting, $roastableItems, $sortOnlyFirst) {
                $q->where(function ($avail) use ($roastableItems, $sortOnlyFirst) {
                    $avail->where('quantity_in', '>', 0);
                    if ($roastableItems->isNotEmpty()) {
                        $avail->whereIn('item', $roastableItems);
                    }
                    if ($sortOnlyFirst->isNotEmpty()) {
                        $avail->whereNotIn('item', $sortOnlyFirst);
                    }
                });
                if ($roasting->raw_material_stock_id) {
                    $q->orWhere('id', $roasting->raw_material_stock_id);
                }
            })
            ->orderByDesc('date')
            ->get();

        // Sorting batches whose item requires roasting (sorted first, then roasted).
        // Filter on the batch's actual remaining balance (falling back to its total
        // output for the rare row that predates quantity_remaining tracking), not
        // the batch's total output alone — a batch can have output but already be
        // used up.
        $sortingStocks = Sorting::with('rawMaterialStock')
            ->where(function ($q) use ($roasting, $roastableItems) {
                $q->where(function ($avail) use ($roastableItems) {
                    $avail->whereRaw('COALESCE(quantity_remaining, quantity_in - COALESCE(loss, 0)) > 0');
                    if ($roastableItems->isNotEmpty()) {
                        $avail->whereHas('rawMaterialStock', fn ($s) => $s->whereIn('item', $roastableItems));
                    }
                });
                if ($roasting->sorting_id) {
                    $q->orWhere('id', $roasting->sorting_id);
                }
            })
            ->orderByDesc('date')
            ->get();

        // Pre-selected source value for edit
        $selectedSource = old('source_batch',
            $roasting->raw_material_stock_id ? 'raw:' . $roasting->raw_material_stock_id :
            ($roasting->sorting_id           ? 'sorting:' . $roasting->sorting_id : '')
        );

        return [
            'roasting'       => $roasting,
            'rawStocks'      => $rawStocks,
            'sortingStocks'  => $sortingStocks,
            'selectedSource' => $selectedSource,
            'employees'      => Employee::orderBy('full_name')->get(),
        ];
    }

    protected function mapPayload(array $data): array
    {
        $sourceBatch = $data['source_batch'] ?? $data['_source_batch_hint'] ?? '';
        unset($data['source_batch'], $data['_source_batch_hint'], $data['source_type'], $data['allocations']);

        [$type, $id] = array_pad(explode(':', $sourceBatch, 2), 2, null);

        $data['raw_material_stock_id'] = $type === 'raw'     ? $id : null;
        $data['sorting_id']            = $type === 'sorting' ? $id : null;

        return $data;
    }

    protected function availableRawStocks(?int $includeId = null)
    {
        $roastableItems = ProductCatalog::active()->production()->requiresRoasting()->pluck('name');
        $sortOnlyFirst  = ProductCatalog::active()->production()->requiresSorting()->pluck('name');

        return RawMaterialStock::query()
            ->where('quantity_in', '>', 0)
            ->when($roastableItems->isNotEmpty(), fn ($q) => $q->whereIn('item', $roastableItems))
            ->when($sortOnlyFirst->isNotEmpty(),  fn ($q) => $q->whereNotIn('item', $sortOnlyFirst))
            ->when($includeId, fn ($q) => $q->orWhere('id', $includeId))
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
