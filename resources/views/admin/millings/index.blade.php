@extends('layouts.admin')

@section('title', 'Milling')
@section('page_title', 'Milling')
@section('page_subtitle', 'Milling batches and flour output')

@section('content')
    <div class="admin-listing-page">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Milling summary">
            @foreach ($summaryStats as $stat)
                <article class="admin-stat-card admin-stat-card--primary">
                    <div class="admin-stat-card-top">
                        <p class="admin-stat-label">{{ $stat['label'] }}</p>
                        <span class="admin-stat-icon admin-stat-icon--inverse">
                            <x-admin.icon :name="$stat['icon']" class="!h-5 !w-5" />
                        </span>
                    </div>
                    <p class="admin-stat-value @if (! empty($stat['valueAccent'])) admin-stat-value--accent @endif">
                        {{ $stat['value'] }}
                    </p>
                </article>
            @endforeach
        </section>

        <x-admin.listing
            :paginator="$millings"
            :search="$search"
            :clear-route="route('admin.millings.index')"
            placeholder="Search batch…"
        >
            <x-slot:actions>
                <a href="{{ route('admin.millings.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
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
                        />
                    </td>
                </tr>
            @empty
                <x-admin.empty-state colspan="6" />
            @endforelse
        </x-admin.listing>
    </div>
@endsection
