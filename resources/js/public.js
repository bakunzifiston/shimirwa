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

    const banner = document.querySelector('[data-site-banner]');
    if (banner) {
        const slides = [...banner.querySelectorAll('[data-site-banner-slide]')];
        const dots = [...banner.querySelectorAll('[data-site-banner-dot]')];
        const prev = banner.querySelector('[data-site-banner-prev]');
        const next = banner.querySelector('[data-site-banner-next]');
        let index = 0;
        let timer = null;
        const interval = 6000;

        const goTo = (nextIndex) => {
            if (slides.length < 2) {
                return;
            }
            slides[index].classList.remove('is-active');
            slides[index].hidden = true;
            if (dots[index]) {
                dots[index].classList.remove('is-active');
                dots[index].setAttribute('aria-selected', 'false');
            }

            index = (nextIndex + slides.length) % slides.length;

            slides[index].classList.add('is-active');
            slides[index].hidden = false;
            if (dots[index]) {
                dots[index].classList.add('is-active');
                dots[index].setAttribute('aria-selected', 'true');
            }
        };

        const startTimer = () => {
            stopTimer();
            timer = window.setInterval(() => goTo(index + 1), interval);
        };

        const stopTimer = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        prev?.addEventListener('click', () => {
            goTo(index - 1);
            startTimer();
        });
        next?.addEventListener('click', () => {
            goTo(index + 1);
            startTimer();
        });
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                goTo(Number(dot.dataset.siteBannerDot));
                startTimer();
            });
        });

        banner.addEventListener('mouseenter', stopTimer);
        banner.addEventListener('mouseleave', startTimer);
        banner.addEventListener('focusin', stopTimer);
        banner.addEventListener('focusout', (e) => {
            if (!banner.contains(e.relatedTarget)) {
                startTimer();
            }
        });

        startTimer();
    }
})();
