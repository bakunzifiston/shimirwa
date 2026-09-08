@extends('layouts.admin')
@section('title', 'Reports')
@section('page_title', 'Reports')
@section('page_subtitle', 'Daily production, packaging and sales logs')

@section('content')

{{-- Date range / month filter + export --}}
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
    @if ($tab === 'monthly')
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="monthly">
            <label class="text-xs font-medium" style="color:var(--admin-text-subtle)">Month</label>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="admin-input" style="max-width:160px">
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Apply</button>
            <a href="{{ route('admin.reports.index', ['tab' => 'monthly']) }}" class="admin-btn admin-btn-ghost admin-btn-sm">This month</a>
        </form>

        {{-- Monthly export links --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.index', ['tab' => 'monthly', 'month' => $month->format('Y-m'), 'export' => 1]) }}"
               class="admin-btn admin-btn-secondary admin-btn-sm flex items-center gap-1.5">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('admin.reports.index', ['tab' => 'monthly', 'month' => $month->format('Y-m'), 'format' => 'pdf']) }}"
               class="admin-btn admin-btn-secondary admin-btn-sm flex items-center gap-1.5">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Export PDF
            </a>
        </div>
    @else
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="text-xs font-medium" style="color:var(--admin-text-subtle)">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="admin-input" style="max-width:160px">
            <label class="text-xs font-medium" style="color:var(--admin-text-subtle)">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="admin-input" style="max-width:160px">
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Apply</button>
            <a href="{{ route('admin.reports.index', ['tab' => $tab]) }}" class="admin-btn admin-btn-ghost admin-btn-sm">This month</a>
        </form>

        {{-- CSV export link --}}
        <a href="{{ route('admin.reports.index', ['tab' => $tab, 'from' => $from->toDateString(), 'to' => $to->toDateString(), 'export' => 1]) }}"
           class="admin-btn admin-btn-secondary admin-btn-sm flex items-center gap-1.5">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
        </a>
    @endif
</div>

