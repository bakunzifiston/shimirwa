@extends('layouts.admin')

@section('title', 'Training Modules')
@section('page_title', 'Training Modules')
@section('page_subtitle', 'Publish and manage training resources shown on the public website')

@section('content')
    <x-admin.listing :paginator="$trainings" :show-search="false">
        <x-slot:toolbar>
            <form method="GET" class="admin-filter-bar">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search trainings…" class="admin-input">
                </div>
                <div class="admin-filter-bar__filters">
                    <select name="status" class="admin-input" aria-label="Status">
                        <option value="">All statuses</option>
                        <option value="published" @selected($status === 'published')>Published</option>
                        <option value="draft" @selected($status === 'draft')>Draft</option>
                    </select>
                    <select name="category" class="admin-input" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach(\App\Models\Training::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-filter-bar__actions">
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                    @if($search || $status || $category)
                        <a href="{{ route('admin.trainings.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </x-slot:toolbar>

        <x-slot:actions>
            <a href="{{ route('admin.trainings.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                New module
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Cover</th>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Published</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse($trainings as $module)
            <tr>
                <td>
                    @if($module->coverImageUrl())
                        <img src="{{ $module->coverImageUrl() }}" alt="" class="h-10 w-10 rounded object-cover border">
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded border text-xs opacity-50">—</span>
                    @endif
                </td>
                <td class="cell-primary">{{ $module->title }}</td>
                <td>
                    <span style="font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:99px;
                                 background:rgba(193,127,62,.1);color:var(--copper-dark)">
                        {{ $module->categoryLabel() }}
                    </span>
                </td>
                <td>
                    @if($module->isPublished())
                        <span style="font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:99px;
                                     background:#dcfce7;color:#166534">Published</span>
                    @else
                        <span style="font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:99px;
                                     background:#f1f5f9;color:#475569">Draft</span>
                    @endif
                </td>
                <td style="font-size:.82rem;color:var(--admin-text-subtle)">
                    {{ $module->published_at?->format('d M Y') ?? '—' }}
                </td>
                <td class="text-right" style="white-space:nowrap">
                    <a href="{{ route('admin.trainings.show', $module) }}" class="admin-btn admin-btn-ghost admin-btn-sm">View</a>
                    <a href="{{ route('admin.trainings.edit', $module) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.trainings.destroy', $module) }}"
                          onsubmit="return confirm('Delete this training module?')" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:2.5rem;color:var(--admin-text-subtle)">
                    No training modules yet.
                    <a href="{{ route('admin.trainings.create') }}" style="color:var(--admin-primary)">Create one</a>
                </td>
            </tr>
        @endforelse
    </x-admin.listing>
@endsection
