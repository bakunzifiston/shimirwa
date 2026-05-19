@extends('layouts.site')

@section('title', 'Contact Us')
@section('meta_description', 'Get in touch with Shimirwa Ltd for orders, partnerships, and inquiries.')

@section('content')
    <section class="site-page-hero">
        <div class="site-container site-reveal">
            <span class="site-eyebrow">Contact</span>
            <h1>We would love to hear from you</h1>
            <p>Send us a message and our team will respond as soon as possible.</p>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-grid-2" style="align-items:start">
                <div class="site-reveal">
                    @if (session('success'))
                        <div class="site-alert site-alert-success" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="site-form" novalidate>
                        @csrf
                        <x-site.input label="Full name" name="name" :required="true" />
                        <x-site.input label="Email" name="email" type="email" :required="true" />
                        <x-site.input label="Phone" name="phone" type="tel" />
                        <x-site.input
                            label="Subject"
                            name="subject"
                            :required="true"
                            :value="old('subject', request('subject'))"
                        />
                        <x-site.input label="Message" name="message" type="textarea" :required="true" />
                        <x-site.button type="submit">Send message</x-site.button>
                    </form>
                </div>

                <aside class="site-reveal">
                    <div class="site-card" style="margin-bottom:1.5rem">
                        <h3 style="margin-top:0">Contact information</h3>
                        <ul style="list-style:none;padding:0;margin:0">
                            <li style="margin-bottom:0.75rem">
                                <strong>Email</strong><br>
                                <a href="mailto:{{ config('site.contact.email') }}">{{ config('site.contact.email') }}</a>
                            </li>
                            <li style="margin-bottom:0.75rem">
                                <strong>Phone</strong><br>
                                {{ config('site.contact.phone') }}
                            </li>
                            <li>
                                <strong>Address</strong><br>
                                {{ config('site.contact.address') }}
                            </li>
                        </ul>
                        <div class="site-social" style="margin-top:1.25rem">
                            @foreach (config('site.social') as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $social['label'] }}">
                                    {{ strtoupper(substr($social['label'], 0, 1)) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="site-map">
                        <iframe
                            src="{{ config('site.contact.map_embed') }}"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Map showing {{ config('site.contact.address') }}"
                        ></iframe>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
