{{--
    Hero with auto-rotating background — SHIMIRWA's 4 crops:
    Maize · Soybeans · Sorghum · Wheat
--}}
@php
$slides = [
    [
        'img'     => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=1920&q=80&fit=crop',
        'label'   => 'Maize',
        'caption' => 'Premium quality maize — field to flour',
    ],
    [
        'img'     => 'https://images.unsplash.com/photo-1601498822-0ef96b11d494?w=1920&q=80&fit=crop',
        'label'   => 'Soybeans',
        'caption' => 'High-protein soybeans, locally sourced',
    ],
    [
        'img'     => 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=1920&q=80&fit=crop',
        'label'   => 'Sorghum',
        'caption' => 'Sorghum — nutritious and traceable',
    ],
    [
        'img'     => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=1920&q=80&fit=crop',
        'label'   => 'Wheat',
        'caption' => 'Finest wheat, milled to perfection',
    ],
];
@endphp

<section class="site-hero" aria-label="Welcome to Shimirwa"
         style="position:relative;min-height:95vh;overflow:hidden;display:flex;align-items:center">

    {{-- ── Rotating crop slides ── --}}
    <div aria-hidden="true" style="position:absolute;inset:0;z-index:0">
        @foreach($slides as $i => $slide)
            <div class="hero-slide {{ $i === 0 ? 'hs-active' : '' }}"
                 data-slide="{{ $i }}"
                 style="position:absolute;inset:0;
                        background-image:url('{{ $slide['img'] }}');
                        background-size:cover;background-position:center;
                        transition:opacity 1.5s ease;
                        opacity:{{ $i === 0 ? '1' : '0' }};
                        animation:{{ $i === 0 ? 'kenBurns 8s ease-in-out infinite alternate' : 'none' }}">
            </div>
        @endforeach

        {{-- Gradient overlay: dark left → transparent right --}}
        <div style="position:absolute;inset:0;
                    background:linear-gradient(105deg,
                      rgba(8,47,87,.93)  0%,
                      rgba(16,73,140,.84) 38%,
                      rgba(16,73,140,.52) 62%,
                      rgba(8,47,87,.25)   100%)">
        </div>
        {{-- Bottom vignette --}}
        <div style="position:absolute;bottom:0;left:0;right:0;height:140px;
                    background:linear-gradient(to top,rgba(8,47,87,.5),transparent)"></div>
    </div>

    {{-- Dot texture --}}
    <div aria-hidden="true"
         style="position:absolute;inset:0;z-index:1;pointer-events:none;
                background-image:radial-gradient(circle,rgba(255,255,255,.055) 1px,transparent 1px);
                background-size:30px 30px"></div>

    {{-- Decorative rings --}}
    <div class="hero-ring" style="z-index:1" aria-hidden="true"></div>
    <div class="hero-ring" style="z-index:1" aria-hidden="true"></div>

    {{-- ── Crop label (changes with slide) ── --}}
    <div id="hero-crop-label" aria-hidden="true"
         style="position:absolute;top:calc(var(--header-h) + 1.5rem);right:2rem;z-index:3;
                font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
                color:var(--copper);background:rgba(193,127,62,.15);
                border:1px solid rgba(193,127,62,.35);
                padding:.35rem 1rem;border-radius:99px;
                transition:opacity .4s ease">
        Maize
    </div>

    {{-- ── Main content ── --}}
    <div class="sc" style="position:relative;z-index:2;width:100%">
        <div class="hero-content" style="padding:8rem 0 5rem">

            {{-- Left: copy --}}
            <div class="hero-copy">
                <div class="hero-eyebrow">
                    Premium Food Processing · Rwanda
                </div>

                <h1 class="hero-title" style="animation:heroIn .85s var(--ease) both">
                    From Rwanda's Finest<br>
                    <span class="accent">Grains to Your Table</span>
                </h1>

                <p class="hero-desc" style="animation:heroIn .85s .18s var(--ease) both">
                    {{ config('site.hero.lead', 'Maize. Soybeans. Sorghum. Wheat. Every grain sourced locally, processed to the highest standards, and delivered fresh across Rwanda.') }}
                </p>

                <div class="hero-actions" style="animation:heroIn .85s .32s var(--ease) both">
                    <a href="{{ route('shop.index') }}" class="btn btn-copper btn-lg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                        Shop Our Products
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline btn-lg">
                        Contact Us
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                </div>

                <div class="hero-trust" style="animation:heroIn .85s .46s var(--ease) both">
                    <div class="hero-trust-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        100% Natural
                    </div>
                    <div class="hero-trust-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        Made in Rwanda
                    </div>
                    <div class="hero-trust-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/></svg>
                        Bulk Supply Available
                    </div>
                    <div class="hero-trust-item">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3"/></svg>
                        Fast Delivery
                    </div>
                </div>
            </div>

            {{-- Right: glassmorphism card --}}
            <div class="hero-visual" aria-hidden="true" style="animation:heroIn .9s .22s var(--ease) both">
                <div class="hero-card">
                    @php $logoUrl = config('site.logo') ? (str_starts_with(config('site.logo'),'http') ? config('site.logo') : asset(config('site.logo'))) : null; @endphp
                    @if($logoUrl)
                        <div class="hero-card-logo"><img src="{{ $logoUrl }}" alt="Shimirwa logo"></div>
                    @endif
                    <div class="hero-card-title">{{ config('site.name') }}</div>
                    <div class="hero-card-sub">Rulindo, Rwanda · Est. 2010</div>

                    {{-- Crop chips --}}
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin:.75rem 0 1.1rem">
                        @foreach(['Maize','Soybeans','Sorghum','Wheat'] as $crop)
                            <span style="font-size:.68rem;font-weight:700;padding:.2rem .65rem;border-radius:99px;
                                         background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);
                                         border:1px solid rgba(255,255,255,.15)">
                                {{ $crop }}
                            </span>
                        @endforeach
                    </div>

                    <div class="hero-stat-row">
                        @foreach(config('site.stats',[]) as $i => $stat)
                            @if($i < 4)
                                <div class="hero-stat">
                                    <div class="hero-stat-val {{ $i % 2 ? 'copper' : '' }}">{{ $stat['value'] }}</div>
                                    <div class="hero-stat-label">{{ $stat['label'] }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Slide navigation ── --}}
    <div style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);
                z-index:3;display:flex;align-items:center;gap:.65rem" aria-label="Slide navigation">
        @foreach($slides as $i => $slide)
            <button class="slide-dot" data-slide="{{ $i }}"
                    aria-label="Slide {{ $i+1 }}: {{ $slide['label'] }}"
                    style="height:.45rem;border-radius:99px;border:none;cursor:pointer;
                           transition:all .35s;
                           width:{{ $i === 0 ? '2rem' : '.45rem' }};
                           background:{{ $i === 0 ? 'var(--copper)' : 'rgba(255,255,255,.35)' }}">
            </button>
        @endforeach
    </div>

    {{-- Scroll hint --}}
    <div class="hero-scroll" style="z-index:3" aria-hidden="true">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="m19 9-7 7-7-7"/>
        </svg>
        Scroll
    </div>
