@extends('layouts.site')

@section('title', 'About Us')
@section('meta_description', 'Learn about Shimirwa Ltd — our mission, values, team, and milestones in soybean processing.')

@section('content')
    <section class="site-page-hero">
        <div class="site-container site-reveal">
            <span class="site-eyebrow">About us</span>
            <h1>Processing excellence rooted in Rwanda</h1>
            <p>From careful sourcing to modern roasting, milling, and packaging — we deliver quality at every step.</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-grid-2" style="align-items:center">
                <div class="site-reveal">
                    <h2>Our story</h2>
                    <p>
                        {{ config('site.name') }} specializes in soybean processing — roasting, sorting, milling,
                        and packaging products for households, retailers, and wholesale partners.
                    </p>
                    <p>
                        Our integrated inventory management ensures traceability from raw materials through
                        finished goods, so every batch meets the standards our clients expect.
                    </p>
                </div>
                <div class="site-card site-reveal">
                    <h3 style="margin-top:0">Our mission</h3>
                    <p style="margin-bottom:0">
                        To provide nutritious, high-quality soybean products while supporting local farmers
                        and building lasting partnerships across the supply chain.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section-alt">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">What we stand for</span>
                <h2>Vision &amp; core values</h2>
                <p>Guiding principles that shape how we work with farmers, staff, and customers.</p>
            </header>
            <div class="site-grid-2">
                @foreach ($values as $value)
                    <article class="site-card site-reveal">
                        <h3 style="margin-top:0;color:var(--site-primary)">{{ $value['title'] }}</h3>
                        <p style="margin:0">{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">Our people</span>
                <h2>Team &amp; leadership</h2>
            </header>
            <div class="site-grid-3">
                @foreach ($team as $member)
                    <article class="site-card site-reveal">
                        <h3 style="margin-top:0">{{ $member['name'] }}</h3>
                        <p style="font-weight:600;color:var(--site-secondary);margin:0 0 0.75rem">{{ $member['role'] }}</p>
                        <p style="margin:0">{{ $member['bio'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section site-section-alt">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <span class="site-eyebrow">Milestones</span>
                <h2>Our journey</h2>
            </header>
            <div class="site-timeline" style="max-width:36rem;margin:0 auto">
                @foreach ($milestones as $milestone)
                    <div class="site-timeline-item site-reveal">
                        <div class="site-timeline-year">{{ $milestone['year'] }}</div>
                        <h3 style="margin:0.25rem 0">{{ $milestone['title'] }}</h3>
                        <p style="margin:0;color:var(--site-text-muted)">{{ $milestone['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
