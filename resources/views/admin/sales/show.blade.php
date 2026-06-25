@extends('layouts.admin')

@section('title', 'Sale')
@section('page_title', $sale->item)
@section('page_subtitle', optional($sale->date)->format('M j, Y'))

@section('header_actions')
    <a href="{{ route('admin.sales.edit', $sale) }}" class="admin-btn admin-btn-primary admin-btn-sm">
        <x-admin.icon name="pencil" class="!h-4 !w-4" /> Edit
    </a>
@endsection

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.sales.edit', $sale) }}"
               data-drawer-src="{{ route('admin.sales.edit', $sale) }}"
               data-drawer-title="Edit"
               class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
        </div>
    </div>
    <div class="admin-card max-w-3xl">
        <div class="admin-card-header"><h2 class="admin-card-title">Sale details</h2></div>
        <div class="admin-card-body">
            <dl class="admin-detail-grid">
                <div class="admin-detail-item"><dt>Client</dt><dd>{{ $sale->client?->full_name }}</dd></div>
                <div class="admin-detail-item"><dt>Employee</dt><dd>{{ $sale->employee?->full_name }}</dd></div>
                @if($sale->returned)
                    <div class="admin-detail-item"><dt>Returned</dt><dd>{{ $sale->returned }}</dd></div>
                @endif
                @if($sale->reason)
                    <div class="admin-detail-item" style="grid-column: span 2"><dt>Reason</dt><dd>{{ $sale->reason }}</dd></div>
                @endif
            </dl>

            @if(is_array($sale->batches) && count($sale->batches))
                @php
                    $embIds = collect($sale->batches)->pluck('emballage_id')->filter()->all();
                    $embMap = \App\Models\Emballage::with(['packagingCatalog.innerUnitCatalog'])
                        ->whereIn('id', $embIds)->get()->keyBy('id');
                @endphp
                <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide" style="color: var(--admin-text-muted)">Line items</h3>
                <div class="admin-table-scroll mt-3 rounded-lg border" style="border-color: var(--admin-border)">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Packaging batch</th>
                                <th>Type</th>
                                <th>Inner units</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->batches as $batch)
                                @php
                                    $emb     = $embMap[$batch['emballage_id'] ?? null] ?? null;
                                    $catName = $emb?->packagingCatalog?->name ?? strtoupper($emb?->packaging_type ?? '—');
                                    $inner   = '';
                                    if ($emb?->packagingCatalog?->hasInnerUnits()) {
                                        $perPkg    = $emb->packagingCatalog->inner_units_per_package;
                                        $innerName = $emb->packagingCatalog->innerUnitCatalog?->name ?? 'inner';
                                        $inner = "{$perPkg} × {$innerName}";
                                    }
                                @endphp
                                <tr>
                                    <td class="cell-primary">{{ $emb?->packaging_batch_id ?? '#'.($batch['emballage_id'] ?? '—') }}</td>
                                    <td>{{ $catName }}</td>
                                    <td>
                                        @if($inner)
                                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded" style="background:#fef9c3;color:#854d0e">{{ $inner }}</span>
                                        @else
                                            <span style="color:var(--admin-text-subtle)">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $batch['quantity'] ?? 0 }}</td>
                                    <td>{{ number_format($batch['unit_price'] ?? 0, 0) }} RWF</td>
                                    <td>{{ number_format($batch['line_total'] ?? 0, 0) }} RWF</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.sales.destroy', $sale) }}" class="admin-form-actions" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
            </form>
        </div>
    </div>
@endsection
