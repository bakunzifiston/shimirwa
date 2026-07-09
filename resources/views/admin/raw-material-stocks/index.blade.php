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

    {{-- Raw / production materials --}}
    @if ($rawRows->isNotEmpty())
    <div class="mb-2 overflow-x-auto">
        <div class="px-1 pb-1 text-xs font-semibold uppercase tracking-wide" style="color:var(--admin-text-subtle)">Raw / production materials</div>
        <table class="admin-table">
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

    {{-- Packaging materials --}}
    @if ($pkgRows->isNotEmpty())
    <div class="mb-4 overflow-x-auto">
        <div class="px-1 pb-1 text-xs font-semibold uppercase tracking-wide" style="color:var(--admin-text-subtle)">Packaging materials</div>
        <table class="admin-table">
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

