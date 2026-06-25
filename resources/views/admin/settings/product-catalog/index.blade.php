@extends('layouts.admin')

@section('title', 'Product Catalog')
@section('page_title', 'Product Catalog')
@section('page_subtitle', 'Items used in production and sold in the shop')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />

    <x-admin.listing
        :paginator="$items"
        :search="$search"
        :clear-route="route('admin.settings.product-catalog.index')"
        placeholder="Search name, sub-category…"
    >
        <x-slot:toolbar>
            <form method="GET" action="{{ route('admin.settings.product-catalog.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search name, sub-category…" class="admin-input">
                </div>
                <select name="category" class="admin-input" style="max-width:160px" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    <option value="production" @selected($category === 'production')>Production</option>
                    <option value="ecommerce"  @selected($category === 'ecommerce')>E-commerce</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Search</button>
                @if ($search || $category)
                    <a href="{{ route('admin.settings.product-catalog.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                @endif
            </form>
            <div class="admin-panel-toolbar-actions">
                <a href="{{ route('admin.settings.product-catalog.create') }}"
                   data-drawer-src="{{ route('admin.settings.product-catalog.create') }}"
                   data-drawer-title="Add catalog item"
                   class="admin-btn admin-btn-primary admin-btn-sm">
                    <x-admin.icon name="plus" class="!h-4 !w-4" />
                    Add item
                </a>
            </div>
        </x-slot:toolbar>

        <x-slot:head>
            <th>Name</th>
            <th>Category</th>
            <th>Sub-category</th>
            <th>Unit</th>
            <th class="text-center">Can Sort</th>
            <th class="text-center">Can Roast</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($items as $catalog)
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
                        <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <svg width="16" height="16" fill="none" stroke="#d1d5db" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    @endif
                </td>
                <td class="text-center">
                    @if ($catalog->requires_roasting)
                        <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <svg width="16" height="16" fill="none" stroke="#d1d5db" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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
                        view-title="View catalog item"
                        edit-title="Edit catalog item"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-10 text-center" style="color:var(--admin-text-muted)">
                    No catalog items yet. Add your first item above.
                </td>
            </tr>
        @endforelse
    </x-admin.listing>
@endsection
