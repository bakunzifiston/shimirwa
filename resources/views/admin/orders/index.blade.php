@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'E-commerce — Orders')
@section('page_subtitle', 'Customer orders from the shop')

@section('content')
    <div class="admin-listing-page">
        <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Orders summary">
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

        <x-admin.listing :paginator="$orders" :show-search="false">
            <x-slot:toolbar>
                <form method="GET" class="admin-filter-bar">
                    <div class="admin-search-wrap">
                        <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                        <input type="search" name="search" value="{{ $search }}" placeholder="Search orders…" class="admin-input">
                    </div>

                    <div class="admin-filter-bar__filters">
                        <select name="order_status" class="admin-input" aria-label="Order status">
                            <option value="">Order status</option>
                            @foreach ($orderStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($orderStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="payment_status" class="admin-input" aria-label="Payment status">
                            <option value="">Payment status</option>
                            @foreach ($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="admin-filter-bar__actions">
                        <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                        @if ($search || $orderStatus || $paymentStatus)
                            <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            <x-slot:head>
                <th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th class="text-right">Actions</th>
            </x-slot:head>

            @forelse ($orders as $order)
                <tr>
                    <td class="cell-primary">{{ $order->order_number }}</td>
                    <td>{{ $order->customer->name }}<br><span class="text-xs opacity-70">{{ $order->customer->phone }}</span></td>
                    <td>{{ number_format($order->total) }} RWF</td>
                    <td>{{ $order->paymentMethodLabel() }}<br><span class="text-xs opacity-70">{{ ucfirst($order->payment_status) }}</span></td>
                    <td>{{ ucfirst($order->order_status) }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn-ghost admin-btn-sm">View</a>
                    </td>
                </tr>
            @empty
                <x-admin.empty-state colspan="6" />
            @endforelse
        </x-admin.listing>
    </div>
@endsection
