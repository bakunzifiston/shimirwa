<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('site.name')) — {{ config('site.tagline') }}</title>
    <meta name="description" content="@yield('meta_description', config('site.tagline'))">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|instrument-sans:400,500,600,700" rel="stylesheet">
    @if (config('site.logo'))
        <link rel="icon" href="{{ asset(config('site.logo')) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/css/public.css', 'resources/js/public.js'])
</head>
<body class="site-body">
    <div class="site-main">
        <x-site.header />

        <main id="main-content">
            @yield('content')
        </main>

        <x-site.footer />
    </div>

    @stack('scripts')

    <script>
    (function () {
        // Global reveal observer
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible', 'is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal, .site-reveal').forEach(el => io.observe(el));

        // Header scroll state
        const header = document.querySelector('[data-site-header]');
        if (header) {
            const onScroll = () => {
                header.classList.toggle('is-scrolled', window.scrollY > 8);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        // Mobile nav toggle
        const toggle = document.querySelector('[data-site-menu-toggle]');
        const mobileNav = document.querySelector('[data-site-mobile-nav]');
        if (toggle && mobileNav) {
            toggle.addEventListener('click', () => {
                const open = mobileNav.hasAttribute('hidden');
                mobileNav.toggleAttribute('hidden', !open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }
    })();
    </script>
</body>
</html>
