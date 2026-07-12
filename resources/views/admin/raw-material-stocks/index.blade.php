@extends('layouts.admin')

@section('title', 'Reception of materials')
@section('page_title', 'Reception of materials')
@section('page_subtitle', 'Raw material stock intake')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />

    {{-- Per-item stock summary --}}
    @if ($itemTotals->isNotEmpty())
    @php
        $rawRows  = $itemTotals->filter(fn($r) => !in_array($r->type, ['Packaging Material','Packaging Staff','packaging material','packaging staff']));
        $pkgRows  = $itemTotals->filter(fn($r) => in_array($r->type, ['Packaging Material','Packaging Staff','packaging material','packaging staff']));
    @endphp

    <div class="admin-card overflow-hidden mb-4">
        {{-- Tab bar --}}
        <div class="flex border-b" style="border-color:var(--admin-border)" id="stock-summary-tabs">
            @if ($rawRows->isNotEmpty())
            <button type="button"
                    data-tab="raw"
                    onclick="switchStockTab('raw')"
                    class="stock-tab-btn px-4 py-2.5 text-sm font-medium flex items-center gap-1.5 border-b-2 transition-colors"
                    style="margin-bottom:-1px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 8v4l3 3"/></svg>
                Raw / Production
                <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold admin-badge admin-badge--primary">{{ $rawRows->count() }}</span>
            </button>
            @endif
            @if ($pkgRows->isNotEmpty())
            <button type="button"
                    data-tab="pkg"
                    onclick="switchStockTab('pkg')"
                    class="stock-tab-btn px-4 py-2.5 text-sm font-medium flex items-center gap-1.5 border-b-2 transition-colors"
                    style="margin-bottom:-1px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2M8 7V5a2 2 0 0 0-4 0v2"/></svg>
                Packaging
                <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold admin-badge admin-badge--neutral">{{ $pkgRows->count() }}</span>
            </button>
            @endif
        </div>

        {{-- Raw / production materials panel --}}
        @if ($rawRows->isNotEmpty())
        <div id="stock-tab-raw" class="stock-tab-panel overflow-x-auto">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Item</th>
                        <th class="text-center">Batches</th>
                        <th class="text-right">Received</th>
                        <th class="text-right">Remaining</th>
                        <th class="text-right">Consumed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rawRows as $row)
                    @php
                        $consumed = max((float)$row->total_received - (float)$row->total_remaining, 0);
                        $pct = $row->total_received > 0 ? round($consumed / $row->total_received * 100) : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $row->item }}</td>
                        <td class="text-center">
                            <span class="admin-badge admin-badge--primary">{{ $row->batches }}</span>
                        </td>
                        <td class="text-right">{{ number_format((float)$row->total_received, 1) }} kg</td>
                        <td class="text-right font-semibold db-revenue-today">{{ number_format((float)$row->total_remaining, 1) }} kg</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 h-1.5 rounded-full overflow-hidden" style="background:var(--admin-border)">
                                    <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#f97316"></div>
                                </div>
                                <span class="text-xs" style="color:var(--admin-text-subtle)">{{ number_format($consumed, 1) }} ({{ $pct }}%)</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Packaging materials panel --}}
        @if ($pkgRows->isNotEmpty())
        <div id="stock-tab-pkg" class="stock-tab-panel overflow-x-auto" style="display:none">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Item</th>
                        <th class="text-center">Batches</th>
                        <th class="text-right">Received (units)</th>
                        <th class="text-right">Remaining</th>
                        <th class="text-right">Used in packaging</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pkgRows as $row)
                    @php
                        $used = max((float)$row->total_received - (float)$row->total_remaining, 0);
                        $pct  = $row->total_received > 0 ? round($used / $row->total_received * 100) : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $row->item }}</td>
                        <td class="text-center">
                            <span class="admin-badge admin-badge--primary">{{ $row->batches }}</span>
                        </td>
                        <td class="text-right">{{ number_format((float)$row->total_received, 0) }}</td>
                        <td class="text-right font-semibold db-revenue-today">{{ number_format((float)$row->total_remaining, 0) }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 h-1.5 rounded-full overflow-hidden" style="background:var(--admin-border)">
                                    <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#7c3aed"></div>
                                </div>
                                <span class="text-xs" style="color:var(--admin-text-subtle)">{{ number_format($used, 0) }} ({{ $pct }}%)</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <script>
    function switchStockTab(active) {
        document.querySelectorAll('.stock-tab-panel').forEach(p => p.style.display = 'none');
        const panel = document.getElementById('stock-tab-' + active);
        if (panel) panel.style.display = '';

        document.querySelectorAll('.stock-tab-btn').forEach(btn => {
            const isActive = btn.dataset.tab === active;
            btn.style.borderBottomColor = isActive ? 'var(--admin-primary)' : 'transparent';
            btn.style.color = isActive ? 'var(--admin-primary)' : 'var(--admin-text-subtle)';
        });
    }
    // Activate first available tab on load
    (function() {
        const first = document.querySelector('.stock-tab-btn');
        if (first) switchStockTab(first.dataset.tab);
    })();
    </script>
    @endif

    <x-admin.listing
        :paginator="$stocks"
        :search="$search"
        :clear-route="route('admin.raw-material-stocks.index')"
        placeholder="Search batch, item, supplierâ€¦"
    >
        <x-slot:actions>
            <a href="{{ route('admin.raw-material-stocks.create') }}" data-drawer-src="{{ route('admin.raw-material-stocks.create') }}" data-drawer-title="Add" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Receive materials
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Item</th><th>Batch</th><th>Received</th><th>Remaining</th><th>Supplier</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($stocks as $stock)
            <tr class="cursor-pointer"
                data-drawer-src="{{ route('admin.raw-material-stocks.show', $stock) }}"
                data-drawer-title="Reception — {{ $stock->item }}">
                <td>{{ optional($stock->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $stock->item }}</td>
                <td class="font-mono text-xs">{{ $stock->batch_number }}</td>
                <td>{{ number_format($stock->received, 1) }} kg</td>
                <td>
                    <span class="font-semibold db-revenue-today">{{ number_format($stock->quantity_in, 1) }} kg</span>
                </td>
                <td>{{ $stock->client?->full_name }}</td>
                <td class="text-right" onclick="if(!event.target.closest('[data-drawer-src]'))event.stopPropagation()">
                    <x-admin.row-actions
                        :view-route="route('admin.raw-material-stocks.show', $stock)"
                        :edit-route="route('admin.raw-material-stocks.edit', $stock)"
                        :delete-route="route('admin.raw-material-stocks.destroy', $stock)"
                        view-title="View reception"
                        edit-title="Edit reception"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="7" />
        @endforelse
    </x-admin.listing>
@endsection

