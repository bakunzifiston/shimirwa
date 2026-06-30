@extends('layouts.site')

@section('title', 'Events')
@section('meta_description', 'See what Shimirwa has been up to — farmer training days, milling demonstrations, community events and more.')

@section('content')

{{-- Page hero --}}
<div class="page-hero" style="min-height:22rem;display:flex;align-items:center">
    <div class="page-hero-bg" aria-hidden="true"></div>
    <div class="sc page-hero-content" style="position:relative;z-index:1">
        <span class="eyebrow eyebrow--white" style="margin-bottom:1rem;display:inline-flex;animation:heroIn .7s var(--ease) both">
            Our Events
        </span>
        <h1 style="animation:heroIn .8s .1s var(--ease) both">On the Ground with Shimirwa</h1>
        <p style="animation:heroIn .8s .2s var(--ease) both">
            Farmer training days, milling demonstrations, community outreach, and company milestones — documented in photos and videos.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-top:1.5rem;animation:heroIn .8s .35s var(--ease) both">
            @foreach(['Farmer Training','Milling Demos','Community Outreach','Company Milestones'] as $tag)
                <span style="font-size:.72rem;font-weight:700;padding:.3rem .9rem;border-radius:99px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2);letter-spacing:.04em">{{ $tag }}</span>
            @endforeach
        </div>
    </div>
</div>

<section class="section" style="padding-top:2.5rem">
    <div class="sc">
        @if($events->isEmpty())
            <div style="text-align:center;padding:5rem 0;color:var(--text-muted)">
                <svg width="64" height="64" fill="none" viewBox="0 0 24 24" style="margin:0 auto 1rem;color:var(--slate-200)" aria-hidden="true">
                    <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M6.75 3v1.5M17.25 3v1.5M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                <p style="font-size:.95rem">No events published yet — check back soon.</p>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,21rem),1fr));gap:1.75rem;margin-bottom:2.5rem">
                @foreach($events as $event)
                    <a href="{{ route('events.show', $event) }}"
                       class="reveal"
                       style="display:flex;flex-direction:column;background:var(--white);
                              border:1px solid var(--border);border-radius:var(--radius-xl);
                              overflow:hidden;box-shadow:var(--shadow-sm);
                              text-decoration:none;color:inherit;
                              transition:box-shadow .2s,transform .2s"
                       onmouseenter="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-3px)'"
                       onmouseleave="this.style.boxShadow='var(--shadow-sm)';this.style.transform=''">

                        {{-- Cover --}}
                        <div style="aspect-ratio:16/9;overflow:hidden;background:linear-gradient(135deg,var(--blue-dark),var(--blue));position:relative">
                            @if($event->coverImageUrl())
                                <img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block">
                            @else
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                                    <svg width="44" height="44" fill="none" stroke="rgba(255,255,255,.3)" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" d="M6.75 3v1.5M17.25 3v1.5M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Photo count badge --}}
                            @if($event->media_count > 0)
                                <span style="position:absolute;bottom:.65rem;right:.65rem;
                                             font-size:.68rem;font-weight:800;
                                             background:rgba(0,0,0,.6);color:white;
                                             padding:.25rem .6rem;border-radius:99px;
                                             display:flex;align-items:center;gap:.3rem">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                    {{ $event->media_count }}
                                </span>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div style="flex:1;display:flex;flex-direction:column;padding:1.2rem 1.35rem 1.4rem">
                            {{-- Date & location --}}
                            <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-bottom:.6rem">
                                @if($event->event_date)
                                    <span style="font-size:.72rem;font-weight:700;color:var(--copper);
                                                 display:flex;align-items:center;gap:.3rem">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M6.75 3v1.5M17.25 3v1.5M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/>
                                        </svg>
                                        {{ $event->event_date->format('d M Y') }}
                                    </span>
                                @endif
                                @if($event->location)
                                    <span style="font-size:.72rem;color:var(--text-muted);
                                                 display:flex;align-items:center;gap:.3rem">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            <path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                        </svg>
                                        {{ $event->location }}
                                    </span>
                                @endif
                            </div>

                            <h2 style="font-size:1rem;font-weight:800;color:var(--slate-900);
                                       line-height:1.4;margin-bottom:.5rem;letter-spacing:-.01em">
                                {{ $event->title }}
                            </h2>

                            @if($event->description)
                                <p style="font-size:.84rem;color:var(--text-muted);line-height:1.6;flex:1;margin-bottom:.85rem">
                                    {{ Str::limit($event->description, 110) }}
                                </p>
                            @endif

                            <span style="font-size:.78rem;font-weight:700;color:var(--blue);
                                         margin-top:auto;display:flex;align-items:center;gap:.25rem">
                                View gallery
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($events->hasPages())
                <div style="display:flex;justify-content:center" class="reveal">
                    {{ $events->links() }}
                </div>
            @endif
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>
@endpush
