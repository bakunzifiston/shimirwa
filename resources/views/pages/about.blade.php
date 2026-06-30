@extends('layouts.site')

@section('title', 'About Us')
@section('meta_description', 'Learn about SHIMIRWA COMPANY Ltd — a proudly Rwandan agri-business transforming maize, soybeans, sorghum, and wheat into premium food products.')

@section('content')

{{-- ── Hero ── --}}
<div class="page-hero" style="min-height:30rem;display:flex;align-items:center">
    <div class="page-hero-bg" aria-hidden="true"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none"></div>
    <div class="sc page-hero-content" style="position:relative;z-index:1">
        <span class="eyebrow eyebrow--white" style="margin-bottom:1rem;display:inline-flex;animation:heroIn .7s var(--ease) both">About Us</span>
        <h1 style="animation:heroIn .8s .1s var(--ease) both">{{ config('site.name') }}</h1>
        <p style="animation:heroIn .8s .2s var(--ease) both;max-width:38rem">
            A proudly Rwandan agri-business transforming locally grown grains into premium food products, traceable from field to shelf.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-top:1.5rem;animation:heroIn .8s .35s var(--ease) both">
            @foreach(['Maize','Soybeans','Sorghum','Wheat'] as $crop)
                <span style="font-size:.72rem;font-weight:700;padding:.3rem .9rem;border-radius:99px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2);letter-spacing:.04em">{{ $crop }}</span>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Who We Are ── --}}