</section>

@push('scripts')
<script>
(function () {
    const slides   = document.querySelectorAll('.hero-slide');
    const dots     = document.querySelectorAll('.slide-dot');
    const cropLabel = document.getElementById('hero-crop-label');
    const crops    = @json(array_column($slides, 'label'));
    const captions = @json(array_column($slides, 'caption'));

    if (!slides.length) return;

    let current = 0;
    let timer;

    function goTo(n) {
        // Fade out current
        slides[current].style.opacity = '0';
        slides[current].style.animation = 'none';
        dots[current].style.width   = '.45rem';
        dots[current].style.background = 'rgba(255,255,255,.35)';

        current = (n + slides.length) % slides.length;

        // Fade in next + restart Ken Burns
        slides[current].style.opacity = '1';
        slides[current].style.animation = 'kenBurns 8s ease-in-out infinite alternate';
        dots[current].style.width   = '2rem';
        dots[current].style.background = 'var(--copper)';

        // Update crop label
        if (cropLabel) {
            cropLabel.style.opacity = '0';
            setTimeout(() => {
                cropLabel.textContent = crops[current];
                cropLabel.style.opacity = '1';
            }, 250);
        }
    }

    function start() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), 5500);
    }

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            clearInterval(timer);
            goTo(parseInt(dot.dataset.slide));
            start();
        });
    });

    start();
})();
</script>
@endpush
