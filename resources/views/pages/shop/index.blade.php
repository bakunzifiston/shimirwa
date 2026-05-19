@extends('layouts.site')

@section('title', 'Shop')
@section('meta_description', 'Browse Shimirwa soybean flour, grits, and bulk products. Search and filter by category.')

@section('content')
    <section class="site-page-hero">
        <div class="site-container site-reveal">
            <span class="site-eyebrow">Shop</span>
            <h1>Our products</h1>
            <p>Premium roasted, milled, and packaged soybean products for every need.</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-shop-toolbar site-reveal">
                <form method="GET" action="{{ route('shop.index') }}" role="search">
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        class="site-input"
                        placeholder="Search products…"
                        aria-label="Search products"
                        style="flex:1;min-width:12rem"
                    >
                    <select name="category" class="site-select" aria-label="Filter by category">
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-site.button type="submit">Search</x-site.button>
                    @if ($search || $category !== 'all')
                        <x-site.button :href="route('shop.index')" variant="secondary">Clear</x-site.button>
                    @endif
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="site-empty site-reveal">
                    <h2>No products found</h2>
                    <p>Try adjusting your search or category filter.</p>
                    <x-site.button :href="route('shop.index')" style="margin-top:1rem">View all products</x-site.button>
                </div>
            @else
                <div class="site-grid-3">
                    @foreach ($products as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
