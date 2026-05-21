@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'E-commerce — Orders')
@section('page_subtitle', 'Customer orders from the shop')

@section('content')
    <x-admin.listing :paginator="$orders" :show-search="false">
        <x-slot:toolbar>
            <form method="GET" class="flex flex-1 flex-wrap items-center gap-2">
                <input type="search" name="search" value="{{ $search }}" placeholder="Order #, name, phone…" class="admin-input flex-1 min-w-[12rem]">
                <select name="order_status" class="admin-input w-auto">
                    <option value="">All order statuses</option>
                    @foreach (\App\Models\Order::orderStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected($orderStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="admin-input w-auto">
                    <option value="">All payment statuses</option>
                    @foreach (\App\Models\Order::paymentStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Filter</button>
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
                <td>{{ ucfirst($order->payment_status) }}</td>
                <td>{{ ucfirst($order->order_status) }}</td>
                <td class="text-right">
                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn-ghost admin-btn-sm">View</a>
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" />
        @endforelse
    </x-admin.listing>
@endsection
