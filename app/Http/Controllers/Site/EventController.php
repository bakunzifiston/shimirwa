<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::published()
            ->withCount('media')
            ->orderByDesc('event_date')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('pages.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        abort_if(! $event->isPublished(), 404);
        $event->load('media');

        $related = Event::published()
            ->where('id', '!=', $event->id)
            ->orderByDesc('event_date')
            ->limit(3)
            ->get();

        return view('pages.events.show', compact('event', 'related'));
    }
}
