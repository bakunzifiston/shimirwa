@extends('layouts.admin')
@section('title', 'Settings — Catalogs')
@section('page_title', 'Settings')
@section('page_subtitle', 'Product and packaging catalog configuration')

@section('content')

{{-- Tabs --}}
<div class="flex gap-2 mb-4 p-1 rounded-lg w-fit" style="background:var(--admin-bg);border:1px solid var(--admin-border)">
    <a href="{{ route('admin.settings.index', ['tab' => 'product']) }}"
       class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-all"
       style="{{ $tab === 'product'
           ? 'background:var(--admin-bg-elevated);box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid var(--admin-border)'
           : 'color:var(--admin-text-subtle);border:1px solid transparent' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Product Catalog
        <span class="admin-badge admin-badge--primary">{{ $productItems->total() }}</span>
    </a>
    <a href="{{ route('admin.settings.index', ['tab' => 'packaging']) }}"
       class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-all"
       style="{{ $tab === 'packaging'
           ? 'background:var(--admin-bg-elevated);box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid var(--admin-border)'
           : 'color:var(--admin-text-subtle);border:1px solid transparent' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2M8 7V5a2 2 0 0 0-4 0v2"/></svg>
        Packaging Catalog
        <span class="admin-badge admin-badge--primary">{{ $packagingItems->total() }}</span>
    </a>
</div>

{{-- Search + Add action bar --}}
<div class="flex flex-wrap items-center justify-between gap-2 mb-3">
    <form method="GET" action="{{ route('admin.settings.index') }}" class="flex gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="search" value="{{ $search }}"
               placeholder="{{ $tab === 'product' ? 'Search products…' : 'Search packaging types…' }}"
               class="admin-input" style="max-width:220px">
        <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Search</button>
        @if ($search)
            <a href="{{ route('admin.settings.index', ['tab' => $tab]) }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
        @endif
    </form>

    @if ($tab === 'product')
        <a href="{{ route('admin.settings.product-catalog.create') }}"
           data-drawer-src="{{ route('admin.settings.product-catalog.create') }}"
           data-drawer-title="Add product"
           class="admin-btn admin-btn-primary admin-btn-sm">
            <x-admin.icon name="plus" class="!h-4 !w-4" /> Add product
        </a>
    @else
        <a href="{{ route('admin.settings.packaging-catalog.create') }}"
           data-drawer-src="{{ route('admin.settings.packaging-catalog.create') }}"
           data-drawer-title="Add packaging type"
           class="admin-btn admin-btn-primary admin-btn-sm">
            <x-admin.icon name="plus" class="!h-4 !w-4" /> Add packaging type
        </a>
    @endif
</div>

{{-- ===== PRODUCT CATALOG TAB ===== --}}
@if ($tab === 'product')
    <div class="admin-card overflow-hidden">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Sub-category</th>
                    <th>Unit</th>
                    <th class="text-center">Sort</th>
                    <th class="text-center">Roast</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productItems as $catalog)
                <tr>
                    <td class="cell-primary">{{ $catalog->name }}</td>
                    <td>
                        @if ($catalog->category === 'production')
                            <span class="admin-badge admin-badge--warning">Production</span>
                        @else
                            <span class="admin-badge admin-badge--primary">E-commerce</span>
                        @endif
                    </td>
                    <td>{{ $catalog->sub_category ?? '—' }}</td>
                    <td>{{ $catalog->unit }}</td>
                    <td class="text-center">
                        @if ($catalog->requires_sorting)
                            <svg width="15" height="15" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg width="15" height="15" fill="none" stroke="#d1d5db" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($catalog->requires_roasting)
                            <svg width="15" height="15" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg width="15" height="15" fill="none" stroke="#d1d5db" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        @endif
                    </td>
                    <td>
                        @if ($catalog->is_active)
                            <span class="admin-badge admin-badge--success">Active</span>
                        @else
                            <span class="admin-badge admin-badge--neutral">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <x-admin.row-actions
                            :view-route="route('admin.settings.product-catalog.show', $catalog)"
                            :edit-route="route('admin.settings.product-catalog.edit', $catalog)"
                            :delete-route="route('admin.settings.product-catalog.destroy', $catalog)"
                            view-title="View product"
                            edit-title="Edit product"
                        />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-10 text-center" style="color:var(--admin-text-muted)">
                        No products yet. Add your first item above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $productItems->appends(['tab' => 'product', 'search' => $search])->links() }}
    </div>

{{-- ===== PACKAGING CATALOG TAB ===== --}}
@else
    <div class="admin-card overflow-hidden">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-right">kg / unit</th>
                    <th class="text-center">Manual weight</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($packagingItems as $catalog)
                <tr>
                    <td class="cell-primary">{{ $catalog->name }}</td>
                    <td class="text-right font-mono font-semibold">
                        {{ $catalog->manual_weight ? '—' : number_format($catalog->kg_per_unit, 3) }}
                    </td>
                    <td class="text-center">
                        @if ($catalog->manual_weight)
                            <span class="admin-badge admin-badge--warning">Manual</span>
                        @else
                            <span class="text-xs" style="color:var(--admin-text-subtle)">Auto</span>
                        @endif
                    </td>
                    <td class="text-sm" style="color:var(--admin-text-subtle)">{{ $catalog->description ?? '—' }}</td>
                    <td>
                        @if ($catalog->is_active)
                            <span class="admin-badge admin-badge--success">Active</span>
                        @else
                            <span class="admin-badge admin-badge--neutral">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <x-admin.row-actions
                            :view-route="route('admin.settings.packaging-catalog.show', $catalog)"
                            :edit-route="route('admin.settings.packaging-catalog.edit', $catalog)"
                            :delete-route="route('admin.settings.packaging-catalog.destroy', $catalog)"
                            view-title="View packaging type"
                            edit-title="Edit packaging type"
                        />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center" style="color:var(--admin-text-muted)">
                        No packaging types yet. Add your first type above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $packagingItems->appends(['tab' => 'packaging', 'search' => $search])->links() }}
    </div>
@endif

@endsection
