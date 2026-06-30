@extends('layouts.site')

@section('title', 'Training & Resources')
@section('meta_description', 'Shimirwa training programs — learn about grain farming, nutrition, flour usage, baking, and growing your food business.')

@section('content')

{{-- Page hero --}}
<div class="page-hero" style="min-height:22rem;display:flex;align-items:center">
    <div class="page-hero-bg" aria-hidden="true"></div>
    <div class="sc page-hero-content" style="position:relative;z-index:1">
        <span class="eyebrow eyebrow--white" style="margin-bottom:1rem;display:inline-flex;animation:heroIn .7s var(--ease) both">
            Training &amp; Resources
        </span>
        <h1 style="animation:heroIn .8s .1s var(--ease) both">Learn. Grow. Flourish.</h1>
        <p style="animation:heroIn .8s .2s var(--ease) both">
            Shimirwa shares practical knowledge on grain farming, flour nutrition, baking techniques, and retail business skills — helping farmers and partners grow with us.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-top:1.5rem;animation:heroIn .8s .35s var(--ease) both">
            @foreach(['Farming & Sourcing','Nutrition & Health','Baking & Cooking','Business & Retail'] as $tag)
                <span style="font-size:.72rem;font-weight:700;padding:.3rem .9rem;border-radius:99px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2);letter-spacing:.04em">{{ $tag }}</span>
            @endforeach
        </div>
    </div>
</div>

<section class="section" style="padding-top:2.5rem">
    <div class="sc">

        @if($trainings->isEmpty())
            <div style="text-align:center;padding:5rem 0;color:var(--text-muted)" class="reveal">
                <svg width="64" height="64" fill="none" viewBox="0 0 24 24" style="margin:0 auto 1rem;color:var(--slate-200)" aria-hidden="true">
                    <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                </svg>
                <p style="font-size:.95rem">Training modules coming soon — check back shortly.</p>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,20rem),1fr));gap:1.75rem;margin-bottom:2.5rem">
                @foreach($trainings as $module)
                    <a href="{{ route('training.show', $module) }}"
                       class="reveal"
                       style="display:flex;flex-direction:column;background:var(--white);
                              border:1px solid var(--border);border-radius:var(--radius-xl);
                              overflow:hidden;box-shadow:var(--shadow-sm);
                              text-decoration:none;color:inherit;
                              transition:box-shadow .2s,transform .2s">
                        {{-- Cover --}}
                        <div style="aspect-ratio:16/9;overflow:hidden;background:linear-gradient(135deg,var(--blue-dark),var(--blue));position:relative">
                            @if($module->coverImageUrl())
                                <img src="{{ $module->coverImageUrl() }}" alt="{{ $module->title }}"
                                     style="width:100%;height:100%;object-fit:cover;display:block">
                            @else
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                                    <svg width="48" height="48" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Category badge --}}
                            <span style="position:absolute;top:.75rem;left:.75rem;
                                         font-size:.65rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
                                         background:rgba(193,127,62,.9);color:white;
                                         padding:.25rem .65rem;border-radius:99px">
                                {{ $module->categoryLabel() }}
                            </span>
                        </div>

                        {{-- Body --}}
                        <div style="flex:1;display:flex;flex-direction:column;padding:1.25rem 1.35rem 1.4rem">
                            <h2 style="font-size:1rem;font-weight:800;color:var(--slate-900);
                                       line-height:1.4;margin-bottom:.5rem;letter-spacing:-.01em">
                                {{ $module->title }}
                            </h2>
                            @if($module->excerpt)
                                <p style="font-size:.84rem;color:var(--text-muted);line-height:1.6;flex:1;margin-bottom:.85rem">
                                    {{ Str::limit($module->excerpt, 120) }}
                                </p>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto">
                                <span style="font-size:.75rem;color:var(--text-muted)">
                                    {{ $module->published_at?->format('d M Y') ?? '' }}
                                </span>
                                <span style="font-size:.78rem;font-weight:700;color:var(--blue);
                                             display:flex;align-items:center;gap:.25rem">
                                    Read more
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" d="m9 18 6-6-6-6"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($trainings->hasPages())
                <div style="display:flex;justify-content:center" class="reveal">
                    {{ $trainings->links() }}
                </div>
            @endif
        @endif

    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    document.querySelectorAll('a[style*="border-radius:var(--radius-xl)"]').forEach(card => {
        card.addEventListener('mouseenter', () => { card.style.boxShadow = 'var(--shadow-lg)'; card.style.transform = 'translateY(-3px)'; });
        card.addEventListener('mouseleave', () => { card.style.boxShadow = 'var(--shadow-sm)'; card.style.transform = ''; });
    });
})();
</script>
@endpush
