@props(['product'])

<article class="site-product-card site-reveal">
    <div class="site-product-image">
        @if (! empty($product['image']))
            <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" loading="lazy">
        @else
            <span class="site-product-placeholder" aria-hidden="true">🌾</span>
        @endif
        @if (! empty($product['badge']))
            <span class="site-product-badge">{{ $product['badge'] }}</span>
        @endif
    </div>
    <div class="site-product-body">
        <h3><a href="{{ route('shop.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
        <p class="site-product-price">{{ number_format($product['price']) }} {{ $product['currency'] }}</p>
        <p class="site-product-desc">{{ $product['short'] }}</p>
        <div class="site-product-actions">
            <x-site.button :href="route('shop.show', $product['slug'])" variant="secondary" size="site-btn-sm">
                View details
            </x-site.button>
            <x-site.button href="{{ route('contact') }}?subject={{ urlencode('Order: '.$product['name']) }}" size="site-btn-sm">
                Add to cart
            </x-site.button>
        </div>
    </div>
</article>
