@extends('layouts.site')

@section('title', $training->title)
@section('meta_description', $training->excerpt ?? Str::limit(strip_tags($training->body ?? ''), 160))

@section('content')

{{-- Breadcrumb --}}
<nav style="background:var(--bg);border-bottom:1px solid var(--border);padding:.65rem 0">
    <div class="sc">
        <ol style="display:flex;align-items:center;gap:.35rem;list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--text-muted)">
            <li><a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none">Home</a></li>
            <li aria-hidden="true"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg></li>
            <li><a href="{{ route('training.index') }}" style="color:var(--text-muted);text-decoration:none">Training</a></li>
            <li aria-hidden="true"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg></li>
            <li style="color:var(--slate-700);font-weight:600">{{ Str::limit($training->title, 50) }}</li>
        </ol>
    </div>
</nav>

<article>
    {{-- Header --}}
    <section class="section" style="padding-top:2.5rem;padding-bottom:0">
        <div class="sc" style="max-width:900px;margin:0 auto">
            <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-bottom:1.25rem">
                <span style="font-size:.7rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase;
                             background:rgba(193,127,62,.12);color:var(--copper-dark);
                             border:1px solid rgba(193,127,62,.3);
                             padding:.3rem .75rem;border-radius:99px">
                    {{ $training->categoryLabel() }}
                </span>
                @if($training->published_at)
                    <span style="font-size:.8rem;color:var(--text-muted)">
                        {{ $training->published_at->format('d F Y') }}
                    </span>
                @endif
                @php $mediaCount = $training->media->count(); @endphp
                @if($mediaCount > 0)
                    <span style="font-size:.8rem;color:var(--text-muted);display:flex;align-items:center;gap:.3rem">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        {{ $mediaCount }} {{ $mediaCount === 1 ? 'photo/video' : 'photos & videos' }}
                    </span>
                @endif
            </div>

            <h1 style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;color:var(--slate-900);
                       letter-spacing:-.025em;line-height:1.2;margin-bottom:.9rem">
                {{ $training->title }}
            </h1>

            @if($training->excerpt)
                <p style="font-size:1.0625rem;color:var(--text-muted);line-height:1.7;margin-bottom:1.75rem;
                           padding-bottom:1.75rem;border-bottom:1px solid var(--border)">
                    {{ $training->excerpt }}
                </p>
            @endif

            {{-- Cover image --}}
            @if($training->coverImageUrl())
                <div style="border-radius:var(--radius-xl);overflow:hidden;margin-bottom:2rem;box-shadow:var(--shadow)">
                    <img src="{{ $training->coverImageUrl() }}" alt="{{ $training->title }}"
                         style="width:100%;max-height:480px;object-fit:cover;display:block">
                </div>
            @endif
        </div>
    </section>

    {{-- Body text --}}
    @if($training->body)
        <section class="section" style="padding-top:0;padding-bottom:2rem">
            <div class="sc" style="max-width:900px;margin:0 auto">
                <div style="font-size:1rem;line-height:1.85;color:var(--slate-700);max-width:72ch">
                    {!! nl2br(e($training->body)) !!}
                </div>
            </div>
        </section>
    @endif

    {{-- ── Media Gallery ── --}}
    @php
        $allMedia = $training->media;
        $photos   = $allMedia->where('type', 'image')->values();
        $videos   = $allMedia->where('type', 'video')->values();
    @endphp

    @if($allMedia->isNotEmpty())
        <section class="section section-alt" style="padding-top:2.5rem;padding-bottom:3rem">
            <div class="sc" style="max-width:1100px;margin:0 auto">

                <div style="margin-bottom:1.75rem">
                    <span class="eyebrow eyebrow--copper">Event Gallery</span>
                    <h2 style="font-size:1.35rem;font-weight:900;color:var(--slate-900);margin-top:.35rem;letter-spacing:-.02em">
                        Photos &amp; Videos
                    </h2>
                </div>

                {{-- Videos --}}
                @if($videos->isNotEmpty())
                    <div style="margin-bottom:2rem">
                        <h3 style="font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
                                   color:var(--text-muted);margin-bottom:1rem">Videos</h3>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,28rem),1fr));gap:1.25rem">
                            @foreach($videos as $vid)
                                <div style="border-radius:var(--radius-xl);overflow:hidden;
                                            box-shadow:var(--shadow);background:var(--white);
                                            border:1px solid var(--border)">
                                    <video controls preload="metadata"
                                           style="width:100%;display:block;max-height:360px;background:#0f172a">
                                        <source src="{{ $vid->url() }}"
                                                type="{{ str_ends_with($vid->path,'webm') ? 'video/webm' : 'video/mp4' }}">
                                        Your browser does not support video playback.
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
                        <h3 style="font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
                                   color:var(--text-muted);margin-bottom:1rem">Photos</h3>
                    @endif
                    <div id="photo-grid"
                         style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,220px),1fr));gap:.75rem">
                        @foreach($photos as $idx => $photo)
                            <button onclick="openLightbox({{ $idx }})"
                                    style="border:none;padding:0;cursor:pointer;
                                           border-radius:var(--radius-lg);overflow:hidden;
                                           aspect-ratio:4/3;display:block;width:100%;
                                           box-shadow:var(--shadow-sm);transition:transform .2s,box-shadow .2s"
                                    onmouseenter="this.style.transform='scale(1.03)';this.style.boxShadow='var(--shadow)'"
                                    onmouseleave="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'"
                                    aria-label="View photo {{ $idx + 1 }}">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? 'Event photo '.($idx+1) }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif
