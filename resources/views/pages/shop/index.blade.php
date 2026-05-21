@extends('layouts.site')

@section('title', 'Shop')
@section('meta_description', 'Browse SHIMIRWA agricultural products — maize, sorghum, soy and more.')

@section('content')
    <section class="site-page-hero">
        <div class="site-container site-reveal">
            <span class="site-eyebrow">Shop</span>
            <h1>Our products</h1>
            <p>Premium processed agricultural products for households and businesses.</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-shop-toolbar site-reveal">
                <form method="GET" action="{{ route('shop.index') }}" role="search" class="site-shop-toolbar-form">
                    <input type="search" name="q" value="{{ $search }}" class="site-input" placeholder="Search products…" aria-label="Search products">
                    <select name="stock" class="site-select" aria-label="Stock filter">
                        <option value="">All availability</option>
                        <option value="in_stock" @selected($stock === 'in_stock')>In stock</option>
                        <option value="out_of_stock" @selected($stock === 'out_of_stock')>Out of stock</option>
                    </select>
                    <x-site.button type="submit">Search</x-site.button>
                    @if ($search || $stock)
                        <x-site.button :href="route('shop.index')" variant="secondary">Clear</x-site.button>
                    @endif
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="site-empty site-reveal">
                    <h2>No products found</h2>
                    <p>Try adjusting your search or check back soon.</p>
                </div>
            @else
                <div class="site-grid-3">
                    @foreach ($products as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
                <div class="site-pagination">{{ $products->links() }}</div>
            @endif
        </div>
    </section>
@endsection
