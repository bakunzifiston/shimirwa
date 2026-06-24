@extends('layouts.admin')

@section('title', 'Reception of materials')
@section('page_title', 'Reception of materials')
@section('page_subtitle', 'Raw material stock intake')

@section('content')
    <div class="admin-listing-page">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Reception summary">
            @foreach ($summaryStats as $stat)
                <article class="admin-stat-card admin-stat-card--primary">
                    <div class="admin-stat-card-top">
                        <p class="admin-stat-label">{{ $stat['label'] }}</p>
                        <span class="admin-stat-icon admin-stat-icon--inverse">
                            <x-admin.icon :name="$stat['icon']" class="!h-5 !w-5" />
                        </span>
                    </div>
                    <p class="admin-stat-value @if (! empty($stat['valueAccent'])) admin-stat-value--accent @endif">
                        {{ $stat['value'] }}
                    </p>
                </article>
            @endforeach
        </section>

        <div class="admin-panel">
            <div class="admin-panel-toolbar">
                <form method="GET" class="admin-filter-bar">
                    <div class="admin-search-wrap">
                        <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                        <input type="search" name="search" value="{{ $search }}" placeholder="Search batch, item, supplier…" class="admin-input">
                    </div>
                    <div class="admin-filter-bar__filters">
                        <select name="type" class="admin-input" aria-label="Material type">
                            <option value="">All types</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter-bar__actions">
                        <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                        @if ($search || $type)
                            <a href="{{ route('admin.raw-material-stocks.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                        @endif
                    </div>
                </form>
                <div class="admin-panel-toolbar-actions">
                    <a href="{{ route('admin.raw-material-stocks.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                        <x-admin.icon name="plus" class="!h-4 !w-4" />
                        Receive materials
                    </a>
                </div>
            </div>

            @if ($stocks->isEmpty())
                <div class="admin-empty admin-empty--panel">
                    <x-admin.icon name="box" class="admin-empty-icon !mx-auto !h-12 !w-12" />
                    <p class="admin-empty-title">No reception records found</p>
                    <p class="admin-empty-text">Try adjusting your filters or record a new material intake.</p>
                    <a href="{{ route('admin.raw-material-stocks.create') }}" class="admin-btn admin-btn-primary admin-btn-sm mt-4">
                        Receive materials
                    </a>
                </div>
            @else
                <div class="admin-card-grid admin-card-grid--stock">
                    @foreach ($stocks as $stock)
                        @php
                            $inStock = $stock->hasAvailableStock();
                            $netIntake = max((float) $stock->received - (float) $stock->rejected, 0);
                        @endphp
                        <article class="admin-entity-card">
                            <header class="admin-entity-card-header">
                                <div class="admin-entity-card-identity">
                                    <span class="admin-entity-card-avatar" aria-hidden="true">
                                        {{ strtoupper(mb_substr($stock->item, 0, 1)) }}
                                    </span>
                                    <div>
                                        <h3 class="admin-entity-card-id">
                                            <a href="{{ route('admin.raw-material-stocks.show', $stock) }}">{{ $stock->batch_number }}</a>
                                        </h3>
                                        <p class="admin-entity-card-sub">{{ $stock->item }}</p>
                                    </div>
                                </div>
                                <span class="admin-badge {{ $inStock ? 'admin-badge--primary' : 'admin-badge--muted' }}">
                                    {{ $inStock ? 'In stock' : 'Depleted' }}
                                </span>
                            </header>

                            <dl class="admin-entity-card-fields">
                                <div>
                                    <dt>Date</dt>
                                    <dd>{{ optional($stock->date)->format('M j, Y') ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Supplier</dt>
                                    <dd>{{ $stock->client?->full_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Employee</dt>
                                    <dd>{{ $stock->employee?->full_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Type</dt>
                                    <dd>{{ $stock->type }}</dd>
                                </div>
                                <div>
                                    <dt>Received</dt>
                                    <dd>{{ number_format($stock->received, 1) }} kg</dd>
                                </div>
                            </dl>

                            <div class="admin-entity-card-highlight">
                                <div>
                                    <strong>{{ number_format($stock->remainingQuantity(), 1) }} kg</strong>
                                    <span>Remaining</span>
                                </div>
                                <div>
                                    <strong>{{ number_format($netIntake, 1) }} kg</strong>
                                    <span>Net intake</span>
                                </div>
                            </div>

                            <footer class="admin-entity-card-footer">
                                <a href="{{ route('admin.raw-material-stocks.show', $stock) }}">View</a>
                                <a href="{{ route('admin.raw-material-stocks.edit', $stock) }}">Edit</a>
                            </footer>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($stocks->hasPages())
                <div class="admin-panel-footer">{{ $stocks->links() }}</div>
            @elseif ($stocks->total() > 0)
                <div class="admin-panel-footer text-sm" style="color: var(--admin-text-muted)">
                    Showing {{ $stocks->firstItem() }}–{{ $stocks->lastItem() }} of {{ $stocks->total() }}
                </div>
            @endif
        </div>
    </div>
@endsection
