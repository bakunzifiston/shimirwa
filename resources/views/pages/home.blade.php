@extends('layouts.site')

@section('title', 'Home')
@section('meta_description', config('site.tagline'))

@section('content')
    {{-- Animated Hero --}}
    <x-site.banner-slider />

    {{-- Vision & Mission --}}
    <x-site.vision-mission />

    {{-- Featured Products --}}
    <section class="py-20 bg-white" aria-labelledby="featured-heading">
        <div class="site-container">
            <div class="sha-section-lead site-reveal">
                <span class="sha-section-eyebrow sha-section-eyebrow--blue">Our Products</span>
                <h2 id="featured-heading" class="text-2xl md:text-3xl font-bold text-slate-900">Featured Products</h2>
                <p class="text-slate-500 max-w-lg mx-auto text-base">Discover our most popular flour, grits, and bulk supply lines.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                @foreach ($featuredProducts as $product)
                    <x-site.product-card :product="$product" />
                @endforeach
            </div>

            <div class="text-center site-reveal">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-[#10498c] text-white font-bold
                          hover:bg-[#082f57] transition-all hover:-translate-y-0.5 shadow-lg hover:shadow-xl text-sm">
                    View all products
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="py-20 relative overflow-hidden" aria-labelledby="stats-heading"
             style="background:linear-gradient(135deg,#10498c 0%,#0d3d72 50%,#082f57 100%)">
        {{-- dot pattern --}}
        <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
             style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:28px 28px"></div>
        {{-- warm orb --}}
        <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full opacity-20 pointer-events-none"
             style="background:radial-gradient(circle, #a66b3b, transparent 70%)"></div>

        <div class="site-container relative z-10">
            <div class="text-center mb-12 site-reveal">
                <span class="sha-section-eyebrow" style="background:rgba(255,255,255,0.12);color:#fbbf24">By the Numbers</span>
                <h2 id="stats-heading" class="text-2xl md:text-3xl font-bold text-white mt-3">Built on consistency and scale</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach ($stats as $stat)
                    <div class="text-center p-6 rounded-2xl site-reveal"
                         style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(8px)">
                        <div class="text-3xl md:text-4xl font-extrabold text-white mb-1 tracking-tight">{{ $stat['value'] }}</div>
                        <div class="text-sm font-medium" style="color:rgba(255,255,255,0.7)">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="py-20 bg-slate-50" aria-labelledby="testimonials-heading">
        <div class="site-container">
            <div class="sha-section-lead site-reveal">
                <span class="sha-section-eyebrow sha-section-eyebrow--amber">Testimonials</span>
                <h2 id="testimonials-heading" class="text-2xl md:text-3xl font-bold text-slate-900">What our partners say</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($testimonials as $testimonial)
                    <figure class="flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm p-6 site-reveal
                                   hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" class="text-[#a66b3b] mb-4 opacity-70" aria-hidden="true">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                        <blockquote class="text-sm text-slate-600 leading-relaxed flex-1 italic mb-5">
                            "{{ $testimonial['quote'] }}"
                        </blockquote>
                        <figcaption class="text-sm font-semibold text-slate-800 border-t border-slate-100 pt-4">
                            {{ $testimonial['author'] }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 relative overflow-hidden"
             style="background:linear-gradient(135deg,#a66b3b 0%,#8a5630 50%,#7a4f2a 100%)">
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none"
             style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:24px 24px"></div>

        <div class="site-container relative z-10 text-center site-reveal">
            <span class="sha-section-eyebrow mb-4" style="background:rgba(255,255,255,0.15);color:#fef3e2;display:inline-flex">
                Let's Work Together
            </span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-4 mt-2">Ready to partner with Shimirwa?</h2>
            <p class="max-w-xl mx-auto mb-10 text-base leading-relaxed" style="color:rgba(255,255,255,0.82)">
                Request a quote, place a bulk order, or learn how our inventory system keeps every batch traceable.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('contact') }}"
                   class="px-8 py-3.5 rounded-xl bg-white font-bold text-[#a66b3b] hover:bg-amber-50 transition-all
                          hover:-translate-y-0.5 shadow-lg hover:shadow-xl text-sm">
                    Get in touch
                </a>
                <a href="{{ route('admin.login', absolute: false) }}"
                   class="px-8 py-3.5 rounded-xl font-bold text-white transition-all hover:-translate-y-0.5 text-sm"
                   style="border:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.08)">
                    Staff Login
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    const els = document.querySelectorAll('.site-reveal');
    if (!els.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // Stagger each element slightly
                setTimeout(() => entry.target.classList.add('is-visible'), i * 60);
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    els.forEach(el => io.observe(el));
})();
</script>
@endpush
