/**
 * Public site: mobile nav, sticky header, scroll reveal.
 */
(function () {
    const header = document.querySelector('.site-header');
    const toggle = document.querySelector('[data-site-menu-toggle]');
    const mobileNav = document.querySelector('[data-site-mobile-nav]');

    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        }, { passive: true });
    }

    if (toggle && mobileNav) {
        toggle.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('is-open');
            mobileNav.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });

        mobileNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileNav.classList.remove('is-open');
                mobileNav.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });
    }

    const reveals = document.querySelectorAll('.site-reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );
        reveals.forEach((el) => observer.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
    }
})();