</article>

{{-- Lightbox --}}
@if(($photos ?? collect())->isNotEmpty())
<div id="lightbox"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);
            align-items:center;justify-content:center;padding:1rem"
     onclick="if(event.target===this)closeLightbox()">
    <button onclick="closeLightbox()"
            style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);
                   border:none;color:white;width:2.5rem;height:2.5rem;border-radius:50%;
                   cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>
    <button onclick="prevPhoto()"
            style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);
                   background:rgba(255,255,255,.15);border:none;color:white;
                   width:2.75rem;height:2.75rem;border-radius:50%;cursor:pointer;
                   display:flex;align-items:center;justify-content:center">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="m15 18-6-6 6-6"/>
        </svg>
    </button>
    <button onclick="nextPhoto()"
            style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);
                   background:rgba(255,255,255,.15);border:none;color:white;
                   width:2.75rem;height:2.75rem;border-radius:50%;cursor:pointer;
                   display:flex;align-items:center;justify-content:center">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
        </svg>
    </button>
    <img id="lightbox-img" src="" alt=""
         style="max-width:min(90vw,1100px);max-height:88vh;object-fit:contain;border-radius:8px;
                box-shadow:0 8px 40px rgba(0,0,0,.6)">
    <div id="lightbox-caption"
         style="position:absolute;bottom:1.5rem;left:50%;transform:translateX(-50%);
                font-size:.82rem;color:rgba(255,255,255,.75);background:rgba(0,0,0,.5);
                padding:.4rem .9rem;border-radius:99px;white-space:nowrap"></div>
</div>

<script>
const photos = @json($photos->map(fn($p) => ['url' => $p->url(), 'caption' => $p->caption])->values());
let lbIdx = 0;

function openLightbox(i) {
    lbIdx = i;
    updateLightbox();
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

function nextPhoto() { lbIdx = (lbIdx + 1) % photos.length; updateLightbox(); }
function prevPhoto() { lbIdx = (lbIdx - 1 + photos.length) % photos.length; updateLightbox(); }

function updateLightbox() {
    document.getElementById('lightbox-img').src = photos[lbIdx].url;
    const cap = document.getElementById('lightbox-caption');
    if (photos[lbIdx].caption) { cap.textContent = photos[lbIdx].caption; cap.style.display = ''; }
    else { cap.style.display = 'none'; }
}

document.addEventListener('keydown', e => {
    if (document.getElementById('lightbox').style.display === 'none') return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft')  prevPhoto();
});
</script>
@endif

{{-- Related modules --}}
@if($related->isNotEmpty())
    <section class="section" aria-labelledby="related-heading">
        <div class="sc">
            <div class="section-lead" style="margin-bottom:1.75rem">
                <span class="eyebrow eyebrow--blue">More Training</span>
                <h2 id="related-heading" class="section-title">Related modules</h2>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,18rem),1fr));gap:1.5rem">
                @foreach($related as $module)
                    <a href="{{ route('training.show', $module) }}"
                       style="display:flex;flex-direction:column;background:var(--white);
                              border:1px solid var(--border);border-radius:var(--radius-xl);
                              overflow:hidden;text-decoration:none;color:inherit;
                              box-shadow:var(--shadow-sm);transition:box-shadow .2s,transform .2s"
                       onmouseenter="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)'"
                       onmouseleave="this.style.boxShadow='var(--shadow-sm)';this.style.transform=''">
                        <div style="aspect-ratio:16/9;overflow:hidden;background:linear-gradient(135deg,var(--blue-dark),var(--blue))">
                            @if($module->coverImageUrl())
                                <img src="{{ $module->coverImageUrl() }}" alt="{{ $module->title }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block">
                            @endif
                        </div>
                        <div style="padding:1rem 1.2rem 1.25rem">
                            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                                        color:var(--copper);margin-bottom:.4rem">{{ $module->categoryLabel() }}</div>
                            <h3 style="font-size:.9375rem;font-weight:800;color:var(--slate-900);line-height:1.4">
                                {{ $module->title }}
                            </h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- CTA strip --}}
<section style="background:var(--blue-dark);padding:3rem 0">
    <div class="sc" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.5rem">
        <div>
            <h2 style="font-size:1.25rem;font-weight:900;color:white;margin-bottom:.35rem">Ready to partner with Shimirwa?</h2>
            <p style="color:rgba(255,255,255,.75);font-size:.875rem">Explore our flour products or get in touch for bulk orders.</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('shop.index') }}" class="btn btn-copper">Shop Flour</a>
            <a href="{{ route('contact') }}" class="btn btn-outline" style="border-color:rgba(255,255,255,.4);color:white">Contact Us</a>
        </div>
    </div>
</section>

@endsection
