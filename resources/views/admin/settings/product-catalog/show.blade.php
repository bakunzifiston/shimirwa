@extends('layouts.admin')

@section('title', $productCatalog->name)
@section('page_title', $productCatalog->name)

@section('content')
    <div class="admin-card" style="max-width:560px">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Catalog item details</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.settings.product-catalog.edit', $productCatalog) }}"
                   data-drawer-src="{{ route('admin.settings.product-catalog.edit', $productCatalog) }}"
                   data-drawer-title="Edit catalog item"
                   class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
            </div>
        </div>
        <div class="admin-card-body">
            <dl class="admin-detail-list">
                <div class="admin-detail-row">
                    <dt>Name</dt>
                    <dd>{{ $productCatalog->name }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Category</dt>
                    <dd>
                        @if ($productCatalog->category === 'production')
                            <span class="admin-badge admin-badge--warning">Production</span>
                        @else
                            <span class="admin-badge admin-badge--primary">E-commerce</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sub-category</dt>
                    <dd>{{ $productCatalog->sub_category ?? '—' }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Unit</dt>
                    <dd>{{ $productCatalog->unit }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Status</dt>
                    <dd>
                        @if ($productCatalog->is_active)
                            <span class="admin-badge admin-badge--success">Active</span>
                        @else
                            <span class="admin-badge admin-badge--neutral">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Process flags</dt>
                    <dd class="flex flex-wrap gap-2">
                        @if ($productCatalog->requires_sorting)
                            <span class="admin-badge admin-badge--warning">Requires sorting</span>
                        @endif
                        @if ($productCatalog->requires_roasting)
                            <span class="admin-badge admin-badge--warning">Requires roasting</span>
                        @endif
                        @if (!$productCatalog->requires_sorting && !$productCatalog->requires_roasting)
                            <span style="color:var(--admin-text-muted)" class="text-sm">None</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sort order</dt>
                    <dd>{{ $productCatalog->sort_order }}</dd>
                </div>
                @if ($productCatalog->description)
                    <div class="admin-detail-row">
                        <dt>Description</dt>
                        <dd>{{ $productCatalog->description }}</dd>
                    </div>
                @endif
                <div class="admin-detail-row">
                    <dt>Created</dt>
                    <dd>{{ $productCatalog->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.settings.product-catalog.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">
            &larr; Back to catalog
        </a>
    </div>
@endsection
