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
    <div class="site-container" style="display:flex;align-items:center;height:100%;gap:1.5rem">
        {{-- Brand / Logo --}}
        <a href="{{ route('home', absolute: false) }}" class="site-brand shrink-0">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ config('site.name') }}" style="height:2.25rem;width:auto">
            @else
                <span class="font-bold text-lg" style="color:var(--site-primary)">{{ config('site.name') }}</span>
            @endif
        </a>

        {{-- Desktop Nav --}}
        <nav class="site-nav hidden md:flex flex-1 justify-center gap-1" aria-label="Main navigation">
            @foreach (config('site.navigation') as $item)
                <a href="{{ route($item['route'], absolute: false) }}"
                   class="site-nav-link px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                          {{ $isNavActive($item['route'])
                              ? 'bg-[#10498c] text-white'
                              : 'text-slate-700 hover:bg-slate-100 hover:text-[#10498c]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Header Actions --}}
        <div class="flex items-center gap-3 ml-auto">
            {{-- Cart --}}
            <a href="{{ route('cart.index', absolute: false) }}"
               class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-slate-100 transition-colors"
               aria-label="Cart ({{ $cartCount }} items)">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                </svg>
                @if ($cartCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 flex items-center justify-center
                                 rounded-full bg-[#10498c] text-white text-[0.65rem] font-bold leading-none
                                 animate-[cart-pop_0.3s_ease]">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

            {{-- Login (desktop) --}}
            <a href="{{ $adminLoginUrl }}"
               class="hidden md:inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                      bg-[#10498c] text-white hover:bg-[#082f57] transition-colors duration-200">
                Login
            </a>

            {{-- Mobile menu toggle --}}
            <button type="button"
                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-full hover:bg-slate-100 transition-colors"
                    data-site-menu-toggle
                    aria-expanded="false"
                    aria-label="Open menu">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <nav class="site-mobile-nav md:hidden border-t border-slate-100 bg-white px-4 py-3 flex flex-col gap-1"
         data-site-mobile-nav aria-label="Mobile navigation" hidden>
        @foreach (config('site.navigation') as $item)
            <a href="{{ route($item['route'], absolute: false) }}"
               class="px-4 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200
                      {{ $isNavActive($item['route'])
                          ? 'bg-[#10498c] text-white'
                          : 'text-slate-700 hover:bg-slate-100' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
        <a href="{{ $adminLoginUrl }}"
           class="mt-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-center
                  bg-[#10498c] text-white hover:bg-[#082f57] transition-colors">
            Login
        </a>
    </nav>
</header>
