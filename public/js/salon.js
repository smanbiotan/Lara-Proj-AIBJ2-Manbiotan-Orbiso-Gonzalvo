/* Salon catalogue: filter service cards by category (no page reload). */
(function () {
    'use strict';

    const buttons = document.querySelectorAll('[data-filter]');
    const cards = document.querySelectorAll('#serviceGrid .service-card');
    const status = document.getElementById('filterStatus');
    if (!buttons.length) return;

    function applyFilter(filter) {
        let visible = 0;
        cards.forEach(function (card) {
            const show = filter === 'all' || card.dataset.category === filter;
            card.classList.toggle('is-hidden', !show);
            card.classList.remove('is-entering');
            if (show) { void card.offsetWidth; card.classList.add('is-entering'); visible++; }
        });
        buttons.forEach(function (b) {
            const active = b.dataset.filter === filter;
            b.classList.toggle('is-active', active);
            b.setAttribute('aria-pressed', String(active));
        });
        if (status) status.textContent = visible + (visible === 1 ? ' service' : ' services') + ' shown';
        history.replaceState(null, '', filter === 'all' ? location.pathname : '#' + filter);
    }

    buttons.forEach(function (b) { b.addEventListener('click', function () { applyFilter(b.dataset.filter); }); });

    // Support links such as /salon#hair
    const fromHash = location.hash.replace('#', '');
    if (fromHash && document.querySelector('[data-filter="' + fromHash + '"]')) applyFilter(fromHash);
})();
