<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emballage;
use App\Models\Milling;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $tab = $request->input('tab', 'packaging');

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
