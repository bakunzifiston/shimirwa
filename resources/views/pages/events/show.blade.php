@extends('layouts.site')

@section('title', $event->title)
@section('meta_description', $event->description ? Str::limit($event->description, 160) : 'Shimirwa event — '.$event->title)

@section('content')

{{-- Breadcrumb --}}
<nav style="background:var(--bg);border-bottom:1px solid var(--border);padding:.65rem 0">
    <div class="sc">
        <ol style="display:flex;align-items:center;gap:.35rem;list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--text-muted)">
            <li><a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none">Home</a></li>
            <li aria-hidden="true"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg></li>
            <li><a href="{{ route('events.index') }}" style="color:var(--text-muted);text-decoration:none">Events</a></li>
            <li aria-hidden="true"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg></li>
            <li style="color:var(--slate-700);font-weight:600">{{ Str::limit($event->title, 50) }}</li>
        </ol>
    </div>
</nav>

<article>
    {{-- Header --}}
    <section class="section" style="padding-top:2.5rem;padding-bottom:1.5rem">
        <div class="sc" style="max-width:980px;margin:0 auto">

            {{-- Meta row --}}
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1.1rem">
                @if($event->event_date)
                    <span style="font-size:.75rem;font-weight:700;color:var(--copper);
                                 background:rgba(193,127,62,.1);border:1px solid rgba(193,127,62,.25);
                                 padding:.3rem .75rem;border-radius:99px;
                                 display:flex;align-items:center;gap:.4rem">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M6.75 3v1.5M17.25 3v1.5M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/>
                        </svg>
                        {{ $event->event_date->format('d F Y') }}
                    </span>
                @endif
                @if($event->location)
                    <span style="font-size:.75rem;color:var(--text-muted);
                                 display:flex;align-items:center;gap:.35rem">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        {{ $event->location }}
                    </span>
                @endif
                @php $mc = $event->media->count(); @endphp
                @if($mc > 0)
                    <span style="font-size:.75rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        {{ $mc }} {{ $mc === 1 ? 'item' : 'photos & videos' }}
                    </span>
                @endif
            </div>

            <h1 style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;color:var(--slate-900);
                       letter-spacing:-.025em;line-height:1.2;margin-bottom:1rem">
                {{ $event->title }}
            </h1>

            @if($event->description)
                <p style="font-size:1.0625rem;color:var(--text-muted);line-height:1.7;max-width:70ch;
                           padding-bottom:1.75rem;border-bottom:1px solid var(--border);margin-bottom:0">
                    {{ $event->description }}
                </p>
            @endif
        </div>
    </section>

    {{-- Cover image --}}
    @if($event->coverImageUrl())
        <div class="sc" style="max-width:980px;margin:0 auto;padding-bottom:1.5rem">
            <div style="border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow)">
                <img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}"
                     style="width:100%;max-height:500px;object-fit:cover;display:block">
            </div>
        </div>
    @endif

    {{-- ── Media Gallery ── --}}
    @php
        $photos = $event->media->where('type','image')->values();
        $videos = $event->media->where('type','video')->values();
    @endphp

    @if($event->media->isNotEmpty())
        <section class="section section-alt" style="padding-top:2.5rem;padding-bottom:3.5rem">
            <div class="sc" style="max-width:1180px;margin:0 auto">

                {{-- Videos --}}
                @if($videos->isNotEmpty())
                    <div style="margin-bottom:{{ $photos->isNotEmpty() ? '2.5rem' : '0' }}">
                        <h2 style="font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
                                   color:var(--text-muted);margin-bottom:1.1rem">Videos</h2>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,30rem),1fr));gap:1.25rem">
                            @foreach($videos as $vid)
                                <div style="border-radius:var(--radius-xl);overflow:hidden;
                                            box-shadow:var(--shadow);border:1px solid var(--border);background:var(--white)">
                                    <video controls preload="metadata"
                                           style="width:100%;display:block;max-height:380px;background:#0f172a">
                                        <source src="{{ $vid->url() }}"
                                                type="{{ str_ends_with($vid->path,'webm') ? 'video/webm' : 'video/mp4' }}">
                                    </video>
                                    @if($vid->caption)
                                        <p style="font-size:.82rem;color:var(--text-muted);padding:.6rem 1rem;margin:0">{{ $vid->caption }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Photos --}}
                @if($photos->isNotEmpty())
                    @if($videos->isNotEmpty())
                        <h2 style="font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
                                   color:var(--text-muted);margin-bottom:1.1rem">Photos</h2>
                    @endif
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,200px),1fr));gap:.7rem">
                        @foreach($photos as $idx => $photo)
                            <button onclick="openLb({{ $idx }})"
                                    style="border:none;padding:0;cursor:pointer;
                                           border-radius:var(--radius-lg);overflow:hidden;
                                           aspect-ratio:4/3;display:block;width:100%;
                                           box-shadow:var(--shadow-sm);transition:transform .2s,box-shadow .2s"
                                    onmouseenter="this.style.transform='scale(1.03)';this.style.boxShadow='var(--shadow)'"
                                    onmouseleave="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'"
                                    aria-label="Open photo {{ $idx + 1 }}">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? 'Event photo '.($idx+1) }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block"
                                     loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif
