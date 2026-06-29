@extends('layouts.site')

@section('title', 'About Us')
@section('meta_description', 'Learn about SHIMIRWA COMPANY Ltd — a proudly Rwandan agri-business transforming maize, soybeans, sorghum, and wheat into premium food products.')

@section('content')

    {{-- ── Page Hero ── --}}
    <div class="page-hero" style="min-height:28rem;display:flex;align-items:center">
        <div class="page-hero-bg" aria-hidden="true"></div>
        {{-- Dot texture --}}
        <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none"></div>
        <div class="sc page-hero-content" style="position:relative;z-index:1">
            <span class="eyebrow eyebrow--white reveal" style="margin-bottom:1rem;display:inline-flex">
                About Us
            </span>
            <h1 style="animation:heroIn .8s var(--ease) both">{{ config('site.name') }}</h1>
            <p style="animation:heroIn .8s .15s var(--ease) both;max-width:40rem">
                A proudly Rwandan agri-business transforming locally grown grains into premium food products — traceable from field to shelf.
            </p>
            {{-- Crop chips --}}
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-top:1.5rem;animation:heroIn .8s .3s var(--ease) both">
                @foreach(['Maize','Soybeans','Sorghum','Wheat'] as $crop)
                    <span style="font-size:.72rem;font-weight:700;padding:.3rem .9rem;border-radius:99px;
                                 background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);
                                 border:1px solid rgba(255,255,255,.2);letter-spacing:.04em">
                        {{ $crop }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Who We Are ── --}}
    <section class="section">
        <div class="sc">
            <div style="display:grid;grid-template-columns:1fr;gap:3rem;align-items:center">
                <div style="max-width:100%">
                    @media (min-width:900px) { div { grid-template-columns: 1fr 1fr; } }
                </div>
            </div>
            <div style="display:grid;gap:3.5rem;align-items:center"
                 class="about-intro-grid">
                {{-- Text --}}
                <div class="reveal">
                    <span class="eyebrow eyebrow--blue" style="margin-bottom:1rem;display:inline-flex">Who We Are</span>
                    <h2 class="section-title" style="margin-bottom:1.25rem">
                        {{ config('site.about.who_we_are_title', 'Our Story') }}
                    </h2>
                    <p style="font-size:1.0625rem;color:var(--text-muted);line-height:1.8;margin-bottom:1.25rem">
                        {{ config('site.about.who_we_are') }}
                    </p>
                    <p style="font-size:1rem;color:var(--text-muted);line-height:1.75">
                        Our operations span the full agricultural value chain — from working directly with smallholder farmers and cooperatives for sourcing, to running state-of-the-art processing facilities that mill, dry, and package products for both household and institutional markets.
                    </p>
                    <div style="display:flex;flex-wrap:wrap;gap:1.5rem;margin-top:2rem">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;color:var(--blue)">
                            <div style="width:2.25rem;height:2.25rem;border-radius:50%;background:var(--blue-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            </div>
                            Rulindo District, Northern Province
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;color:var(--copper-dark)">
                            <div style="width:2.25rem;height:2.25rem;border-radius:50%;background:var(--copper-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6.75 3v1.5M17.25 3v1.5M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
                            </div>
                            Founded 2010
                        </div>
                    </div>
                </div>

                {{-- Stats card --}}
                <div class="reveal" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    @foreach(config('site.stats', []) as $stat)
                        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);
                                    padding:1.5rem;text-align:center;box-shadow:var(--shadow-sm);
                                    transition:transform .2s,box-shadow .2s"
                             onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow)'"
                             onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                            <div style="font-size:1.875rem;font-weight:900;color:var(--blue);letter-spacing:-.02em;margin-bottom:.25rem">
                                {{ $stat['value'] }}
                            </div>
                            <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em">
                                {{ $stat['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ── Mission & Vision ── --}}
    <section class="section section-alt">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--blue reveal">Our Purpose</span>
                <h2 class="section-title reveal">Mission &amp; Vision</h2>
                <p class="reveal">The principles that drive everything we do — from farm gate to finished product.</p>
            </div>
            <div class="vm-grid">
                {{-- Mission --}}
                <article class="vm-card blue reveal">
                    <div class="vm-card-pattern" aria-hidden="true"></div>
                    <div class="vm-card-icon">
                        <svg width="24" height="24" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                    </div>
                    <div class="vm-card-title">{{ config('site.mission.title', 'Our Mission') }}</div>
                    <div class="vm-card-text">{{ config('site.mission.text') }}</div>
                </article>
                {{-- Vision --}}
                <article class="vm-card copper reveal">
                    <div class="vm-card-pattern" aria-hidden="true"></div>
                    <div class="vm-card-icon">
                        <svg width="24" height="24" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M2.036 12.322a1 1 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                            <path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </div>
                    <div class="vm-card-title">{{ config('site.vision.title', 'Our Vision') }}</div>
                    <div class="vm-card-text">{{ config('site.vision.text') }}</div>
                </article>
            </div>
        </div>
    </section>

    {{-- ── What We Process ── --}}
    <section class="section">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--copper reveal">Our Raw Materials</span>
                <h2 class="section-title reveal">What We Process</h2>
                <p class="reveal">We source four key crops directly from Rwandan farmers and cooperatives, transforming them into premium flour and food products.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));gap:1.25rem">
                @php
                $crops = [
                    ['name'=>'Maize','desc'=>'Our most processed grain — milled into smooth maize flour for cooking, baking, and porridge blends. A staple for Rwandan households.','icon'=>'🌽','color'=>'var(--copper)','bg'=>'var(--copper-light)'],
                    ['name'=>'Soybeans','desc'=>'High-protein soybeans processed into flour and fortified blends, supporting nutrition programs and health-conscious consumers.','icon'=>'🫘','color'=>'var(--blue)','bg'=>'var(--blue-light)'],
                    ['name'=>'Sorghum','desc'=>'Nutrient-rich sorghum milled into flour for traditional recipes, porridge mixes, and specialty food products.','icon'=>'🌾','color'=>'var(--copper-dark)','bg'=>'var(--copper-light)'],
                    ['name'=>'Wheat','desc'=>'Premium wheat processed to fine flour suitable for baking, bread-making, and a wide range of household and commercial uses.','icon'=>'🌾','color'=>'var(--blue-dark)','bg'=>'var(--blue-light)'],
                ];
                @endphp
                @foreach($crops as $crop)
                    <div class="reveal" style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);
                                padding:1.75rem;box-shadow:var(--shadow-sm);
                                transition:transform .22s,box-shadow .22s"
                         onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='var(--shadow-lg)'"
                         onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                        <div style="width:3rem;height:3rem;border-radius:var(--radius);background:{{ $crop['bg'] }};
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:1.4rem;margin-bottom:1rem">
                            {{ $crop['icon'] }}
                        </div>
                        <div style="font-size:1rem;font-weight:800;color:var(--slate-800);margin-bottom:.5rem">
                            {{ $crop['name'] }}
                        </div>
                        <div style="font-size:.84rem;color:var(--text-muted);line-height:1.65">
                            {{ $crop['desc'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Core Values ── --}}
    <section class="section section-alt">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--blue reveal">Our Values</span>
                <h2 class="section-title reveal">{{ config('site.about.values_title', 'What We Stand For') }}</h2>
                <p class="reveal">The principles that shape how we work with farmers, clients, and communities every single day.</p>
            </div>

            <div class="features-grid">
                @foreach(config('site.about.values', []) as $value)
                    <div class="feature-item reveal">
                        <div class="feature-icon {{ $loop->even ? 'copper' : '' }}" style="font-size:.85rem;font-weight:900;font-family:var(--font)">
                            {{ $value['number'] }}
                        </div>
                        <div>
                            <div class="feature-title">{{ $value['title'] }}</div>
                            <div class="feature-desc">{{ $value['text'] }}</div>
                        </div>
                    </div>
                @endforeach
                {{-- Extra value not in config --}}
                <div class="feature-item reveal">
                    <div class="feature-icon" style="font-size:.85rem;font-weight:900">05</div>
                    <div>
                        <div class="feature-title">Customer Satisfaction</div>
                        <div class="feature-desc">We deliver consistent value to every partner and client, from the first order to every reorder.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Milestones timeline ── --}}
    <section class="section">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--copper reveal">Our Journey</span>
                <h2 class="section-title reveal">From Humble Beginnings to Growing Impact</h2>
            </div>

            <div style="position:relative;max-width:52rem;margin:0 auto">
                {{-- Vertical line --}}
                <div style="position:absolute;left:5.5rem;top:0;bottom:0;width:2px;background:var(--border)"
                     class="reveal" aria-hidden="true"></div>

                @foreach(config('site.milestones', []) as $milestone)
                    <div class="reveal" style="display:grid;grid-template-columns:5.5rem 1fr;gap:1.75rem;
                                margin-bottom:2.5rem;align-items:flex-start;position:relative">
                        {{-- Year --}}
                        <div style="text-align:right;padding-right:1.5rem;position:relative">
                            <span style="font-size:.9rem;font-weight:900;color:var(--blue);letter-spacing:-.01em">
                                {{ $milestone['year'] }}
                            </span>
                            {{-- dot --}}
                            <span style="position:absolute;right:-5px;top:50%;transform:translateY(-50%);
                                         width:10px;height:10px;border-radius:50%;
                                         background:var(--copper);border:2px solid white;
                                         box-shadow:0 0 0 2px var(--copper)"
                                  aria-hidden="true"></span>
                        </div>
                        {{-- Content --}}
                        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);
                                    padding:1.25rem 1.5rem;box-shadow:var(--shadow-sm)">
                            <div style="font-size:.9375rem;font-weight:700;color:var(--slate-800);margin-bottom:.3rem">
                                {{ $milestone['title'] }}
                            </div>
                            <div style="font-size:.84rem;color:var(--text-muted);line-height:1.6">
                                {{ $milestone['text'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Services overview ── --}}
    <section class="section section-alt">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--blue reveal">What We Do</span>
                <h2 class="section-title reveal">Our Services</h2>
                <p class="reveal">End-to-end agro-processing — from farmer partnerships to retail-ready packaging.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:1.5rem">
                @php
                $services = [
                    [
                        'title' => 'Sourcing & Aggregation',
                        'icon'  => '<path stroke-linecap="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582"/>',
                        'points'=> [
                            'Partnering with local farmers and cooperatives',
                            'Fair trade pricing and reliable off-take agreements',
                            'Training farmers in good agricultural practices',
                        ],
                        'color' => 'blue',
                    ],
                    [
                        'title' => 'Processing & Quality Control',
                        'icon'  => '<path stroke-linecap="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L4.2 15.3m0 0-1.233 3.086M19.8 15.3l1.233 3.086M4.2 15.3l6.55 1.639a9 9 0 0 0 2.5 0L19.8 15.3"/>',
                        'points'=> [
                            'Modern milling and drying technologies',
                            'Stringent food safety standards and lab testing',
                            'Customized solutions for domestic & export markets',
                        ],
                        'color' => 'copper',
                    ],
                    [
                        'title' => 'Packaging & Distribution',
                        'icon'  => '<path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
                        'points'=> [
                            '1kg, 5kg, and bulk carton packaging options',
                            'Partnerships with supermarkets and agro-dealers',
                            'On-time delivery and logistics across Rwanda',
                        ],
                        'color' => 'blue',
                    ],
                ];
                @endphp
                @foreach($services as $service)
                    <div class="reveal" style="background:var(--white);border:1px solid var(--border);
                                border-radius:var(--radius-xl);padding:2rem;box-shadow:var(--shadow-sm);
                                transition:transform .22s,box-shadow .22s"
                         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow)'"
                         onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                        <div style="width:3rem;height:3rem;border-radius:var(--radius);
                                    background:{{ $service['color']==='copper' ? 'var(--copper-light)' : 'var(--blue-light)' }};
                                    display:flex;align-items:center;justify-content:center;margin-bottom:1.1rem;
                                    color:{{ $service['color']==='copper' ? 'var(--copper-dark)' : 'var(--blue)' }}">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $service['icon'] !!}
                            </svg>
                        </div>
                        <div style="font-size:1rem;font-weight:800;color:var(--slate-800);margin-bottom:.85rem">
                            {{ $service['title'] }}
                        </div>
                        <ul style="display:flex;flex-direction:column;gap:.5rem">
                            @foreach($service['points'] as $point)
                                <li style="display:flex;align-items:flex-start;gap:.5rem;font-size:.84rem;color:var(--text-muted);line-height:1.55">
                                    <svg width="14" height="14" fill="none" stroke="{{ $service['color']==='copper' ? 'var(--copper)' : 'var(--blue)' }}" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.15rem" aria-hidden="true"><path stroke-linecap="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Partners & Clients ── --}}
    <section class="section">
        <div class="sc">
            <div class="section-lead">
                <span class="eyebrow eyebrow--copper reveal">Our Network</span>
                <h2 class="section-title reveal">Partners &amp; Clients</h2>
                <p class="reveal">We proudly collaborate with a growing network of stakeholders across Rwanda's agricultural value chain.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr));gap:1rem">
                @php
                $partners = [
                    ['label'=>'Farmer Cooperatives','desc'=>'Strengthening local production capacity and ensuring fair trade.','icon'=>'👩‍🌾'],
                    ['label'=>'Agro-Dealers','desc'=>'Expanding product availability across Rwanda through trusted networks.','icon'=>'🏪'],
                    ['label'=>'Supermarkets & Retail','desc'=>'Supplying trusted consumer goods to shelves nationwide.','icon'=>'🛒'],
                    ['label'=>'Government Institutions','desc'=>'Supporting food security and national development initiatives.','icon'=>'🏛️'],
                ];
                @endphp
                @foreach($partners as $partner)
                    <div class="reveal" style="text-align:center;padding:1.75rem 1.25rem;background:var(--slate-50);
                                border:1px solid var(--border);border-radius:var(--radius-lg);
                                transition:transform .2s,box-shadow .2s"
                         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow)'"
                         onmouseout="this.style.transform='';this.style.boxShadow='none'">
                        <div style="font-size:2rem;margin-bottom:.75rem">{{ $partner['icon'] }}</div>
                        <div style="font-size:.875rem;font-weight:700;color:var(--slate-800);margin-bottom:.4rem">{{ $partner['label'] }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);line-height:1.55">{{ $partner['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA ── --}}
    <section class="cta-section">
        <div class="cta-bg" aria-hidden="true"></div>
        <div class="sc" style="position:relative;z-index:1;text-align:center">
            <span class="eyebrow eyebrow--white reveal" style="margin-bottom:1rem;display:inline-flex">
                Join Us
            </span>
            <h2 class="cta-title reveal">
                Are you a farmer, cooperative, or distributor?
            </h2>
            <p class="cta-desc reveal">
                Register as a supplier or reach out to discuss partnership opportunities. Together, we can build a stronger agricultural future for Rwanda.
            </p>
            <div class="cta-actions reveal">
                <a href="{{ route('contact') }}" class="btn btn-lg"
                   style="background:white;color:var(--copper-dark);box-shadow:0 4px 14px rgba(0,0,0,.15)">
                    Register as Supplier
                </a>
                <a href="{{ route('shop.index') }}" class="btn btn-outline btn-lg">
                    View Our Products
                </a>
            </div>
        </div>
    </section>

@endsection
