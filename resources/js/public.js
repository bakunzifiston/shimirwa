/**
 * Shimirwa public site: mobile nav, sticky header, scroll reveal.
 */
(function () {
    // Reveal on scroll (.reveal and .site-reveal)
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible', 'is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
        document.querySelectorAll('.reveal, .site-reveal').forEach(el => io.observe(el));
    } else {
        document.querySelectorAll('.reveal, .site-reveal').forEach(el => {
            el.classList.add('visible', 'is-visible');
        });
    }

    // Sticky header scroll state
    const header = document.querySelector('[data-site-header]');
    if (header) {
        const updateHeader = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        window.addEventListener('scroll', updateHeader, { passive: true });
        updateHeader();
    }

    // Mobile nav toggle
    const toggle    = document.querySelector('[data-site-menu-toggle]');
    const mobileNav = document.querySelector('[data-site-mobile-nav]');
    if (toggle && mobileNav) {
        toggle.addEventListener('click', () => {
            const open = mobileNav.hasAttribute('hidden');
            mobileNav.toggleAttribute('hidden', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });
        mobileNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileNav.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });
    }
})();
