@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of inventory, production, and sales')

@section('content')

{{-- ══ ROW 1 — Welcome + 3 KPI tiles (all same row, grouped) ══ --}}
<div class="db-top-row mb-4">

    <div class="db-welcome-card">
        <div class="db-welcome-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div class="db-welcome-body">
            <p class="db-welcome-name">{{ auth()->user()->name }}</p>
            <p class="db-welcome-sub">Welcome back to Shimirwa IMS</p>
            <p class="db-welcome-date">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="db-welcome-meta">
            <div class="db-welcome-meta-item">
                <span class="db-welcome-meta-val">{{ $todaySales }}</span>
                <span class="db-welcome-meta-label">Sales today</span>
            </div>
            <div class="db-welcome-meta-item">
                <span class="db-welcome-meta-val">{{ $todayPackagings }}</span>
                <span class="db-welcome-meta-label">Packaged today</span>
            </div>
        </div>
    </div>

    {{-- Flour available --}}
    <div class="db-kpi-tile db-kpi-tile--blue">
        <div class="db-kpi-header">
            <div>
                <div class="db-kpi-tile-label">Flour Available</div>
                <div class="db-kpi-tile-value">{{ number_format($millingOutput, 1) }}</div>
                <div class="db-kpi-tile-unit">kg unpackaged</div>
            </div>
            <span class="db-kpi-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
        </div>
        <div class="db-kpi-tile-footer">
            <span>{{ number_format($millingLoss, 1) }} kg milling loss</span>
            <span class="db-kpi-badge db-kpi-badge--neu">All time</span>
        </div>
    </div>

    {{-- Packaged units --}}
    <div class="db-kpi-tile db-kpi-tile--mid">
        <div class="db-kpi-header">
            <div>
                <div class="db-kpi-tile-label">Packaged Units</div>
                <div class="db-kpi-tile-value">{{ number_format($packagingUnits, 0) }}</div>
                <div class="db-kpi-tile-unit">total units</div>
            </div>
            <span class="db-kpi-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
            </span>
        </div>
        <div class="db-kpi-tile-footer">
            <span>{{ $packagingRuns }} runs · {{ $packagingDmg }} damaged</span>
            <span class="db-kpi-badge db-kpi-badge--neu">All time</span>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="db-kpi-tile db-kpi-tile--light">
        <div class="db-kpi-header">
            <div>
                <div class="db-kpi-tile-label">Total Revenue</div>
                <div class="db-kpi-tile-value">{{ number_format($revenue, 0) }}</div>
                <div class="db-kpi-tile-unit">RWF</div>
            </div>
            <span class="db-kpi-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </span>
        </div>
        <div class="db-kpi-tile-footer">
            <span>{{ $salesCount }} sales</span>
            @if ($revenueGrowth !== null)
                <span class="db-kpi-badge {{ $revenueGrowth >= 0 ? 'db-kpi-badge--up' : 'db-kpi-badge--down' }}">
                    {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}% vs last month
                </span>
            @else
                <span class="db-kpi-badge db-kpi-badge--neu">This month</span>
            @endif
        </div>
    </div>

</div>

