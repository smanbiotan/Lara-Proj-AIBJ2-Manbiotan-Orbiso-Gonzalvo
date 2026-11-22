/* =========================================================================
   ABOUT PAGE — Member 2: Gerald EJ Gonzalvo
   Features: READ MORE / READ LESS toggle
   Extra: animated number counters that start when scrolled into view
   ========================================================================= */
(function () {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Read More / Read Less ---------- */
    document.querySelectorAll('[data-read-more-toggle]').forEach(function (button) {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        const label = button.querySelector('[data-label]');
        if (!panel) return;

        button.addEventListener('click', function () {
            const isOpen = button.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                // Collapse: animate from current height down to zero.
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(function () { panel.style.maxHeight = '0px'; });
                panel.classList.remove('is-open');
            } else {
                // Expand to the content's real height.
                panel.classList.add('is-open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }

            button.setAttribute('aria-expanded', String(!isOpen));
            label.textContent = isOpen ? 'Read More' : 'Read Less';
        });
    });

    // Keep expanded panels sized correctly if the window is resized.
    window.addEventListener('resize', function () {
        document.querySelectorAll('[data-read-more].is-open').forEach(function (panel) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        });
    });

    /* ---------- Animated counters ---------- */
    const counters = document.querySelectorAll('[data-count-to]');

    function animate(el) {
        const target = Number(el.dataset.countTo) || 0;
        const suffix = el.dataset.suffix || '';
        if (reducedMotion) { el.textContent = target + suffix; return; }

        const duration = 1400;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out
            el.textContent = Math.round(target * eased) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { animate(entry.target); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.4 });
        counters.forEach(function (c) { observer.observe(c); });
    } else {
        counters.forEach(animate);
    }
})();
