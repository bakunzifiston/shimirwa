/**
 * Admin panel UI: mobile sidebar, theme toggle, flash dismiss.
 */
(function () {
    const storageKey = 'admin-theme';

    function getPreferredTheme() {
        const stored = localStorage.getItem(storageKey);
        if (stored === 'light' || stored === 'dark') {
            return stored;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(storageKey, theme);
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            const icon = btn.querySelector('svg');
            if (icon) {
                icon.innerHTML = theme === 'dark'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>';
            }
        });
    }

    function initTheme() {
        applyTheme(getPreferredTheme());
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(next);
            });
        });
    }

    function initSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('admin-sidebar-overlay');
        const openBtn = document.getElementById('admin-sidebar-open');
        const closeBtn = document.getElementById('admin-sidebar-close');

        if (!sidebar) return;

        const open = () => {
            sidebar.classList.add('open');
            overlay?.classList.add('visible');
            document.body.style.overflow = 'hidden';
        };

        const close = () => {
            sidebar.classList.remove('open');
            overlay?.classList.remove('visible');
            document.body.style.overflow = '';
        };

        openBtn?.addEventListener('click', open);
        closeBtn?.addEventListener('click', close);
        overlay?.addEventListener('click', close);

        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) close();
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) close();
        });
    }

    function initFlashDismiss() {
        document.querySelectorAll('[data-dismiss]').forEach((el) => {
            el.addEventListener('click', () => el.closest('.admin-alert')?.remove());
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            initSidebar();
            initFlashDismiss();
        });
    } else {
        initTheme();
        initSidebar();
        initFlashDismiss();
    }
})();
