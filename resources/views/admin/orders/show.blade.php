@extends('layouts.admin')

@section('title', $order->order_number)
@section('page_title', 'Order '.$order->order_number)

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="admin-card space-y-3">
            <h3 class="font-semibold">Customer</h3>
            <p><strong>Name:</strong> {{ $order->customer->name }}</p>
            <p><strong>Phone:</strong> {{ $order->customer->phone }}</p>
            @if ($order->customer->email)<p><strong>Email:</strong> {{ $order->customer->email }}</p>@endif
            <p><strong>Address:</strong> {{ $order->customer->address }}</p>
            <p><strong>Payment method:</strong> {{ $order->paymentMethodLabel() }}</p>
            <p><strong>Payment status:</strong> {{ ucfirst($order->payment_status) }}</p>
            <p class="text-sm opacity-70">Placed {{ $order->created_at->format('M j, Y H:i') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="admin-card space-y-3">
            @csrf
            @method('PUT')
            <h3 class="font-semibold">Update status</h3>
            <div>
                <label class="admin-label">Payment status</label>
                <select name="payment_status" class="admin-input">
                    @foreach (\App\Models\Order::paymentStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_status', $order->payment_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Order status</label>
                <select name="order_status" class="admin-input">
                    @foreach (\App\Models\Order::orderStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('order_status', $order->order_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Notes</label>
                <textarea name="notes" class="admin-input" rows="3">{{ old('notes', $order->notes) }}</textarea>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
        </form>
    </div>

    <div class="admin-card mt-6">
        <h3 class="mb-3 font-semibold">Order items</h3>
        <table class="admin-table">
            <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price) }} RWF</td>
                        <td>{{ number_format($item->line_total) }} RWF</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="text-right font-semibold">Total</td><td class="font-semibold">{{ number_format($order->total) }} RWF</td></tr>
            </tfoot>
        </table>
    </div>
@endsection
