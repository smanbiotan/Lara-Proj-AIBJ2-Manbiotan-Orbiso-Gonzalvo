/* =========================================================================
   HOME PAGE — Member 1: Sherwin Alvin Manbiotan
   Features: IMAGE SLIDER / CAROUSEL
   - Automatic slideshow with smooth cross-fade
   - Small clickable progress indicators
   - Autoplay continues after a manual selection
   - Pauses while the browser tab is hidden (saves resources)
   ========================================================================= */
(function () {
    'use strict';

    const slider = document.getElementById('heroSlider');
    if (!slider) return;

    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    const indicators = Array.from(slider.querySelectorAll('.hero-indicator'));
    const interval = Math.max(3000, Number(slider.dataset.interval) || 6000);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (slides.length < 2) return;

    let current = 0;
    let timer = null;

    slider.style.setProperty('--slide-duration', interval + 'ms');

    function restartIndicatorAnimation(button) {
        const fill = button.querySelector('.hero-indicator-fill');
        // Re-trigger the CSS progress animation from zero.
        fill.style.display = 'none';
        void fill.offsetWidth;
        fill.style.display = '';
    }

    function showSlide(index) {
        current = (index + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
            const active = i === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        indicators.forEach(function (button, i) {
            const active = i === current;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
            if (active) restartIndicatorAnimation(button);
        });

        schedule();
    }

    function schedule() {
        clearTimeout(timer);
        if (reducedMotion || document.hidden) return;
        timer = setTimeout(function () { showSlide(current + 1); }, interval);
    }

    // Clicking an indicator jumps to that slide; autoplay then continues.
    indicators.forEach(function (button) {
        button.addEventListener('click', function () {
            showSlide(Number(button.dataset.slide));
        });
    });

    // Keyboard: left/right arrows when focus is on the indicators.
    slider.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { showSlide(current + 1); indicators[current].focus(); }
        if (e.key === 'ArrowLeft') { showSlide(current - 1); indicators[current].focus(); }
    });

    document.addEventListener('visibilitychange', function () { document.hidden ? clearTimeout(timer) : schedule(); });

    showSlide(0);
})();
