@props([
    'paginator',
    'search' => '',
    'clearRoute' => null,
    'placeholder' => 'Search…',
    'showSearch' => true,
])

<div {{ $attributes->merge(['class' => 'admin-panel']) }}>
    @if ($showSearch || isset($actions) || isset($toolbar))
        <div class="admin-panel-toolbar">
            @isset($toolbar)
                {{ $toolbar }}
            @elseif ($showSearch)
                <form method="GET" class="admin-filter-bar">
                    <div class="admin-search-wrap">
                        <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                        <input type="search" name="search" value="{{ $search }}" placeholder="{{ $placeholder }}" class="admin-input">
                    </div>
                    <div class="admin-filter-bar__actions">
                        <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Search</button>
                        @if ($search && $clearRoute)
                            <a href="{{ $clearRoute }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                        @endif
                    </div>
                </form>
            @endif
            @isset($actions)
                <div class="admin-panel-toolbar-actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="admin-table-scroll">
        <table class="admin-table">
            @isset($head)
                <thead><tr>{{ $head }}</tr></thead>
            @endisset
            <tbody>{{ $slot }}</tbody>
        </table>
    </div>

    @if ($paginator->hasPages())
        <div class="admin-panel-footer">{{ $paginator->links() }}</div>
    @elseif ($paginator->total() > 0)
        <div class="admin-panel-footer text-sm" style="color: var(--admin-text-muted)">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </div>
    @endif
</div>
