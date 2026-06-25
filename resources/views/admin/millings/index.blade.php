@extends('layouts.admin')

@section('title', 'Milling')
@section('page_title', 'Milling')
@section('page_subtitle', 'Milling batches and flour output')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />
    <x-admin.listing
        :paginator="$millings"
        :search="$search"
        :clear-route="route('admin.millings.index')"
        placeholder="Search batchâ€¦"
    >
        <x-slot:actions>
            <a href="{{ route('admin.millings.create') }}" data-drawer-src="{{ route('admin.millings.create') }}" data-drawer-title="Add" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add milling
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Date</th><th>Batch</th><th>Mixed</th><th>Output</th><th>Employee</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($millings as $milling)
            <tr>
                <td>{{ optional($milling->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $milling->batch_number }}</td>
                <td>{{ number_format($milling->total_mixed_quantity, 2) }} kg</td>
                <td>{{ number_format($milling->output_flour, 2) }} kg</td>
                <td>{{ $milling->employee?->full_name }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.millings.show', $milling)"
                        :edit-route="route('admin.millings.edit', $milling)"
                        :delete-route="route('admin.millings.destroy', $milling)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" />
        @endforelse
    </x-admin.listing>
@endsection

