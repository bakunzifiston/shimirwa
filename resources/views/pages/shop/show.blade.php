@extends('layouts.site')

@section('title', $product['name'])
@section('meta_description', $product['short'])

@section('content')
    <section class="site-page-hero">
        <div class="site-container">
            <nav class="site-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a> /
                <a href="{{ route('shop.index') }}">Shop</a> /
                <span>{{ $product['name'] }}</span>
            </nav>
            <h1>{{ $product['name'] }}</h1>
            <p>{{ $product['short'] }}</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-grid-2 site-reveal" style="align-items:start">
                <div class="site-product-image" style="border-radius:var(--site-radius-lg);aspect-ratio:1">
                    @if (! empty($product['image']))
                        <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
                    @else
                        <span class="site-product-placeholder" aria-hidden="true">🌾</span>
                    @endif
                    @if (! empty($product['badge']))
                        <span class="site-product-badge">{{ $product['badge'] }}</span>
                    @endif
                </div>
                <div>
                    <p class="site-product-price" style="font-size:1.5rem;margin:0 0 1rem">
                        {{ number_format($product['price']) }} {{ $product['currency'] }}
                    </p>
                    <p>{{ $product['description'] }}</p>
                    @if (! empty($product['features']))
                        <h2 style="font-size:1.125rem;margin:1.5rem 0 0.75rem">Features</h2>
                        <ul style="padding-left:1.25rem;color:var(--site-text-muted)">
                            @foreach ($product['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:1.5rem">
                        <x-site.button :href="route('contact').'?subject='.urlencode('Order: '.$product['name'])">
                            Request a quote
                        </x-site.button>
                        <x-site.button :href="route('shop.index')" variant="secondary">
                            Back to shop
                        </x-site.button>
                    </div>
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
