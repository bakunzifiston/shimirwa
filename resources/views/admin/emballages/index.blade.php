@extends('layouts.admin')

@section('title', 'Packaging')
@section('page_title', 'Packaging')
@section('page_subtitle', 'Packaging and emballage records')

@section('content')
    <div class="admin-listing-page">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Packaging summary">
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
            :paginator="$emballages"
            :search="$search"
            :clear-route="route('admin.emballages.index')"
            placeholder="Search packaging…"
        >
            <x-slot:actions>
                <a href="{{ route('admin.emballages.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
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
                        />
                    </td>
                </tr>
            @empty
                <x-admin.empty-state colspan="6" />
            @endforelse
        </x-admin.listing>
    </div>
@endsection
