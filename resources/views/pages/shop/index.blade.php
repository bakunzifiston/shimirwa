@extends('layouts.site')

@section('title', 'Shop')
@section('meta_description', 'Browse Shimirwa products — premium maize, soybean, sorghum and wheat flour available in 1kg, 5kg and bulk.')

@section('content')

{{-- ── Page Hero ── --}}
<div class="page-hero" style="padding:3.5rem 0 2.5rem">
    <div class="page-hero-bg" aria-hidden="true"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none"></div>
    <div class="sc page-hero-content" style="position:relative;z-index:1">
        <span class="eyebrow eyebrow--white" style="margin-bottom:.85rem;display:inline-flex">Our Products</span>
        <h1 style="animation:heroIn .7s var(--ease) both">Shop All Products</h1>
        <p style="animation:heroIn .7s .12s var(--ease) both">
            Premium maize, soybean, sorghum &amp; wheat flour — available in household and bulk sizes.
        </p>
    </div>
</div>

<section class="section" style="padding-top:2.5rem">
    <div class="sc">

        {{-- ── Alerts ── --}}
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:1.5rem">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom:1.5rem">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- ── Search & Filter toolbar ── --}}
        <form method="GET" action="{{ route('shop.index') }}" role="search">
            <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-xl);
                        padding:1.1rem 1.25rem;box-shadow:var(--shadow-sm);margin-bottom:2rem;
                        display:flex;flex-wrap:wrap;gap:.75rem;align-items:center">

                {{-- Search --}}
                <div style="flex:1;min-width:13rem;position:relative;display:flex;align-items:center">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                         style="position:absolute;left:.9rem;color:var(--slate-400);pointer-events:none" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="search" name="q" value="{{ $search }}"
                           placeholder="Search products…"
                           style="width:100%;padding:.65rem 1rem .65rem 2.5rem;border:1.5px solid var(--border);
                                  border-radius:var(--radius-sm);font-size:.875rem;font-family:var(--font);
                                  color:var(--text);background:var(--slate-50);transition:all .15s"
                           onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)';this.style.background='white'"
                           onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none';this.style.background='var(--slate-50)'"
                           aria-label="Search products">
                </div>

                {{-- Availability filter --}}
                <select name="stock"
                        style="padding:.65rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);
                               font-size:.875rem;background:var(--slate-50);color:var(--text);
                               font-family:var(--font);cursor:pointer;min-width:10rem"
                        aria-label="Filter by availability">
                    <option value="">All availability</option>
                    <option value="in_stock"     @selected($stock === 'in_stock')>In stock</option>
                    <option value="out_of_stock" @selected($stock === 'out_of_stock')>Out of stock</option>
                </select>

                {{-- Buttons --}}
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
                @if($search || $stock)
                    <a href="{{ route('shop.index') }}" class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        Clear
                    </a>
                @endif

                {{-- Result count --}}
                <span style="margin-left:auto;font-size:.8rem;color:var(--text-muted);font-weight:500;white-space:nowrap">
                    {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}
                </span>
            </div>
        </form>

        {{-- ── Product grid or empty state ── --}}
        @if($products->isEmpty())
            <div style="text-align:center;padding:5rem 2rem">
                <div style="width:5rem;height:5rem;border-radius:50%;background:var(--slate-100);
                            display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:var(--slate-300)" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <h2 style="font-size:1.2rem;font-weight:700;color:var(--slate-700);margin-bottom:.5rem">No products found</h2>
                <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem">
                    @if($search || $stock)
                        Try adjusting your filters or search term.
                    @else
                        Products will appear here once added by the admin.
                    @endif
                </p>
                @if($search || $stock)
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-sm">Clear filters</a>
                @endif
            </div>
        @else
            <div class="products-grid" style="margin-bottom:2.5rem">
                @foreach($products as $product)
                    <x-site.product-card :product="$product" />
                @endforeach
            </div>

            {{-- ── Pagination ── --}}
            @if($products->hasPages())
                <div style="display:flex;justify-content:center;margin-top:1rem">
                    {{ $products->links('vendor.pagination.simple-tailwind') }}
                </div>
            @endif
        @endif
    </div>
</section>

{{-- ── Why buy from us ── --}}
<section class="section section-alt">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--blue reveal">Why Choose Us</span>
            <h2 class="section-title reveal">Trusted by Farmers. Preferred by Clients.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));gap:1.25rem">
            @php
            $why = [
                ['icon'=>'<path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>','title'=>'Certified Quality','desc'=>'Every batch tested and certified before leaving our Rulindo facility.','color'=>'blue'],
                ['icon'=>'<path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>','title'=>'100% Local','desc'=>'Maize, soybeans, sorghum and wheat sourced directly from Rwandan farmers.','color'=>'copper'],
                ['icon'=>'<path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25"/>','title'=>'Fast Delivery','desc'=>'Reliable nationwide distribution with priority fulfilment for bulk orders.','color'=>'blue'],
                ['icon'=>'<path stroke-linecap="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/>','title'=>'Bulk Orders','desc'=>'1kg, 5kg, and carton options available for households, retailers and institutions.','color'=>'copper'],
            ];
            @endphp
            @foreach($why as $item)
                <div class="reveal" style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow-sm);
                            transition:transform .22s var(--ease),box-shadow .22s"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                    <div style="width:3rem;height:3rem;border-radius:var(--radius);margin-bottom:1rem;
                                background:{{ $item['color']==='copper' ? 'var(--copper-light)' : 'var(--blue-light)' }};
                                display:flex;align-items:center;justify-content:center;
                                color:{{ $item['color']==='copper' ? 'var(--copper-dark)' : 'var(--blue)' }}">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    </div>
                    <div style="font-size:.9375rem;font-weight:700;color:var(--slate-800);margin-bottom:.5rem">{{ $item['title'] }}</div>
                    <div style="font-size:.84rem;color:var(--text-muted);line-height:1.65">{{ $item['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cta-section">
    <div class="cta-bg" aria-hidden="true"></div>
    <div class="sc" style="position:relative;z-index:1;text-align:center">
        <h2 class="cta-title reveal">Need a bulk order or custom quote?</h2>
        <p class="cta-desc reveal">Contact our team directly for wholesale pricing, custom packaging, and delivery arrangements across Rwanda.</p>
        <div class="cta-actions reveal">
            <a href="{{ route('contact') }}" class="btn btn-lg" style="background:white;color:var(--copper-dark);box-shadow:0 4px 14px rgba(0,0,0,.15)">
                Get a Quote
            </a>
            <a href="{{ route('about') }}" class="btn btn-outline btn-lg">About Us</a>
        </div>
    </div>
</section>

@endsection
