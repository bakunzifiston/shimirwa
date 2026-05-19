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
