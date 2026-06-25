<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emballage;
use App\Models\Milling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FlourStockController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'flour');

        // Milling batches with remaining unpackaged flour
        $flourBatches = Milling::with('employee')
            ->where('output_flour', '>', 0)
            ->orderByDesc('date')
            ->get();

        $totalFlour  = (float) Milling::sum('output_flour');
        $totalMilled = (float) Milling::sum('total_mixed_quantity');

        // Packaging records
        $search = $request->string('search')->trim()->toString();
        $packagings = Emballage::query()
            ->with(array_filter([
                'milling',
                'employee',
                Schema::hasColumn('emballages', 'packaging_catalog_id') ? 'packagingCatalog' : null,
            ]))
            ->when($search, fn ($q) => $q->where('packaging_batch_id', 'like', "%{$search}%"))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $packagingSummary = $this->packagingSummary();

        $pageStats = [
            ['label' => 'Flour available', 'value' => number_format($totalFlour, 1).' kg',  'icon' => 'scale',   'color' => 'blue'],
            ['label' => 'Total milled',    'value' => number_format($totalMilled, 1).' kg', 'icon' => 'cog',     'color' => 'sky'],
            ['label' => 'Packaging runs',  'value' => Emballage::count(),                   'icon' => 'package', 'color' => 'green'],
            ['label' => 'Units packed',    'value' => number_format((float) Emballage::sum('item'), 0), 'icon' => 'box', 'color' => 'purple'],
        ];

        return view('admin.flour-stock.index', compact(
            'tab', 'flourBatches', 'totalFlour', 'packagings', 'packagingSummary', 'search', 'pageStats'
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{name: string, total_units: int, total_flour: float, total_damaged: int}>
     */
    private function packagingSummary()
    {
        if (Schema::hasColumn('emballages', 'packaging_catalog_id')) {
            return Emballage::with('packagingCatalog')
                ->selectRaw('packaging_catalog_id, packaging_type, SUM(item) as total_units, SUM(quantity) as total_flour, SUM(damaged) as total_damaged')
                ->groupBy('packaging_catalog_id', 'packaging_type')
                ->get()
                ->map(fn ($row) => [
                    'name' => $row->packagingCatalog?->name ?? ($row->packaging_type ?? 'Unknown'),
                    'total_units' => (int) $row->total_units,
                    'total_flour' => (float) $row->total_flour,
                    'total_damaged' => (int) $row->total_damaged,
                ]);
        }

        return Emballage::query()
            ->selectRaw('packaging_type, SUM(item) as total_units, SUM(quantity) as total_flour, SUM(damaged) as total_damaged')
            ->groupBy('packaging_type')
            ->get()
            ->map(fn ($row) => [
                'name' => strtoupper((string) ($row->packaging_type ?: 'Unknown')),
                'total_units' => (int) $row->total_units,
                'total_flour' => (float) $row->total_flour,
                'total_damaged' => (int) $row->total_damaged,
            ]);
    }
}
