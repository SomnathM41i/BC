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
    const backdrop = document.querySelector('[data-site-menu-backdrop]');
    const closeButton = panel?.querySelector('[data-site-menu-close]');

    if (!toggle || !panel) {
        return;
    }

    const closedIcon = toggle.querySelector('[data-site-menu-icon="closed"]');
    const openIcon = toggle.querySelector('[data-site-menu-icon="open"]');
    const menuLabel = toggle.getAttribute('aria-label') || 'Open main menu';
    const closeLabel = toggle.dataset.closeLabel || 'Close';
    const desktop = window.matchMedia('(min-width: 1240px)');
    let isOpen = false;

    const setState = (next) => {
        isOpen = next;

        panel.classList.toggle('-translate-x-full', !isOpen);
        panel.classList.toggle('translate-x-0', isOpen);
        panel.setAttribute('aria-hidden', String(!isOpen));
        panel.inert = !isOpen;

        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? closeLabel : menuLabel);
        toggle.classList.toggle('invisible', isOpen);
        closedIcon?.classList.toggle('hidden', isOpen);
        openIcon?.classList.toggle('hidden', !isOpen);

        if (backdrop) {
            backdrop.classList.toggle('opacity-0', !isOpen);
            backdrop.classList.toggle('opacity-100', isOpen);
            backdrop.classList.toggle('pointer-events-none', !isOpen);
        }

        document.documentElement.classList.toggle('overflow-hidden', isOpen);

        if (isOpen) {
            closeButton?.focus({ preventScroll: true });
        } else {
            toggle.focus({ preventScroll: true });
        }
    };

    toggle.addEventListener('click', () => setState(!isOpen));
    closeButton?.addEventListener('click', () => setState(false));
    backdrop?.addEventListener('click', () => setState(false));

    panel.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setState(false)));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            setState(false);
        }
    });

    desktop.addEventListener('change', (event) => {
        if (event.matches && isOpen) {
            setState(false);
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
