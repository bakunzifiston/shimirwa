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
                <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide" style="color: var(--admin-text-muted)">Line items</h3>
                <div class="admin-table-scroll mt-3 rounded-lg border" style="border-color: var(--admin-border)">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Packaging #</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->batches as $batch)
                                <tr>
                                    <td class="cell-primary">#{{ $batch['emballage_id'] ?? '—' }}</td>
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
