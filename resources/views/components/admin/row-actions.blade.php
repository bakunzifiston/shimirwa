@props([
    'viewRoute'    => null,
    'editRoute'    => null,
    'deleteRoute'  => null,
    'viewTitle'    => 'View details',
    'editTitle'    => 'Edit',
    'deleteConfirm'=> 'Delete this record?',
])

<div {{ $attributes->merge(['class' => 'admin-row-actions']) }}>
    @if ($viewRoute)
        <a href="{{ $viewRoute }}"
           data-drawer-src="{{ $viewRoute }}"
           data-drawer-title="{{ $viewTitle }}"
           class="action-view">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View
        </a>
    @endif

    @if ($editRoute)
        <a href="{{ $editRoute }}"
           data-drawer-src="{{ $editRoute }}"
           data-drawer-title="{{ $editTitle }}"
           class="action-edit">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </a>
    @endif

    @if ($deleteRoute)
        <form method="POST" action="{{ $deleteRoute }}" style="display:inline"
              onsubmit="return confirm('{{ $deleteConfirm }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-delete">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/></svg>
                Delete
            </button>
        </form>
    @endif

    {{ $slot }}
</div>
