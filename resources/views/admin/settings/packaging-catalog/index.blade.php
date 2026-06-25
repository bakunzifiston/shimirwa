@extends('layouts.admin')
@section('title', 'Packaging Catalog')
@section('page_title', 'Packaging Catalog')
@section('page_subtitle', 'Define packaging types and their flour-per-unit weight')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />

    <x-admin.listing
        :paginator="$items"
        :search="$search"
        :clear-route="route('admin.settings.packaging-catalog.index')"
        placeholder="Search packaging types…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.settings.packaging-catalog.create') }}"
               data-drawer-src="{{ route('admin.settings.packaging-catalog.create') }}"
               data-drawer-title="Add packaging type"
               class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add type
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Name</th>
            <th class="text-right">kg / unit</th>
            <th>Inner units</th>
            <th class="text-center">Manual weight</th>
            <th>Description</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($items as $catalog)
            <tr>
                <td class="cell-primary">{{ $catalog->name }}</td>
                <td class="text-right font-mono font-semibold">{{ number_format($catalog->kg_per_unit, 3) }}</td>
                <td class="text-sm">
                    @if ($catalog->hasInnerUnits())
                        <span class="font-semibold" style="color:var(--admin-primary)">{{ $catalog->inner_units_per_package }} ×</span>
                        <span style="color:var(--admin-text)">{{ $catalog->innerUnitCatalog?->name ?? '—' }}</span>
                    @else
                        <span style="color:var(--admin-text-subtle)">—</span>
                    @endif
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
                <td colspan="7" class="py-10 text-center" style="color:var(--admin-text-muted)">
                    No packaging types yet. Add your first type above.
                </td>
            </tr>
        @endforelse
    </x-admin.listing>
@endsection
