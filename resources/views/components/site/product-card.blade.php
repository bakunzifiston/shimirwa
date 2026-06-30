@props(['product'])

@php
    /** @var \App\Models\Product $product */
    $imageUrl = $product->primaryImageUrl();
    $inStock  = $product->isInStock();
    $price    = $product->effectivePrice();
@endphp

<article class="product-card reveal">
    {{-- Image --}}
    <div class="product-card-img">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <div class="product-card-placeholder">
                <svg width="60" height="60" viewBox="0 0 64 64" fill="none" style="opacity:.25" aria-hidden="true">
                    <circle cx="32" cy="32" r="24" fill="var(--blue)"/>
                    <circle cx="32" cy="32" r="14" fill="var(--copper)"/>
                    <path d="M32 22v20M22 32h20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
        @endif

        {{-- Badge --}}
        @if (! $inStock)
            <span class="product-card-badge out">Out of stock</span>
        @elseif ($product->discount_price && (float) $product->discount_price < (float) $product->price)
            <span class="product-card-badge sale">Sale</span>
        @endif

        {{-- Hover overlay --}}
        @if ($inStock)
            <div class="product-card-overlay">
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-outline btn-sm" style="color:white;border-color:rgba(255,255,255,.7)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                        Quick add
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="product-card-body">
        <div class="product-card-name">
            <a href="{{ route('shop.show', $product) }}" style="display:block">{{ $product->name }}</a>
        </div>

        <p class="product-card-desc">{{ $product->shortDescription() }}</p>

        <div class="product-card-price">
            <span class="price">{{ number_format($price) }} RWF</span>
            @if ($product->discount_price && (float) $product->discount_price < (float) $product->price)
                <span class="old-price">{{ number_format($product->price) }}</span>
            @endif
        </div>

        <div class="product-card-footer">
            <a href="{{ route('shop.show', $product) }}" class="btn btn-ghost btn-sm" style="justify-content:center">
                Details
            </a>
            @if ($inStock)
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
                        Add to cart
                    </button>
                </form>
            @else
                <span class="btn btn-sm" style="background:var(--slate-100);color:var(--slate-400);cursor:default;justify-content:center">
                    Out of stock
                </span>
            @endif
        </div>
    </div>
</article>
