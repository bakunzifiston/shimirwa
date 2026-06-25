@extends('layouts.admin')

@section('title', 'Roasting')
@section('page_title', 'Roasting')
@section('page_subtitle', 'Roasting production records')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />
    <x-admin.listing
        :paginator="$roastings"
        :search="$search"
        :clear-route="route('admin.roastings.index')"
        placeholder="Search batchâ€¦"
    >
        <x-slot:actions>
            <a href="{{ route('admin.roastings.create') }}" data-drawer-src="{{ route('admin.roastings.create') }}" data-drawer-title="Add" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add roasting
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Item</th><th>Batch</th><th>Qty in</th><th>Loss</th><th>Qty out</th><th>Chef</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($roastings as $roasting)
            @php
                $item = $roasting->rawMaterialStock?->item
                     ?? $roasting->sorting?->rawMaterialStock?->item
                     ?? '—';
            @endphp
            <tr>
                <td>{{ optional($roasting->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $item }}</td>
                <td class="font-mono text-xs">{{ $roasting->batch }}</td>
                <td>{{ number_format($roasting->quantity_in, 1) }} kg</td>
                <td class="text-red-500">{{ number_format($roasting->loss ?? 0, 1) }} kg</td>
                <td class="font-semibold db-revenue-today">{{ number_format($roasting->quantity_out, 1) }} kg</td>
                <td>{{ $roasting->chef?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.roastings.show', $roasting)"
                        :edit-route="route('admin.roastings.edit', $roasting)"
                        :delete-route="route('admin.roastings.destroy', $roasting)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="8" />
        @endforelse
    </x-admin.listing>
@endsection

