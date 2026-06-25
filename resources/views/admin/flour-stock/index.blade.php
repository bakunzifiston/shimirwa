@extends('layouts.admin')
@section('title', 'Flour Stock & Packaging')
@section('page_title', 'Flour Stock & Packaging')
@section('page_subtitle', 'Available flour from milling and all packaging records')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />

    {{-- Tabs --}}
    <div class="flex gap-2 mb-4 p-1 rounded-lg w-fit" style="background:var(--admin-bg);border:1px solid var(--admin-border)">
        <a href="{{ route('admin.flour-stock.index', ['tab' => 'flour']) }}"
           class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-all"
           style="{{ $tab === 'flour'
               ? 'background:var(--admin-bg-elevated);box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid var(--admin-border)'
               : 'color:var(--admin-text-subtle);border:1px solid transparent' }}">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
            Flour available
            @if ($flourBatches->isNotEmpty())
                <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold"
                      class="admin-badge admin-badge--primary">{{ $flourBatches->count() }}</span>
            @endif
        </a>
        <a href="{{ route('admin.flour-stock.index', ['tab' => 'packaging']) }}"
           class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-all"
           style="{{ $tab === 'packaging'
               ? 'background:var(--admin-bg-elevated);box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid var(--admin-border)'
               : 'color:var(--admin-text-subtle);border:1px solid transparent' }}">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2M8 7V5a2 2 0 0 0-4 0v2"/></svg>
            Packaging records
        </a>
    </div>

    {{-- ===== FLOUR TAB ===== --}}
    @if ($tab === 'flour')

        {{-- Summary by total --}}
        <div class="admin-card p-4 mb-4 flex items-center justify-between">
            <div>
                <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Total unpackaged flour</div>
                <div class="text-3xl font-bold db-revenue-today">{{ number_format($totalFlour, 1) }} kg</div>
            </div>
            <a href="{{ route('admin.emballages.create') }}"
               data-drawer-src="{{ route('admin.emballages.create') }}"
               data-drawer-title="Add packaging"
               class="admin-btn admin-btn-primary admin-btn-sm">
                + Package flour
            </a>
        </div>

        @if ($flourBatches->isEmpty())
            <div class="admin-card px-6 py-10 text-center">
                <div class="text-4xl mb-2">🌾</div>
                <div class="text-sm font-medium mb-1">No flour available</div>
                <div class="text-xs" style="color:var(--admin-text-subtle)">All milled flour has been packaged. Record a milling run to add more.</div>
                <a href="{{ route('admin.millings.create') }}" class="admin-btn admin-btn-secondary admin-btn-sm mt-3 inline-block">Go to milling</a>
            </div>
        @else
            <div class="admin-card overflow-hidden">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch #</th>
                            <th class="text-right">Total milled</th>
                            <th class="text-right db-revenue-today">Available flour</th>
                            <th class="text-right">Loss</th>
                            <th>Employee</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flourBatches as $batch)
                        @php
                            $pct = $batch->total_mixed_quantity > 0
                                ? round($batch->output_flour / $batch->total_mixed_quantity * 100, 1)
                                : 0;
                        @endphp
                        <tr>
                            <td>{{ optional($batch->date)->format('d M Y') }}</td>
                            <td class="font-mono font-semibold">{{ $batch->batch_number }}</td>
                            <td class="text-right">{{ number_format($batch->total_mixed_quantity, 1) }} kg</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="hidden sm:block w-20 rounded-full h-1.5" style="background:var(--admin-border)">
                                        <div class="h-1.5 rounded-full" style="width:{{ min($pct,100) }}%;background:#10498C"></div>
                                    </div>
                                    <span class="font-bold db-revenue-today">{{ number_format($batch->output_flour, 1) }} kg</span>
                                    <span class="text-xs" style="color:var(--admin-text-subtle)">({{ $pct }}%)</span>
                                </div>
                            </td>
                            <td class="text-right text-red-500">{{ number_format($batch->loss ?? 0, 1) }} kg</td>
                            <td>{{ $batch->employee?->full_name ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.millings.show', $batch) }}"
                                   data-drawer-src="{{ route('admin.millings.show', $batch) }}"
                                   data-drawer-title="Milling details"
                                   class="text-xs underline db-revenue-today">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right text-xs font-semibold" style="color:var(--admin-text-subtle)">Total available:</td>
                            <td class="text-right font-bold text-lg db-revenue-today">{{ number_format($totalFlour, 1) }} kg</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

    {{-- ===== PACKAGING TAB ===== --}}
    @else

        {{-- Packaging summary by type --}}
        @if ($packagingSummary->isNotEmpty())
        <div class="grid gap-3 mb-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($packagingSummary as $s)
            <div class="admin-card p-4">
                <div class="text-xs mb-1 font-medium" style="color:var(--admin-text-subtle)">{{ $s['name'] }}</div>
                <div class="text-2xl font-bold">{{ number_format($s['total_units']) }}</div>
                <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">
                    units · {{ number_format($s['total_flour'], 1) }} kg flour
                    @if ($s['total_damaged'] > 0)
                        · <span class="text-red-500">{{ $s['total_damaged'] }} damaged</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Search bar + add button --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <form method="GET" action="{{ route('admin.flour-stock.index') }}" class="flex gap-2">
                <input type="hidden" name="tab" value="packaging">
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Search batch ID…"
                       class="admin-input" style="max-width:220px">
                <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Search</button>
                @if ($search)
                    <a href="{{ route('admin.flour-stock.index', ['tab' => 'packaging']) }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                @endif
            </form>
            <a href="{{ route('admin.emballages.create') }}"
               data-drawer-src="{{ route('admin.emballages.create') }}"
               data-drawer-title="Add packaging"
               class="admin-btn admin-btn-primary admin-btn-sm">
                + Add packaging
            </a>
        </div>

        @if ($packagings->isEmpty())
            <div class="admin-card px-6 py-10 text-center text-sm" style="color:var(--admin-text-subtle)">
                No packaging records yet.
            </div>
        @else
            <div class="admin-card overflow-hidden">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch ID</th>
                            <th>Type</th>
                            <th class="text-right">Units</th>
                            <th class="text-right">Flour used</th>
                            <th class="text-right">Damaged</th>
                            <th>From milling</th>
                            <th>Employee</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($packagings as $pkg)
                        <tr>
                            <td>{{ optional($pkg->date)->format('d M Y') }}</td>
                            <td class="font-mono font-semibold cell-primary">{{ $pkg->packaging_batch_id }}</td>
                            <td>
                                @if ($pkg->packagingCatalog)
                                    <span class="admin-badge admin-badge--primary">{{ $pkg->packagingCatalog->name }}</span>
                                @else
                                    <span class="admin-badge admin-badge--neutral">{{ strtoupper($pkg->packaging_type ?? '—') }}</span>
                                @endif
                            </td>
                            <td class="text-right font-bold">{{ number_format($pkg->item) }}</td>
                            <td class="text-right db-revenue-today" style="font-weight:600">{{ number_format($pkg->quantity, 1) }} kg</td>
                            <td class="text-right {{ $pkg->damaged > 0 ? 'text-red-500 font-semibold' : '' }}">
                                {{ $pkg->damaged > 0 ? $pkg->damaged : '—' }}
                            </td>
                            <td class="font-mono text-xs">{{ $pkg->milling?->batch_number ?? '—' }}</td>
                            <td>{{ $pkg->employee?->full_name ?? '—' }}</td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :view-route="route('admin.emballages.show', $pkg)"
                                    :edit-route="route('admin.emballages.edit', $pkg)"
                                    :delete-route="route('admin.emballages.destroy', $pkg)"
                                />
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $packagings->appends(['tab' => 'packaging', 'search' => $search])->links() }}
            </div>
        @endif
    @endif

@endsection
