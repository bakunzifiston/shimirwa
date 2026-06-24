@extends('layouts.admin')

@section('title', 'Roasting')
@section('page_title', 'Roasting')
@section('page_subtitle', 'Roasting production records')

@section('content')
    <div class="admin-listing-page">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Roasting summary">
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
                <th>Date</th><th>Batch</th><th>Qty in</th><th>Qty out</th><th>Remaining</th><th>Loss</th><th>Chef</th><th class="text-right">Actions</th>
            </x-slot:head>

            @forelse ($roastings as $roasting)
                <tr>
                    <td>{{ optional($roasting->date)->format('Y-m-d') }}</td>
                    <td class="cell-primary">{{ $roasting->batch }}</td>
                    <td>{{ number_format($roasting->quantity_in, 2) }} kg</td>
                    <td>{{ number_format($roasting->quantityOut(), 2) }} kg</td>
                    <td>{{ number_format($roasting->remainingUsable(), 2) }} kg</td>
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
                <x-admin.empty-state colspan="8" />
            @endforelse
        </x-admin.listing>
    </div>
@endsection
