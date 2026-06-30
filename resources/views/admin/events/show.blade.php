@extends('layouts.admin')

@section('title', $event->title)
@section('page_title', 'Event')
@section('page_subtitle', $event->title)

@section('content')
    <div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <a href="{{ route('admin.events.edit', $event) }}" class="admin-btn admin-btn-primary admin-btn-sm">
            <x-admin.icon name="cog" class="!h-4 !w-4" /> Edit
        </a>
        @if($event->isPublished())
            <a href="{{ route('events.show', $event) }}" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm">
                View on website ↗
            </a>
        @endif
        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
              onsubmit="return confirm('Delete this event and all its media?')">
            @csrf @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
        </form>
    </div>

    <div style="display:grid;gap:1.25rem;max-width:940px">

        {{-- Info card --}}
        <div class="admin-form-panel" style="margin-bottom:0">
            <div class="admin-form-panel-body" style="padding:1rem 1.25rem">
                <div style="display:flex;flex-wrap:wrap;gap:1.25rem;align-items:center;justify-content:space-between">
                    <div style="display:flex;gap:1.25rem;flex-wrap:wrap">
                        <div>
                            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-text-subtle);margin-bottom:.25rem">Status</div>
                            @if($event->isPublished())
                                <span style="font-size:.78rem;font-weight:700;padding:.25rem .7rem;border-radius:99px;background:#dcfce7;color:#166534">Published</span>
                            @else
                                <span style="font-size:.78rem;font-weight:700;padding:.25rem .7rem;border-radius:99px;background:#f1f5f9;color:#475569">Draft</span>
                            @endif
                        </div>
                        @if($event->event_date)
                        <div>
                            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-text-subtle);margin-bottom:.25rem">Event Date</div>
                            <span style="font-size:.875rem;color:var(--admin-text)">{{ $event->event_date->format('d F Y') }}</span>
                        </div>
                        @endif
                        @if($event->location)
                        <div>
                            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-text-subtle);margin-bottom:.25rem">Location</div>
                            <span style="font-size:.875rem;color:var(--admin-text)">{{ $event->location }}</span>
                        </div>
                        @endif
                        <div>
                            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-text-subtle);margin-bottom:.25rem">Media</div>
                            <span style="font-size:.875rem;color:var(--admin-text)">{{ $event->media->count() }} file(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cover --}}
        @if($event->coverImageUrl())
            <div class="admin-form-panel" style="margin-bottom:0">
                <header class="admin-form-panel-head"><div><h3 class="admin-form-panel-title">Cover image</h3></div></header>
                <div class="admin-form-panel-body">
                    <img src="{{ $event->coverImageUrl() }}" alt=""
                         style="max-height:260px;width:auto;max-width:100%;border-radius:8px;object-fit:cover;border:1px solid var(--admin-border)">
                </div>
            </div>
        @endif

        {{-- Description --}}
        @if($event->description)
            <div class="admin-form-panel" style="margin-bottom:0">
                <header class="admin-form-panel-head"><div><h3 class="admin-form-panel-title">Description</h3></div></header>
                <div class="admin-form-panel-body">
                    <p style="font-size:.9375rem;color:var(--admin-text);line-height:1.7;white-space:pre-line">{{ $event->description }}</p>
                </div>
            </div>
        @endif

        {{-- Media gallery --}}
        <div class="admin-form-panel" style="margin-bottom:0">
            <header class="admin-form-panel-head">
                <div>
                    <h3 class="admin-form-panel-title">Photos &amp; Videos</h3>
                    <p class="admin-form-panel-desc">
                        {{ $event->media->where('type','image')->count() }} photo(s) ·
                        {{ $event->media->where('type','video')->count() }} video(s)
                    </p>
                </div>
            </header>
            <div class="admin-form-panel-body">
                @if($event->media->isNotEmpty())
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.85rem">
                        @foreach($event->media as $item)
                            <div style="position:relative;border-radius:10px;overflow:hidden;
                                        border:1px solid var(--admin-border)">
                                @if($item->isImage())
                                    <a href="{{ $item->url() }}" target="_blank">
                                        <img src="{{ $item->url() }}" alt=""
                                             style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block">
                                    </a>
                                @else
                                    <video controls style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block;background:#000">
                                        <source src="{{ $item->url() }}" type="video/mp4">
                                    </video>
                                    <span style="position:absolute;top:.4rem;left:.4rem;font-size:.58rem;font-weight:800;text-transform:uppercase;
                                                 background:rgba(16,73,140,.85);color:white;padding:.18rem .45rem;border-radius:4px">VIDEO</span>
                                @endif
                                <form method="POST"
                                      action="{{ route('admin.events.media.destroy', [$event, $item]) }}"
                                      onsubmit="return confirm('Remove this file?')"
                                      style="position:absolute;top:.4rem;right:.4rem">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            style="width:1.7rem;height:1.7rem;border-radius:50%;border:none;cursor:pointer;
                                                   background:rgba(239,68,68,.9);color:white;display:flex;align-items:center;justify-content:center">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:1rem">
                        <a href="{{ route('admin.events.edit', $event) }}" class="admin-btn admin-btn-secondary admin-btn-sm">
                            + Add more photos/videos
                        </a>
                    </div>
                @else
                    <p style="color:var(--admin-text-subtle);font-size:.875rem">
                        No media yet.
                        <a href="{{ route('admin.events.edit', $event) }}" style="color:var(--admin-primary)">Upload photos &amp; videos</a>
                    </p>
                @endif
            </div>
        </div>

    </div>
@endsection
