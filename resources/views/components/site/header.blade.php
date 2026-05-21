@php
    $logo = config('site.logo');
    $logoUrl = $logo && str_starts_with($logo, 'http') ? $logo : ($logo ? asset($logo) : null);
    $adminLoginUrl = route('admin.login', absolute: false);
    $cartCount = app(\App\Services\CartService::class)->count();

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
        <a href="{{ route('home', absolute: false) }}" class="site-brand site-brand--logo-only">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ config('site.name') }}">
            @else
                <span>{{ config('site.name') }}</span>
            @endif
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
            <a href="{{ route('cart.index', absolute: false) }}" class="site-cart-link" aria-label="Cart ({{ $cartCount }} items)">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                @if ($cartCount > 0)<span class="site-cart-badge">{{ $cartCount }}</span>@endif
            </a>
            <a href="{{ $adminLoginUrl }}" class="site-btn site-btn-primary site-btn-sm site-header-login">Login</a>
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
        <a href="{{ $adminLoginUrl }}" class="site-btn site-btn-primary site-mobile-login">Login</a>
    </nav>
</header>
