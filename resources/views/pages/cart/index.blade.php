@extends('layouts.site')

@section('title', 'Cart')

@section('content')
    <section class="site-page-hero">
        <div class="site-container">
            <h1>Your cart</h1>
            <p>Review items before checkout.</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            @if (session('error'))
                <div class="site-alert site-alert-error">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="site-alert site-alert-success">{{ session('success') }}</div>
            @endif

            @if ($items->isEmpty())
                <div class="site-empty">
                    <h2>Your cart is empty</h2>
                    <x-site.button :href="route('shop.index')" style="margin-top:1rem">Browse products</x-site.button>
                </div>
            @else
                @if ($hasStockIssues)
                    <div class="site-alert site-alert-error">Some items have stock issues. Update quantities before checkout.</div>
                @endif

                <div class="site-cart-layout">
                    <div class="site-cart-items">
                        @foreach ($items as $row)
                            @php $item = $row['item']; $product = $row['product']; @endphp
                            <article class="site-cart-item {{ ! $row['stock_ok'] ? 'site-cart-item--error' : '' }}">
                                <div>
                                    <h3>
                                        @if ($product)
                                            <a href="{{ route('shop.show', $product) }}">{{ $item['name'] }}</a>
                                        @else
                                            {{ $item['name'] }}
                                        @endif
                                    </h3>
                                    <p class="site-cart-item-price">{{ number_format($item['unit_price']) }} RWF each</p>
                                    @if (! $row['stock_ok'])
                                        <p class="site-cart-item-warning">Only {{ $row['available_stock'] }} available</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="site-cart-qty-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="{{ $row['available_stock'] }}" class="site-input site-input-qty">
                                    <button type="submit" class="site-btn site-btn-secondary site-btn-sm">Update</button>
                                </form>
                                <p class="site-cart-line-total">{{ number_format($row['line_total']) }} RWF</p>
                                <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="site-cart-remove" aria-label="Remove">×</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                    <aside class="site-cart-summary">
                        <h2>Order summary</h2>
                        <p class="site-cart-summary-row"><span>Subtotal</span><strong>{{ number_format($subtotal) }} RWF</strong></p>
                        @if ($hasStockIssues)
                            <span class="site-btn site-btn-primary site-btn-lg" style="display:inline-flex;opacity:0.5;pointer-events:none;width:100%">Proceed to checkout</span>
                        @else
                            <x-site.button :href="route('checkout.show')" size="site-btn-lg" style="width:100%">
                                Proceed to checkout
                            </x-site.button>
                        @endif
                        <x-site.button :href="route('shop.index')" variant="secondary" style="margin-top:0.75rem;width:100%">Continue shopping</x-site.button>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
