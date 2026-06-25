@extends('layouts.admin')

@section('title', 'Sales')
@section('page_title', 'Sales')
@section('page_subtitle', 'Sales and distribution')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />

    <x-admin.listing
        :paginator="$sales"
        :search="$search"
        :clear-route="route('admin.sales.index')"
        placeholder="Search sales…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.sales.create') }}"
               data-drawer-src="{{ route('admin.sales.create') }}"
               data-drawer-title="Add sale"
               class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add sale
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th>
            <th>Item</th>
            <th>Batches</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Total (RWF)</th>
            <th>Client</th>
            <th>Employee</th>
            <th>Returned</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($sales as $sale)
            <tr>
                <td class="text-sm whitespace-nowrap">{{ optional($sale->date)->format('d M Y') }}</td>
                <td class="cell-primary">{{ $sale->item }}</td>

                {{-- Batch summary: list packaging types sold --}}
                <td class="text-sm">
                    @if(is_array($sale->batches) && count($sale->batches))
                        <div class="flex flex-wrap gap-1">
                            @foreach($sale->batches as $b)
                                @php $emb = $sale->resolvedEmballages[$b['emballage_id'] ?? 0] ?? null; @endphp
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                                      style="background:var(--admin-bg-elevated);border:1px solid var(--admin-border)">
                                    {{ $emb?->packagingCatalog?->name ?? strtoupper($emb?->packaging_type ?? '—') }}
                                    @if($emb?->packagingCatalog?->hasInnerUnits())
                                        <span style="color:#854d0e">+{{ $emb->packagingCatalog->inner_units_per_package }}×{{ $emb->packagingCatalog->innerUnitCatalog?->name }}</span>
                                    @endif
                                    ×{{ $b['quantity'] ?? 0 }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span style="color:var(--admin-text-subtle)">—</span>
                    @endif
                </td>

                <td class="text-right font-mono font-semibold">{{ number_format($sale->quantity) }}</td>
                <td class="text-right font-mono font-semibold">{{ number_format($sale->total_price, 0) }}</td>
                <td class="text-sm">{{ $sale->client?->full_name ?? '—' }}</td>
                <td class="text-sm">{{ $sale->employee?->full_name ?? '—' }}</td>
                <td class="text-sm text-center">
                    @if($sale->returned > 0)
                        <span class="admin-badge admin-badge--warning">{{ $sale->returned }}</span>
                    @else
                        <span style="color:var(--admin-text-subtle)">—</span>
                    @endif
                </td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.sales.show', $sale)"
                        :edit-route="route('admin.sales.edit', $sale)"
                        :delete-route="route('admin.sales.destroy', $sale)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="9" />
        @endforelse
    </x-admin.listing>
@endsection
