@extends('layouts.admin')

@section('title', 'Packaging')
@section('page_title', 'Packaging')
@section('page_subtitle', 'Packaging and emballage records')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />
    <x-admin.listing
        :paginator="$emballages"
        :search="$search"
        :clear-route="route('admin.emballages.index')"
        placeholder="Search packaging…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.emballages.create') }}" data-drawer-src="{{ route('admin.emballages.create') }}" data-drawer-title="Add packaging" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add packaging
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Batch ID</th><th>Type</th><th>Units</th><th>Flour (kg)</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($emballages as $emballage)
            <tr>
                <td>{{ optional($emballage->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $emballage->packaging_batch_id }}</td>
                <td><span class="admin-badge admin-badge--primary">{{ strtoupper($emballage->packaging_type ?? '') }}</span></td>
                <td>{{ $emballage->item }}</td>
                <td>{{ number_format($emballage->quantity, 2) }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.emballages.show', $emballage)"
                        :edit-route="route('admin.emballages.edit', $emballage)"
                        :delete-route="route('admin.emballages.destroy', $emballage)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" />
        @endforelse
    </x-admin.listing>
@endsection