<section class="section">
    <div class="sc">
        <div class="about-intro-grid" style="display:grid;gap:3.5rem;align-items:center">

            {{-- Text side --}}
            <div class="reveal">
                <span class="eyebrow eyebrow--blue" style="margin-bottom:1rem;display:inline-flex">Who We Are</span>
                <h2 class="section-title" style="margin-bottom:1.25rem">{{ config('site.about.who_we_are_title') }}</h2>
                <p style="font-size:1.0625rem;color:var(--text-muted);line-height:1.8;margin-bottom:1rem">
                    {{ config('site.about.who_we_are') }}
                </p>
                <p style="font-size:.9375rem;color:var(--text-muted);line-height:1.75;margin-bottom:2rem">
                    Our operations span the full agricultural value chain — from working directly with smallholder farmers and cooperatives for sourcing, to running state-of-the-art processing facilities that mill, dry, and package products for both household and institutional markets.
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Shop Products</a>
                    <a href="{{ route('contact') }}" class="btn btn-ghost">Contact Us</a>
                </div>
            </div>

            {{-- Stats cards grid --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem" class="reveal">
                @foreach(config('site.stats', []) as $i => $stat)
                    <div style="background:{{ $i % 2 === 0 ? 'var(--blue)' : 'var(--white)' }};
                                color:{{ $i % 2 === 0 ? 'white' : 'var(--slate-800)' }};
                                border:1px solid {{ $i % 2 === 0 ? 'transparent' : 'var(--border)' }};
                                border-radius:var(--radius-lg);padding:1.75rem;text-align:center;
                                box-shadow:{{ $i % 2 === 0 ? '0 8px 24px rgba(16,73,140,.25)' : 'var(--shadow-sm)' }};
                                transition:transform .2s var(--ease),box-shadow .2s"
                         onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='{{ $i % 2 === 0 ? '0 16px 40px rgba(16,73,140,.35)' : 'var(--shadow-lg)' }}'"
                         onmouseout="this.style.transform='';this.style.boxShadow='{{ $i % 2 === 0 ? '0 8px 24px rgba(16,73,140,.25)' : 'var(--shadow-sm)' }}'">
                        <div style="font-size:2rem;font-weight:900;letter-spacing:-.03em;margin-bottom:.3rem;
                                    color:{{ $i % 2 === 0 ? 'white' : 'var(--blue)' }}">
                            {{ $stat['value'] }}
                        </div>
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                                    color:{{ $i % 2 === 0 ? 'rgba(255,255,255,.75)' : 'var(--text-muted)' }}">
                            {{ $stat['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── Mission & Vision cards ── --}}
<section class="section section-alt">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--blue reveal">Our Purpose</span>
            <h2 class="section-title reveal">Mission &amp; Vision</h2>
        </div>
        <div class="vm-grid">
            <article class="vm-card blue reveal">
                <div class="vm-card-pattern" aria-hidden="true"></div>
                <div class="vm-card-icon">
                    <svg width="22" height="22" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                </div>
                <div class="vm-card-title">{{ config('site.mission.title') }}</div>
                <div class="vm-card-text">{{ config('site.mission.text') }}</div>
            </article>
            <article class="vm-card copper reveal">
                <div class="vm-card-pattern" aria-hidden="true"></div>
                <div class="vm-card-icon">
                    <svg width="22" height="22" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" d="M2.036 12.322a1 1 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                </div>
                <div class="vm-card-title">{{ config('site.vision.title') }}</div>
                <div class="vm-card-text">{{ config('site.vision.text') }}</div>
            </article>
        </div>
    </div>
</section>

{{-- ── What We Process (crop cards) ── --}}
<section class="section">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--copper reveal">Our Raw Materials</span>
            <h2 class="section-title reveal">What We Process</h2>
            <p class="reveal">We source four key crops directly from Rwandan farmers, transforming them into premium flour and food products.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));gap:1.25rem">
            @php
            $crops = [
                ['name'=>'Maize','emoji'=>'🌽','color'=>'var(--copper)','bg'=>'var(--copper-light)','desc'=>'Milled into smooth maize flour for cooking, baking, and porridge. A staple for Rwandan households and businesses.','tag'=>'Most Processed'],
                ['name'=>'Soybeans','emoji'=>'🫘','color'=>'var(--blue)','bg'=>'var(--blue-light)','desc'=>'High-protein soybeans processed into flour and fortified blends, supporting nutrition programs and health-conscious consumers.','tag'=>'High Protein'],
                ['name'=>'Sorghum','emoji'=>'🌾','color'=>'var(--copper-dark)','bg'=>'var(--copper-light)','desc'=>'Nutrient-rich sorghum milled into flour for traditional recipes, porridge mixes, and specialty food products.','tag'=>'Nutrient Rich'],
                ['name'=>'Wheat','emoji'=>'🌾','color'=>'var(--blue-dark)','bg'=>'var(--blue-light)','desc'=>'Premium wheat processed to fine flour for baking, bread-making, and a wide range of household and commercial uses.','tag'=>'Premium Grade'],
            ];
            @endphp
            @foreach($crops as $crop)
                <div class="reveal" style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-xl);padding:1.75rem;
                            box-shadow:var(--shadow-sm);position:relative;overflow:hidden;
                            transition:transform .25s var(--ease),box-shadow .25s,border-color .25s"
                     onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='var(--shadow-lg)';this.style.borderColor='rgba(16,73,140,.2)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)';this.style.borderColor='var(--border)'">
                    {{-- top accent bar --}}
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:{{ $crop['color'] }};border-radius:var(--radius-xl) var(--radius-xl) 0 0"></div>
                    {{-- tag --}}
                    <span style="position:absolute;top:1rem;right:1rem;font-size:.62rem;font-weight:700;
                                 letter-spacing:.06em;text-transform:uppercase;padding:.2rem .6rem;
                                 border-radius:99px;background:{{ $crop['bg'] }};color:{{ $crop['color'] }}">
                        {{ $crop['tag'] }}
                    </span>
                    {{-- icon --}}
                    <div style="font-size:2.25rem;margin-bottom:1rem;margin-top:.25rem">{{ $crop['emoji'] }}</div>
                    <div style="font-size:1.0625rem;font-weight:800;color:var(--slate-800);margin-bottom:.6rem">{{ $crop['name'] }}</div>
                    <div style="font-size:.84rem;color:var(--text-muted);line-height:1.65">{{ $crop['desc'] }}</div>
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
            <h2 class="section-title reveal">{{ config('site.about.values_title') }}</h2>
            <p class="reveal">The principles that shape how we work with farmers, clients, and communities every single day.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr));gap:1.25rem">
            @php
            $allValues = array_merge(config('site.about.values', []), [
                ['number'=>'05','title'=>'Customer Satisfaction','text'=>'We deliver consistent value to every partner and client, from the first order to every reorder.'],
            ]);
            $valueColors = ['var(--blue)','var(--copper)','var(--blue)','var(--copper)','var(--blue)'];
            $valueBgs    = ['var(--blue-light)','var(--copper-light)','var(--blue-light)','var(--copper-light)','var(--blue-light)'];
            @endphp
            @foreach($allValues as $i => $value)
                <div class="reveal" style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow-sm);
                            display:flex;flex-direction:column;gap:.75rem;
                            transition:transform .22s var(--ease),box-shadow .22s"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                    <div style="width:3rem;height:3rem;border-radius:var(--radius);
                                background:{{ $valueBgs[$i] ?? 'var(--blue-light)' }};
                                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <span style="font-size:.85rem;font-weight:900;color:{{ $valueColors[$i] ?? 'var(--blue)' }}">
                            {{ $value['number'] }}
                        </span>
                    </div>
                    <div style="font-size:.9375rem;font-weight:700;color:var(--slate-800)">{{ $value['title'] }}</div>
                    <div style="font-size:.84rem;color:var(--text-muted);line-height:1.65">{{ $value['text'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Services 3-column cards ── --}}
<section class="section">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--copper reveal">What We Do</span>
            <h2 class="section-title reveal">Our Services</h2>
            <p class="reveal">End-to-end agro-processing — from farmer partnerships to retail-ready packaging.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:1.5rem">
            @php
            $services = [
                ['title'=>'Sourcing & Aggregation','color'=>'blue','svg'=>'<path stroke-linecap="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253"/>','points'=>['Partnering with local farmers & cooperatives','Fair trade pricing and off-take agreements','Training farmers in good agricultural practices']],
                ['title'=>'Processing & Quality Control','color'=>'copper','svg'=>'<path stroke-linecap="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L4.2 15.3m0 0-1.233 3.086M19.8 15.3l1.233 3.086"/>','points'=>['Modern milling & drying technologies','Stringent food safety & lab testing','Customized solutions for domestic & export markets']],
                ['title'=>'Packaging & Distribution','color'=>'blue','svg'=>'<path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>','points'=>['1kg, 5kg and bulk carton packaging','Partnerships with supermarkets & agro-dealers','On-time delivery and logistics across Rwanda']],
            ];
            @endphp
            @foreach($services as $svc)
                @php $isCopper = $svc['color'] === 'copper'; @endphp
                <div class="reveal" style="border-radius:var(--radius-xl);overflow:hidden;
                            border:1px solid var(--border);box-shadow:var(--shadow-sm);background:var(--white);
                            transition:transform .25s var(--ease),box-shadow .25s"
                     onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='var(--shadow-lg)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
                    {{-- Card header band --}}
                    <div style="padding:1.75rem;background:{{ $isCopper ? 'linear-gradient(135deg,var(--copper),var(--copper-dark))' : 'linear-gradient(135deg,var(--blue),var(--blue-dark))' }}">
                        <div style="width:3rem;height:3rem;border-radius:var(--radius);background:rgba(255,255,255,.15);
                                    display:flex;align-items:center;justify-content:center;margin-bottom:1rem">
                            <svg width="22" height="22" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">{!! $svc['svg'] !!}</svg>
                        </div>
                        <div style="font-size:1.0625rem;font-weight:800;color:white">{{ $svc['title'] }}</div>
                    </div>
                    {{-- Points --}}
                    <div style="padding:1.5rem">
                        <ul style="display:flex;flex-direction:column;gap:.65rem">
                            @foreach($svc['points'] as $point)
                                <li style="display:flex;align-items:flex-start;gap:.55rem;font-size:.84rem;color:var(--text-muted);line-height:1.55">
                                    <svg width="15" height="15" fill="none" stroke="{{ $isCopper ? 'var(--copper)' : 'var(--blue)' }}" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem" aria-hidden="true"><path stroke-linecap="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Real Products from Admin ── --}}
