<section class="site-hero-animated" aria-label="Welcome to Shimirwa">
    {{-- Full-bleed background: wheat field photo --}}
    <div class="sha-bg-photo" aria-hidden="true">
        <img src="https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=1920&q=85&fit=crop"
             alt="" loading="eager" decoding="async"
             onerror="this.src='{{ asset('images/banners/banner-products.png') }}'">
    </div>

    {{-- Gradient overlay: dark on left for text, fades to semi-transparent right --}}
    <div class="sha-bg-overlay" aria-hidden="true"></div>

    {{-- Floating particles --}}
    <div class="sha-particles" aria-hidden="true">
        @for ($i = 1; $i <= 14; $i++)
            <span class="sha-p sha-p--{{ $i }}"></span>
        @endfor
    </div>

    {{-- Content --}}
    <div class="site-container sha-content">

        {{-- Left: copy --}}
        <div class="sha-copy">
            <span class="sha-eyebrow">
                <span class="sha-eyebrow-dot"></span>
                {{ config('site.hero.eyebrow', 'Premium Food Processing') }}
            </span>

            <h1 class="sha-headline">
                {!! nl2br(e(config('site.hero.headline', 'Quality Flour from Rwanda\'s Finest Grains'))) !!}
            </h1>

            <p class="sha-lead">
                {{ config('site.hero.lead', 'Traceable from field to bag — every batch certified, every delivery fresh.') }}
            </p>

            <div class="sha-actions">
                <a href="{{ route('shop.index') }}" class="sha-btn sha-btn--primary">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                    </svg>
                    Browse Products
                </a>
                <a href="{{ route('contact') }}" class="sha-btn sha-btn--outline">
                    Contact Us
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>

            <div class="sha-badges">
                <span class="sha-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    100% Natural
                </span>
                <span class="sha-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    Made in Rwanda
                </span>
                <span class="sha-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/></svg>
                    Bulk Orders
                </span>
            </div>
        </div>

        {{-- Right: product photo floating card --}}
        <div class="sha-visual" aria-hidden="true">
            <div class="sha-product-frame">
                <img src="{{ asset('images/banners/banner-products.png') }}"
                     alt="Shimirwa flour product range"
                     loading="eager" decoding="async">
                <div class="sha-product-frame-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    BINO &amp; KURA — in stock
                </div>
            </div>

            <div class="sha-float-stat">
                <span class="sha-float-stat-number">6+</span>
                <span class="sha-float-stat-label">Products</span>
            </div>
        </div>
    </div>

    <div class="sha-scroll" aria-hidden="true"><span></span></div>
</section>
