@extends('layouts.site')

@section('title', 'Shop')
@section('meta_description', 'Browse Shimirwa products — premium flour, grits, and agricultural supplies.')

@section('content')
    {{-- Page Hero --}}
    <div class="page-hero">
        <div class="page-hero-bg" aria-hidden="true"></div>
        <div class="sc page-hero-content">
            <span class="eyebrow eyebrow--white" style="margin-bottom:.75rem;display:inline-flex">Our Products</span>
            <h1>Shop All Products</h1>
            <p>Premium processed agricultural products for households and businesses across Rwanda.</p>
        </div>
    </div>

    <section class="section">
        <div class="sc">
            {{-- Search & Filter Toolbar --}}
            <form method="GET" action="{{ route('shop.index') }}" role="search" class="shop-toolbar">
                <input type="search" name="q" value="{{ $search }}"
                       class="shop-input"
                       placeholder="Search products…"
                       aria-label="Search products">

                <select name="stock" class="shop-select" aria-label="Stock filter">
                    <option value="">All availability</option>
                    <option value="in_stock"     @selected($stock === 'in_stock')>In stock</option>
                    <option value="out_of_stock" @selected($stock === 'out_of_stock')>Out of stock</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                    </svg>
                    Search
                </button>
                @if ($search || $stock)
                    <a href="{{ route('shop.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>

            {{-- Results --}}
            @if ($products->isEmpty())
                <div style="text-align:center;padding:5rem 0">
                    <svg width="72" height="72" fill="none" viewBox="0 0 24 24" style="color:var(--slate-200);margin:0 auto 1.25rem" aria-hidden="true">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <h2 style="font-size:1.2rem;font-weight:700;color:var(--slate-700);margin-bottom:.5rem">No products found</h2>
                    <p style="color:var(--text-muted);font-size:.9rem">Try adjusting your search or check back soon.</p>
                </div>
            @else
                <div class="products-grid" style="margin-bottom:2.5rem">
                    @foreach ($products as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
                <div style="display:flex;justify-content:center">{{ $products->links() }}</div>
            @endif
        </div>
    </section>
@endsection
