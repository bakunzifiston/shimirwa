@extends('layouts.admin')

@section('title', 'Training Modules')
@section('page_title', 'Training Modules')
@section('page_subtitle', 'Publish and manage training resources shown on the public website')

@section('content')
<div class="admin-listing-page">
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
                        <option value="draft"     @selected($status === 'draft')>Draft</option>
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
            <th>Media</th>
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
                    <span class="admin-badge">{{ $module->categoryLabel() }}</span>
                </td>
                <td style="font-size:.82rem">
                    {{ $module->media_count }} file(s)
                </td>
                <td>
                    @if($module->isPublished())
                        <span class="admin-badge admin-badge--primary">Published</span>
                    @else
                        <span class="admin-badge">Draft</span>
                    @endif
                </td>
                <td style="font-size:.82rem;color:var(--admin-text-subtle);white-space:nowrap">
                    {{ $module->published_at?->format('d M Y') ?? '—' }}
                </td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.trainings.show', $module)"
                        :edit-route="route('admin.trainings.edit', $module)"
                        :delete-route="route('admin.trainings.destroy', $module)"
                        view-title="{{ $module->title }}"
                        delete-confirm="Delete this training module and all its media?"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="7"
                title="No training modules yet"
                message="Create your first training module to share knowledge on the public website." />
        @endforelse

    </x-admin.listing>
</div>
@endsection
