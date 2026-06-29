@extends('layouts.site')

@section('title', 'Home')
@section('meta_description', config('site.tagline'))

@section('content')
    {{-- Hero --}}
    <x-site.banner-slider />

    {{-- Trust strip --}}
    <div class="features-strip">
        <div class="sc">
            <div class="features-grid">
                <div class="feature-item reveal">
                    <div class="feature-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div>
                        <div class="feature-title">Certified Quality</div>
                        <div class="feature-desc">Every batch tested and certified before leaving our facility.</div>
                    </div>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-icon copper">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </div>
                    <div>
                        <div class="feature-title">Fast Delivery</div>
                        <div class="feature-desc">Reliable nationwide distribution with bulk order priority.</div>
                    </div>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    </div>
                    <div>
                        <div class="feature-title">Locally Sourced</div>
                        <div class="feature-desc">Proudly supporting Rwandan farmers with direct procurement.</div>
                    </div>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-icon copper">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3"/></svg>
                    </div>
                    <div>
                        <div class="feature-title">Bulk Orders</div>
                        <div class="feature-desc">Custom packaging and bulk supply for distributors and retailers.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vision & Mission --}}
    <x-site.vision-mission />

    {{-- Featured Products --}}
    <section class="section" aria-labelledby="featured-heading">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--blue reveal">Our Products</span>
                <h2 id="featured-heading" class="section-title reveal">Featured Products</h2>
                <p class="reveal">Discover our most popular flour, grits, and bulk supply lines — all traceable from source.</p>
            </div>

            @if($featuredProducts->isNotEmpty())
                <div class="products-grid reveal" style="margin-bottom:2.5rem">
                    @foreach ($featuredProducts as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:3rem 0;color:var(--text-muted)" class="reveal">
                    <svg width="56" height="56" fill="none" viewBox="0 0 24 24" style="margin:0 auto 1rem;color:var(--slate-200)" aria-hidden="true">
                        <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                    <p style="font-size:.95rem">Products coming soon — check back shortly.</p>
                </div>
            @endif

            <div style="text-align:center" class="reveal">
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                    View all products
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="stats-section" aria-labelledby="stats-heading">
        <div class="stats-bg" aria-hidden="true"></div>
        <div class="stats-orb" aria-hidden="true"></div>
        <div class="stats-orb" aria-hidden="true"></div>
        <div class="sc" style="position:relative;z-index:1">
            <div class="section-lead reveal">
                <span class="eyebrow eyebrow--white">By The Numbers</span>
                <h2 id="stats-heading" class="section-title reveal" style="color:white">Built on consistency and scale</h2>
            </div>
            <div class="stats-grid">
                @foreach ($stats as $stat)
                    <div class="stat-card reveal">
                        <div class="stat-val {{ isset($stat['accent']) ? 'copper' : '' }}">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="section section-alt" aria-labelledby="testimonials-heading">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--copper reveal">Testimonials</span>
                <h2 id="testimonials-heading" class="section-title reveal">What our partners say</h2>
            </div>
            <div class="testimonial-grid">
                @foreach ($testimonials as $testimonial)
                    <figure class="testimonial-card reveal">
                        <div class="testimonial-stars">
                            @for ($i = 0; $i < 5; $i++)
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                            @endfor
                        </div>
                        <blockquote class="testimonial-quote">"{{ $testimonial['quote'] }}"</blockquote>
                        <figcaption class="testimonial-author">{{ $testimonial['author'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="cta-section" aria-labelledby="cta-heading">
        <div class="cta-bg" aria-hidden="true"></div>
        <div class="sc" style="position:relative;z-index:1">
            <span class="eyebrow eyebrow--white reveal" style="margin-bottom:1rem;display:inline-flex">Let's Work Together</span>
            <h2 id="cta-heading" class="cta-title reveal">Ready to partner with Shimirwa?</h2>
            <p class="cta-desc reveal">
                Request a quote, place a bulk order, or learn how our inventory system keeps every batch traceable from field to bag.
            </p>
            <div class="cta-actions reveal">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg"
                   style="background:white;color:var(--copper-dark);box-shadow:0 4px 14px rgba(0,0,0,.15)">
                    Get in touch
                </a>
                <a href="{{ route('admin.login', absolute: false) }}" class="btn btn-outline btn-lg">
                    Staff Login
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible', 'is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal, .site-reveal').forEach(el => io.observe(el));
})();
</script>
@endpush
