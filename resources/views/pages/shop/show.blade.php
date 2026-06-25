@extends('layouts.site')

@section('title', $product->name)
@section('meta_description', $product->shortDescription(160))

@section('content')
    <section class="py-10">
        <div class="site-container">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mb-8" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-[#10498c] transition-colors">Home</a>
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
                <a href="{{ route('shop.index') }}" class="hover:text-[#10498c] transition-colors">Shop</a>
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
                <span class="text-slate-800 font-medium">{{ $product->name }}</span>
            </nav>

            {{-- Alerts --}}
            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
            @endif

            {{-- Product Layout --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 site-reveal">

                {{-- Gallery --}}
                <div>
                    {{-- Main image --}}
                    <div class="rounded-2xl overflow-hidden border border-slate-100 bg-gradient-to-br from-blue-50 to-amber-50"
                         style="aspect-ratio:1" id="main-image-wrap">
                        @if ($product->images->isNotEmpty())
                            <img id="main-image"
                                 src="{{ $product->images->first()->url() }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg width="80" height="80" viewBox="0 0 64 64" fill="none" class="opacity-20" aria-hidden="true">
                                    <path d="M32 8C18 8 8 20 8 32s10 24 24 24 24-10 24-24S46 8 32 8z" fill="#10498c"/>
                                    <path d="M20 32c0-6.6 5.4-12 12-12s12 5.4 12 12-5.4 12-12 12-12-5.4-12-12z" fill="#a66b3b"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if ($product->images->count() > 1)
                        <div class="flex gap-2 mt-3 overflow-x-auto pb-1">
                            @foreach ($product->images as $image)
                                <button type="button"
                                        onclick="document.getElementById('main-image').src='{{ $image->url() }}';
                                                 document.querySelectorAll('.thumb-btn').forEach(b=>b.classList.remove('ring-2','ring-[#10498c]'));
                                                 this.classList.add('ring-2','ring-[#10498c]')"
                                        class="thumb-btn shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-transparent
                                               {{ $loop->first ? 'ring-2 ring-[#10498c]' : '' }} transition-all hover:opacity-80">
                                    <img src="{{ $image->url() }}" alt="" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="flex flex-col gap-5">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">{{ $product->name }}</h1>

                        <div class="flex items-baseline gap-3 mb-3">
                            <span class="text-2xl font-bold" style="color:var(--site-primary)">
                                {{ number_format($product->effectivePrice()) }} RWF
                            </span>
                            @if ($product->discount_price && (float) $product->discount_price < (float) $product->price)
                                <span class="text-base text-slate-400 line-through">{{ number_format($product->price) }} RWF</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Sale</span>
                            @endif
                        </div>

                        @unless ($product->isInStock())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                Out of stock
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 text-green-700 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                In stock
                            </span>
                        @endunless
                    </div>

                    <div class="prose prose-sm text-slate-600 border-t border-slate-100 pt-5">
                        {!! nl2br(e($product->description)) !!}
                    </div>

                    {{-- Add to cart / Contact --}}
                    @if ($product->isInStock())
                        <form method="POST" action="{{ route('cart.add', $product) }}"
                              class="flex gap-3 items-end border-t border-slate-100 pt-5">
                            @csrf
                            <div class="flex flex-col gap-1">
                                <label for="qty" class="text-xs font-medium text-slate-600">Quantity</label>
                                <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden">
                                    <button type="button"
                                            onclick="const i=document.getElementById('qty');i.value=Math.max(1,+i.value-1)"
                                            class="w-10 h-11 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors font-bold text-lg">
                                        −
                                    </button>
                                    <input type="number" id="qty" name="quantity" value="1"
                                           min="1" max="{{ $product->stock_quantity }}"
                                           class="w-14 h-11 text-center border-0 focus:outline-none text-sm font-medium">
                                    <button type="button"
                                            onclick="const i=document.getElementById('qty');i.value=Math.min({{ $product->stock_quantity }},+i.value+1)"
                                            class="w-10 h-11 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors font-bold text-lg">
                                        +
                                    </button>
                                </div>
                            </div>
                            <button type="submit"
                                    class="flex-1 flex items-center justify-center gap-2 px-6 py-3 rounded-xl
                                           bg-[#10498c] text-white font-semibold hover:bg-[#082f57] transition-colors">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                                </svg>
                                Add to cart
                            </button>
                        </form>
                    @else
                        <div class="border-t border-slate-100 pt-5">
                            <a href="{{ route('contact') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border-2 border-[#10498c] text-[#10498c]
                                      font-semibold hover:bg-[#10498c] hover:text-white transition-colors">
                                Contact us to order
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related products --}}
            @if ($related->isNotEmpty())
                <div class="mt-16">
                    <h2 class="text-xl font-bold text-slate-900 mb-6">Related products</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($related as $item)
                            <x-site.product-card :product="$item" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
