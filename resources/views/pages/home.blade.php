@extends('layouts.site')

@section('title', 'Home')
@section('meta_description', config('site.tagline'))

@section('content')
    <x-site.banner-slider />

    <x-site.vision-mission />

    <section class="site-section" aria-labelledby="featured-heading">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">Our products</span>
                <h2 id="featured-heading">Featured products</h2>
                <p>Discover our most popular flour, grits, and bulk supply lines.</p>
            </header>
            <div class="site-grid-4">
                @foreach ($featuredProducts as $product)
                    <x-site.product-card :product="$product" />
                @endforeach
            </div>
            <p style="text-align:center;margin-top:2rem">
                <x-site.button :href="route('shop.index')">View all products</x-site.button>
            </p>
        </div>
    </section>

    <section class="site-section site-section-alt" aria-labelledby="stats-heading">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">By the numbers</span>
                <h2 id="stats-heading">Built on consistency and scale</h2>
            </header>
            <div class="site-stats">
                @foreach ($stats as $stat)
                    <div class="site-stat site-reveal">
                        <div class="site-stat-value">{{ $stat['value'] }}</div>
                        <div class="site-stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section" aria-labelledby="testimonials-heading">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">Testimonials</span>
                <h2 id="testimonials-heading">What our partners say</h2>
            </header>
            <div class="site-grid-3">
                @foreach ($testimonials as $testimonial)
                    <figure class="site-testimonial site-reveal">
                        <blockquote>&ldquo;{{ $testimonial['quote'] }}&rdquo;</blockquote>
                        <figcaption><cite>{{ $testimonial['author'] }}</cite></figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section site-section-alt">
        <div class="site-container site-reveal">
            <div class="site-cta">
                <h2>Ready to partner with Shimirwa?</h2>
                <p>Request a quote, place a bulk order, or learn how our inventory system keeps every batch traceable.</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
                    <x-site.button :href="route('contact')" variant="accent" size="site-btn-lg">Get in touch</x-site.button>
                    <x-site.button :href="route('admin.login', absolute: false)" size="site-btn-lg" style="background:#fff;color:var(--site-primary);border-color:#fff">
                        Staff login
                    </x-site.button>
                </div>
            </div>
        </div>
    </section>
@endsection
