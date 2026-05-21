@php
    $slides = config('site.banners', []);
@endphp

@if (count($slides) > 0)
    <section class="site-banner" data-site-banner aria-label="Featured highlights" aria-roledescription="carousel">
        <div class="site-banner-track" data-site-banner-track>
            @foreach ($slides as $index => $slide)
                @php
                    $src = str_starts_with($slide['image'], 'http')
                        ? $slide['image']
                        : asset($slide['image']);
                @endphp
                <div class="site-banner-slide {{ $index === 0 ? 'is-active' : '' }}"
                     data-site-banner-slide
                     role="group"
                     aria-roledescription="slide"
                     aria-label="{{ $index + 1 }} of {{ count($slides) }}"
                     @if ($index !== 0) hidden @endif>
                    <img src="{{ $src }}" alt="{{ $slide['alt'] ?? '' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async">
                </div>
            @endforeach
        </div>

        <div class="site-banner-overlay" aria-hidden="true"></div>

        <div class="site-container site-banner-content">
            <div class="site-banner-copy site-reveal">
                <span class="site-eyebrow site-banner-eyebrow">{{ config('site.hero.eyebrow') }}</span>
                <h1 id="hero-heading">{{ config('site.hero.headline') }}</h1>
                <p class="site-banner-lead">{{ config('site.hero.lead') }}</p>
                <div class="site-banner-actions">
                    <x-site.button :href="route('shop.index')" variant="accent" size="site-btn-lg">
                        Browse products
                    </x-site.button>
                    <x-site.button :href="route('contact')" variant="secondary" size="site-btn-lg" class="site-banner-btn-outline">
                        Contact us
                    </x-site.button>
                </div>
            </div>
        </div>

        @if (count($slides) > 1)
            <div class="site-banner-controls">
                <button type="button" class="site-banner-arrow site-banner-arrow--prev" data-site-banner-prev aria-label="Previous slide">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 6l-6 6 6 6"/></svg>
                </button>
                <div class="site-banner-dots" role="tablist" aria-label="Choose slide">
                    @foreach ($slides as $index => $slide)
                        <button type="button"
                                class="site-banner-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-site-banner-dot="{{ $index }}"
                                role="tab"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <button type="button" class="site-banner-arrow site-banner-arrow--next" data-site-banner-next aria-label="Next slide">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>
        @endif
    </section>
@endif