<section class="section section-alt">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--blue reveal">Our Products</span>
            <h2 class="section-title reveal">What We Sell</h2>
            <p class="reveal">Browse a selection of our current product line — all managed through our production system and available for order.</p>
        </div>

        @if($products->isNotEmpty())
            <div class="products-grid" style="margin-bottom:2rem">
                @foreach($products as $product)
                    <x-site.product-card :product="$product" />
                @endforeach
            </div>
            <div style="text-align:center" class="reveal">
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                    View all products
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        @else
            <div style="text-align:center;padding:3rem;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-xl)">
                <p style="color:var(--text-muted);margin-bottom:1rem">Products will appear here once added in the admin panel.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-sm">Go to Shop</a>
            </div>
        @endif
    </div>
</section>

{{-- ── Milestones horizontal timeline ── --}}
<section class="section">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--copper reveal">Our Journey</span>
            <h2 class="section-title reveal">Milestones</h2>
        </div>
        @php $milestones = config('site.milestones', []); $total = count($milestones); @endphp
        <div class="reveal" style="overflow-x:auto;padding-bottom:.5rem">
            <div style="display:grid;grid-template-columns:repeat({{ $total }},1fr);min-width:36rem;position:relative">
                <div aria-hidden="true"
                     style="position:absolute;top:1rem;
                            left:calc(100% / {{ $total * 2 }});
                            right:calc(100% / {{ $total * 2 }});
                            height:2px;background:linear-gradient(to right,var(--blue) 75%,var(--copper) 100%)">
                </div>
                @foreach($milestones as $i => $milestone)
                    @php $isLast = $i === $total - 1; @endphp
                    <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 .5rem">
                        <div style="position:relative;z-index:1;width:2rem;height:2rem;border-radius:50%;
                                    border:2.5px solid {{ $isLast ? 'var(--copper)' : 'var(--blue)' }};
                                    background:var(--white);display:flex;align-items:center;justify-content:center;
                                    margin-bottom:1.25rem;
                                    box-shadow:0 0 0 4px {{ $isLast ? 'rgba(193,127,62,.12)' : 'rgba(16,73,140,.1)' }}">
                            <span style="width:.6rem;height:.6rem;border-radius:50%;background:{{ $isLast ? 'var(--copper)' : 'var(--blue)' }}"></span>
                        </div>
                        <div style="font-size:1.3rem;font-weight:900;letter-spacing:-.02em;margin-bottom:.35rem;color:{{ $isLast ? 'var(--copper)' : 'var(--blue)' }}">
                            {{ $milestone['year'] }}
                        </div>
                        <div style="font-size:.875rem;font-weight:700;color:var(--slate-800);margin-bottom:.4rem">{{ $milestone['title'] }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);line-height:1.6;max-width:11rem">{{ $milestone['text'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── Partners ── --}}
