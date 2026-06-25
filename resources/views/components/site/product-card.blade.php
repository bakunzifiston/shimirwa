@props(['product'])

@php
    /** @var \App\Models\Product $product */
    $imageUrl = $product->primaryImageUrl();
    $inStock  = $product->isInStock();
    $price    = $product->effectivePrice();
@endphp

<article class="group relative flex flex-col rounded-2xl overflow-hidden border border-slate-100 bg-white
                shadow-sm hover:shadow-lg transition-shadow duration-300 site-reveal">

    {{-- Image / Placeholder --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 to-amber-50" style="aspect-ratio:4/3">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" class="opacity-30" aria-hidden="true">
                    <path d="M32 8C18 8 8 20 8 32s10 24 24 24 24-10 24-24S46 8 32 8z" fill="#10498c"/>
                    <path d="M20 32c0-6.6 5.4-12 12-12s12 5.4 12 12-5.4 12-12 12-12-5.4-12-12z" fill="#a66b3b"/>
                    <path d="M32 24v16M24 32h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        @if (! $inStock)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800/80 text-white backdrop-blur-sm">
                Out of stock
            </span>
        @elseif ($product->discount_price && (float) $product->discount_price < (float) $product->price)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500 text-white">
                Sale
            </span>
        @endif

        {{-- Hover overlay with quick add to cart --}}
        @if ($inStock)
            <div class="absolute inset-0 bg-[#10498c]/80 flex items-center justify-center
                        opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-[#10498c]
                                   font-semibold text-sm hover:bg-blue-50 transition-colors shadow-lg">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                        Add to cart
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="flex flex-col flex-1 p-4 gap-2">
        <h3 class="text-sm font-semibold text-slate-800 leading-snug">
            <a href="{{ route('shop.show', $product) }}" class="hover:text-[#10498c] transition-colors">
                {{ $product->name }}
            </a>
        </h3>

        <div class="flex items-baseline gap-2">
            <span class="text-base font-bold" style="color:var(--site-primary)">
                {{ number_format($price) }} RWF
            </span>
            @if ($product->discount_price && (float) $product->discount_price < (float) $product->price)
                <span class="text-xs text-slate-400 line-through">{{ number_format($product->price) }}</span>
            @endif
        </div>

        <p class="text-xs text-slate-500 leading-relaxed flex-1">{{ $product->shortDescription() }}</p>

        <div class="flex gap-2 mt-1 pt-2 border-t border-slate-100">
            <a href="{{ route('shop.show', $product) }}"
               class="flex-1 text-center px-3 py-2 rounded-lg border border-slate-200 text-xs font-medium
                      text-slate-600 hover:border-[#10498c] hover:text-[#10498c] transition-colors">
                View details
            </a>
            @if ($inStock)
                <form method="POST" action="{{ route('cart.add', $product) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                            class="w-full px-3 py-2 rounded-lg text-xs font-semibold text-white transition-colors
                                   bg-[#10498c] hover:bg-[#082f57]">
                        Add to cart
                    </button>
                </form>
            @else
                <span class="flex-1 text-center px-3 py-2 rounded-lg text-xs font-medium text-slate-400 bg-slate-50">
                    Out of stock
                </span>
            @endif
        </div>
    </div>
</article>
