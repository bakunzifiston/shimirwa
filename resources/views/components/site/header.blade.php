@php
    $logo = config('site.logo');
    $logoUrl = $logo && str_starts_with($logo, 'http') ? $logo : ($logo ? asset($logo) : null);
    $adminLoginUrl = route('admin.login', absolute: false);

    $isNavActive = function (string $route): bool {
        if (request()->routeIs($route)) {
            return true;
        }
        if (str_ends_with($route, '.index')) {
            return request()->routeIs(str_replace('.index', '.*', $route));
        }

        return false;
    };
@endphp

<header class="site-header" role="banner">
    <div class="site-container site-header-inner">
        <a href="{{ route('home', absolute: false) }}" class="site-brand">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ config('site.name') }}">
            @endif
            <span>{{ config('site.name') }}</span>
        </a>

        <nav class="site-nav" aria-label="Main navigation">
            @foreach (config('site.navigation') as $item)
                <a href="{{ route($item['route'], absolute: false) }}"
                   class="site-nav-link {{ $isNavActive($item['route']) ? 'is-active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="site-header-actions">
            <a href="{{ $adminLoginUrl }}" class="site-btn site-btn-primary site-btn-sm site-header-login">
                <span class="site-btn-label-short">Login</span>
                <span class="site-btn-label-long">Login to Admin Panel</span>
            </a>
            <button type="button" class="site-menu-toggle" data-site-menu-toggle aria-expanded="false" aria-label="Open menu">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    <nav class="site-mobile-nav" data-site-mobile-nav aria-label="Mobile navigation" hidden>
        @foreach (config('site.navigation') as $item)
            <a href="{{ route($item['route'], absolute: false) }}"
               class="{{ $isNavActive($item['route']) ? 'is-active' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
        <a href="{{ $adminLoginUrl }}" class="site-btn site-btn-primary site-mobile-login">
            Login to Admin Panel
        </a>
    </nav>
</header>
