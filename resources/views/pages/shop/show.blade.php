@extends('layouts.site')

@section('title', $product->name)
@section('meta_description', $product->shortDescription(160))

@section('content')
    <section class="site-page-hero">
        <div class="site-container">
            <nav class="site-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a> /
                <a href="{{ route('shop.index') }}">Shop</a> /
                <span>{{ $product->name }}</span>
            </nav>
            <h1>{{ $product->name }}</h1>
            <p>{{ $product->shortDescription() }}</p>
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

            <div class="site-product-detail site-reveal">
                <div class="site-product-gallery">
                    @forelse ($product->images as $image)
                        <img src="{{ $image->url() }}" alt="{{ $product->name }}" class="site-product-gallery-img">
                    @empty
                        <div class="site-product-image" style="border-radius:var(--site-radius-lg);aspect-ratio:1">
                            <span class="site-product-placeholder">🌾</span>
                        </div>
                    @endforelse
                </div>
                <div class="site-product-detail-info">
                    <p class="site-product-price" style="font-size:1.5rem;margin:0 0 0.5rem">
                        {{ number_format($product->effectivePrice()) }} RWF
                        @if ($product->discount_price)
                            <span style="font-size:1rem;text-decoration:line-through;opacity:0.6;margin-left:0.5rem">{{ number_format($product->price) }} RWF</span>
                        @endif
                    </p>
                    @unless ($product->isInStock())
                        <p class="site-product-stock out-of-stock">Out of stock</p>
                    @endunless
                    <div class="site-product-detail-desc">{!! nl2br(e($product->description)) !!}</div>

                    @if ($product->isInStock())
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="site-add-cart-form">
                            @csrf
                            <label class="site-field">
                                <span>Quantity</span>
                                <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="site-input site-input-qty">
                            </label>
                            <x-site.button type="submit" size="site-btn-lg">Add to cart</x-site.button>
                        </form>
                    @else
                        <x-site.button :href="route('contact')" variant="secondary" size="site-btn-lg">Contact us to order</x-site.button>
                    @endif
                </div>
            </div>

            @if ($related->isNotEmpty())
                <header class="site-section-header" style="margin-top:3rem">
                    <h2>Related products</h2>
                </header>
                <div class="site-grid-3">
                    @foreach ($related as $item)
                        <x-site.product-card :product="$item" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
