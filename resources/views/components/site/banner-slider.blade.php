{{--
    Hero — SHIMIRWA farm-to-flour, richer content
    Background: rotating crop slideshow
--}}
@php
$slides = [
    [
        'img'   => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=1920&q=80&fit=crop',
        'label' => 'Wheat',
    ],
    [
        'img'   => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=1920&q=80&fit=crop',
        'label' => 'Maize',
    ],
    [
        'img'   => 'https://images.unsplash.com/photo-1523741543316-beb7fc7023d8?w=1920&q=80&fit=crop',
        'label' => 'Sorghum',
    ],
    [
        'img'   => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920&q=80&fit=crop',
        'label' => 'Soybeans',
    ],
];
@endphp

<section class="site-hero" aria-label="Welcome to Shimirwa"
         style="position:relative;min-height:100vh;overflow:hidden;display:flex;flex-direction:column;align-items:stretch">

    {{-- ── Rotating background slides ── --}}
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

        {{-- Heavy left-side overlay for text legibility --}}
        <div style="position:absolute;inset:0;
                    background:linear-gradient(110deg,
                        rgba(6,35,65,.97)   0%,
                        rgba(8,47,87,.92)  30%,
                        rgba(16,73,140,.70) 55%,
                        rgba(8,47,87,.30)  80%,
                        rgba(8,47,87,.10) 100%)">
        </div>
        <div style="position:absolute;bottom:0;left:0;right:0;height:160px;
                    background:linear-gradient(to top,rgba(6,35,65,.6),transparent)"></div>
    </div>

    {{-- Dot texture --}}
    <div aria-hidden="true"
         style="position:absolute;inset:0;z-index:1;pointer-events:none;
                background-image:radial-gradient(circle,rgba(255,255,255,.045) 1px,transparent 1px);
                background-size:28px 28px"></div>

    {{-- Decorative rings --}}
    <div class="hero-ring" style="z-index:1" aria-hidden="true"></div>
    <div class="hero-ring" style="z-index:1" aria-hidden="true"></div>

    {{-- ── Current crop pill ── --}}
    <div id="hero-crop-label" aria-hidden="true"
         style="position:absolute;top:calc(var(--header-h) + 1.5rem);right:2rem;z-index:3;
                font-size:.7rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
                color:var(--copper);background:rgba(193,127,62,.15);
                border:1px solid rgba(193,127,62,.35);
                padding:.35rem 1rem;border-radius:99px;transition:opacity .4s ease">
        Wheat
    </div>

    {{-- ── Main hero content ── --}}
    <div class="sc" style="position:relative;z-index:2;width:100%;flex:1;display:flex;align-items:center">
        <div class="hero-content" style="padding:7rem 0 4rem;width:100%">

            {{-- ── LEFT: all the copy ── --}}
            <div class="hero-copy">

                {{-- Eyebrow --}}
                <div class="hero-eyebrow" style="margin-bottom:1.1rem">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    Grain Milling &amp; Flour · Rulindo, Rwanda · Est. 2010
                </div>

                {{-- Headline --}}
                <h1 class="hero-title" style="animation:heroIn .85s var(--ease) both;margin-bottom:1.25rem">
                    Rwanda's Finest<br>
                    Grains Milled Into<br>
                    <span class="accent">Premium Flour</span>
                </h1>

                {{-- Rich body copy --}}
                <div style="animation:heroIn .85s .15s var(--ease) both">
                    <p class="hero-desc" style="margin-bottom:.9rem">
                        At <strong style="color:rgba(255,255,255,.95)">SHIMIRWA COMPANY Ltd</strong>, we partner directly with Rwandan smallholder farmers to collect
                        <strong style="color:rgba(255,255,255,.9)">maize, soybeans, sorghum, and wheat</strong> — then run every batch through our
                        in-house sorting, roasting, and milling facility in Rulindo District.
                    </p>
                    <p class="hero-desc" style="margin-bottom:1.25rem">
                        The result is a range of <strong style="color:var(--copper)">finely milled, nutrient-rich flour products</strong> sold under our
                        <strong style="color:rgba(255,255,255,.95)">BINO</strong> and <strong style="color:rgba(255,255,255,.95)">KURA</strong> brands —
                        available in 1 kg, 5 kg, and bulk 25 kg formats for households, retailers, schools, and institutions across Rwanda.
                    </p>
                </div>

                {{-- Farm-to-flour process strip --}}
                <div style="animation:heroIn .85s .28s var(--ease) both;
                            display:flex;align-items:center;flex-wrap:wrap;gap:.35rem;
                            margin-bottom:1.5rem">
                    @foreach([
                        ["🌾","Farm\nCollection"],
                        ["⚙️","Sorting &\nCleaning"],
                        ["🔥","Roasting"],
                        ["⚙","Milling"],
                        ["🌾","Flour &\nPackaging"],
                    ] as $idx => $step)
                        <div style="display:flex;align-items:center;gap:.35rem">
                            <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);
                                        border-radius:var(--radius-sm);padding:.4rem .65rem;
                                        text-align:center;min-width:4.25rem">
                                <div style="font-size:.95rem;line-height:1;margin-bottom:.18rem">{{ $step[0] }}</div>
                                <div style="font-size:.58rem;font-weight:700;color:rgba(255,255,255,.78);
                                            white-space:pre-line;letter-spacing:.03em;text-transform:uppercase;line-height:1.25">{{ $step[1] }}</div>
                            </div>
                            @if(!$loop->last)
                                <svg width="12" height="12" fill="none" stroke="var(--copper)" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                                </svg>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- CTAs --}}
                <div class="hero-actions" style="animation:heroIn .85s .38s var(--ease) both;margin-bottom:1.75rem">
                    <a href="{{ route('shop.index') }}" class="btn btn-copper btn-lg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                        Shop Our Flour
                    </a>
                    <a href="{{ route('about') }}" class="btn btn-outline btn-lg">
                        Our Story
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline btn-lg"
                       style="border-color:rgba(255,255,255,.3);color:rgba(255,255,255,.8)">
                        Bulk Order
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="hero-trust" style="animation:heroIn .85s .48s var(--ease) both">
                    <div class="hero-trust-item">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        100% Natural Flour
                    </div>
                    <div class="hero-trust-item">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        Rwandan Farmers
                    </div>
                    <div class="hero-trust-item">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/></svg>
                        1kg · 5kg · 25kg
                    </div>
                    <div class="hero-trust-item">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3"/></svg>
                        Nationwide Delivery
                    </div>
                </div>
            </div>

            {{-- ── RIGHT: brand card ── --}}
            <div class="hero-visual" aria-hidden="true" style="animation:heroIn .9s .2s var(--ease) both">
                <div class="hero-card">
                    @php $logoUrl = config('site.logo') ? (str_starts_with(config('site.logo'),'http') ? config('site.logo') : asset(config('site.logo'))) : null; @endphp
                    @if($logoUrl)
                        <div class="hero-card-logo"><img src="{{ $logoUrl }}" alt="Shimirwa logo"></div>
                    @endif
                    <div class="hero-card-title">{{ config('site.name') }}</div>
                    <div class="hero-card-sub">Rulindo District · Rwanda · Est. 2010</div>

                    {{-- Brand names --}}
                    <div style="margin:.85rem 0;padding:.65rem .75rem;
                                background:rgba(255,255,255,.07);border-radius:var(--radius-sm);
                                border:1px solid rgba(255,255,255,.12)">
                        <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
                                    color:rgba(255,255,255,.5);margin-bottom:.5rem">Our Flour Brands</div>
                        <div style="display:flex;gap:.5rem">
                            <div style="flex:1;text-align:center;padding:.45rem .5rem;
                                        background:rgba(193,127,62,.25);border:1px solid rgba(193,127,62,.4);
                                        border-radius:var(--radius-sm)">
                                <div style="font-size:1.05rem;font-weight:900;color:var(--copper);letter-spacing:.04em">BINO</div>
                                <div style="font-size:.58rem;color:rgba(255,255,255,.65);font-weight:600;text-transform:uppercase;letter-spacing:.05em">Composite Flour</div>
                            </div>
                            <div style="flex:1;text-align:center;padding:.45rem .5rem;
                                        background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);
                                        border-radius:var(--radius-sm)">
                                <div style="font-size:1.05rem;font-weight:900;color:white;letter-spacing:.04em">KURA</div>
                                <div style="font-size:.58rem;color:rgba(255,255,255,.65);font-weight:600;text-transform:uppercase;letter-spacing:.05em">Wheat Flour</div>
                            </div>
                        </div>
                    </div>

                    {{-- Raw materials we process --}}
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
                                color:rgba(255,255,255,.5);margin-bottom:.4rem">Grains We Mill</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:1rem">
                        @foreach(['🌽 Maize','🫘 Soybeans','🌾 Sorghum','🌾 Wheat'] as $crop)
                            <span style="font-size:.67rem;font-weight:700;padding:.22rem .6rem;border-radius:99px;
                                         background:rgba(255,255,255,.1);color:rgba(255,255,255,.88);
                                         border:1px solid rgba(255,255,255,.14)">{{ $crop }}</span>
                        @endforeach
                    </div>

                    {{-- Stats --}}
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

    {{-- ── Slide navigation dots ── --}}
    <div style="position:absolute;bottom:2.25rem;left:50%;transform:translateX(-50%);
                z-index:3;display:flex;align-items:center;gap:.6rem" aria-label="Slide navigation">
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
    const slides    = document.querySelectorAll('.hero-slide');
    const dots      = document.querySelectorAll('.slide-dot');
    const cropLabel = document.getElementById('hero-crop-label');
    const crops     = @json(array_column($slides, 'label'));

    if (!slides.length) return;

    let current = 0, timer;

    function goTo(n) {
        slides[current].style.opacity = '0';
        slides[current].style.animation = 'none';
        dots[current].style.width      = '.45rem';
        dots[current].style.background = 'rgba(255,255,255,.35)';

        current = (n + slides.length) % slides.length;

        slides[current].style.opacity = '1';
        slides[current].style.animation = 'kenBurns 8s ease-in-out infinite alternate';
        dots[current].style.width      = '2rem';
        dots[current].style.background = 'var(--copper)';

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

    dots.forEach(dot => dot.addEventListener('click', () => {
        clearInterval(timer);
        goTo(parseInt(dot.dataset.slide));
        start();
    }));

    start();
})();
</script>
@endpush
