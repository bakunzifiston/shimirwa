@extends('layouts.site')

@section('title', 'Order confirmed')

@section('content')
    <section class="site-section">
        <div class="site-container site-checkout-success site-reveal">
            <h1>Thank you for your order</h1>
            <p>Your order <strong>{{ $order->order_number }}</strong> has been received.</p>
            <div class="site-card" style="max-width:32rem;margin:1.5rem 0;text-align:left">
                <p><strong>Customer:</strong> {{ $order->customer->name }}</p>
                <p><strong>Phone:</strong> {{ $order->customer->phone }}</p>
                <p><strong>Total:</strong> {{ number_format($order->total) }} RWF</p>
                <p><strong>Payment:</strong> {{ $order->paymentMethodLabel() }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->order_status) }} · {{ ucfirst($order->payment_status) }}</p>
                @if ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                    <p class="text-sm" style="margin-top:0.75rem;opacity:0.85">Please have the exact amount ready when your order arrives.</p>
                @endif
            </div>
            <x-site.button :href="route('shop.index')">Continue shopping</x-site.button>
        </div>
    </section>
@endsection
