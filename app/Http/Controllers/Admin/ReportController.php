<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emballage;
use App\Models\Milling;
use App\Models\Order;
use App\Models\RawMaterialStock;
use App\Models\Roasting;
use App\Models\Sale;
use App\Models\Sorting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse|Response
    {
        $tab = $request->input('tab', 'packaging');

        if ($tab === 'monthly') {
            return $this->monthlyIndex($request);
        }

        // Date range filter — default: current month
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $packagingRows  = collect();
        $salesRows      = collect();
        $stockRows      = collect();
        $packagingSummary = collect();
        $salesSummary   = [];

        if ($tab === 'packaging') {
            [$packagingRows, $packagingSummary] = $this->packagingReport($from, $to);
        } elseif ($tab === 'sales') {
            [$salesRows, $salesSummary] = $this->salesReport($from, $to);
        } elseif ($tab === 'stock') {
            $stockRows = $this->stockReport($from, $to);
        }

        // CSV export
        if ($request->boolean('export')) {
            return $this->exportCsv($tab, $from, $to, $packagingRows, $salesRows, $stockRows);
        }

        return view('admin.reports.index', compact(
            'tab', 'from', 'to',
            'packagingRows', 'packagingSummary',
            'salesRows', 'salesSummary',
            'stockRows'
        ));
    }

    // -------------------------------------------------------------------------
    // Monthly report — consolidated view of production and business data for
    // a single calendar month, exportable as CSV or PDF.
    // -------------------------------------------------------------------------
    private function monthlyIndex(Request $request): View|StreamedResponse|Response
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month').'-01')
            : now()->startOfMonth();

        $from = $month->copy()->startOfMonth()->startOfDay();
        $to   = $month->copy()->endOfMonth()->endOfDay();

        $monthly = $this->monthlyReport($from, $to);

        if ($request->input('format') === 'pdf') {
            return $this->exportMonthlyPdf($from, $to, $monthly);
        }

        if ($request->boolean('export')) {
            return $this->exportMonthlyCsv($from, $to, $monthly);
        }

        $tab = 'monthly';
        $packagingRows  = collect();
        $salesRows      = collect();
        $stockRows      = collect();
        $packagingSummary = collect();
        $salesSummary   = [];

        return view('admin.reports.index', compact(
            'tab', 'from', 'to', 'month', 'monthly',
            'packagingRows', 'packagingSummary',
            'salesRows', 'salesSummary',
            'stockRows'
        ));
    }

    /**
     * Build the consolidated monthly data set: raw materials received,
     * production pipeline (sorting/roasting/milling), packaging, sales,
     * online shop orders, and current stock levels.
     */
    private function monthlyReport(Carbon $from, Carbon $to): array
    {
        $dateRange = [$from->toDateString(), $to->toDateString()];

        // ---- Raw materials received ----
        $rawMaterials = RawMaterialStock::whereBetween('date', $dateRange)
            ->selectRaw('item, type, SUM(received) as received, SUM(rejected) as rejected, COUNT(*) as batches')
            ->groupBy('item', 'type')
            ->orderBy('item')
            ->get();

        $rawMaterialsTotals = [
            'received' => (float) $rawMaterials->sum('received'),
            'rejected' => (float) $rawMaterials->sum('rejected'),
            'batches'  => (int) $rawMaterials->sum('batches'),
        ];

        // ---- Production pipeline: sorting, roasting, milling ----
        $sorting = Sorting::whereBetween('date', $dateRange)
            ->selectRaw('SUM(quantity_in) as input, SUM(loss) as loss, COUNT(*) as batches')
            ->first();

        $roasting = Roasting::whereBetween('date', $dateRange)
            ->selectRaw('SUM(quantity_in) as input, SUM(loss) as loss, COUNT(*) as batches')
            ->first();

        $milling = Milling::whereBetween('date', $dateRange)
            ->selectRaw('SUM(total_mixed_quantity) as mixed, SUM(loss) as loss, COUNT(*) as batches')
            ->first();

        $production = [
            'sorting'  => [
                'input'   => (float) ($sorting->input ?? 0),
                'loss'    => (float) ($sorting->loss ?? 0),
                'output'  => max((float) ($sorting->input ?? 0) - (float) ($sorting->loss ?? 0), 0),
                'batches' => (int) ($sorting->batches ?? 0),
            ],
            'roasting' => [
                'input'   => (float) ($roasting->input ?? 0),
                'loss'    => (float) ($roasting->loss ?? 0),
                'output'  => max((float) ($roasting->input ?? 0) - (float) ($roasting->loss ?? 0), 0),
                'batches' => (int) ($roasting->batches ?? 0),
            ],
            'milling'  => [
                'input'   => (float) ($milling->mixed ?? 0),
                'loss'    => (float) ($milling->loss ?? 0),
                'output'  => max((float) ($milling->mixed ?? 0) - (float) ($milling->loss ?? 0), 0),
                'batches' => (int) ($milling->batches ?? 0),
            ],
        ];

        // ---- Production pipeline, broken down by raw material item ----
        $productionByItem = $this->productionByItem($dateRange);

        // ---- Packaging, by product ----
        $packagingByProduct = Emballage::with('packagingCatalog')
            ->whereBetween('date', $dateRange)
            ->get()
            ->groupBy(fn ($e) => $e->packagingCatalog?->name ?? strtoupper($e->packaging_type ?? 'Unknown'))
            ->map(fn ($rows, $name) => [
                'name'    => $name,
                'units'   => (float) $rows->sum('item'),
                'kg'      => (float) $rows->sum('quantity'),
                'damaged' => (int) $rows->sum('damaged'),
                'batches' => $rows->count(),
            ])
            ->sortByDesc('kg')
            ->values();

        $packagingTotals = [
            'units'   => (float) $packagingByProduct->sum('units'),
            'kg'      => (float) $packagingByProduct->sum('kg'),
            'damaged' => (int) $packagingByProduct->sum('damaged'),
        ];

        // ---- Sales, by product ----
        $salesByProduct = Sale::whereBetween('date', $dateRange)
            ->selectRaw('item, SUM(quantity) as units, SUM(total_price) as revenue, SUM(returned) as returned, COUNT(*) as txns')
            ->groupBy('item')
            ->orderByDesc('revenue')
            ->get();

        $salesTotals = [
            'units'    => (float) $salesByProduct->sum('units'),
            'revenue'  => (float) $salesByProduct->sum('revenue'),
            'returned' => (float) $salesByProduct->sum('returned'),
        ];

        // ---- Top clients by revenue ----
        $topClients = Sale::whereBetween('date', $dateRange)
            ->whereNotNull('client_id')
            ->with('client')
            ->get()
            ->groupBy('client_id')
            ->map(fn ($rows) => [
                'name'    => $rows->first()->client?->full_name ?? '—',
                'units'   => (float) $rows->sum('quantity'),
                'revenue' => (float) $rows->sum('total_price'),
            ])
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        // ---- Online shop orders ----
        $shopOrdersByStatus = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('order_status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('order_status')
            ->get();

        $shopOrderTotals = [
            'orders'  => (int) $shopOrdersByStatus->sum('count'),
            'revenue' => (float) Order::whereBetween('created_at', [$from, $to])
                ->where('payment_status', Order::PAYMENT_PAID)
                ->sum('total'),
        ];

        // ---- Stock levels as of the end of the selected month (not "live now") ----
        $rawStockAsOf      = $this->rawMaterialBalanceAsOf($to);
        $flourStockAsOf    = $this->flourBalanceAsOf($to);
        $packagedStockAsOf = $this->packagedStockBalanceAsOf($to);

        return compact(
            'rawMaterials', 'rawMaterialsTotals',
            'production', 'productionByItem',
            'packagingByProduct', 'packagingTotals',
            'salesByProduct', 'salesTotals', 'topClients',
            'shopOrdersByStatus', 'shopOrderTotals',
            'rawStockAsOf', 'flourStockAsOf', 'packagedStockAsOf'
        );
    }

    /**
     * Production pipeline broken down per raw material item (e.g. Maize,
     * Sorghum) instead of a single stage-wide total. Sorting and roasting
     * batches each draw from exactly one raw material stock (directly, or
     * for roasting via a sorting batch), so their per-item figures are
     * exact. A milling batch can mix several items together under one
     * batch-level loss figure, so each item's loss/output share is
     * allocated proportionally to its share of the batch's milled weight.
     *
     * Milling item rows also include additives (e.g. sugar) that are mixed
     * in but flagged "excludes_from_milled_weight" — unlike
     * production['milling'], which reports actual flour weight only, this
     * breakdown's totals are the literal sum of the rows shown, so the
     * table is internally consistent on its own.
     */
    private function productionByItem(array $dateRange): array
    {
        $sortingByItem = Sorting::join('raw_material_stocks', 'sortings.raw_material_stock_id', '=', 'raw_material_stocks.id')
            ->whereBetween('sortings.date', $dateRange)
            ->selectRaw('raw_material_stocks.item as item, SUM(sortings.quantity_in) as input, SUM(sortings.loss) as loss, COUNT(*) as batches')
            ->groupBy('raw_material_stocks.item')
            ->get()
            ->map(fn ($row) => $this->pipelineRow($row->item, (float) $row->input, (float) $row->loss, (int) $row->batches))
            ->sortBy('item')
            ->values();

        $roastingDirect = Roasting::join('raw_material_stocks', 'roastings.raw_material_stock_id', '=', 'raw_material_stocks.id')
            ->whereBetween('roastings.date', $dateRange)
            ->selectRaw('raw_material_stocks.item as item, SUM(roastings.quantity_in) as input, SUM(roastings.loss) as loss, COUNT(*) as batches')
            ->groupBy('raw_material_stocks.item')
            ->get();

        $roastingViaSorting = Roasting::join('sortings', 'roastings.sorting_id', '=', 'sortings.id')
            ->join('raw_material_stocks', 'sortings.raw_material_stock_id', '=', 'raw_material_stocks.id')
            ->whereBetween('roastings.date', $dateRange)
            ->selectRaw('raw_material_stocks.item as item, SUM(roastings.quantity_in) as input, SUM(roastings.loss) as loss, COUNT(*) as batches')
            ->groupBy('raw_material_stocks.item')
            ->get();

        $roastingByItem = $roastingDirect->concat($roastingViaSorting)
            ->groupBy('item')
            ->map(fn ($rows, $item) => $this->pipelineRow($item, (float) $rows->sum('input'), (float) $rows->sum('loss'), (int) $rows->sum('batches')))
            ->sortBy('item')
            ->values();

        $millingBatchCount = Milling::whereBetween('date', $dateRange)->count();
        $millingTotals = [];
        Milling::whereBetween('date', $dateRange)->get()->each(function (Milling $milling) use (&$millingTotals) {
            $ingredients = $milling->resolvedIngredients();
            $batchMixed  = (float) $ingredients->reject(fn ($i) => $i['excluded_from_weight'])->sum('quantity');
            $batchLoss   = (float) $milling->loss;

            foreach ($ingredients as $ing) {
                $item = $ing['item_name'];
                $qty  = (float) $ing['quantity'];
                $itemLoss = (! $ing['excluded_from_weight'] && $batchMixed > 0)
                    ? $batchLoss * ($qty / $batchMixed)
                    : 0.0;

                $millingTotals[$item] ??= ['input' => 0.0, 'loss' => 0.0, 'batches' => []];
                $millingTotals[$item]['input']  += $qty;
                $millingTotals[$item]['loss']   += $itemLoss;
                $millingTotals[$item]['batches'][$milling->id] = true;
            }
        });

        $millingByItem = collect($millingTotals)
            ->map(fn ($t, $item) => $this->pipelineRow($item, $t['input'], $t['loss'], count($t['batches'])))
            ->sortBy('item')
            ->values();

        return [
            'sorting'  => ['rows' => $sortingByItem, 'totals' => $this->pipelineTotals($sortingByItem, $sortingByItem->sum('batches'))],
            'roasting' => ['rows' => $roastingByItem, 'totals' => $this->pipelineTotals($roastingByItem, $roastingByItem->sum('batches'))],
            'milling'  => ['rows' => $millingByItem, 'totals' => $this->pipelineTotals($millingByItem, $millingBatchCount)],
        ];
    }

    private function pipelineRow(string $item, float $input, float $loss, int $batches): array
    {
        return [
            'item'    => $item,
            'input'   => $input,
            'loss'    => $loss,
            'output'  => max($input - $loss, 0),
            'batches' => $batches,
        ];
    }

    /**
     * Totals for a stage's item rows: input/loss/output are the literal sum
     * of what's shown (so the table always reconciles with its own Total
     * row), while batches is passed in separately since summing per-item
     * batch counts double-counts a milling batch that mixed several items.
     */
    private function pipelineTotals(\Illuminate\Support\Collection $rows, int $batches): array
    {
        return [
            'input'   => (float) $rows->sum('input'),
            'loss'    => (float) $rows->sum('loss'),
            'output'  => (float) $rows->sum('output'),
            'batches' => $batches,
        ];
    }

    /**
     * Raw material balance per item as of a given date: net received minus
     * everything drawn from it (sorting, roasting, milling, and packaging-
     * material draws — primary, overflow, and inner units), each filtered
     * by the consuming record's own date. Mirrors the deduction logic in
     * the Sorting/Roasting/Milling/Emballage model events, but scoped to
     * "as of" a point in time instead of "right now".
     */
    private function rawMaterialBalanceAsOf(Carbon $asOf): \Illuminate\Support\Collection
    {
        $asOfDate = $asOf->toDateString();

        $received = RawMaterialStock::where('date', '<=', $asOfDate)
            ->selectRaw('item, SUM(received) - SUM(rejected) as net')
            ->groupBy('item')
            ->pluck('net', 'item');

        $sortingConsumed = Sorting::join('raw_material_stocks', 'sortings.raw_material_stock_id', '=', 'raw_material_stocks.id')
            ->where('sortings.date', '<=', $asOfDate)
            ->selectRaw('raw_material_stocks.item as item, SUM(sortings.quantity_in) as qty')
            ->groupBy('raw_material_stocks.item')
            ->pluck('qty', 'item');

        // Only roastings sourced directly from raw material draw from raw_material_stocks —
        // roastings sourced from a sorting batch draw from that sorting's remaining stock instead.
        $roastingConsumed = Roasting::join('raw_material_stocks', 'roastings.raw_material_stock_id', '=', 'raw_material_stocks.id')
            ->where('roastings.date', '<=', $asOfDate)
            ->selectRaw('raw_material_stocks.item as item, SUM(roastings.quantity_in) as qty')
            ->groupBy('raw_material_stocks.item')
            ->pluck('qty', 'item');

        $rawStockItemMap = RawMaterialStock::pluck('item', 'id');

        $millingConsumed = [];
        Milling::where('date', '<=', $asOfDate)->select('items')->get()->each(function ($milling) use (&$millingConsumed, $rawStockItemMap) {
            foreach ($milling->items ?? [] as $it) {
                if (($it['source'] ?? '') !== 'raw') {
                    continue;
                }
                $item = $rawStockItemMap[(int) ($it['stock_id'] ?? 0)] ?? null;
                if ($item) {
                    $millingConsumed[$item] = ($millingConsumed[$item] ?? 0) + (float) ($it['quantity'] ?? 0);
                }
            }
        });

        $packagingMaterialConsumed = [];
        Emballage::with('packagingCatalog')
            ->where('date', '<=', $asOfDate)
            ->get()
            ->each(function (Emballage $e) use (&$packagingMaterialConsumed, $rawStockItemMap) {
                $primaryUnits = $e->primaryPackagingUnits();
                if ($e->raw_material_stock_id && $primaryUnits > 0) {
                    $item = $rawStockItemMap[$e->raw_material_stock_id] ?? null;
                    if ($item) {
                        $packagingMaterialConsumed[$item] = ($packagingMaterialConsumed[$item] ?? 0) + $primaryUnits;
                    }
                }

                foreach ($e->packaging_overflow ?? [] as $ov) {
                    $stockId = $ov['stock_id'] ?? null;
                    $units   = (float) ($ov['units'] ?? 0);
                    if ($stockId && $units > 0) {
                        $item = $rawStockItemMap[$stockId] ?? null;
                        if ($item) {
                            $packagingMaterialConsumed[$item] = ($packagingMaterialConsumed[$item] ?? 0) + $units;
                        }
                    }
                }

                $innerUnits = $e->innerUnitsTotal();
                if ($innerUnits > 0 && $e->inner_stock_id) {
                    $item = $rawStockItemMap[$e->inner_stock_id] ?? null;
                    if ($item) {
                        $packagingMaterialConsumed[$item] = ($packagingMaterialConsumed[$item] ?? 0) + $innerUnits;
                    }
                }
            });

        $items = collect($received->keys())
            ->merge($sortingConsumed->keys())
            ->merge($roastingConsumed->keys())
            ->merge(array_keys($millingConsumed))
            ->merge(array_keys($packagingMaterialConsumed))
            ->unique()
            ->sort()
            ->values();

        return $items
            ->map(function ($item) use ($received, $sortingConsumed, $roastingConsumed, $millingConsumed, $packagingMaterialConsumed) {
                $balance = (float) ($received[$item] ?? 0)
                    - (float) ($sortingConsumed[$item] ?? 0)
                    - (float) ($roastingConsumed[$item] ?? 0)
                    - (float) ($millingConsumed[$item] ?? 0)
                    - (float) ($packagingMaterialConsumed[$item] ?? 0);

                return (object) ['item' => $item, 'remaining' => $balance];
            })
            ->filter(fn ($row) => $row->remaining > 0.01)
            ->sortBy('item')
            ->values();
    }

    /**
     * Unpackaged milled flour as of a given date: flour produced (mixed minus
     * loss) by millings up to that date, minus flour drawn by packaging
     * records up to that date. Mirrors Milling::output_flour's live deduction
     * in Emballage's model events, scoped to "as of" a point in time.
     */
    private function flourBalanceAsOf(Carbon $asOf): float
    {
        $asOfDate = $asOf->toDateString();

        $produced = (float) Milling::where('date', '<=', $asOfDate)
            ->selectRaw('COALESCE(SUM(total_mixed_quantity), 0) - COALESCE(SUM(loss), 0) as produced')
            ->value('produced');

        $packaged = (float) Emballage::where('date', '<=', $asOfDate)->sum('quantity');

        return max($produced - $packaged, 0);
    }

    /**
     * Packaged product balance per product as of a given date: units
     * packaged up to that date minus units sold net of returns up to that
     * date. Mirrors stockReport()'s opening-balance calculation, scoped to
     * an arbitrary "as of" date instead of a report window's start.
     */
    private function packagedStockBalanceAsOf(Carbon $asOf): \Illuminate\Support\Collection
    {
        $asOfDate = $asOf->toDateString();

        $packagedIn = Emballage::with('packagingCatalog')
            ->where('date', '<=', $asOfDate)
            ->get()
            ->groupBy(fn ($e) => $e->packagingCatalog?->name ?? strtoupper($e->packaging_type ?? 'Unknown'))
            ->map(fn ($rows) => (float) $rows->sum('item'));

        $soldOut = Sale::where('date', '<=', $asOfDate)
            ->selectRaw('item, SUM(quantity) - SUM(returned) as net_sold')
            ->groupBy('item')
            ->pluck('net_sold', 'item');

        $names = collect($packagedIn->keys())->merge($soldOut->keys())->unique()->sort()->values();

        return $names
            ->map(fn ($name) => [
                'name'  => $name,
                'units' => (float) ($packagedIn[$name] ?? 0) - (float) ($soldOut[$name] ?? 0),
            ])
            ->filter(fn ($row) => $row['units'] > 0.01)
            ->sortByDesc('units')
            ->values();
    }

    private function exportMonthlyCsv(Carbon $from, Carbon $to, array $m): StreamedResponse
    {
        $filename = "shimirwa-monthly-report-{$from->format('Y-m')}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($from, $to, $m) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Shimirwa Ltd — Monthly Report', $from->format('F Y')]);
            fputcsv($out, []);

            fputcsv($out, ['RAW MATERIALS RECEIVED']);
            fputcsv($out, ['Item', 'Type', 'Received (kg)', 'Rejected (kg)', 'Batches']);
            foreach ($m['rawMaterials'] as $row) {
                fputcsv($out, [$row->item, $row->type, number_format($row->received, 3), number_format($row->rejected, 3), $row->batches]);
            }
            fputcsv($out, ['Total', '', number_format($m['rawMaterialsTotals']['received'], 3), number_format($m['rawMaterialsTotals']['rejected'], 3), $m['rawMaterialsTotals']['batches']]);
            fputcsv($out, []);

            fputcsv($out, ['PRODUCTION']);
            fputcsv($out, ['Stage', 'Item', 'Input (kg)', 'Loss (kg)', 'Output (kg)', 'Batches']);
            foreach (['sorting' => 'Sorting', 'roasting' => 'Roasting', 'milling' => 'Milling (flour)'] as $key => $label) {
                foreach ($m['productionByItem'][$key]['rows'] as $row) {
                    fputcsv($out, [$label, $row['item'], number_format($row['input'], 3), number_format($row['loss'], 3), number_format($row['output'], 3), $row['batches']]);
                }
                $t = $m['productionByItem'][$key]['totals'];
                fputcsv($out, [$label, 'Total', number_format($t['input'], 3), number_format($t['loss'], 3), number_format($t['output'], 3), $t['batches']]);
            }
            fputcsv($out, []);

            fputcsv($out, ['PACKAGING']);
            fputcsv($out, ['Product', 'Units Packed', 'Kg Packed', 'Damaged', 'Batches']);
            foreach ($m['packagingByProduct'] as $row) {
                fputcsv($out, [$row['name'], number_format($row['units']), number_format($row['kg'], 3), $row['damaged'], $row['batches']]);
            }
            fputcsv($out, ['Total', number_format($m['packagingTotals']['units']), number_format($m['packagingTotals']['kg'], 3), $m['packagingTotals']['damaged'], '']);
            fputcsv($out, []);

            fputcsv($out, ['SALES']);
            fputcsv($out, ['Product', 'Units Sold', 'Revenue (RWF)', 'Returned', 'Transactions']);
            foreach ($m['salesByProduct'] as $row) {
                fputcsv($out, [$row->item, number_format($row->units), number_format($row->revenue, 2), number_format($row->returned), $row->txns]);
            }
            fputcsv($out, ['Total', number_format($m['salesTotals']['units']), number_format($m['salesTotals']['revenue'], 2), number_format($m['salesTotals']['returned']), '']);
            fputcsv($out, []);

            fputcsv($out, ['TOP CLIENTS']);
            fputcsv($out, ['Client', 'Units', 'Revenue (RWF)']);
            foreach ($m['topClients'] as $row) {
                fputcsv($out, [$row['name'], number_format($row['units']), number_format($row['revenue'], 2)]);
            }
            fputcsv($out, []);

            fputcsv($out, ['ONLINE SHOP ORDERS']);
            fputcsv($out, ['Status', 'Orders', 'Revenue (RWF)']);
            foreach ($m['shopOrdersByStatus'] as $row) {
                fputcsv($out, [ucfirst($row->order_status), $row->count, number_format($row->revenue ?? 0, 2)]);
            }
            fputcsv($out, ['Total orders', $m['shopOrderTotals']['orders'], number_format($m['shopOrderTotals']['revenue'], 2).' (paid)']);
            fputcsv($out, []);

            fputcsv($out, ['STOCK LEVELS AS OF '.strtoupper($to->format('d M Y'))]);
            fputcsv($out, ['Raw materials']);
            fputcsv($out, ['Item', 'Remaining (kg)']);
            foreach ($m['rawStockAsOf'] as $row) {
                fputcsv($out, [$row->item, number_format($row->remaining, 3)]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Unpackaged milled flour (kg)', number_format($m['flourStockAsOf'], 3)]);
            fputcsv($out, []);
            fputcsv($out, ['Packaged products']);
            fputcsv($out, ['Product', 'Units in stock']);
            foreach ($m['packagedStockAsOf'] as $row) {
                fputcsv($out, [$row['name'], number_format($row['units'])]);
            }

            fclose($out);
        }, 200, $headers);
    }

    private function exportMonthlyPdf(Carbon $from, Carbon $to, array $monthly): Response
    {
        $pdf = Pdf::loadView('admin.reports.monthly-pdf', [
            'from'        => $from,
            'to'          => $to,
            'monthly'     => $monthly,
            'logoDataUri' => $this->logoDataUri(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("shimirwa-monthly-report-{$from->format('Y-m')}.pdf");
    }

    /**
     * Embed the configured admin logo as a base64 data URI so dompdf can
     * render it reliably regardless of local file/chroot resolution.
     */
    private function logoDataUri(): ?string
    {
        $logo = config('admin.logo');
        if (! $logo || str_starts_with($logo, 'http')) {
            return null;
        }

        $path = public_path($logo);
        if (! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'svg'         => 'image/svg+xml',
            'webp'        => 'image/webp',
            default       => null,
        };
        if (! $mime) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    private function exportCsv(
        string $tab,
        Carbon $from,
        Carbon $to,
        $packagingRows,
        $salesRows,
        $stockRows
    ): StreamedResponse {
        $filename = "shimirwa-{$tab}-report-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($tab, $from, $to, $packagingRows, $salesRows, $stockRows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens it correctly
            fwrite($out, "\xEF\xBB\xBF");

            if ($tab === 'packaging') {
                fputcsv($out, ['Shimirwa — Packaging Log', $from->format('d M Y').' to '.$to->format('d M Y')]);
                fputcsv($out, []);
                fputcsv($out, ['Date', 'Initial Qty (kg)', 'Flour In (kg)', 'Packaging Batch', 'Type', 'Units Packed', 'Kg Packed', 'Damaged', 'Milling Batch', 'Employee', 'Unpackaged (kg)']);

                foreach ($packagingRows as $row) {
                    if ($row['batches']->isEmpty()) {
                        fputcsv($out, [
                            $row['date'],
                            number_format($row['initial_qty'], 3),
                            number_format($row['qty_in'], 3),
                            '', '', '', '', '', '', '',
                            number_format($row['qty_unpacked'], 3),
                        ]);
                    } else {
                        $first = true;
                        foreach ($row['batches'] as $b) {
                            fputcsv($out, [
                                $first ? $row['date'] : '',
                                $first ? number_format($row['initial_qty'], 3) : '',
                                $first ? number_format($row['qty_in'], 3) : '',
                                $b['packaging_batch_id'],
                                $b['catalog_name'],
                                $b['units'],
                                number_format($b['kg'], 3),
                                $b['damaged'],
                                $b['milling_batch'],
                                $b['employee'],
                                $first ? number_format($row['qty_unpacked'], 3) : '',
                            ]);
                            $first = false;
                        }
                    }
                }

            } elseif ($tab === 'sales') {
                fputcsv($out, ['Shimirwa — Sales Log', $from->format('d M Y').' to '.$to->format('d M Y')]);
                fputcsv($out, []);
                fputcsv($out, ['Date', 'Opening Stock', 'Packaged In', 'Product', 'Client', 'Units Sold', 'Returned', 'Balance', 'Revenue (RWF)']);

                foreach ($salesRows as $row) {
                    if ($row['sales']->isEmpty()) {
                        fputcsv($out, [
                            $row['date'],
                            $row['initial_stock'],
                            $row['entered'],
                            '', '', '', '',
                            $row['balance'],
                            number_format($row['revenue'], 2),
                        ]);
                    } else {
                        $first = true;
                        foreach ($row['sales'] as $s) {
                            fputcsv($out, [
                                $first ? $row['date'] : '',
                                $first ? $row['initial_stock'] : '',
                                $first ? $row['entered'] : '',
                                $s['item'],
                                $s['client'],
                                $s['units'],
                                $s['returned'],
                                $first ? $row['balance'] : '',
                                number_format($s['revenue'], 2),
                            ]);
                            $first = false;
                        }
                    }
                }

            } elseif ($tab === 'stock') {
                fputcsv($out, ['Shimirwa — Final Product Stock', $from->format('d M Y').' to '.$to->format('d M Y')]);

                foreach ($stockRows as $itemRow) {
                    fputcsv($out, []);
                    fputcsv($out, [$itemRow['item'], 'Opening balance: '.$itemRow['opening_balance']]);
                    fputcsv($out, ['Date', 'In (ENTRE)', 'Out (SORTIE)', 'Returned', 'Balance (SOLDE)']);
                    foreach ($itemRow['days'] as $day) {
                        fputcsv($out, [
                            $day['date'],
                            $day['entered'],
                            $day['sold'],
                            $day['returned'],
                            $day['balance'],
                        ]);
                    }
                    fputcsv($out, ['Final balance', '', '', '', $itemRow['final_balance']]);
                }
            }

            fclose($out);
        }, 200, $headers);
    }

    // -------------------------------------------------------------------------
    // Packaging daily log
    // -------------------------------------------------------------------------
    private function packagingReport(Carbon $from, Carbon $to): array
    {
        // All packaging records in range, ordered by date
        $emballages = Emballage::with(['milling', 'packagingCatalog', 'employee'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Milling batches in range (flour produced each day)
        $millingsByDate = Milling::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($m) => Carbon::parse($m->date)->toDateString());

        // Group packaging records by date
        $byDate = $emballages->groupBy(fn ($e) => Carbon::parse($e->date)->toDateString());

        // Build all dates in range
        $allDates = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $allDates->push($cursor->toDateString());
            $cursor->addDay();
        }

        // Rolling "initial qty" = flour available at start of each day
        // For each date, initial = previous day's remaining flour across all millings
        // We compute this as: cumulative milled up to (but not including) this date
        //                     minus cumulative packaged up to (but not including) this date
        // However, we only track within the report window. We compute a running balance.
        $rows = collect();

        // Pre-compute: total milled before the range starts (to get opening balance)
        $openingMilled   = (float) Milling::where('date', '<', $from->toDateString())->sum('output_flour');
        $openingPackaged = (float) Emballage::where('date', '<', $from->toDateString())->sum('quantity');
        $runningAvailable = $openingMilled + $openingPackaged; // output_flour already accounts for packaged

        // Actually output_flour on Milling is the CURRENT remaining flour on each milling batch.
        // To build a daily report we use simpler approach:
        // initial_qty per day = sum of output_flour on all millings as of start of that day
        // We compute this directly: for each date, group the packagings and millings.

        // Simpler: just emit one row per packaging record, with the day's milled qty alongside.
        // If a day has millings but no packagings, show a milling-only row.
        // Track running unpackaged per milling batch.

        $millingFlourByBatch = [];

        // Load all milling batches to date to know their starting output_flour
        // output_flour already reflects current remaining. For historical we need original.
        // original output = total_mixed_quantity - loss
        $allMillings = Milling::orderBy('date')->orderBy('id')->get();
        foreach ($allMillings as $m) {
            $original = max(0, (float)$m->total_mixed_quantity - (float)($m->loss ?? 0));
            $millingFlourByBatch[$m->id] = [
                'batch'    => $m->batch_number,
                'date'     => Carbon::parse($m->date)->toDateString(),
                'original' => $original,
                'packed'   => 0.0,
            ];
        }

        // Accumulate packaging against each milling batch (all history, not just range)
        $allEmballages = Emballage::orderBy('date')->orderBy('id')->get();
        foreach ($allEmballages as $e) {
            if ($e->milling_id && isset($millingFlourByBatch[$e->milling_id])) {
                $millingFlourByBatch[$e->milling_id]['packed'] += (float)$e->quantity;
            }
        }

        // Now build rows per date in range
        foreach ($allDates as $date) {
            $dayEmballages = $byDate->get($date, collect());
            $dayMillings   = $millingsByDate->get($date, collect());

            if ($dayEmballages->isEmpty() && $dayMillings->isEmpty()) {
                continue; // skip empty days
            }

            // Total flour milled this day
            $qtyIn = $dayMillings->sum(fn ($m) => (float) $m->total_mixed_quantity);

            // Flour packaged today
            $qtyPacked = $dayEmballages->sum(fn ($e) => (float) $e->quantity);

            // Units packed today (for display)
            $unitsPacked = $dayEmballages->sum(fn ($e) => (float) $e->item);

            // Initial qty = total remaining flour (all batches) at start of this day
            // = sum over all milling batches where batch date <= this date of (original - packed before today)
            $initialQty = 0.0;
            foreach ($millingFlourByBatch as $bid => $bdata) {
                if ($bdata['date'] < $date) {
                    // Flour packed before today from this batch
                    $packedBeforeToday = (float) Emballage::where('milling_id', $bid)
                        ->where('date', '<', $date)
                        ->sum('quantity');
                    $remaining = max(0, $bdata['original'] - $packedBeforeToday);
                    $initialQty += $remaining;
                }
            }

            // Remaining after today's packaging
            $qtyUnpacked = max(0, $initialQty + $qtyIn - $qtyPacked);

            // Collect the packaging batches details for this day
            $batchDetails = $dayEmballages->map(fn ($e) => [
                'packaging_batch_id' => $e->packaging_batch_id,
                'catalog_name'       => $e->packagingCatalog?->name ?? strtoupper($e->packaging_type ?? '—'),
                'units'              => (float) $e->item,
                'kg'                 => (float) $e->quantity,
                'damaged'            => (int) $e->damaged,
                'milling_batch'      => $e->milling?->batch_number ?? '—',
                'employee'           => $e->employee?->full_name ?? '—',
            ]);

            $rows->push([
                'date'        => $date,
                'initial_qty' => $initialQty,
                'qty_in'      => $qtyIn,
                'qty_packed'  => $qtyPacked,
                'units_packed'=> $unitsPacked,
                'qty_unpacked'=> $qtyUnpacked,
                'millings'    => $dayMillings,
                'batches'     => $batchDetails,
            ]);
        }

        // Summary totals
        $summary = [
            'total_packed'  => $rows->sum('qty_packed'),
            'total_units'   => $rows->sum('units_packed'),
            'days_active'   => $rows->count(),
            'current_stock' => (float) Milling::sum('output_flour'),
        ];

        return [$rows, $summary];
    }

    // -------------------------------------------------------------------------
    // Sales daily log
    // -------------------------------------------------------------------------
    private function salesReport(Carbon $from, Carbon $to): array
    {
        $sales = Sale::with(['client', 'employee'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $byDate = $sales->groupBy(fn ($s) => Carbon::parse($s->date)->toDateString());

        // Packaging entries per day (stock additions via emballage)
        $emballagesByDate = Emballage::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($e) => Carbon::parse($e->date)->toDateString());

        // All dates
        $allDates = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $allDates->push($cursor->toDateString());
            $cursor->addDay();
        }

        // Opening stock of final products (units) = packaged before range - sold before range
        $openingIn  = (float) Emballage::where('date', '<', $from->toDateString())->sum('item');
        $openingSold = (float) Sale::where('date', '<', $from->toDateString())->sum('quantity');
        $runningStock = $openingIn - $openingSold;

        $rows = collect();

        foreach ($allDates as $date) {
            $daySales    = $byDate->get($date, collect());
            $dayPackaged = $emballagesByDate->get($date, collect());

            if ($daySales->isEmpty() && $dayPackaged->isEmpty()) {
                continue;
            }

            $initialStock = $runningStock;

            // Additions from packaging
            $entered = $dayPackaged->sum(fn ($e) => (float) $e->item);

            // Units sold today
            $sold = $daySales->sum(fn ($s) => (int) $s->quantity);

            // Revenue today
            $revenue = $daySales->sum(fn ($s) => (float) $s->total_price);

            // Returns today
            $returned = $daySales->sum(fn ($s) => (int) ($s->returned ?? 0));

            $runningStock = $initialStock + $entered - $sold + $returned;

            $saleDetails = $daySales->map(fn ($s) => [
                'item'       => $s->item,
                'client'     => $s->client?->full_name ?? '—',
                'units'      => (int) $s->quantity,
                'revenue'    => (float) $s->total_price,
                'returned'   => (int) ($s->returned ?? 0),
                'employee'   => $s->employee?->full_name ?? '—',
            ]);

            $rows->push([
                'date'          => $date,
                'initial_stock' => $initialStock,
                'entered'       => $entered,
                'sold'          => $sold,
                'returned'      => $returned,
                'balance'       => $runningStock,
                'revenue'       => $revenue,
                'sales'         => $saleDetails,
            ]);
        }

        $summary = [
            'total_sold'    => $rows->sum('sold'),
            'total_revenue' => $rows->sum('revenue'),
            'total_returned'=> $rows->sum('returned'),
            'current_stock' => $runningStock ?? 0,
            'days_active'   => $rows->count(),
        ];

        return [$rows, $summary];
    }

    // -------------------------------------------------------------------------
    // Combined stock of final products (packaging in + sales out, daily balance)
    // -------------------------------------------------------------------------
    private function stockReport(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        // This gives the spreadsheet-style view combining both packaging inputs and sales outputs
        // grouped by product/item type

        $emballages = Emballage::with(['packagingCatalog'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')->get();

        $sales = Sale::with(['client'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')->get();

        // Collect all unique item names from both sides
        $itemNames = collect();
        $emballages->each(fn ($e) => $itemNames->push($e->packagingCatalog?->name ?? ($e->packaging_type ?? 'Unknown')));
        $sales->each(fn ($s) => $itemNames->push($s->item));
        $itemNames = $itemNames->unique()->sort()->values();

        $rows = collect();

        foreach ($itemNames as $itemName) {
            // Opening balance before range
            $openingIn = Emballage::where('date', '<', $from->toDateString())
                ->where(function ($q) use ($itemName) {
                    $q->whereHas('packagingCatalog', fn ($p) => $p->where('name', $itemName))
                      ->orWhere('packaging_type', $itemName);
                })->sum('item');

            $openingOut = Sale::where('date', '<', $from->toDateString())
                ->where('item', $itemName)
                ->sum('quantity');

            $openingBalance = (float)$openingIn - (float)$openingOut;

            // Daily movements
            $cursor = $from->copy()->startOfDay();
            $balance = $openingBalance;
            $dayRows = collect();

            while ($cursor->lte($to)) {
                $dateStr = $cursor->toDateString();

                $dayIn = $emballages->filter(function ($e) use ($dateStr, $itemName) {
                    $name = $e->packagingCatalog?->name ?? ($e->packaging_type ?? 'Unknown');
                    return $name === $itemName && Carbon::parse($e->date)->toDateString() === $dateStr;
                })->sum(fn ($e) => (float) $e->item);

                $daySales = $sales->filter(fn ($s) => $s->item === $itemName && Carbon::parse($s->date)->toDateString() === $dateStr);
                $dayOut   = $daySales->sum(fn ($s) => (int) $s->quantity);
                $dayRet   = $daySales->sum(fn ($s) => (int) ($s->returned ?? 0));

                if ($dayIn > 0 || $dayOut > 0 || $dayRet > 0) {
                    $balance += $dayIn - $dayOut + $dayRet;
                    $dayRows->push([
                        'date'    => $dateStr,
                        'entered' => $dayIn,
                        'sold'    => $dayOut,
                        'returned'=> $dayRet,
                        'balance' => $balance,
                    ]);
                } else {
                    $cursor->addDay();
                    continue;
                }

                $cursor->addDay();
            }

            if ($dayRows->isNotEmpty()) {
                $rows->push([
                    'item'            => $itemName,
                    'opening_balance' => $openingBalance,
                    'days'            => $dayRows,
                    'final_balance'   => $balance,
                ]);
            }
        }

        return $rows;
    }
}
