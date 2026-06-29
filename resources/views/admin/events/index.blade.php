@extends('layouts.admin')

@section('title', 'Events')
@section('page_title', 'Events')
@section('page_subtitle', 'Create and publish events with photos and videos to the public website')

@section('content')
<div class="admin-listing-page">
    <x-admin.listing :paginator="$events" :show-search="false">

        <x-slot:toolbar>
            <form method="GET" class="admin-filter-bar">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search events…" class="admin-input">
                </div>
                <div class="admin-filter-bar__filters">
                    <select name="status" class="admin-input" aria-label="Status">
                        <option value="">All statuses</option>
                        <option value="published" @selected($status === 'published')>Published</option>
                        <option value="draft"     @selected($status === 'draft')>Draft</option>
                    </select>
                </div>
                <div class="admin-filter-bar__actions">
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                    @if($search || $status)
                        <a href="{{ route('admin.events.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </x-slot:toolbar>

        <x-slot:actions>
            <a href="{{ route('admin.events.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                New event
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Cover</th>
            <th>Title</th>
            <th>Date</th>
            <th>Location</th>
            <th>Media</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse($events as $event)
            <tr>
                <td>
                    @if($event->coverImageUrl())
                        <img src="{{ $event->coverImageUrl() }}" alt="" class="h-10 w-10 rounded object-cover border">
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded border text-xs opacity-50">—</span>
                    @endif
                </td>
                <td class="cell-primary">{{ $event->title }}</td>
                <td style="font-size:.82rem;color:var(--admin-text-subtle);white-space:nowrap">
                    {{ $event->event_date?->format('d M Y') ?? '—' }}
                </td>
                <td style="font-size:.82rem;color:var(--admin-text-subtle)">
                    {{ $event->location ?? '—' }}
                </td>
                <td style="font-size:.82rem">
                    {{ $event->media_count }} file(s)
                </td>
                <td>
                    @if($event->isPublished())
                        <span class="admin-badge admin-badge--primary">Published</span>
                    @else
                        <span class="admin-badge">Draft</span>
                    @endif
                </td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.events.show', $event)"
                        :edit-route="route('admin.events.edit', $event)"
                        :delete-route="route('admin.events.destroy', $event)"
                        view-title="{{ $event->title }}"
                        delete-confirm="Delete this event and all its photos/videos?"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="7"
                title="No events yet"
                message="Create your first event to share photos and videos on the public website." />
        @endforelse

    </x-admin.listing>
</div>
@endsection