<section class="section section-alt">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--blue reveal">Our Network</span>
            <h2 class="section-title reveal">Partners &amp; Clients</h2>
            <p class="reveal">We proudly collaborate with a growing network of stakeholders across Rwanda's agricultural value chain.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr));gap:1rem">
            @php
            $partners = [
                ['label'=>'Farmer Cooperatives','desc'=>'Strengthening local production capacity with fair trade.','icon'=>'👩‍🌾','color'=>'var(--blue)','bg'=>'var(--blue-light)'],
                ['label'=>'Agro-Dealers','desc'=>'Expanding product availability across Rwanda.','icon'=>'🏪','color'=>'var(--copper-dark)','bg'=>'var(--copper-light)'],
                ['label'=>'Supermarkets & Retail','desc'=>'Supplying trusted consumer goods to shelves nationwide.','icon'=>'🛒','color'=>'var(--blue)','bg'=>'var(--blue-light)'],
                ['label'=>'Government Institutions','desc'=>'Supporting food security and national development.','icon'=>'🏛️','color'=>'var(--copper-dark)','bg'=>'var(--copper-light)'],
            ];
            @endphp
            @foreach($partners as $partner)
                <div class="reveal" style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-lg);padding:1.75rem;text-align:center;
                            box-shadow:var(--shadow-sm);
                            transition:transform .22s var(--ease),box-shadow .22s,border-color .22s"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow)';this.style.borderColor='rgba(16,73,140,.18)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-sm)';this.style.borderColor='var(--border)'">
                    <div style="width:3rem;height:3rem;border-radius:50%;background:{{ $partner['bg'] }};
                                display:flex;align-items:center;justify-content:center;
                                margin:0 auto .9rem;font-size:1.4rem">
                        {{ $partner['icon'] }}
                    </div>
                    <div style="font-size:.875rem;font-weight:700;color:var(--slate-800);margin-bottom:.4rem">{{ $partner['label'] }}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);line-height:1.55">{{ $partner['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Testimonials ── --}}
<section class="section">
    <div class="sc">
        <div class="section-lead">
            <span class="eyebrow eyebrow--copper reveal">Testimonials</span>
            <h2 class="section-title reveal">What Our Partners Say</h2>
        </div>
        <div class="testimonial-grid">
            @foreach(config('site.testimonials', []) as $t)
                <figure class="testimonial-card reveal">
                    <div class="testimonial-stars">
                        @for($i=0;$i<5;$i++)
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="testimonial-quote">"{{ $t['quote'] }}"</blockquote>
                    <figcaption class="testimonial-author">{{ $t['author'] }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cta-section">
    <div class="cta-bg" aria-hidden="true"></div>
    <div class="sc" style="position:relative;z-index:1;text-align:center">
        <span class="eyebrow eyebrow--white reveal" style="margin-bottom:1rem;display:inline-flex">Join Us</span>
        <h2 class="cta-title reveal">Are you a farmer, cooperative, or distributor?</h2>
        <p class="cta-desc reveal">Register as a supplier or reach out to discuss partnership opportunities. Together, we can build a stronger agricultural future for Rwanda.</p>
        <div class="cta-actions reveal">
            <a href="{{ route('contact') }}" class="btn btn-lg" style="background:white;color:var(--copper-dark);box-shadow:0 4px 14px rgba(0,0,0,.15)">
                Register as Supplier
            </a>
            <a href="{{ route('shop.index') }}" class="btn btn-outline btn-lg">View Our Products</a>
        </div>
    </div>
</section>

@endsection
