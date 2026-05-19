<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('admin.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-shell">
        <div id="admin-sidebar-overlay" class="admin-sidebar-overlay" aria-hidden="true"></div>

        <aside id="admin-sidebar" class="admin-sidebar" aria-label="Main navigation">
            <div class="admin-sidebar-brand">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand-link">
                    <x-admin.logo class="admin-sidebar-logo admin-sidebar-logo--large" />
                </a>
            </div>

            <nav class="admin-nav">
                @foreach (config('admin.navigation') as $section)
                    <div class="admin-nav-group">
                        <div class="admin-nav-group-label">{{ $section['group'] }}</div>
                        @foreach ($section['items'] as $item)
                            @php
                                $active = request()->routeIs(str_replace('.index', '.*', $item['route']))
                                    || request()->routeIs($item['route']);
                            @endphp
                            <a href="{{ route($item['route']) }}"
                               class="admin-nav-link {{ $active ? 'active' : '' }}">
                                <x-admin.icon :name="$item['icon']" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-chip">
                    <span class="admin-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="admin-user-name block">{{ auth()->user()->name }}</span>
                        <span class="admin-user-role">Administrator</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm w-full" style="color: rgb(226 232 240 / 0.85); justify-content: flex-start;">
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button type="button" id="admin-sidebar-open" class="admin-topbar-menu-btn" aria-label="Open menu">
                    <x-admin.icon name="menu" class="!h-5 !w-5" />
                </button>

                <div class="admin-page-heading">
                    <h1 class="admin-page-title">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('page_subtitle')
                        <p class="admin-page-subtitle">@yield('page_subtitle')</p>
                    @endif
                </div>

                <div class="admin-topbar-actions">
                    @yield('header_actions')

                    <button type="button" class="admin-icon-btn" data-theme-toggle aria-label="Toggle theme">
                        <x-admin.icon name="sun" class="!h-5 !w-5" />
                    </button>
                </div>
            </header>

            <main class="admin-content">
                @if (session('success'))
                    <div class="admin-alert admin-alert-success" role="alert">
                        <span class="flex-1">{{ session('success') }}</span>
                        <button type="button" data-dismiss class="admin-icon-btn admin-btn-icon" aria-label="Dismiss">×</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert">
                        <div class="flex-1">
                            <p class="font-medium">Please fix the following:</p>
                            <ul class="mt-1 list-inside list-disc text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" data-dismiss class="admin-icon-btn admin-btn-icon" aria-label="Dismiss">×</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
