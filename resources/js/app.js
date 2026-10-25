import './bootstrap';

/**
 * Public website behaviour. Everything here is scoped to data attributes that
 * only exist on the marketing site, so the admin app is unaffected.
 */
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initHeaderShadow();
    initReveal();
});

function initMobileMenu() {
    const toggle = document.querySelector('[data-site-menu-toggle]');
    const panel = document.querySelector('[data-site-menu]');

    if (!toggle || !panel) {
        return;
    }

    const open = () => {
        panel.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('overflow-hidden', 'lg:overflow-auto');
    };

    const close = () => {
        panel.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('overflow-hidden', 'lg:overflow-auto');
    };

    toggle.addEventListener('click', () => {
        if (panel.classList.contains('hidden')) {
            open();
        } else {
            close();
        }
    });

    panel.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}

function initHeaderShadow() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const update = () => {
        header.classList.toggle('shadow-[0_10px_30px_-18px_rgba(36,36,36,0.45)]', window.scrollY > 8);
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
}

function initReveal() {
    const items = document.querySelectorAll('.reveal');

    if (!items.length) {
        return;
    }

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    items.forEach((item) => observer.observe(item));
}