{{-- Tabs --}}
<div class="flex gap-2 mb-4 p-1 rounded-lg w-fit" style="background:var(--admin-bg);border:1px solid var(--admin-border)">
    @php
        $tabs = [
            'monthly'   => ['label' => 'Monthly report',        'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
            'packaging' => ['label' => 'Packaging log',         'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2M8 7V5a2 2 0 0 0-4 0v2"/>'],
            'sales'     => ['label' => 'Sales log',             'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
            'stock'     => ['label' => 'Final product stock',   'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
        ];
    @endphp
    @foreach ($tabs as $key => $meta)
        <a href="{{ route('admin.reports.index', $key === 'monthly' ? ['tab' => $key] : ['tab' => $key, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
           class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-all"
           style="{{ $tab === $key
               ? 'background:var(--admin-bg-elevated);box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid var(--admin-border)'
               : 'color:var(--admin-text-subtle);border:1px solid transparent' }}">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $meta['icon'] !!}</svg>
            {{ $meta['label'] }}
        </a>
    @endforeach
</div>

{{-- =========================================================== --}}
{{-- MONTHLY REPORT TAB                                          --}}
{{-- =========================================================== --}}
@if ($tab === 'monthly')

    <div class="flex items-center gap-3 mb-4">
        <x-admin.logo class="admin-sidebar-logo" />
        <div>
            <h2 class="text-lg font-semibold" style="color:var(--admin-text)">Shimirwa Ltd — {{ $month->format('F Y') }}</h2>
            <p class="text-xs" style="color:var(--admin-text-subtle)">Consolidated production and business summary for the month. Export as CSV or PDF using the buttons above.</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Raw material received</div>
            <div class="text-2xl font-bold">{{ number_format($monthly['rawMaterialsTotals']['received'], 1) }} kg</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Flour milled</div>
            <div class="text-2xl font-bold">{{ number_format($monthly['production']['milling']['output'], 1) }} kg</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Units packaged</div>
            <div class="text-2xl font-bold db-revenue-today">{{ number_format($monthly['packagingTotals']['units']) }}</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Sales revenue</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($monthly['salesTotals']['revenue'], 0) }} RWF</div>
        </div>
    </div>

    {{-- Raw materials received --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Raw materials received</h3>
    @if ($monthly['rawMaterials']->isEmpty())
        <div class="admin-card px-6 py-6 text-center text-sm mb-6" style="color:var(--admin-text-subtle)">No raw material intake this month.</div>
    @else
        <div class="admin-card overflow-hidden mb-6">
            <table class="admin-table w-full">
                <thead>
                    <tr><th>Item</th><th>Type</th><th class="text-right">Received (kg)</th><th class="text-right">Rejected (kg)</th><th class="text-right">Batches</th></tr>
                </thead>
                <tbody>
                    @foreach ($monthly['rawMaterials'] as $row)
                    <tr>
                        <td class="font-medium">{{ $row->item }}</td>
                        <td style="color:var(--admin-text-subtle)">{{ $row->type }}</td>
                        <td class="text-right text-green-600 font-semibold">{{ number_format($row->received, 1) }}</td>
                        <td class="text-right text-red-500">{{ number_format($row->rejected, 1) }}</td>
                        <td class="text-right">{{ $row->batches }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold" colspan="2">Total</td>
                        <td class="text-right font-bold text-green-600">{{ number_format($monthly['rawMaterialsTotals']['received'], 1) }}</td>
                        <td class="text-right font-bold text-red-500">{{ number_format($monthly['rawMaterialsTotals']['rejected'], 1) }}</td>
                        <td class="text-right font-bold">{{ $monthly['rawMaterialsTotals']['batches'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Production pipeline, by raw material item --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Production pipeline</h3>
    @foreach (['sorting' => 'Sorting', 'roasting' => 'Roasting', 'milling' => 'Milling (flour)'] as $key => $label)
    @php($rows = $monthly['productionByItem'][$key]['rows'])
    @php($t = $monthly['productionByItem'][$key]['totals'])
    <div class="admin-card overflow-hidden mb-4">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th colspan="5" class="text-left" style="color:var(--admin-text)">{{ $label }}</th>
                </tr>
                <tr><th>Item</th><th class="text-right">Input (kg)</th><th class="text-right">Loss (kg)</th><th class="text-right">Output (kg)</th><th class="text-right">Batches</th></tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                <tr>
                    <td class="font-medium">{{ $row['item'] }}</td>
                    <td class="text-right">{{ number_format($row['input'], 1) }}</td>
                    <td class="text-right text-red-500">{{ $row['loss'] > 0 ? number_format($row['loss'], 1) : '—' }}</td>
                    <td class="text-right font-semibold db-revenue-today">{{ number_format($row['output'], 1) }}</td>
                    <td class="text-right">{{ $row['batches'] }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-sm py-4" style="color:var(--admin-text-subtle)">No {{ strtolower($label) }} recorded this month.</td></tr>
                @endforelse
                @if ($rows->count() > 1)
                <tr style="border-top:2px solid var(--admin-border)">
                    <td class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($t['input'], 1) }}</td>
                    <td class="text-right font-bold text-red-500">{{ $t['loss'] > 0 ? number_format($t['loss'], 1) : '—' }}</td>
                    <td class="text-right font-bold">{{ number_format($t['output'], 1) }}</td>
                    <td class="text-right font-bold">{{ $t['batches'] }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endforeach

    {{-- Packaging by product --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Packaging by product</h3>
    @if ($monthly['packagingByProduct']->isEmpty())
        <div class="admin-card px-6 py-6 text-center text-sm mb-6" style="color:var(--admin-text-subtle)">No packaging this month.</div>
    @else
        <div class="admin-card overflow-hidden mb-6">
            <table class="admin-table w-full">
                <thead>
                    <tr><th>Product</th><th class="text-right">Units</th><th class="text-right">Kg</th><th class="text-right">Damaged</th><th class="text-right">Batches</th></tr>
                </thead>
                <tbody>
                    @foreach ($monthly['packagingByProduct'] as $row)
                    <tr>
                        <td class="font-medium">{{ $row['name'] }}</td>
                        <td class="text-right font-semibold db-revenue-today">{{ number_format($row['units']) }}</td>
                        <td class="text-right">{{ number_format($row['kg'], 1) }}</td>
                        <td class="text-right {{ $row['damaged'] > 0 ? 'text-red-500' : '' }}" style="{{ $row['damaged'] <= 0 ? 'color:var(--admin-text-subtle)' : '' }}">{{ $row['damaged'] > 0 ? $row['damaged'] : '—' }}</td>
                        <td class="text-right">{{ $row['batches'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold">Total</td>
                        <td class="text-right font-bold db-revenue-today">{{ number_format($monthly['packagingTotals']['units']) }}</td>
                        <td class="text-right font-bold">{{ number_format($monthly['packagingTotals']['kg'], 1) }}</td>
                        <td class="text-right font-bold text-red-500">{{ $monthly['packagingTotals']['damaged'] }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Sales by product --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Sales by product</h3>
    @if ($monthly['salesByProduct']->isEmpty())
        <div class="admin-card px-6 py-6 text-center text-sm mb-6" style="color:var(--admin-text-subtle)">No sales this month.</div>
    @else
        <div class="admin-card overflow-hidden mb-6">
            <table class="admin-table w-full">
                <thead>
                    <tr><th>Product</th><th class="text-right">Units sold</th><th class="text-right">Revenue</th><th class="text-right">Returned</th><th class="text-right">Transactions</th></tr>
                </thead>
                <tbody>
                    @foreach ($monthly['salesByProduct'] as $row)
                    <tr>
                        <td class="font-medium">{{ $row->item }}</td>
                        <td class="text-right text-red-500 font-semibold">{{ number_format($row->units) }}</td>
                        <td class="text-right text-green-600 font-semibold">{{ number_format($row->revenue, 0) }} RWF</td>
                        <td class="text-right {{ $row->returned > 0 ? 'text-amber-500' : '' }}" style="{{ $row->returned <= 0 ? 'color:var(--admin-text-subtle)' : '' }}">{{ $row->returned > 0 ? number_format($row->returned) : '—' }}</td>
                        <td class="text-right">{{ $row->txns }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold">Total</td>
                        <td class="text-right font-bold text-red-500">{{ number_format($monthly['salesTotals']['units']) }}</td>
                        <td class="text-right font-bold text-green-600">{{ number_format($monthly['salesTotals']['revenue'], 0) }} RWF</td>
                        <td class="text-right font-bold text-amber-500">{{ $monthly['salesTotals']['returned'] > 0 ? number_format($monthly['salesTotals']['returned']) : '—' }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if ($monthly['topClients']->isNotEmpty())
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Top clients</h3>
    <div class="admin-card overflow-hidden mb-6">
        <table class="admin-table w-full">
            <thead>
                <tr><th>Client</th><th class="text-right">Units</th><th class="text-right">Revenue</th></tr>
            </thead>
            <tbody>
                @foreach ($monthly['topClients'] as $row)
                <tr>
                    <td class="font-medium">{{ $row['name'] }}</td>
                    <td class="text-right">{{ number_format($row['units']) }}</td>
                    <td class="text-right text-green-600 font-semibold">{{ number_format($row['revenue'], 0) }} RWF</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Online shop orders --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">Online shop orders</h3>
    @if ($monthly['shopOrdersByStatus']->isEmpty())
        <div class="admin-card px-6 py-6 text-center text-sm mb-6" style="color:var(--admin-text-subtle)">No online orders this month.</div>
    @else
        <div class="admin-card overflow-hidden mb-6">
            <table class="admin-table w-full">
                <thead>
                    <tr><th>Status</th><th class="text-right">Orders</th><th class="text-right">Revenue</th></tr>
                </thead>
                <tbody>
                    @foreach ($monthly['shopOrdersByStatus'] as $row)
                    <tr>
                        <td class="font-medium">{{ ucfirst($row->order_status) }}</td>
                        <td class="text-right">{{ $row->count }}</td>
                        <td class="text-right">{{ number_format($row->revenue ?? 0, 0) }} RWF</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold">Total ({{ $monthly['shopOrderTotals']['orders'] }} orders)</td>
                        <td></td>
                        <td class="text-right font-bold text-green-600">{{ number_format($monthly['shopOrderTotals']['revenue'], 0) }} RWF paid</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Stock as of month end --}}
    <h3 class="font-semibold text-sm mb-2" style="color:var(--admin-text)">
        Stock levels
        <span class="text-xs font-normal" style="color:var(--admin-text-subtle)">(as of {{ $to->format('d M Y') }})</span>
    </h3>
    <div class="grid gap-4 lg:grid-cols-2 mb-6">
        <div class="admin-card overflow-hidden">
            <table class="admin-table w-full">
                <thead><tr><th>Raw material</th><th class="text-right">Remaining (kg)</th></tr></thead>
                <tbody>
                    @forelse ($monthly['rawStockAsOf'] as $row)
                    <tr>
                        <td>{{ $row->item }}</td>
                        <td class="text-right font-semibold">{{ number_format($row->remaining, 1) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-sm py-4" style="color:var(--admin-text-subtle)">No raw material in stock.</td></tr>
                    @endforelse
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold">Unpackaged milled flour</td>
                        <td class="text-right font-bold text-amber-600">{{ number_format($monthly['flourStockAsOf'], 1) }} kg</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="admin-card overflow-hidden">
            <table class="admin-table w-full">
                <thead><tr><th>Packaged product</th><th class="text-right">Units in stock</th></tr></thead>
                <tbody>
                    @forelse ($monthly['packagedStockAsOf'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-right font-semibold db-revenue-today">{{ number_format($row['units']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-sm py-4" style="color:var(--admin-text-subtle)">No packaged product in stock.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- =========================================================== --}}
{{-- PACKAGING LOG TAB                                           --}}
{{-- =========================================================== --}}
@elseif ($tab === 'packaging')

    {{-- Summary cards --}}
    @if ($packagingSummary)
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Days active</div>
            <div class="text-2xl font-bold">{{ $packagingSummary['days_active'] }}</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Total kg packaged</div>
            <div class="text-2xl font-bold db-revenue-today">{{ number_format($packagingSummary['total_packed'], 1) }} kg</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Total units packed</div>
            <div class="text-2xl font-bold">{{ number_format($packagingSummary['total_units']) }}</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Current unpackaged stock</div>
            <div class="text-2xl font-bold text-amber-600">{{ number_format($packagingSummary['current_stock'], 1) }} kg</div>
        </div>
    </div>
    @endif

    @if ($packagingRows->isEmpty())
        <div class="admin-card px-6 py-10 text-center text-sm" style="color:var(--admin-text-subtle)">
            No packaging records in the selected date range.
        </div>
    @else
        {{-- Daily packaging log table --}}
        <div class="admin-card overflow-hidden">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right" style="color:var(--admin-text-subtle)">Initial qty (kg)</th>
                        <th class="text-right">Flour in (kg)</th>
                        <th>Batch / Type</th>
                        <th class="text-right db-revenue-today">Qty packed</th>
                        <th class="text-right text-amber-600">Unpackaged</th>
                        <th>Employee</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packagingRows as $row)
                        {{-- Date header row --}}
                        <tr style="border-top:2px solid var(--admin-border)">
                            <td class="font-semibold db-revenue-today">
                                {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                                <span class="text-xs ml-1" style="color:var(--admin-text-subtle)">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('l') }}
                                </span>
                            </td>
                            <td class="text-right font-mono text-sm" style="color:var(--admin-text-subtle)">
                                {{ number_format($row['initial_qty'], 1) }} kg
                            </td>
                            <td class="text-right font-semibold">
                                @if ($row['qty_in'] > 0)
                                    <span style="color:#16a34a">+{{ number_format($row['qty_in'], 1) }} kg</span>
                                @else
                                    <span style="color:var(--admin-text-subtle)">—</span>
                                @endif
                            </td>
                            <td></td>
                            <td class="text-right font-bold db-revenue-today">
                                {{ number_format($row['units_packed']) }} units
                                <div class="text-xs font-normal" style="color:var(--admin-text-subtle)">{{ number_format($row['qty_packed'], 1) }} kg</div>
                            </td>
                            <td class="text-right font-bold {{ $row['qty_unpacked'] > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                {{ number_format($row['qty_unpacked'], 1) }} kg
                            </td>
                            <td></td>
                        </tr>
                        {{-- Individual packaging batch sub-rows --}}
                        @foreach ($row['batches'] as $b)
                        <tr class="text-sm">
                            <td class="pl-6" style="color:var(--admin-text-subtle)">
                                <span class="text-xs">↳ batch</span>
                            </td>
                            <td></td>
                            <td></td>
                            <td>
                                <span class="admin-badge admin-badge--primary text-xs">{{ $b['catalog_name'] }}</span>
                                <span class="font-mono text-xs ml-1" style="color:var(--admin-text-subtle)">{{ $b['packaging_batch_id'] }}</span>
                                @if ($b['milling_batch'] !== '—')
                                    <span class="text-xs ml-1" style="color:var(--admin-text-subtle)">from mill {{ $b['milling_batch'] }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="font-semibold">{{ number_format($b['units']) }}</span> units
                                <span class="text-xs ml-1" style="color:var(--admin-text-subtle)">· {{ number_format($b['kg'], 1) }} kg</span>
                                @if ($b['damaged'] > 0)
                                    <span class="text-xs ml-1 text-red-500">{{ $b['damaged'] }} dmg</span>
                                @endif
                            </td>
                            <td></td>
                            <td class="text-sm" style="color:var(--admin-text-subtle)">{{ $b['employee'] }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold" colspan="2">Totals</td>
                        <td class="text-right font-semibold text-green-600">
                            +{{ number_format($packagingRows->sum('qty_in'), 1) }} kg
                        </td>
                        <td></td>
                        <td class="text-right font-bold db-revenue-today">
                            {{ number_format($packagingRows->sum('units_packed')) }} units
                            <div class="text-xs font-normal">{{ number_format($packagingRows->sum('qty_packed'), 1) }} kg</div>
                        </td>
                        <td class="text-right font-bold text-amber-600">
                            {{ number_format($packagingSummary['current_stock'] ?? 0, 1) }} kg
                            <div class="text-xs font-normal" style="color:var(--admin-text-subtle)">current stock</div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

{{-- =========================================================== --}}
{{-- SALES LOG TAB                                               --}}
{{-- =========================================================== --}}
@elseif ($tab === 'sales')

    @if ($salesSummary)
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Days with sales</div>
            <div class="text-2xl font-bold">{{ $salesSummary['days_active'] }}</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Total units sold</div>
            <div class="text-2xl font-bold db-revenue-today">{{ number_format($salesSummary['total_sold']) }}</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Total revenue</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($salesSummary['total_revenue'], 0) }} RWF</div>
        </div>
        <div class="admin-card p-4">
            <div class="text-xs mb-1" style="color:var(--admin-text-subtle)">Returns</div>
            <div class="text-2xl font-bold text-red-500">{{ number_format($salesSummary['total_returned']) }}</div>
        </div>
    </div>
    @endif

    @if ($salesRows->isEmpty())
        <div class="admin-card px-6 py-10 text-center text-sm" style="color:var(--admin-text-subtle)">
            No sales in the selected date range.
        </div>
    @else
        <div class="admin-card overflow-hidden">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right" style="color:var(--admin-text-subtle)">Opening stock</th>
                        <th class="text-right text-green-600">In (packaged)</th>
                        <th>Product / Client</th>
                        <th class="text-right text-red-500">Out (sold)</th>
                        <th class="text-right text-amber-500">Returned</th>
                        <th class="text-right font-semibold db-revenue-today">Balance</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesRows as $row)
                        <tr style="border-top:2px solid var(--admin-border)">
                            <td class="font-semibold db-revenue-today">
                                {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                                <span class="text-xs ml-1" style="color:var(--admin-text-subtle)">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('l') }}
                                </span>
                            </td>
                            <td class="text-right font-mono text-sm" style="color:var(--admin-text-subtle)">
                                {{ number_format($row['initial_stock']) }}
                            </td>
                            <td class="text-right font-semibold text-green-600">
                                @if ($row['entered'] > 0)
                                    +{{ number_format($row['entered']) }}
                                @else
                                    <span style="color:var(--admin-text-subtle)">—</span>
                                @endif
                            </td>
                            <td></td>
                            <td class="text-right font-bold text-red-500">
                                {{ number_format($row['sold']) }}
                            </td>
                            <td class="text-right {{ $row['returned'] > 0 ? 'text-amber-500 font-semibold' : '' }}" style="{{ $row['returned'] <= 0 ? 'color:var(--admin-text-subtle)' : '' }}">
                                {{ $row['returned'] > 0 ? '+'.number_format($row['returned']) : '—' }}
                            </td>
                            <td class="text-right font-bold db-revenue-today">
                                {{ number_format($row['balance']) }}
                            </td>
                            <td class="text-right font-semibold text-green-600">
                                {{ number_format($row['revenue'], 0) }} RWF
                            </td>
                        </tr>
                        @foreach ($row['sales'] as $s)
                        <tr class="text-sm">
                            <td class="pl-6" style="color:var(--admin-text-subtle)"><span class="text-xs">↳ sale</span></td>
                            <td></td>
                            <td></td>
                            <td>
                                <span class="font-medium">{{ $s['item'] }}</span>
                                @if ($s['client'] !== '—')
                                    <span class="text-xs ml-1" style="color:var(--admin-text-subtle)">→ {{ $s['client'] }}</span>
                                @endif
                            </td>
                            <td class="text-right text-red-500">{{ number_format($s['units']) }}</td>
                            <td class="text-right {{ $s['returned'] > 0 ? 'text-amber-500' : '' }}" style="{{ $s['returned'] <= 0 ? 'color:var(--admin-text-subtle)' : '' }}">
                                {{ $s['returned'] > 0 ? $s['returned'] : '—' }}
                            </td>
                            <td></td>
                            <td class="text-right">{{ number_format($s['revenue'], 0) }} RWF</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--admin-border)">
                        <td class="font-bold" colspan="3">Totals</td>
                        <td></td>
                        <td class="text-right font-bold text-red-500">{{ number_format($salesSummary['total_sold'] ?? 0) }}</td>
                        <td class="text-right font-semibold text-amber-500">
                            {{ ($salesSummary['total_returned'] ?? 0) > 0 ? number_format($salesSummary['total_returned']) : '—' }}
                        </td>
                        <td class="text-right font-bold db-revenue-today">
                            {{ number_format($salesSummary['current_stock'] ?? 0) }}
                            <div class="text-xs font-normal" style="color:var(--admin-text-subtle)">current</div>
                        </td>
                        <td class="text-right font-bold text-green-600">{{ number_format($salesSummary['total_revenue'] ?? 0, 0) }} RWF</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

{{-- =========================================================== --}}
{{-- STOCK OF FINAL PRODUCTS TAB                                 --}}
{{-- =========================================================== --}}
@elseif ($tab === 'stock')

    @if ($stockRows->isEmpty())
        <div class="admin-card px-6 py-10 text-center text-sm" style="color:var(--admin-text-subtle)">
            No stock movements in the selected date range.
        </div>
    @else
        @foreach ($stockRows as $itemRow)
        <div class="mb-6">
            {{-- Item header --}}
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-base" style="color:var(--admin-text)">{{ $itemRow['item'] }}</h3>
                <div class="flex gap-3 text-xs" style="color:var(--admin-text-subtle)">
                    <span>Opening: <strong>{{ number_format($itemRow['opening_balance']) }}</strong></span>
                    <span>Final balance: <strong class="db-revenue-today">{{ number_format($itemRow['final_balance']) }}</strong></span>
                </div>
            </div>

            <div class="admin-card overflow-hidden">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right text-green-600">In (ENTRE)</th>
                            <th class="text-right text-red-500">Out (SORTIE)</th>
                            <th class="text-right text-amber-500">Returned</th>
                            <th class="text-right font-semibold db-revenue-today">Balance (SOLDE)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemRow['days'] as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                            <td class="text-right font-semibold text-green-600">
                                {{ $day['entered'] > 0 ? '+'.number_format($day['entered']) : '—' }}
                            </td>
                            <td class="text-right font-semibold text-red-500">
                                {{ $day['sold'] > 0 ? number_format($day['sold']) : '—' }}
                            </td>
                            <td class="text-right {{ $day['returned'] > 0 ? 'text-amber-500 font-semibold' : '' }}" style="{{ $day['returned'] <= 0 ? 'color:var(--admin-text-subtle)' : '' }}">
                                {{ $day['returned'] > 0 ? '+'.$day['returned'] : '—' }}
                            </td>
                            <td class="text-right font-bold db-revenue-today">{{ number_format($day['balance']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="font-semibold text-xs" style="color:var(--admin-text-subtle)">Final balance</td>
                            <td class="text-right font-bold text-green-600">
                                +{{ number_format($itemRow['days']->sum('entered')) }}
                            </td>
                            <td class="text-right font-bold text-red-500">
                                {{ number_format($itemRow['days']->sum('sold')) }}
                            </td>
                            <td class="text-right font-bold text-amber-500">
                                {{ number_format($itemRow['days']->sum('returned')) }}
                            </td>
                            <td class="text-right font-bold text-lg db-revenue-today">
                                {{ number_format($itemRow['final_balance']) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach
    @endif

@endif

@endsection
