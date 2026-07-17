@extends('layouts.admin')

@section('title', 'Packaging')
@section('page_title', $emballage->packaging_batch_id)
@section('page_subtitle', strtoupper($emballage->packaging_type ?? 'Packaging'))

@section('header_actions')
    <a href="{{ route('admin.emballages.edit', $emballage) }}" class="admin-btn admin-btn-primary admin-btn-sm">
        <x-admin.icon name="pencil" class="!h-4 !w-4" /> Edit
    </a>
@endsection

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.emballages.edit', $emballage) }}"
               data-drawer-src="{{ route('admin.emballages.edit', $emballage) }}"
               data-drawer-title="Edit"
               class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
        </div>
    </div>
    <div class="admin-card max-w-3xl">
        <div class="admin-card-header"><h2 class="admin-card-title">Packaging details</h2></div>
        <div class="admin-card-body">
            <dl class="admin-detail-grid">
                <div class="admin-detail-item"><dt>Date</dt><dd>{{ optional($emballage->date)->format('M j, Y') }}</dd></div>
                <div class="admin-detail-item"><dt>Batch ID</dt><dd>{{ $emballage->packaging_batch_id }}</dd></div>
                <div class="admin-detail-item"><dt>Type</dt><dd><span class="admin-badge admin-badge--primary">{{ strtoupper($emballage->packaging_type ?? '') }}</span></dd></div>
                <div class="admin-detail-item"><dt>Units</dt><dd>{{ $emballage->item }}</dd></div>
                <div class="admin-detail-item"><dt>Flour (kg)</dt><dd>{{ number_format($emballage->quantity, 2) }}</dd></div>
                <div class="admin-detail-item"><dt>Milling batch</dt><dd>{{ $emballage->milling?->batch_number ?? '—' }}</dd></div>
                @if(!empty($emballage->milling_overflow))
                    @php
                        $ovMillings = \App\Models\Milling::whereIn('id', collect($emballage->milling_overflow)->pluck('milling_id'))->get()->keyBy('id');
                    @endphp
                    <div class="admin-detail-item span-2" style="grid-column: span 2">
                        <dt>Also drew flour from</dt>
                        <dd>
                            @foreach($emballage->milling_overflow as $ov)
                                <span class="admin-badge admin-badge--warning" style="margin-right:4px">
                                    {{ $ovMillings[$ov['milling_id'] ?? 0]->batch_number ?? '#'.($ov['milling_id'] ?? '?') }} — {{ number_format((float)($ov['quantity'] ?? 0), 2) }} kg
                                </span>
                            @endforeach
                        </dd>
                    </div>
                @endif
                <div class="admin-detail-item">
                    <dt>Packaging material batch</dt>
                    <dd>{{ $emballage->rawMaterialStock?->batch_number ?? '—' }}
                        @if($emballage->raw_material_stock_id)
                            ({{ number_format($emballage->primaryPackagingUnits()) }} units)
                        @endif
                    </dd>
                </div>
                @if(!empty($emballage->packaging_overflow))
                    @php
                        $ovStocks = \App\Models\RawMaterialStock::whereIn('id', collect($emballage->packaging_overflow)->pluck('stock_id'))->get()->keyBy('id');
                    @endphp
                    <div class="admin-detail-item span-2" style="grid-column: span 2">
                        <dt>Also took packaging from</dt>
                        <dd>
                            @foreach($emballage->packaging_overflow as $ov)
                                <span class="admin-badge admin-badge--primary" style="margin-right:4px">
                                    {{ $ovStocks[$ov['stock_id'] ?? 0]->batch_number ?? '#'.($ov['stock_id'] ?? '?') }} — {{ number_format((float)($ov['units'] ?? 0)) }} units
                                </span>
                            @endforeach
                        </dd>
                    </div>
                @endif
                <div class="admin-detail-item"><dt>Employee</dt><dd>{{ $emballage->employee?->full_name }}</dd></div>
                @if($emballage->comment)
                    <div class="admin-detail-item span-2" style="grid-column: span 2"><dt>Comment</dt><dd>{{ $emballage->comment }}</dd></div>
                @endif
            </dl>
            <form method="POST" action="{{ route('admin.emballages.destroy', $emballage) }}" class="admin-form-actions" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
            </form>
        </div>
    </div>
@endsection