{{-- ══ ROW 2 — Area chart (left, large) + Donut (right, fitted) ══ --}}
<div class="db-charts-row mb-4">

    {{-- Monthly area chart --}}
    <div class="admin-card db-chart-main">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">Monthly activity</h2>
                <p class="text-xs mt-0.5" style="color:var(--admin-text-muted)">Revenue &amp; packaging — last 12 months</p>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem">
                {{-- Date range filter --}}
                <form method="GET" action="{{ route('admin.dashboard') }}" style="display:flex;align-items:center;gap:0.375rem">
                    <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->toDateString()) }}"
                           class="admin-input" style="padding:0.3rem 0.5rem;font-size:0.75rem;height:auto;width:auto">
                    <span style="font-size:0.75rem;color:var(--admin-text-muted)">→</span>
                    <input type="date" name="to" value="{{ request('to', now()->toDateString()) }}"
                           class="admin-input" style="padding:0.3rem 0.5rem;font-size:0.75rem;height:auto;width:auto">
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Filter</button>
                </form>
                <a href="{{ route('admin.reports.index') }}" class="admin-btn admin-btn-secondary admin-btn-sm">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block;vertical-align:middle;margin-right:3px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Report
                </a>
            </div>
        </div>
        <div class="admin-card-body" style="padding:0.75rem 1.25rem 1rem">
            <div class="db-chart-legend mb-3">
                <span class="db-legend-dot" style="background:#10498C"></span><span class="db-legend-label">Revenue (RWF)</span>
                <span class="db-legend-dot" style="background:#60a5fa;margin-left:1rem"></span><span class="db-legend-label">Flour packaged (kg)</span>
            </div>
            <div style="position:relative;height:230px">
                <canvas id="chartMonthly"></canvas>
            </div>
        </div>
    </div>

    {{-- Pipeline donut — properly fitted --}}
    <div class="admin-card db-chart-side">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Production breakdown</h2>
            <span class="text-xs" style="color:var(--admin-text-muted)">All time</span>
        </div>
        <div class="admin-card-body db-donut-layout">
            {{-- Donut responsive container --}}
            <div class="db-donut-wrap">
                <canvas id="chartDonut"></canvas>
                <div class="db-donut-center">
                    <span class="db-donut-value">{{ number_format($millingOutput, 0) }}</span>
                    <span class="db-donut-label">kg output</span>
                </div>
            </div>
            {{-- Legend rows --}}
            @php
            $donutItems = [
                ['label'=>'Output flour',  'value'=>$millingOutput,  'color'=>'#10498C'],
                ['label'=>'Milling loss',  'value'=>$millingLoss,    'color'=>'#1a6abf'],
                ['label'=>'Roasting loss', 'value'=>$roastingLoss,   'color'=>'#2e88d4'],
                ['label'=>'Sort loss',     'value'=>$sortingLoss,    'color'=>'#60a5fa'],
                ['label'=>'Rejected',      'value'=>$rawRejected,    'color'=>'#93c5fd'],
            ];
            @endphp
            <div class="db-donut-legend">
                @foreach ($donutItems as $di)
                <div class="db-donut-row">
                    <span class="db-legend-dot" style="background:{{ $di['color'] }}"></span>
                    <span class="db-donut-row-label">{{ $di['label'] }}</span>
                    <span class="db-donut-row-val">{{ number_format($di['value'], 1) }} kg</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══ ROW 2b — Column chart (daily this month) ══ --}}
<div class="mb-4">
    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">Daily production — {{ now()->format('F Y') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--admin-text-muted)">Units packaged and sales per day this month</p>
            </div>
        </div>
        <div class="admin-card-body" style="padding:0.75rem 1.25rem 1rem">
            <div style="position:relative;height:240px">
                <canvas id="chartBar"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ══ ROW 3 — Today + Quick actions ══ --}}
