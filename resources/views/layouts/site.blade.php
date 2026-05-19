<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('site.name')) — {{ config('site.tagline') }}</title>
    <meta name="description" content="@yield('meta_description', config('site.tagline'))">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
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
</body>
</html>
