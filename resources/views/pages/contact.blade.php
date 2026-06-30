@extends('layouts.site')

@section('title', 'Contact Us')
@section('meta_description', 'Get in touch with Shimirwa Company Ltd — orders, partnerships, bulk supply, and inquiries.')

@section('content')

{{-- ── Page Hero ── --}}
<div class="page-hero" style="padding:3.5rem 0 2.5rem">
    <div class="page-hero-bg" aria-hidden="true"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none"></div>
    <div class="sc page-hero-content" style="position:relative;z-index:1">
        <span class="eyebrow eyebrow--white" style="margin-bottom:.85rem;display:inline-flex">Get in Touch</span>
        <h1 style="animation:heroIn .7s var(--ease) both">Contact Us</h1>
        <p style="animation:heroIn .7s .12s var(--ease) both">
            Questions about our flour products, bulk orders, or partnerships? We're here to help.
        </p>
    </div>
</div>

<section class="section" style="padding-top:2.5rem">
    <div class="sc">

        {{-- Success alert --}}
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:2rem">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Two-column layout ── --}}
        <div style="display:grid;grid-template-columns:1fr;gap:2.5rem;align-items:start" class="contact-grid">

            {{-- ── Contact form ── --}}
            <div style="background:var(--white);border:1px solid var(--border);
                        border-radius:var(--radius-xl);box-shadow:var(--shadow);overflow:hidden">

                {{-- Panel header --}}
                <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);
                            display:flex;align-items:center;gap:.75rem">
                    <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-sm);flex-shrink:0;
                                background:var(--blue-light);display:flex;align-items:center;justify-content:center">
                        <svg width="16" height="16" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:.95rem;font-weight:800;color:var(--slate-900)">Send us a message</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">We reply within 24 hours on business days</div>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('contact.store') }}"
                      style="padding:1.5rem;display:flex;flex-direction:column;gap:1.2rem"
                      novalidate>
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.45rem;letter-spacing:.01em">
                            Full name <span style="color:#dc2626">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Your full name"
                               style="width:100%;padding:.7rem 1rem;
                                      border:1.5px solid {{ $errors->has('name') ? '#fca5a5' : 'var(--border)' }};
                                      border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                      color:var(--text);background:{{ $errors->has('name') ? '#fef2f2' : 'var(--white)' }};
                                      outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box"
                               onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('name') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        @error('name')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;font-weight:500">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);margin-bottom:.45rem;letter-spacing:.01em">
                            Email <span style="color:#dc2626">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               placeholder="you@example.com"
                               style="width:100%;padding:.7rem 1rem;
                                      border:1.5px solid {{ $errors->has('email') ? '#fca5a5' : 'var(--border)' }};
                                      border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                      color:var(--text);background:{{ $errors->has('email') ? '#fef2f2' : 'var(--white)' }};
                                      outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box"
                               onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('email') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        @error('email')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;font-weight:500">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);margin-bottom:.45rem;letter-spacing:.01em">
                            Phone
                            <span style="margin-left:.35rem;font-size:.72rem;font-weight:500;color:var(--text-muted)">(optional)</span>
                        </label>
                        <div style="position:relative">
                            <span style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);
                                         font-size:.82rem;color:var(--text-muted);font-weight:600;pointer-events:none">+250</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                   placeholder="788 000 000"
                                   style="width:100%;padding:.7rem 1rem .7rem 3.25rem;
                                          border:1.5px solid var(--border);
                                          border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                          color:var(--text);background:var(--white);
                                          outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box"
                                   onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                                   onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                        </div>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);margin-bottom:.45rem;letter-spacing:.01em">
                            Subject <span style="color:#dc2626">*</span>
                        </label>
                        <input type="text" name="subject" value="{{ old('subject', request('subject')) }}" required
                               placeholder="e.g. Bulk order inquiry, Product question…"
                               style="width:100%;padding:.7rem 1rem;
                                      border:1.5px solid {{ $errors->has('subject') ? '#fca5a5' : 'var(--border)' }};
                                      border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                      color:var(--text);background:{{ $errors->has('subject') ? '#fef2f2' : 'var(--white)' }};
                                      outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box"
                               onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('subject') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        @error('subject')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;font-weight:500">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);margin-bottom:.45rem;letter-spacing:.01em">
                            Message <span style="color:#dc2626">*</span>
                        </label>
                        <textarea name="message" rows="5" required
                                  placeholder="Tell us about your order, requirements, or questions…"
                                  style="width:100%;padding:.7rem 1rem;
                                         border:1.5px solid {{ $errors->has('message') ? '#fca5a5' : 'var(--border)' }};
                                         border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                         color:var(--text);background:{{ $errors->has('message') ? '#fef2f2' : 'var(--white)' }};
                                         outline:none;transition:border-color .15s,box-shadow .15s;
                                         resize:vertical;min-height:7rem;box-sizing:border-box"
                                  onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                                  onblur="this.style.borderColor='{{ $errors->has('message') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">{{ old('message') }}</textarea>
                        @error('message')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;font-weight:500">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center;margin-top:.25rem">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                        </svg>
                        Send Message
                    </button>
                </form>
            </div>

            {{-- ── Info sidebar ── --}}
            <div style="display:flex;flex-direction:column;gap:1.25rem">

                {{-- Contact details card --}}
                <div style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);overflow:hidden">
                    <div style="padding:1rem 1.25rem;background:linear-gradient(135deg,var(--blue),var(--blue-dark));color:white">
                        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.2rem">Contact</div>
                        <div style="font-size:1rem;font-weight:800">Our details</div>
                    </div>
                    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:1rem">
                        @foreach([
                            ['icon'=>'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75','label'=>'Email','value'=>config('site.contact.email'),'href'=>'mailto:'.config('site.contact.email'),'color'=>'blue'],
                            ['icon'=>'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z','label'=>'Phone','value'=>config('site.contact.phone'),'href'=>'tel:'.preg_replace('/\s+/','',config('site.contact.phone')),'color'=>'copper'],
                            ['icon'=>'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z','label'=>'Address','value'=>config('site.contact.address'),'href'=>null,'color'=>'blue'],
                        ] as $info)
                            <div style="display:flex;align-items:flex-start;gap:.85rem">
                                <div style="width:2.25rem;height:2.25rem;flex-shrink:0;border-radius:var(--radius-sm);
                                            background:{{ $info['color']==='copper' ? 'var(--copper-light)' : 'var(--blue-light)' }};
                                            display:flex;align-items:center;justify-content:center">
                                    <svg width="15" height="15" fill="none"
                                         stroke="{{ $info['color']==='copper' ? 'var(--copper-dark)' : 'var(--blue)' }}"
                                         stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" d="{{ $info['icon'] }}"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                                                color:var(--text-muted);margin-bottom:.2rem">{{ $info['label'] }}</div>
                                    @if($info['href'])
                                        <a href="{{ $info['href'] }}"
                                           style="font-size:.875rem;font-weight:600;color:var(--slate-800);
                                                  text-decoration:none;transition:color .15s"
                                           onmouseover="this.style.color='var(--blue)'"
                                           onmouseout="this.style.color='var(--slate-800)'">{{ $info['value'] }}</a>
                                    @else
                                        <div style="font-size:.875rem;color:var(--slate-700);line-height:1.5">{{ $info['value'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Business hours card --}}
                <div style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);padding:1.25rem">
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem">
                        <div style="width:2rem;height:2rem;border-radius:var(--radius-sm);
                                    background:var(--copper-light);display:flex;align-items:center;justify-content:center">
                            <svg width="13" height="13" fill="none" stroke="var(--copper-dark)" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div style="font-size:.875rem;font-weight:800;color:var(--slate-900)">Business Hours</div>
                    </div>
                    @foreach([['Mon – Fri','7:00 AM – 6:00 PM'],['Saturday','8:00 AM – 2:00 PM'],['Sunday','Closed']] as [$day,$time])
                        <div style="display:flex;justify-content:space-between;align-items:center;
                                    padding:.45rem 0;border-bottom:1px solid var(--border);font-size:.82rem;
                                    {{ $loop->last ? 'border:none' : '' }}">
                            <span style="color:var(--slate-600);font-weight:500">{{ $day }}</span>
                            <span style="font-weight:700;color:{{ $time==='Closed' ? 'var(--slate-400)' : 'var(--blue)' }}">{{ $time }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Quick channels --}}
                <div style="background:linear-gradient(135deg,var(--copper),var(--copper-dark));
                            border-radius:var(--radius-xl);padding:1.25rem;color:white">
                    <div style="font-size:.875rem;font-weight:800;margin-bottom:.35rem">Need a bulk order?</div>
                    <div style="font-size:.78rem;opacity:.9;margin-bottom:1rem;line-height:1.5">
                        Call or WhatsApp us directly for wholesale pricing and large supply arrangements.
                    </div>
                    <a href="tel:{{ preg_replace('/\s+/', '', config('site.contact.phone')) }}"
                       style="display:inline-flex;align-items:center;gap:.45rem;font-size:.82rem;font-weight:700;
                              padding:.5rem 1rem;border-radius:var(--radius-sm);
                              background:rgba(255,255,255,.2);color:white;text-decoration:none;
                              border:1px solid rgba(255,255,255,.3);transition:background .15s"
                       onmouseover="this.style.background='rgba(255,255,255,.3)'"
                       onmouseout="this.style.background='rgba(255,255,255,.2)'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        {{ config('site.contact.phone') }}
                    </a>
                </div>

                {{-- Map --}}
                <div style="border-radius:var(--radius-xl);overflow:hidden;border:1px solid var(--border);
                            box-shadow:var(--shadow-sm);aspect-ratio:16/9">
                    <iframe src="{{ config('site.contact.map_embed') }}"
                            width="100%" height="100%"
                            style="border:none;display:block"
                            allowfullscreen loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Shimirwa Company Ltd location — {{ config('site.contact.address') }}">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cta-section">
    <div class="cta-bg" aria-hidden="true"></div>
    <div class="sc" style="position:relative;z-index:1;text-align:center">
        <h2 class="cta-title reveal">Ready to place a bulk order?</h2>
        <p class="cta-desc reveal">Browse our flour products and add them to your cart, or contact us directly for custom pricing.</p>
        <div class="cta-actions reveal">
            <a href="{{ route('shop.index') }}" class="btn btn-lg" style="background:white;color:var(--blue-dark);box-shadow:0 4px 14px rgba(0,0,0,.15)">
                Browse Products
            </a>
            <a href="{{ route('about') }}" class="btn btn-outline btn-lg">About Us</a>
        </div>
    </div>
</section>

@endsection
