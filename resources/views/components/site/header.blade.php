@php
    $logo = config('site.logo');
    $logoUrl = $logo && str_starts_with($logo, 'http') ? $logo : ($logo ? asset($logo) : null);
    $adminLoginUrl = route('admin.login', absolute: false);
    $cartCount = app(\App\Services\CartService::class)->count();

    $isNavActive = function (string $route): bool {
        if (request()->routeIs($route)) return true;
        if (str_ends_with($route, '.index')) {
            return request()->routeIs(str_replace('.index', '.*', $route));
        }
        return false;
    };
@endphp

<header class="site-header" role="banner" data-site-header>
    <div class="sc header-inner">
        {{-- Brand --}}
        <a href="{{ route('home', absolute: false) }}" class="site-brand">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ config('site.name') }}">
            @endif
            <div class="header-logo-text">
                {{ config('site.name') }}
                <span>Est. Rwanda</span>
            </div>
        </a>

        {{-- Desktop Nav --}}
        <nav class="site-nav" aria-label="Main navigation">
            @foreach (config('site.navigation') as $item)
                <a href="{{ route($item['route'], absolute: false) }}"
                   class="nav-link {{ $isNavActive($item['route']) ? 'active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Header Actions --}}
        <div class="header-actions">
            {{-- Cart --}}
            <a href="{{ route('cart.index', absolute: false) }}"
               class="cart-btn"
               aria-label="Cart ({{ $cartCount }} items)">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                </svg>
                @if ($cartCount > 0)
                    <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>

            {{-- Login (desktop) --}}
            <a href="{{ $adminLoginUrl }}" class="login-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                Login
            </a>

            {{-- Mobile toggle --}}
            <button type="button" class="menu-toggle"
                    data-site-menu-toggle
                    aria-expanded="false"
                    aria-label="Open menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <nav class="mobile-nav" data-site-mobile-nav aria-label="Mobile navigation" hidden>
        @foreach (config('site.navigation') as $item)
            <a href="{{ route($item['route'], absolute: false) }}"
               class="{{ $isNavActive($item['route']) ? 'active' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
        <a href="{{ $adminLoginUrl }}" class="mobile-login">Login</a>
    </nav>
</header>