<div class="db-mid-row mb-4">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Today's activity</h2>
            <span class="text-xs" style="color:var(--admin-text-muted)">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="admin-card-body">
            @php
            $todayItems = [
                ['label'=>'Receptions', 'value'=>$todayReceptions, 'color'=>'#082f57', 'route'=>'admin.raw-material-stocks.index'],
                ['label'=>'Sortings',   'value'=>$todaySortings,   'color'=>'#10498c', 'route'=>'admin.sortings.index'],
                ['label'=>'Roastings',  'value'=>$todayRoastings,  'color'=>'#1a6abf', 'route'=>'admin.roastings.index'],
                ['label'=>'Millings',   'value'=>$todayMillings,   'color'=>'#2e88d4', 'route'=>'admin.millings.index'],
                ['label'=>'Packagings', 'value'=>$todayPackagings, 'color'=>'#60a5fa', 'route'=>'admin.flour-stock.index'],
                ['label'=>'Sales',      'value'=>$todaySales,      'color'=>'#93c5fd', 'route'=>'admin.sales.index'],
            ];
            @endphp
            <div class="db-today-grid">
                @foreach ($todayItems as $ti)
                <a href="{{ route($ti['route']) }}" class="db-today-cell">
                    <span class="db-today-count" style="color:{{ $ti['value'] > 0 ? $ti['color'] : 'var(--admin-text-subtle)' }}">{{ $ti['value'] }}</span>
                    <span class="db-today-bar-wrap"><span class="db-today-bar" style="background:{{ $ti['color'] }};opacity:{{ $ti['value'] > 0 ? 1 : 0.15 }}"></span></span>
                    <span class="db-today-label">{{ $ti['label'] }}</span>
                </a>
                @endforeach
            </div>
            @if ($todayRevenue > 0)
            <div class="mt-3 pt-3" style="border-top:1px solid var(--admin-border)">
                <span class="text-xs" style="color:var(--admin-text-muted)">Revenue today:</span>
                <span class="ml-2 font-bold text-sm db-revenue-today">{{ number_format($todayRevenue, 0) }} RWF</span>
            </div>
            @endif
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Quick actions</h2>
        </div>
        <div class="admin-card-body">
            <div class="db-quick-grid">
                @php
                $actions = [
                    ['label'=>'Receive',  'icon'=>'box',     'color'=>'#082f57', 'soft'=>'#dbeafe', 'route'=>'admin.raw-material-stocks.create', 'title'=>'Receive materials'],
                    ['label'=>'Sorting',  'icon'=>'filter',  'color'=>'#10498c', 'soft'=>'#bfdbfe', 'route'=>'admin.sortings.create',            'title'=>'Record sorting'],
                    ['label'=>'Roasting', 'icon'=>'fire',    'color'=>'#1a6abf', 'soft'=>'#93c5fd', 'route'=>'admin.roastings.create',           'title'=>'Record roasting'],
                    ['label'=>'Milling',  'icon'=>'cog',     'color'=>'#2e88d4', 'soft'=>'#bfdbfe', 'route'=>'admin.millings.create',            'title'=>'Record milling'],
                    ['label'=>'Package',  'icon'=>'package', 'color'=>'#1a6abf', 'soft'=>'#dbeafe', 'route'=>'admin.emballages.create',          'title'=>'Record packaging'],
                    ['label'=>'Sale',     'icon'=>'cart',    'color'=>'#10498C', 'soft'=>'#e8f0fa', 'route'=>'admin.sales.create',               'title'=>'Record sale'],
                ];
                @endphp
                @foreach ($actions as $act)
                <a href="{{ route($act['route']) }}"
                   data-drawer-src="{{ route($act['route']) }}"
                   data-drawer-title="{{ $act['title'] }}"
                   class="db-quick-cell">
                    <span class="db-quick-icon" style="background:{{ $act['soft'] }};color:{{ $act['color'] }}">
                        <x-admin.icon :name="$act['icon']" class="!h-5 !w-5" />
                    </span>
                    <span class="db-quick-label">{{ $act['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══ ROW 4 — Activity feeds ══ --}}
<div class="db-bottom-row">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Recent sales</h2>
            <a href="{{ route('admin.sales.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">All →</a>
        </div>
        <div class="admin-card-body !p-0">
            @forelse ($recentSales as $sale)
            <div class="db-feed-row">
                <span class="db-feed-dot" style="background:#10498C"></span>
                <div class="db-feed-body">
                    <p class="db-feed-title">{{ $sale->item }}</p>
                    <p class="db-feed-sub">{{ $sale->client?->full_name ?? '—' }} · {{ optional($sale->date)->format('d M') }}</p>
                </div>
                <span class="admin-badge admin-badge--primary">{{ number_format((float)$sale->total_price, 0) }} RWF</span>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm" style="color:var(--admin-text-muted)">No sales yet.</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Recent packaging</h2>
            <a href="{{ route('admin.flour-stock.index', ['tab'=>'packaging']) }}" class="admin-btn admin-btn-ghost admin-btn-sm">All →</a>
        </div>
        <div class="admin-card-body !p-0">
            @forelse ($recentPackaging as $pkg)
            <div class="db-feed-row">
                <span class="db-feed-dot" style="background:#60a5fa"></span>
                <div class="db-feed-body">
                    <p class="db-feed-title">{{ $pkg->packagingCatalog?->name ?? strtoupper($pkg->packaging_type ?? '—') }} <span class="font-normal" style="color:var(--admin-text-muted)">{{ number_format((float)$pkg->item, 0) }} units</span></p>
                    <p class="db-feed-sub">{{ $pkg->milling?->batch_number ?? '—' }} · {{ optional($pkg->date)->format('d M') }}</p>
                </div>
                <span class="text-sm font-bold db-revenue-today">{{ number_format((float)$pkg->quantity, 1) }} kg</span>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm" style="color:var(--admin-text-muted)">No packaging yet.</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Loss summary</h2>
            <a href="{{ route('admin.reports.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Reports →</a>
        </div>
        <div class="admin-card-body !p-0">
            @php
            $totalInput = $rawReceived ?: 1;
            $losses = [
                ['label'=>'Rejected',      'val'=>$rawRejected,  'unit'=>'kg',    'color'=>'#60a5fa', 'dark'=>'#93c5fd'],
                ['label'=>'Sorting loss',  'val'=>$sortingLoss,  'unit'=>'kg',    'color'=>'#2e88d4', 'dark'=>'#60a5fa'],
                ['label'=>'Roasting loss', 'val'=>$roastingLoss, 'unit'=>'kg',    'color'=>'#1a6abf', 'dark'=>'#60a5fa'],
                ['label'=>'Milling loss',  'val'=>$millingLoss,  'unit'=>'kg',    'color'=>'#10498c', 'dark'=>'#93c5fd'],
                ['label'=>'Damaged units', 'val'=>$packagingDmg, 'unit'=>'units', 'color'=>'#10498c', 'dark'=>'#93c5fd'],
                ['label'=>'Returns',       'val'=>$returned,     'unit'=>'units', 'color'=>'#10498c', 'dark'=>'#93c5fd'],
            ];
            @endphp
            @foreach ($losses as $loss)
            <div class="db-feed-row">
                <span class="db-feed-dot" style="background:{{ $loss['color'] }}"></span>
                <div class="db-feed-body">
                    <p class="db-feed-title">{{ $loss['label'] }}</p>
                    @php $pct = $loss['unit'] === 'kg' && $totalInput > 0 ? round($loss['val'] / $totalInput * 100, 1) : null; @endphp
                    @if ($pct !== null)
                    <div class="db-loss-bar-wrap"><div class="db-loss-bar" style="width:{{ min($pct * 3, 100) }}%;background:{{ $loss['color'] }}"></div></div>
                    @endif
                </div>
                <span class="text-sm font-semibold db-loss-val" style="--lc:{{ $loss['color'] }};--lc-dark:{{ $loss['dark'] }};color:var(--lc)">{{ number_format($loss['val'], 1) }} {{ $loss['unit'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark = document.documentElement.dataset.theme === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    const monthly = @json($monthlyRevenue);
    const labels  = monthly.map(m => m.label);

    const ctxM = document.getElementById('chartMonthly').getContext('2d');
    const gradBlue = ctxM.createLinearGradient(0, 0, 0, 230);
    gradBlue.addColorStop(0, 'rgba(16,73,140,0.25)');
    gradBlue.addColorStop(1, 'rgba(16,73,140,0)');
    const gradLight = ctxM.createLinearGradient(0, 0, 0, 230);
    gradLight.addColorStop(0, 'rgba(96,165,250,0.2)');
    gradLight.addColorStop(1, 'rgba(96,165,250,0)');

    new Chart(ctxM, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Revenue (RWF)',
                    data: monthly.map(m => m.revenue),
                    borderColor: '#10498C',
                    backgroundColor: gradBlue,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10498C',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Flour packaged (kg)',
                    data: @json($monthlyPackaging->pluck('kg')),
                    borderColor: '#60a5fa',
                    backgroundColor: gradLight,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#60a5fa',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#0f172a',
                    bodyColor:  isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ctx.datasetIndex === 0
                            ? ' ' + Number(ctx.raw).toLocaleString() + ' RWF'
                            : ' ' + Number(ctx.raw).toLocaleString() + ' kg',
                    }
                },
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 } } },
                y:  { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 11 }, callback: v => v.toLocaleString() }, position: 'left' },
                y1: { grid: { drawOnChartArea: false }, ticks: { color: '#60a5fa', font: { size: 11 }, callback: v => v + ' kg' }, position: 'right' },
            },
        },
    });

    // ── Daily column chart ──────────────────────────────────────────────
    const ctxB = document.getElementById('chartBar').getContext('2d');
    new Chart(ctxB, {
        type: 'bar',
        data: {
            labels: @json($dailyLabels),
            datasets: [
                {
                    label: 'Units packaged',
                    data: @json($dailyPackaging),
                    backgroundColor: '#10498C',
                    hoverBackgroundColor: '#082f57',
                    borderRadius: 5,
                    borderSkipped: false,
                    categoryPercentage: 0.75,
                    barPercentage: 0.55,
                },
                {
                    label: 'Sales qty',
                    data: @json($dailySales),
                    backgroundColor: '#60a5fa',
                    hoverBackgroundColor: '#2e88d4',
                    borderRadius: 5,
                    borderSkipped: false,
                    categoryPercentage: 0.75,
                    barPercentage: 0.55,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: labelColor,
                        font: { size: 11 },
                        boxWidth: 12,
                        boxHeight: 12,
                        borderRadius: 3,
                        useBorderRadius: true,
                    },
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#0f172a',
                    bodyColor:  isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        title: ctx => 'Day ' + ctx[0].label,
                        label: ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString(),
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: labelColor, font: { size: 10 } },
                    border: { display: false },
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { size: 11 }, callback: v => v.toLocaleString() },
                    beginAtZero: true,
                    border: { display: false },
                },
            },
        },
    });

    // Donut — use responsive: true so it fills its container
    const ctxD = document.getElementById('chartDonut').getContext('2d');
    new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: ['Output flour','Mill loss','Roast loss','Sort loss','Rejected'],
            datasets: [{
                data: [{{ $pipelineDonut['values'][4] }}, {{ $pipelineDonut['values'][3] }}, {{ $pipelineDonut['values'][2] }}, {{ $pipelineDonut['values'][1] }}, {{ $pipelineDonut['values'][0] }}],
                backgroundColor: ['#082f57','#10498c','#1a6abf','#2e88d4','#60a5fa'],
                borderWidth: 0,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#0f172a',
                    bodyColor:  isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString() + ' kg' },
                },
            },
        },
    });
})();
</script>
@endpush

@endsection
