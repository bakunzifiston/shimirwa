@extends('layouts.admin')

@section('title', 'Reception of materials')
@section('page_title', 'Reception of materials')
@section('page_subtitle', 'Raw material stock intake')

@section('content')
    <x-admin.listing
        :paginator="$stocks"
        :search="$search"
        :clear-route="route('admin.raw-material-stocks.index')"
        placeholder="Search batch, item, supplier…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.raw-material-stocks.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Receive materials
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Item</th><th>Batch</th><th>Qty in</th><th>Supplier</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($stocks as $stock)
            <tr>
                <td>{{ optional($stock->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $stock->item }}</td>
                <td>{{ $stock->batch_number }}</td>
                <td>{{ number_format($stock->quantity_in, 2) }} kg</td>
                <td>{{ $stock->client?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.raw-material-stocks.show', $stock)"
                        :edit-route="route('admin.raw-material-stocks.edit', $stock)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" />
        @endforelse
    </x-admin.listing>
@endsection
