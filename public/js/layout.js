/* =========================================================================
   Blush & Bloom Salon — shared layout behaviour (Member 1)
   - Light / dark theme toggle (saved in localStorage)
   - Auto-hiding header (hide on scroll down, show on scroll up)
   - Mobile navigation menu
   - Back-to-top button
   ========================================================================= */
(function () {
    'use strict';

    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Theme toggle: sun/moon morph + circular reveal ---------- */
    const themeToggle = document.getElementById('themeToggle');
    const applyThemeLabel = () => {
        if (!themeToggle) return;
        const dark = root.getAttribute('data-theme') === 'dark';
        const label = dark ? 'Switch to light mode' : 'Switch to dark mode';
        themeToggle.setAttribute('aria-pressed', String(dark));
        themeToggle.setAttribute('aria-label', label);
        themeToggle.title = label;
    };
    const setTheme = (next) => {
        root.setAttribute('data-theme', next);
        try { localStorage.setItem('bb-theme', next); } catch (e) { /* storage unavailable */ }
        applyThemeLabel();
    };
    themeToggle?.addEventListener('click', (event) => {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

        // Browsers without the View Transitions API (or reduced motion): switch instantly.
        if (!document.startViewTransition || reducedMotion) { setTheme(next); return; }

        // The new theme grows as a circle from the button that was clicked.
        const rect = themeToggle.getBoundingClientRect();
        const x = event.clientX || rect.left + rect.width / 2;
        const y = event.clientY || rect.top + rect.height / 2;
        const radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));

        root.classList.add('theme-transition');
        const transition = document.startViewTransition(() => setTheme(next));
        transition.ready.then(() => {
            root.animate(
                { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
                { duration: 700, easing: 'cubic-bezier(.65, 0, .35, 1)', pseudoElement: '::view-transition-new(root)' }
            );
        }).catch(() => {});
        transition.finished.finally(() => root.classList.remove('theme-transition'));
    });
    applyThemeLabel();

    /* ---------- Mobile menu ---------- */
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const setMenu = (open) => {
        if (!menuToggle || !mobileMenu) return;
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        mobileMenu.classList.toggle('is-open', open);
    };
    menuToggle?.addEventListener('click', () => setMenu(menuToggle.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });
    window.addEventListener('resize', () => { if (window.innerWidth > 1020) setMenu(false); });

    /* ---------- Auto-hiding header + back-to-top ---------- */
    const header = document.getElementById('siteHeader');
    const backToTop = document.getElementById('backToTop');
    let lastY = window.scrollY;
    let ticking = false;

    const onScroll = () => {
        const y = Math.max(0, window.scrollY);
        const menuOpen = menuToggle?.getAttribute('aria-expanded') === 'true';
        if (header) {
            if (y < 80 || menuOpen) header.classList.remove('is-hidden');
            else if (y > lastY + 6) header.classList.add('is-hidden');   // scrolling down
            else if (y < lastY - 6) header.classList.remove('is-hidden'); // scrolling up
        }
        backToTop?.classList.toggle('is-visible', y > 500);
        if (Math.abs(y - lastY) > 6) lastY = y;
        ticking = false;
    };
    window.addEventListener('scroll', () => {
        if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
    }, { passive: true });

    // Keyboard users: show the header whenever focus moves into it.
    header?.addEventListener('focusin', () => header.classList.remove('is-hidden'));

    backToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
        document.getElementById('main-content')?.focus({ preventScroll: true });
    });
})();
