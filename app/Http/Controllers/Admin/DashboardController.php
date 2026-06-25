<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Milling;
use App\Models\RawMaterialStock;
use App\Models\Roasting;
use App\Models\Sale;
use App\Models\Sorting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // ── Date filter (from chart filter form) ─────────────────────────────
        $from = $request->filled('from') ? $request->input('from') : null;
        $to   = $request->filled('to')   ? $request->input('to')   : null;

        $scoped = fn($q) => $from && $to
            ? $q->whereBetween('date', [$from, $to])
            : $q;

        // ── Pipeline stock levels (always all-time for KPI cards) ────────────
        $rawStock       = (float) RawMaterialStock::sum('quantity_in');
        $rawReceived    = (float) RawMaterialStock::sum('received');
        $rawRejected    = (float) RawMaterialStock::sum('rejected');

        $sortingAvail   = Sorting::all()->sum(fn ($s) => max((float)$s->quantity_in - (float)($s->loss ?? 0), 0));
        $sortingTotal   = (float) Sorting::sum('quantity_in');
        $sortingLoss    = (float) Sorting::sum('loss');

        $roastingAvail  = Roasting::all()->sum(fn ($r) => max((float)$r->quantity_in - (float)($r->loss ?? 0), 0));
        $roastingTotal  = (float) Roasting::sum('quantity_in');
        $roastingLoss   = (float) Roasting::sum('loss');

        $millingOutput  = (float) Milling::sum('output_flour');
        $millingTotal   = (float) Milling::sum('total_mixed_quantity');
        $millingLoss    = (float) Milling::sum('loss');

        $packagingUnits = (float) $scoped(Emballage::query())->sum('item');
        $packagingDmg   = (float) $scoped(Emballage::query())->sum('damaged');
        $packagingRuns  = $scoped(Emballage::query())->count();

        $revenue        = (float) $scoped(Sale::query())->sum('total_price');
        $salesCount     = $scoped(Sale::query())->count();
        $returned       = (float) $scoped(Sale::query())->sum('returned');

        // ── People ───────────────────────────────────────────────────────────
        $employees      = Employee::count();
        $clients        = Client::where('role', 'client')->count();
        $suppliers      = Client::where('role', 'supplier')->count();

        // ── Today's activity ─────────────────────────────────────────────────
        $today = now()->toDateString();
        $todayReceptions  = RawMaterialStock::whereDate('date', $today)->count();
        $todaySortings    = \App\Models\Sorting::whereDate('date', $today)->count();
        $todayRoastings   = \App\Models\Roasting::whereDate('date', $today)->count();
        $todayMillings    = \App\Models\Milling::whereDate('date', $today)->count();
        $todayPackagings  = Emballage::whereDate('date', $today)->count();
        $todaySales       = Sale::whereDate('date', $today)->count();
        $todayRevenue     = (float) Sale::whereDate('date', $today)->sum('total_price');

        // ── This month revenue vs last month ─────────────────────────────────
        $monthRevenue     = (float) Sale::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_price');
        $lastMonthRevenue = (float) Sale::whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('total_price');
        $revenueGrowth    = $lastMonthRevenue > 0
            ? round(($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 1)
            : null;

        // ── Monthly revenue chart (last 12 months) ────────────────────────────
        $monthlyRevenue = collect();
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $monthlyRevenue->push([
                'label'   => $d->format('M Y'),
                'revenue' => (float) Sale::whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('total_price'),
                'units'   => (int) Sale::whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('quantity'),
            ]);
        }

        // ── Monthly packaging chart (last 12 months) ─────────────────────────
        $monthlyPackaging = collect();
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $monthlyPackaging->push([
                'label' => $d->format('M Y'),
                'kg'    => (float) Emballage::whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('quantity'),
                'units' => (float) Emballage::whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('item'),
            ]);
        }

        // ── Pipeline donut data ────────────────────────────────────────────────
        $totalLoss = $rawRejected + $sortingLoss + $roastingLoss + $millingLoss;
        $pipelineDonut = [
            'labels' => ['Rejected','Sort loss','Roast loss','Mill loss','Output flour'],
            'values' => [$rawRejected, $sortingLoss, $roastingLoss, $millingLoss, $millingOutput],
        ];

        // ── Daily this month (for column chart) ──────────────────────────
        $daysInMonth = now()->daysInMonth;
        $dailyPackaging = collect();
        $dailySales     = collect();
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = now()->startOfMonth()->addDays($d - 1)->toDateString();
            $dailyPackaging->push((float) Emballage::whereDate('date', $date)->sum('item'));
            $dailySales->push((float) Sale::whereDate('date', $date)->sum('quantity'));
        }
        $dailyLabels = collect(range(1, $daysInMonth))->map(fn($d) => (string)$d);

        // ── Chart data bundle (used by admin-dashboard.js) ────────────────────
        $chartData = [
            'monthlyRevenue'  => $monthlyRevenue,
            'monthlyPackaging'=> $monthlyPackaging,
            'pipelineDonut'   => $pipelineDonut,
            'dailyLabels'     => $dailyLabels,
            'dailyPackaging'  => $dailyPackaging,
            'dailySales'      => $dailySales,
        ];

        // ── Recent activity ───────────────────────────────────────────────────
        $recentSales = Sale::with(['client', 'employee'])
            ->latest('date')->latest('id')->limit(5)->get();

        $recentStock = RawMaterialStock::with('client')
            ->latest('date')->latest('id')->limit(5)->get();

        $recentPackaging = Emballage::with(['packagingCatalog', 'milling', 'employee'])
            ->latest('date')->latest('id')->limit(5)->get();

        return view('admin.dashboard', compact(
            'rawStock', 'rawReceived', 'rawRejected',
            'sortingAvail', 'sortingTotal', 'sortingLoss',
            'roastingAvail', 'roastingTotal', 'roastingLoss',
            'millingOutput', 'millingTotal', 'millingLoss',
            'packagingUnits', 'packagingDmg', 'packagingRuns',
            'revenue', 'salesCount', 'returned',
            'employees', 'clients', 'suppliers',
            'recentSales', 'recentStock', 'recentPackaging',
            'todayReceptions', 'todaySortings', 'todayRoastings',
            'todayMillings', 'todayPackagings', 'todaySales', 'todayRevenue',
            'monthRevenue', 'lastMonthRevenue', 'revenueGrowth',
            'monthlyRevenue', 'monthlyPackaging', 'pipelineDonut', 'totalLoss',
            'dailyLabels', 'dailyPackaging', 'dailySales',
            'chartData'
        ));
    }
}
