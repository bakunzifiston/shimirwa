@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'Online Orders')
@section('page_subtitle', 'Customer orders placed through the shop')

@section('content')
<div class="admin-listing-page">

    {{-- ── Summary stats ── --}}
    <section class="admin-stats-grid admin-stats-grid--primary" aria-label="Orders summary">
        @foreach($summaryStats as $stat)
            <article class="admin-stat-card admin-stat-card--primary">
                <div class="admin-stat-card-top">
                    <p class="admin-stat-label">{{ $stat['label'] }}</p>
                    <span class="admin-stat-icon admin-stat-icon--inverse">
                        <x-admin.icon :name="$stat['icon']" class="!h-5 !w-5" />
                    </span>
                </div>
                <p class="admin-stat-value @if(!empty($stat['valueAccent'])) admin-stat-value--accent @endif">
                    {{ $stat['value'] }}
                </p>
            </article>
        @endforeach
    </section>

    <x-admin.listing :paginator="$orders" :show-search="false">
        <x-slot:toolbar>
            <form method="GET" class="admin-filter-bar">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color:var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Search by order #, name, or phone…"
                           class="admin-input">
                </div>
                <div class="admin-filter-bar__filters">
                    <select name="order_status" class="admin-input" aria-label="Order status">
                        <option value="">All statuses</option>
                        @foreach($orderStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($orderStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="payment_status" class="admin-input" aria-label="Payment status">
                        <option value="">All payments</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-filter-bar__actions">
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                    @if($search || $orderStatus || $paymentStatus)
                        <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </x-slot:toolbar>

        <x-slot:head>
            <th style="width:7rem">Order #</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Order Status</th>
            <th>Payment</th>
            <th>Date</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse($orders as $order)
            <tr>
                {{-- Order number --}}
                <td class="cell-primary" style="font-family:monospace;font-size:.8rem;letter-spacing:.02em">
                    {{ $order->order_number }}
                </td>

                {{-- Customer -- most important column --}}
                <td>
                    <div style="font-weight:700;color:var(--admin-text);font-size:.875rem">
                        {{ $order->customer?->name ?? '—' }}
                    </div>
                    <div style="font-size:.75rem;color:var(--admin-text-subtle);margin-top:.1rem">
                        📞 {{ $order->customer?->phone ?? '—' }}
                    </div>
                    @if($order->customer?->email)
                        <div style="font-size:.72rem;color:var(--admin-text-subtle)">
                            ✉ {{ $order->customer->email }}
                        </div>
                    @endif
                    <div style="font-size:.72rem;color:var(--admin-text-subtle);margin-top:.15rem;max-width:14rem;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                         title="{{ $order->customer?->address }}">
                        📍 {{ $order->customer?->address ?? '—' }}
                    </div>
                </td>

                {{-- Items count --}}
                <td>
                    <span style="font-size:.8rem;font-weight:600;color:var(--admin-text)">
                        {{ $order->items_count }} item{{ $order->items_count !== 1 ? 's' : '' }}
                    </span>
                </td>

                {{-- Total --}}
                <td style="font-weight:700;color:var(--admin-primary)">
                    {{ number_format($order->total) }} RWF
                </td>

                {{-- Order status badge --}}
                <td>
                    @php
                        $oColors = [
                            'new'        => 'background:#fef3c7;color:#92400e',
                            'processing' => 'background:#dbeafe;color:#1e40af',
                            'shipped'    => 'background:#ede9fe;color:#5b21b6',
                            'completed'  => 'background:#dcfce7;color:#166534',
                            'cancelled'  => 'background:#fee2e2;color:#991b1b',
                        ];
                        $oStyle = $oColors[$order->order_status] ?? 'background:#f1f5f9;color:#475569';
                    @endphp
                    <span style="{{ $oStyle }};padding:.25rem .65rem;border-radius:99px;font-size:.72rem;font-weight:700;display:inline-block">
                        {{ $orderStatuses[$order->order_status] ?? ucfirst($order->order_status) }}
                    </span>
                </td>

                {{-- Payment --}}
                <td>
                    @php
                        $pColors = [
                            'pending'   => 'background:#fef3c7;color:#92400e',
                            'paid'      => 'background:#dcfce7;color:#166534',
                            'cancelled' => 'background:#fee2e2;color:#991b1b',
                        ];
                        $pStyle = $pColors[$order->payment_status] ?? 'background:#f1f5f9;color:#475569';
                    @endphp
                    <span style="{{ $pStyle }};padding:.25rem .65rem;border-radius:99px;font-size:.72rem;font-weight:700;display:inline-block">
                        {{ $paymentStatuses[$order->payment_status] ?? ucfirst($order->payment_status) }}
                    </span>
                </td>

                {{-- Date --}}
                <td style="font-size:.78rem;color:var(--admin-text-subtle);white-space:nowrap">
                    {{ $order->created_at->format('d M Y') }}<br>
                    <span style="opacity:.65">{{ $order->created_at->format('H:i') }}</span>
                </td>

                {{-- Actions --}}
                <td class="text-right">
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="admin-btn admin-btn-ghost admin-btn-sm"
                       data-drawer-src="{{ route('admin.orders.show', $order) }}"
                       data-drawer-title="Order {{ $order->order_number }}">
                        View
                    </a>
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="8" />
        @endforelse
    </x-admin.listing>
</div>
@endsection
