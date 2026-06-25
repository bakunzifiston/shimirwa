@extends('layouts.site')

@section('title', 'Shop')
@section('meta_description', 'Browse SHIMIRWA agricultural products — maize, sorghum, soy and more.')

@section('content')
    {{-- Hero --}}
    <section class="relative py-16 overflow-hidden" style="background:linear-gradient(135deg,#10498c 0%,#082f57 100%)">
        <div class="site-container relative z-10 text-center site-reveal">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold tracking-wider uppercase
                         bg-white/15 text-blue-100 mb-4">Products</span>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Our Products</h1>
            <p class="text-blue-100 max-w-xl mx-auto">Premium processed agricultural products for households and businesses.</p>
        </div>
        <div class="absolute inset-0 opacity-5" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:24px 24px"></div>
    </section>

    <section class="py-10">
        <div class="site-container">
            {{-- Search & Filter Toolbar --}}
            <form method="GET" action="{{ route('shop.index') }}" role="search"
                  class="flex flex-wrap gap-3 mb-8 p-4 bg-white rounded-2xl border border-slate-100 shadow-sm site-reveal">
                <input type="search" name="q" value="{{ $search }}"
                       class="flex-1 min-w-[12rem] px-4 py-2.5 rounded-xl border border-slate-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition"
                       placeholder="Search products…" aria-label="Search products">

                <select name="stock"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition"
                        aria-label="Stock filter">
                    <option value="">All availability</option>
                    <option value="in_stock"     @selected($stock === 'in_stock')>In stock</option>
                    <option value="out_of_stock" @selected($stock === 'out_of_stock')>Out of stock</option>
                </select>

                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#10498c] text-white text-sm font-semibold
                               hover:bg-[#082f57] transition-colors">
                    Search
                </button>
                @if ($search || $stock)
                    <a href="{{ route('shop.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium
                              hover:bg-slate-50 transition-colors">
                        Clear
                    </a>
                @endif
            </form>

            {{-- Results --}}
            @if ($products->isEmpty())
                <div class="flex flex-col items-center py-20 text-center site-reveal">
                    <svg width="64" height="64" fill="none" viewBox="0 0 24 24" class="text-slate-300 mb-4" aria-hidden="true">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-slate-700 mb-2">No products found</h2>
                    <p class="text-slate-500 text-sm">Try adjusting your search or check back soon.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    @foreach ($products as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
                <div class="flex justify-center">{{ $products->links() }}</div>
            @endif
        </div>
    </section>
@endsection
