@extends('layouts.admin')

@section('title', 'Sorting')
@section('page_title', 'Sorting')
@section('page_subtitle', 'Sorting production records')

@section('content')
    <x-admin.listing
        :paginator="$sortings"
        :search="$search"
        :clear-route="route('admin.sortings.index')"
        placeholder="Search batch…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.sortings.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add sorting
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Item</th><th>Batch</th><th>Qty in</th><th>Loss</th><th>Employee</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($sortings as $sorting)
            <tr>
                <td>{{ optional($sorting->date)->format('Y-m-d') }}</td>
                <td>{{ $sorting->rawMaterialStock?->item }}</td>
                <td class="cell-primary">{{ $sorting->rawMaterialStock?->batch_number }}</td>
                <td>{{ number_format($sorting->quantity_in, 2) }} kg</td>
                <td>{{ number_format($sorting->loss, 2) }} kg</td>
                <td>{{ $sorting->employee?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.sortings.show', $sorting)"
                        :edit-route="route('admin.sortings.edit', $sorting)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="7" />
        @endforelse
    </x-admin.listing>
@endsection
