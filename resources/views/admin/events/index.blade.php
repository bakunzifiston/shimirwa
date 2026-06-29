@extends('layouts.admin')

@section('title', 'Events')
@section('page_title', 'Events')
@section('page_subtitle', 'Create and publish events with photos and videos to the public website')

@section('content')
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
            <th>Photos/Videos</th>
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
                <td style="font-size:.82rem;color:var(--admin-text-subtle)">
                    {{ $event->event_date?->format('d M Y') ?? '—' }}
                </td>
                <td style="font-size:.82rem;color:var(--admin-text-subtle)">
                    {{ $event->location ?? '—' }}
                </td>
                <td style="font-size:.82rem">{{ $event->media_count }} file(s)</td>
                <td>
                    @if($event->isPublished())
                        <span style="font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:99px;background:#dcfce7;color:#166534">Published</span>
                    @else
                        <span style="font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:99px;background:#f1f5f9;color:#475569">Draft</span>
                    @endif
                </td>
                <td class="text-right" style="white-space:nowrap">
                    <a href="{{ route('admin.events.show', $event) }}" class="admin-btn admin-btn-ghost admin-btn-sm">View</a>
                    <a href="{{ route('admin.events.edit', $event) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                          onsubmit="return confirm('Delete this event?')" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:2.5rem;color:var(--admin-text-subtle)">
                    No events yet.
                    <a href="{{ route('admin.events.create') }}" style="color:var(--admin-primary)">Create one</a>
                </td>
            </tr>
        @endforelse
    </x-admin.listing>
@endsection
