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
                {{-- User info row --}}
                <div class="admin-user-row">
                    <span class="admin-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <span class="admin-user-info">
                        <span class="admin-user-name">{{ auth()->user()->name }}</span>
                        <span class="admin-user-role">Administrator</span>
                    </span>
                </div>
                {{-- Action row: settings + sign out --}}
                <div class="admin-user-actions">
                    <a href="{{ route('admin.settings.index') }}"
                       title="Settings"
                       class="admin-user-action-btn {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        Settings
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}" style="flex:1">
                        @csrf
                        <button type="submit" class="admin-user-action-btn admin-user-action-btn--danger" title="Sign out">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Sign out
                        </button>
                    </form>
                </div>
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
    {{-- ── Slide-over drawer ──────────────────────────────────────────── --}}
    <div id="admin-drawer-backdrop"
         class="admin-drawer-backdrop"
         aria-hidden="true"></div>

    <aside id="admin-drawer"
           class="admin-drawer"
           role="dialog"
           aria-modal="true"
           aria-label="Form panel">
        <div class="admin-drawer-header">
            <h2 class="admin-drawer-title" id="admin-drawer-title">Form</h2>
            <button type="button" id="admin-drawer-close"
                    class="admin-icon-btn"
                    aria-label="Close panel">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="admin-drawer-body" id="admin-drawer-body">
            <div class="admin-drawer-loading">
                <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24" style="color:var(--admin-primary)">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        </div>
    </aside>

    @stack('scripts')
    <script>
    (function () {
        const backdrop = document.getElementById('admin-drawer-backdrop');
        const drawer   = document.getElementById('admin-drawer');
        const bodyEl   = document.getElementById('admin-drawer-body');
        const titleEl  = document.getElementById('admin-drawer-title');
        const closeBtn = document.getElementById('admin-drawer-close');

        const SPINNER = `<div class="admin-drawer-loading">
            <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24" style="color:var(--admin-primary)">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg></div>`;

        /* ── Extract content from a fetched full-page HTML string ── */
        function extractContent(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            // Prefer .admin-content main area → strip layout chrome
            const main = tmp.querySelector('.admin-content');
            return main ? main.innerHTML : (tmp.querySelector('.admin-card, form') || tmp).outerHTML;
        }

        /* ── Run scripts inside a container ── */
        function runScripts(container) {
            container.querySelectorAll('script').forEach(old => {
                const s = document.createElement('script');
                s.textContent = old.textContent;
                old.replaceWith(s);
            });
        }

        /* ── Inject HTML and wire forms ── */
        function inject(html) {
            bodyEl.innerHTML = extractContent(html);
            runScripts(bodyEl);
            wireDeleteForms(bodyEl);
            wireForms(bodyEl);
        }

        /* ── Wire DELETE forms (show pages have delete confirm forms) ── */
        function wireDeleteForms(container) {
            container.querySelectorAll('form[onsubmit]').forEach(form => {
                // keep native confirm + submit, just reload after
                const orig = form.onsubmit;
                form.addEventListener('submit', function (e) {
                    // if native confirm returns false, stop
                    if (orig && orig.call(form, e) === false) { e.preventDefault(); return; }
                    e.preventDefault();
                    const fd = new FormData(form);
                    // Check for _method=DELETE
                    const method = fd.get('_method') || 'POST';
                    fetch(form.action, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(() => { closeDrawer(); window.location.reload(); });
                });
            });
        }

        /* ── Wire save/edit forms → reload on success, show errors inline ── */
        function wireForms(container) {
            container.querySelectorAll('form[method="POST"]:not([onsubmit]), form[method="post"]:not([onsubmit])').forEach(form => {
                // skip if already has _method DELETE (handled above)
                if (form.querySelector('input[name="_method"]')) return;

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const btn = form.querySelector('[type="submit"]');
                    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        redirect: 'manual'
                    }).then(r => {
                        if (r.type === 'opaqueredirect' || r.redirected || r.status === 302 || r.status === 200) {
                            return r.text().then(html => {
                                // If redirect took us to index page → success
                                if (r.redirected || r.type === 'opaqueredirect') {
                                    closeDrawer();
                                    window.location.reload();
                                    return;
                                }
                                // Check if response contains validation errors
                                if (html.includes('admin-alert-error') || html.includes('Please fix')) {
                                    inject(html);
                                } else {
                                    closeDrawer();
                                    window.location.reload();
                                }
                            });
                        }
                        return r.text().then(html => inject(html));
                    }).catch(() => {
                        closeDrawer();
                        window.location.reload();
                    });
                });
            });
        }

        /* ── Open drawer ── */
        function openDrawer(url, title) {
            titleEl.textContent = title || 'Details';
            bodyEl.innerHTML = SPINNER;
            backdrop.classList.add('visible');
            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => inject(html))
                .catch(() => {
                    bodyEl.innerHTML = '<p style="padding:1.5rem;color:#dc2626">Failed to load. Please try again.</p>';
                });
        }

        /* ── Close drawer ── */
        function closeDrawer() {
            backdrop.classList.remove('visible');
            drawer.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { bodyEl.innerHTML = SPINNER; }, 300);
        }

        closeBtn.addEventListener('click', closeDrawer);
        backdrop.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

        /* ── Intercept any [data-drawer-src] click ── */
        document.addEventListener('click', function (e) {
            const link = e.target.closest('[data-drawer-src]');
            if (!link) return;
            e.preventDefault();
            openDrawer(link.dataset.drawerSrc, link.dataset.drawerTitle || link.textContent.trim());
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