</article>

{{-- Lightbox --}}
@if($photos->isNotEmpty())
<div id="lb"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.93);
            align-items:center;justify-content:center;padding:1rem"
     onclick="if(event.target===this)closeLb()">

    <button onclick="closeLb()"
            style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);
                   border:none;color:white;width:2.5rem;height:2.5rem;border-radius:50%;
                   cursor:pointer;display:flex;align-items:center;justify-content:center">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>

    <button onclick="lbPrev()"
            style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);
                   background:rgba(255,255,255,.15);border:none;color:white;
                   width:2.75rem;height:2.75rem;border-radius:50%;cursor:pointer;
                   display:flex;align-items:center;justify-content:center">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="m15 18-6-6 6-6"/>
        </svg>
    </button>

    <button onclick="lbNext()"
            style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);
                   background:rgba(255,255,255,.15);border:none;color:white;
                   width:2.75rem;height:2.75rem;border-radius:50%;cursor:pointer;
                   display:flex;align-items:center;justify-content:center">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
        </svg>
    </button>

    <img id="lb-img" src="" alt=""
         style="max-width:min(92vw,1200px);max-height:88vh;object-fit:contain;
                border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,.6)">

    <div id="lb-counter"
         style="position:absolute;bottom:1.5rem;left:50%;transform:translateX(-50%);
                font-size:.82rem;color:rgba(255,255,255,.7);background:rgba(0,0,0,.5);
                padding:.35rem .85rem;border-radius:99px"></div>
</div>

<script>
const lbPhotos = @json($photos->map(fn($p) => $p->url())->values());
let lbIdx = 0;

function openLb(i) {
    lbIdx = i; updateLb();
    document.getElementById('lb').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLb() {
    document.getElementById('lb').style.display = 'none';
    document.body.style.overflow = '';
}
function lbNext() { lbIdx = (lbIdx + 1) % lbPhotos.length; updateLb(); }
function lbPrev() { lbIdx = (lbIdx - 1 + lbPhotos.length) % lbPhotos.length; updateLb(); }
function updateLb() {
    document.getElementById('lb-img').src = lbPhotos[lbIdx];
    document.getElementById('lb-counter').textContent = (lbIdx + 1) + ' / ' + lbPhotos.length;
}
document.addEventListener('keydown', e => {
    if (document.getElementById('lb').style.display === 'none') return;
    if (e.key === 'Escape')      closeLb();
    if (e.key === 'ArrowRight')  lbNext();
    if (e.key === 'ArrowLeft')   lbPrev();
});
</script>
@endif

{{-- Recent events --}}
@if($related->isNotEmpty())
    <section class="section" aria-labelledby="more-events">
        <div class="sc">
            <div class="section-lead" style="margin-bottom:1.75rem">
                <span class="eyebrow eyebrow--blue">More Events</span>
                <h2 id="more-events" class="section-title">Other events</h2>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,18rem),1fr));gap:1.5rem">
                @foreach($related as $ev)
                    <a href="{{ route('events.show', $ev) }}"
                       style="display:flex;flex-direction:column;background:var(--white);
                              border:1px solid var(--border);border-radius:var(--radius-xl);
                              overflow:hidden;text-decoration:none;color:inherit;
                              box-shadow:var(--shadow-sm);transition:box-shadow .2s,transform .2s"
                       onmouseenter="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)'"
                       onmouseleave="this.style.boxShadow='var(--shadow-sm)';this.style.transform=''">
                        <div style="aspect-ratio:16/9;overflow:hidden;background:linear-gradient(135deg,var(--blue-dark),var(--blue))">
                            @if($ev->coverImageUrl())
                                <img src="{{ $ev->coverImageUrl() }}" alt="{{ $ev->title }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block">
                            @endif
                        </div>
                        <div style="padding:1rem 1.2rem 1.25rem">
                            @if($ev->event_date)
                                <div style="font-size:.68rem;font-weight:700;color:var(--copper);margin-bottom:.35rem">
                                    {{ $ev->event_date->format('d M Y') }}
                                </div>
                            @endif
                            <h3 style="font-size:.9375rem;font-weight:800;color:var(--slate-900);line-height:1.4">
                                {{ $ev->title }}
                            </h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
