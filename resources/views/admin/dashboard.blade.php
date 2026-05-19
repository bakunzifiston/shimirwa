@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of inventory, production, and sales')

@section('content')
    <div class="admin-stats-grid mb-6">
        @foreach ($stats as $stat)
            <article class="admin-stat-card">
                <span class="admin-stat-icon admin-stat-icon--{{ $stat['tone'] ?? 'primary' }}">
                    <x-admin.icon :name="$stat['icon']" class="!h-6 !w-6" />
                </span>
                <div class="min-w-0">
                    <p class="admin-stat-label">{{ $stat['label'] }}</p>
                    <p class="admin-stat-value">{{ $stat['value'] }}</p>
                    <p class="admin-stat-hint">{{ $stat['hint'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <div class="admin-dashboard-grid">
        <div class="space-y-6">
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Recent sales</h2>
                    <a href="{{ route('admin.sales.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">View all</a>
                </div>
                <div class="admin-card-body !p-0">
                    @if ($recentSales->isEmpty())
                        <p class="px-5 py-8 text-center text-sm" style="color: var(--admin-text-muted)">No sales recorded yet.</p>
                    @else
                        <ul class="admin-activity-list px-5">
                            @foreach ($recentSales as $sale)
                                <li class="admin-activity-item">
                                    <span class="admin-activity-dot"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium">{{ $sale->item }}</p>
                                        <p class="text-sm" style="color: var(--admin-text-muted)">
                                            {{ $sale->client?->full_name ?? '—' }}
                                            · {{ optional($sale->date)->format('M j, Y') }}
                                        </p>
                                    </div>
                                    <span class="admin-badge admin-badge--primary shrink-0">
                                        {{ number_format((float) $sale->total_price, 0) }} RWF
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Recent material reception</h2>
                    <a href="{{ route('admin.raw-material-stocks.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">View all</a>
                </div>
                <div class="admin-card-body !p-0">
                    @if ($recentStock->isEmpty())
                        <p class="px-5 py-8 text-center text-sm" style="color: var(--admin-text-muted)">No stock entries yet.</p>
                    @else
                        <ul class="admin-activity-list px-5">
                            @foreach ($recentStock as $stock)
                                <li class="admin-activity-item">
                                    <span class="admin-activity-dot" style="background: var(--admin-primary)"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium">{{ $stock->item }}</p>
                                        <p class="text-sm" style="color: var(--admin-text-muted)">
                                            Batch {{ $stock->batch_number }}
                                            · {{ $stock->client?->full_name ?? '—' }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-medium">{{ number_format($stock->quantity_in, 1) }} kg</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Quick actions</h2>
                </div>
                <div class="admin-card-body">
                    <nav class="admin-quick-actions">
                        <a href="{{ route('admin.raw-material-stocks.create') }}" class="admin-quick-action">
                            <x-admin.icon name="box" />
                            Receive materials
                        </a>
                        <a href="{{ route('admin.roastings.create') }}" class="admin-quick-action">
                            <x-admin.icon name="fire" />
                            Record roasting
                        </a>
                        <a href="{{ route('admin.millings.create') }}" class="admin-quick-action">
                            <x-admin.icon name="cog" />
                            Record milling
                        </a>
                        <a href="{{ route('admin.emballages.create') }}" class="admin-quick-action">
                            <x-admin.icon name="package" />
                            Record packaging
                        </a>
                        <a href="{{ route('admin.sales.create') }}" class="admin-quick-action">
                            <x-admin.icon name="cart" />
                            Record sale
                        </a>
                        <a href="{{ route('admin.employees.create') }}" class="admin-quick-action">
                            <x-admin.icon name="users" />
                            Add employee
                        </a>
                    </nav>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Production pipeline</h2>
                </div>
                <div class="admin-card-body text-sm" style="color: var(--admin-text-muted)">
                    <p class="mb-3">Track flow from reception through roasting, sorting, milling, packaging, and sales.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="admin-badge admin-badge--primary">Reception</span>
                        <span class="admin-badge admin-badge--primary">Roasting</span>
                        <span class="admin-badge admin-badge--primary">Sorting</span>
                        <span class="admin-badge admin-badge--primary">Milling</span>
                        <span class="admin-badge admin-badge--primary">Packaging</span>
                        <span class="admin-badge admin-badge--success">Sales</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
@endsection
