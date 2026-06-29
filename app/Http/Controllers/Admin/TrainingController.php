<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->string('search')->trim()->toString();
        $status   = $request->string('status')->toString();
        $category = $request->string('category')->toString();

        $trainings = Training::query()
            ->withCount('media')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            }))
            ->when(in_array($status, [Training::STATUS_DRAFT, Training::STATUS_PUBLISHED], true), fn ($q) => $q->where('status', $status))
            ->when(array_key_exists($category, Training::CATEGORIES), fn ($q) => $q->where('category', $category))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.trainings.index', compact('trainings', 'search', 'status', 'category'));
    }

    public function create(): View
    {
        $training = new Training(['status' => Training::STATUS_DRAFT, 'category' => 'general']);

        return view('admin.trainings.create', compact('training'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'trainings');
        }

        if ($data['status'] === Training::STATUS_PUBLISHED) {
            $data['published_at'] ??= now();
        }

        $training = Training::create($data);
        $this->storeMedia($training, $request->file('media') ?? []);

        return redirect()->route('admin.trainings.show', $training)->with('success', 'Training module created successfully.');
    }

    public function show(Training $training): View
    {
        $training->load('media');

        return view('admin.trainings.show', compact('training'));
    }

    public function edit(Training $training): View
    {
        $training->load('media');

        return view('admin.trainings.edit', compact('training'));
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $data = $this->validated($request, $training->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $training->id);

        if ($request->hasFile('cover_image')) {
            if ($training->cover_image) {
                $this->deleteFile($training->cover_image);
            }
            $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'trainings');
        }

        if ($data['status'] === Training::STATUS_PUBLISHED && ! $training->published_at) {
            $data['published_at'] = now();
        }

        $training->update($data);
        $this->storeMedia($training, $request->file('media') ?? []);

        return redirect()->route('admin.trainings.show', $training)->with('success', 'Training module updated.');
    }

    public function destroy(Training $training): RedirectResponse
    {
        if ($training->cover_image) {
            $this->deleteFile($training->cover_image);
        }

        foreach ($training->media as $item) {
            $this->deleteFile($item->path);
        }

        $training->delete();

        return redirect()->route('admin.trainings.index')->with('success', 'Training module deleted.');
    }

    public function destroyMedia(Training $training, TrainingMedia $media): RedirectResponse
    {
        abort_if($media->training_id !== $training->id, 404);
        $this->deleteFile($media->path);
        $media->delete();

        return back()->with('success', 'Media item removed.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'category'    => 'required|in:'.implode(',', array_keys(Training::CATEGORIES)),
            'excerpt'     => 'nullable|string|max:500',
            'body'        => 'nullable|string',
            'status'      => 'required|in:draft,published',
            'cover_image' => 'nullable|image|max:8192',
            'media'       => 'nullable|array',
            'media.*'     => 'file|mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov,avi|max:102400',
        ]);
    }

    private function storeMedia(Training $training, array $files): void
    {
        $sort = (int) $training->media()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $mime = $file->getMimeType() ?? '';
            $type = str_starts_with($mime, 'video/') ? TrainingMedia::TYPE_VIDEO : TrainingMedia::TYPE_IMAGE;
            $path = $this->storeFile($file, 'training-media');

            TrainingMedia::create([
                'training_id' => $training->id,
                'type'        => $type,
                'path'        => $path,
                'sort_order'  => ++$sort,
            ]);
        }
    }

    private function storeFile(UploadedFile $file, string $folder): string
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
        $slug     = Str::slug($value) ?: 'training';
        $original = $slug;
        $i        = 1;

        while (Training::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }
}
