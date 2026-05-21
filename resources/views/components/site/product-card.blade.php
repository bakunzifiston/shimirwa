@props(['product'])

@php
    /** @var \App\Models\Product $product */
    $imageUrl = $product->primaryImageUrl();
    $inStock = $product->isInStock();
    $price = $product->effectivePrice();
@endphp

<article class="site-product-card site-reveal">
    <div class="site-product-image">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="site-product-placeholder" aria-hidden="true">🌾</span>
        @endif
        @if (! $inStock)
            <span class="site-product-badge">Out of stock</span>
        @elseif ($product->discount_price)
            <span class="site-product-badge">Sale</span>
        @endif
    </div>
    <div class="site-product-body">
        <h3><a href="{{ route('shop.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="site-product-price">
            {{ number_format($price) }} RWF
            @if ($product->discount_price && (float) $product->discount_price < (float) $product->price)
                <span style="font-size:0.8125rem;font-weight:400;text-decoration:line-through;color:var(--site-text-muted);margin-left:0.35rem">{{ number_format($product->price) }}</span>
            @endif
        </p>
        <p class="site-product-desc">{{ $product->shortDescription() }}</p>
        @unless ($inStock)
            <p class="site-product-stock out-of-stock">Out of stock</p>
        @endunless
        <div class="site-product-actions">
            <x-site.button :href="route('shop.show', $product)" variant="secondary" size="site-btn-sm">View details</x-site.button>
            @if ($inStock)
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <x-site.button type="submit" size="site-btn-sm">Add to cart</x-site.button>
                </form>
            @endif
        </div>
    </div>
</article>
