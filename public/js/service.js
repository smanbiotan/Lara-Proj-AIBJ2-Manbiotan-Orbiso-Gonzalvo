/* Service detail slideshow: autoplay, previous/next, thumbnails, indicators,
   pause on hover or keyboard focus, arrow-key navigation, reduced-motion support. */
(function () {
    'use strict';

    const gallery = document.getElementById('serviceGallery');
    if (!gallery) return;

    const slides = Array.from(gallery.querySelectorAll('.gallery-slide'));
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
    const dots = Array.from(gallery.querySelectorAll('.gallery-dot'));
    const interval = Math.max(3000, Number(gallery.dataset.interval) || 5000);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (slides.length < 2) return;

    let current = 0;
    let timer = null;
    let paused = false;

    function show(index) {
        current = (index + slides.length) % slides.length;
        slides.forEach(function (s, i) { s.classList.toggle('is-active', i === current); s.setAttribute('aria-hidden', i === current ? 'false' : 'true'); });
        thumbs.forEach(function (t, i) { t.classList.toggle('is-active', i === current); t.setAttribute('aria-pressed', i === current ? 'true' : 'false'); });
        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
        schedule();
    }

    function schedule() {
        clearTimeout(timer);
        if (!paused && !reducedMotion && !document.hidden) timer = setTimeout(function () { show(current + 1); }, interval);
    }

    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', function () { show(current - 1); });
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', function () { show(current + 1); });
    thumbs.forEach(function (t) { t.addEventListener('click', function () { show(Number(t.dataset.galleryThumb)); }); });

    // Pause autoplay while the visitor is looking (hover) or using the keyboard (focus).
    gallery.addEventListener('mouseenter', function () { paused = true; clearTimeout(timer); });
    gallery.addEventListener('mouseleave', function () { paused = false; schedule(); });
    gallery.addEventListener('focusin', function () { paused = true; clearTimeout(timer); });
    gallery.addEventListener('focusout', function (e) { if (!gallery.contains(e.relatedTarget)) { paused = false; schedule(); } });
    gallery.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); show(current - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); show(current + 1); }
    });
    document.addEventListener('visibilitychange', schedule);

    show(0);
})();
