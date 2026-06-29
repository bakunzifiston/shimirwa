<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $events = Event::query()
            ->withCount('media')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when(in_array($status, [Event::STATUS_DRAFT, Event::STATUS_PUBLISHED], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.index', compact('events', 'search', 'status'));
    }

    public function create(): View
    {
        $event = new Event(['status' => Event::STATUS_DRAFT]);

        return view('admin.events.create', compact('event'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->saveFile($request->file('cover_image'), 'events');
        }

        if ($data['status'] === Event::STATUS_PUBLISHED) {
            $data['published_at'] ??= now();
        }

        $event = Event::create($data);
        $this->saveMedia($event, $request->file('media') ?? []);

        return redirect()->route('admin.events.show', $event)->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load('media');

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $event->load('media');

        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validated($request, $event->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $event->id);

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                $this->deleteFile($event->cover_image);
            }
            $data['cover_image'] = $this->saveFile($request->file('cover_image'), 'events');
        }

        if ($data['status'] === Event::STATUS_PUBLISHED && ! $event->published_at) {
            $data['published_at'] = now();
        }

        $event->update($data);
        $this->saveMedia($event, $request->file('media') ?? []);

        return redirect()->route('admin.events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->cover_image) {
            $this->deleteFile($event->cover_image);
        }
        foreach ($event->media as $item) {
            $this->deleteFile($item->path);
        }
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    public function destroyMedia(Event $event, EventMedia $media): RedirectResponse
    {
        abort_if($media->event_id !== $event->id, 404);
        $this->deleteFile($media->path);
        $media->delete();

        return back()->with('success', 'Media item removed.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'event_date'   => 'nullable|date',
            'location'     => 'nullable|string|max:255',
            'status'       => 'required|in:draft,published',
            'cover_image'  => 'nullable|image|max:10240',
            'media'        => 'nullable|array',
            'media.*'      => 'file|mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov,avi|max:102400',
        ]);
    }

    private function saveMedia(Event $event, array $files): void
    {
        $sort = (int) $event->media()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $mime = $file->getMimeType() ?? '';
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $this->saveFile($file, 'event-media');

            EventMedia::create([
                'event_id'   => $event->id,
                'type'       => $type,
                'path'       => $path,
                'sort_order' => ++$sort,
            ]);
        }
    }

    private function saveFile(UploadedFile $file, string $folder): string
    {
        $dir = public_path('uploads/'.$folder);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $ext      = $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid().'.'.strtolower($ext);
        $file->move($dir, $filename);

        return 'uploads/'.$folder.'/'.$filename;
    }

    private function deleteFile(string $path): void
    {
        if (str_starts_with($path, 'uploads/')) {
            $full = public_path($path);
            if (File::isFile($full)) {
                File::delete($full);
            }
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($value) ?: 'event';
        $original = $slug;
        $i        = 1;
        while (Event::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }
}
