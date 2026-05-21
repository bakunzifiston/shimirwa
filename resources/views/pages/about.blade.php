@extends('layouts.site')

@section('title', 'About Us')
@section('meta_description', config('site.about.who_we_are'))

@section('content')
    <section class="site-page-hero site-page-hero--about">
        <div class="site-container site-reveal">
            <span class="site-eyebrow">{{ config('site.about.hero_eyebrow') }}</span>
            <h1>{{ config('site.about.hero_title') }}</h1>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-about-intro site-reveal">
                <h2>{{ config('site.about.who_we_are_title') }}</h2>
                <p>{{ config('site.about.who_we_are') }}</p>
            </div>
        </div>
    </section>

    <section class="site-section site-section-alt">
        <div class="site-container">
            <header class="site-section-header site-reveal">
                <h2 id="values-heading">{{ config('site.about.values_title') }}</h2>
            </header>
            <div class="site-about-values">
                @foreach (config('site.about.values') as $value)
                    <article class="site-about-value site-reveal">
                        <span class="site-about-value-num" aria-hidden="true">{{ $value['number'] }}</span>
                        <div>
                            <h3>{{ $value['title'] }}</h3>
                            <p>{{ $value['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container site-reveal" style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
            <x-site.button :href="route('shop.index')" size="site-btn-lg">Explore our products</x-site.button>
            <x-site.button :href="route('contact')" variant="secondary" size="site-btn-lg">Contact us</x-site.button>
        </div>
    </section>
@endsection
