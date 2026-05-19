@extends('layouts.admin')

@section('title', 'Roasting')
@section('page_title', 'Roasting')
@section('page_subtitle', 'Roasting production records')

@section('content')
    <x-admin.listing
        :paginator="$roastings"
        :search="$search"
        :clear-route="route('admin.roastings.index')"
        placeholder="Search batch…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.roastings.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add roasting
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Batch</th><th>Qty in</th><th>Loss</th><th>Chef</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($roastings as $roasting)
            <tr>
                <td>{{ optional($roasting->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $roasting->batch }}</td>
                <td>{{ number_format($roasting->quantity_in, 2) }} kg</td>
                <td>{{ number_format($roasting->loss, 2) }} kg</td>
                <td>{{ $roasting->chef?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.roastings.show', $roasting)"
                        :edit-route="route('admin.roastings.edit', $roasting)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" />
        @endforelse
    </x-admin.listing>
@endsection
