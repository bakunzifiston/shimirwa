@extends('layouts.admin')

@section('title', $order->order_number)
@section('page_title', 'Order '.$order->order_number)
@section('page_subtitle', 'Placed '.$order->created_at->format('d M Y, H:i'))

@section('content')

@if(session('success'))
    <div class="admin-alert admin-alert-success" role="alert" style="margin-bottom:1.25rem">
        <span class="flex-1">{{ session('success') }}</span>
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr;gap:1.25rem">

    {{-- ── Customer card ── --}}
    <div class="admin-card">
        <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                   color:var(--admin-text-subtle);margin:0 0 1rem">
            👤 Customer who placed this order
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:1rem">
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-text-subtle);margin-bottom:.2rem;font-weight:600">Full Name</div>
                <div style="font-size:.9375rem;font-weight:800;color:var(--admin-text)">{{ $order->customer->name }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-text-subtle);margin-bottom:.2rem;font-weight:600">Phone</div>
                <div style="font-size:.9375rem;font-weight:700;color:var(--admin-primary)">
                    <a href="tel:{{ preg_replace('/\s+/', '', $order->customer->phone) }}"
                       style="color:inherit;text-decoration:none">
                        📞 {{ $order->customer->phone }}
                    </a>
                </div>
            </div>
            @if($order->customer->email)
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-text-subtle);margin-bottom:.2rem;font-weight:600">Email</div>
                <div style="font-size:.875rem;color:var(--admin-text)">
                    <a href="mailto:{{ $order->customer->email }}" style="color:inherit">{{ $order->customer->email }}</a>
                </div>
            </div>
            @endif
            <div style="grid-column:1/-1">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-text-subtle);margin-bottom:.2rem;font-weight:600">Delivery Address</div>
                <div style="font-size:.875rem;color:var(--admin-text);font-weight:500">
                    📍 {{ $order->customer->address }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Order status card ── --}}
    <div class="admin-card">
        <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                   color:var(--admin-text-subtle);margin:0 0 1rem">
            🛍 Order Details
        </h3>
        <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;margin-bottom:1rem">
            @php
                $oColors = ['new'=>'background:#fef3c7;color:#92400e','processing'=>'background:#dbeafe;color:#1e40af','shipped'=>'background:#ede9fe;color:#5b21b6','completed'=>'background:#dcfce7;color:#166534','cancelled'=>'background:#fee2e2;color:#991b1b'];
                $pColors = ['pending'=>'background:#fef3c7;color:#92400e','paid'=>'background:#dcfce7;color:#166534','cancelled'=>'background:#fee2e2;color:#991b1b'];
            @endphp
            <div>
                <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-text-subtle);display:block;margin-bottom:.2rem">Order Status</span>
                <span style="{{ $oColors[$order->order_status] ?? '' }};padding:.3rem .75rem;border-radius:99px;font-size:.8rem;font-weight:700;display:inline-block">
                    {{ \App\Models\Order::orderStatuses()[$order->order_status] ?? ucfirst($order->order_status) }}
                </span>
            </div>
            <div>
                <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-text-subtle);display:block;margin-bottom:.2rem">Payment</span>
                <span style="{{ $pColors[$order->payment_status] ?? '' }};padding:.3rem .75rem;border-radius:99px;font-size:.8rem;font-weight:700;display:inline-block">
                    {{ \App\Models\Order::paymentStatuses()[$order->payment_status] ?? ucfirst($order->payment_status) }}
                </span>
            </div>
            <div>
                <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-text-subtle);display:block;margin-bottom:.2rem">Method</span>
                <span style="font-size:.85rem;font-weight:600;color:var(--admin-text)">{{ $order->paymentMethodLabel() }}</span>
            </div>
            <div style="margin-left:auto">
                <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-text-subtle);display:block;margin-bottom:.2rem;text-align:right">Order Total</span>
                <span style="font-size:1.35rem;font-weight:900;color:var(--admin-primary)">{{ number_format($order->total) }} RWF</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($order->notes)
            <div style="padding:.75rem;background:var(--admin-surface-2);border-radius:.5rem;
                        font-size:.84rem;color:var(--admin-text-subtle);margin-bottom:1rem">
                <strong>Notes:</strong> {{ $order->notes }}
            </div>
        @endif

        {{-- Update form --}}
        <form method="POST" action="{{ route('admin.orders.update', $order) }}"
              style="border-top:1px solid var(--admin-border);padding-top:1rem;
                     display:grid;grid-template-columns:1fr 1fr;gap:.75rem;align-items:end">
            @csrf @method('PUT')
            <div>
                <label class="admin-label">Update order status</label>
                <select name="order_status" class="admin-input">
                    @foreach(\App\Models\Order::orderStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('order_status', $order->order_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Update payment status</label>
                <select name="payment_status" class="admin-input">
                    @foreach(\App\Models\Order::paymentStatuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_status', $order->payment_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="grid-column:1/-1">
                <label class="admin-label">Notes</label>
                <textarea name="notes" class="admin-input" rows="2">{{ old('notes', $order->notes) }}</textarea>
            </div>
            <div style="grid-column:1/-1">
                <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
            </div>
        </form>
    </div>

    {{-- ── Items card ── --}}
    <div class="admin-card">
        <h3 style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                   color:var(--admin-text-subtle);margin:0 0 1rem">
            📦 Items Ordered
        </h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Unit Price</th>
                    <th style="text-align:right">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="font-weight:600">{{ $item->product_name }}</td>
                        <td style="text-align:center">{{ $item->quantity }}</td>
                        <td style="text-align:right">{{ number_format($item->unit_price) }} RWF</td>
                        <td style="text-align:right;font-weight:700">{{ number_format($item->line_total) }} RWF</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:700;font-size:.875rem">Total</td>
                    <td style="text-align:right;font-weight:900;color:var(--admin-primary);font-size:1rem">
                        {{ number_format($order->total) }} RWF
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

@endsection
