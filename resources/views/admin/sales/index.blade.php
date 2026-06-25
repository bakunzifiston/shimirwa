@extends('layouts.admin')

@section('title', 'Sales')
@section('page_title', 'Sales')
@section('page_subtitle', 'Sales and distribution')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />
    <x-admin.listing
        :paginator="$sales"
        :search="$search"
        :clear-route="route('admin.sales.index')"
        placeholder="Search sales…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.sales.create') }}" data-drawer-src="{{ route('admin.sales.create') }}" data-drawer-title="Add sale" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add sale
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Product</th><th>Client</th><th>Employee</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($sales as $sale)
            <tr>
                <td>{{ optional($sale->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $sale->item }}</td>
                <td>{{ $sale->client?->full_name }}</td>
                <td>{{ $sale->employee?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.sales.show', $sale)"
                        :edit-route="route('admin.sales.edit', $sale)"
                        :delete-route="route('admin.sales.destroy', $sale)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="5" />
        @endforelse
    </x-admin.listing>
@endsection
