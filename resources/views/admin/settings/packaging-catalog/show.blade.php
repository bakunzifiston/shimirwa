@extends('layouts.admin')
@section('title', $item->name)
@section('page_title', $item->name)

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.settings.packaging-catalog.edit', $item) }}"
           data-drawer-src="{{ route('admin.settings.packaging-catalog.edit', $item) }}"
           data-drawer-title="Edit packaging type"
           class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
    </div>

    <div class="admin-card" style="max-width:500px">
        <div class="admin-card-body">
            <dl class="admin-detail-list">
                <div class="admin-detail-row">
                    <dt>Name</dt>
                    <dd class="font-semibold">{{ $item->name }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Flour per unit</dt>
                    <dd class="font-mono font-bold text-lg db-revenue-today">
                        {{ $item->manual_weight ? '—' : number_format($item->kg_per_unit, 3) . ' kg' }}
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Weight mode</dt>
                    <dd>
                        @if ($item->manual_weight)
                            <span class="admin-badge admin-badge--warning">Manual entry</span>
                        @else
                            <span class="admin-badge admin-badge--success">Auto (units × {{ number_format($item->kg_per_unit, 3) }} kg)</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Status</dt>
                    <dd>
                        @if ($item->is_active)
                            <span class="admin-badge admin-badge--success">Active</span>
                        @else
                            <span class="admin-badge admin-badge--neutral">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sort order</dt>
                    <dd>{{ $item->sort_order }}</dd>
                </div>
                @if ($item->description)
                    <div class="admin-detail-row">
                        <dt>Description</dt>
                        <dd>{{ $item->description }}</dd>
                    </div>
                @endif
                <div class="admin-detail-row">
                    <dt>Times used in packaging</dt>
                    <dd>{{ $item->emballages_count ?? '—' }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Created</dt>
                    <dd>{{ $item->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <a href="{{ route('admin.settings.packaging-catalog.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">
            &larr; Back to packaging catalog
        </a>
        <form method="POST" action="{{ route('admin.settings.packaging-catalog.destroy', $item) }}"
              onsubmit="return confirm('Delete this packaging type?')">
            @csrf @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-sm" style="border-color:#fecaca;color:#dc2626;background:#fff5f5">Delete</button>
        </form>
    </div>
@endsection
