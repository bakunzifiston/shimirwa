@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Key metrics and recent activity')

@section('content')
    <div class="admin-dashboard">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Key metrics">
            @foreach ($primaryStats as $stat)
                <article class="admin-stat-card admin-stat-card--primary">
                    <div class="admin-stat-card-top">
                        <p class="admin-stat-label">{{ $stat['label'] }}</p>
                        <span class="admin-stat-icon admin-stat-icon--{{ $stat['tone'] ?? 'primary' }}">
                            <x-admin.icon :name="$stat['icon']" class="!h-5 !w-5" />
                        </span>
                    </div>
                    <p class="admin-stat-value">{{ $stat['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="admin-charts-grid" aria-label="Charts">
            <article class="admin-card admin-chart-card">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title">Monthly revenue</h2>
                        <p class="admin-chart-subtitle">IMS sales and shop orders — last 6 months</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="admin-chart-wrap">
                        <canvas id="admin-chart-bar" role="img" aria-label="Bar chart of monthly revenue"></canvas>
                    </div>
                </div>
            </article>

            <article class="admin-card admin-chart-card">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title">Raw material intake</h2>
                        <p class="admin-chart-subtitle">Net quantity received by material type</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    @if (empty($chartData['pie']['labels']))
                        <p class="admin-dashboard-empty">No raw material data yet.</p>
                    @else
                        <div class="admin-chart-wrap admin-chart-wrap--pie">
                            <canvas id="admin-chart-pie" role="img" aria-label="Pie chart of raw material intake"></canvas>
                        </div>
                    @endif
                </div>
            </article>
        </section>

        <section class="admin-metric-strip" aria-label="Additional metrics">
            @foreach ($secondaryStats as $metric)
                <div class="admin-metric-strip-item">
                    <span class="admin-metric-strip-label">{{ $metric['label'] }}</span>
                    <span class="admin-metric-strip-value">{{ $metric['value'] }}</span>
                </div>
            @endforeach
        </section>

        <div class="admin-dashboard-grid">
            <div class="admin-dashboard-main">
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Recent shop orders</h2>
                        <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">View all</a>
                    </div>
                    <div class="admin-card-body admin-card-body--flush">
                        @if ($recentOrders->isEmpty())
                            <p class="admin-dashboard-empty">No shop orders yet.</p>
                        @else
                            <div class="admin-data-list">
                                @foreach ($recentOrders as $order)
                                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-data-list-row">
                                        <div class="admin-data-list-main">
                                            <span class="admin-data-list-title">{{ $order->order_number }}</span>
                                            <span class="admin-data-list-meta">
                                                {{ $order->customer?->name ?? 'Guest' }}
                                                · {{ $order->created_at->format('M j, Y') }}
                                                · {{ \App\Models\Order::orderStatuses()[$order->order_status] ?? $order->order_status }}
                                            </span>
                                        </div>
                                        <span class="admin-data-list-value">{{ number_format((float) $order->total, 0) }} RWF</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Recent sales</h2>
                        <a href="{{ route('admin.sales.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">View all</a>
                    </div>
                    <div class="admin-card-body admin-card-body--flush">
                        @if ($recentSales->isEmpty())
                            <p class="admin-dashboard-empty">No sales recorded yet.</p>
                        @else
                            <div class="admin-data-list">
                                @foreach ($recentSales as $sale)
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="admin-data-list-row">
                                        <div class="admin-data-list-main">
                                            <span class="admin-data-list-title">{{ $sale->item }}</span>
                                            <span class="admin-data-list-meta">
                                                {{ $sale->client?->full_name ?? '—' }}
                                                · {{ optional($sale->date)->format('M j, Y') }}
                                            </span>
                                        </div>
                                        <span class="admin-data-list-value">{{ number_format((float) $sale->total_price, 0) }} RWF</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Recent material reception</h2>
                        <a href="{{ route('admin.raw-material-stocks.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">View all</a>
                    </div>
                    <div class="admin-card-body admin-card-body--flush">
                        @if ($recentStock->isEmpty())
                            <p class="admin-dashboard-empty">No stock entries yet.</p>
                        @else
                            <div class="admin-data-list">
                                @foreach ($recentStock as $stock)
                                    <a href="{{ route('admin.raw-material-stocks.show', $stock) }}" class="admin-data-list-row">
                                        <div class="admin-data-list-main">
                                            <span class="admin-data-list-title">{{ $stock->item }}</span>
                                            <span class="admin-data-list-meta">
                                                Batch {{ $stock->batch_number }}
                                                · {{ $stock->client?->full_name ?? '—' }}
                                            </span>
                                        </div>
                                        <span class="admin-data-list-value">{{ number_format($stock->quantity_in, 1) }} kg</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="admin-dashboard-aside">
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Quick actions</h2>
                    </div>
                    <div class="admin-card-body">
                        <nav class="admin-quick-actions admin-quick-actions--grid">
                            <a href="{{ route('admin.products.create') }}" class="admin-quick-action">
                                <x-admin.icon name="package" />
                                Add product
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="admin-quick-action">
                                <x-admin.icon name="cart" />
                                View orders
                            </a>
                            <a href="{{ route('admin.raw-material-stocks.create') }}" class="admin-quick-action">
                                <x-admin.icon name="box" />
                                Receive materials
                            </a>
                            <a href="{{ route('admin.sales.create') }}" class="admin-quick-action">
                                <x-admin.icon name="cart" />
                                Record sale
                            </a>
                            <a href="{{ route('admin.emballages.create') }}" class="admin-quick-action">
                                <x-admin.icon name="package" />
                                Record packaging
                            </a>
                            <a href="{{ route('admin.employees.create') }}" class="admin-quick-action">
                                <x-admin.icon name="users" />
                                Add employee
                            </a>
                        </nav>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.adminDashboardCharts = @json($chartData);
    </script>
    @vite('resources/js/admin-dashboard.js')
@endpush
