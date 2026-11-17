/* Blush & Bloom Salon – Admin Portal behaviour */
(function () {
    'use strict';

    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // Mobile sidebar
    const btn = document.getElementById('adminMenuBtn');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminBackdrop');
    function setSidebar(open) {
        if (!sidebar) return;
        sidebar.classList.toggle('is-open', open);
        btn?.setAttribute('aria-expanded', String(open));
        if (backdrop) backdrop.hidden = !open;
    }
    btn?.addEventListener('click', function () { setSidebar(!sidebar.classList.contains('is-open')); });
    backdrop?.addEventListener('click', function () { setSidebar(false); });

    // Account menu (click the admin name at the bottom of the sidebar)
    const menuBtn = document.getElementById('accountMenuBtn');
    const popover = document.getElementById('accountPopover');
    function setMenu(open) {
        if (!menuBtn || !popover) return;
        menuBtn.setAttribute('aria-expanded', String(open));
        if (open) {
            popover.hidden = false;
            requestAnimationFrame(function () { popover.classList.add('is-open'); });
            popover.querySelector('.account-item')?.focus();
        } else {
            popover.classList.remove('is-open');
            setTimeout(function () { if (menuBtn.getAttribute('aria-expanded') === 'false') popover.hidden = true; }, reducedMotion ? 0 : 180);
        }
    }
    menuBtn?.addEventListener('click', function (e) { e.stopPropagation(); setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'); });
    document.addEventListener('click', function (e) {
        if (popover && !popover.hidden && !popover.contains(e.target) && e.target !== menuBtn) setMenu(false);
    });
    popover?.addEventListener('keydown', function (e) {
        const items = Array.from(popover.querySelectorAll('.account-item, [data-theme-choice]'));
        const i = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); items[(i + 1) % items.length]?.focus(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); items[(i - 1 + items.length) % items.length]?.focus(); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (menuBtn?.getAttribute('aria-expanded') === 'true') { setMenu(false); menuBtn.focus(); }
        setSidebar(false);
    });

    // Light / Dark switch: applies instantly (circular reveal) and is saved to the admin account.
    function applyTheme(theme, origin) {
        const swap = function () {
            root.setAttribute('data-theme', theme);
            document.querySelectorAll('[data-theme-choice]').forEach(function (b) {
                b.setAttribute('aria-checked', String(b.dataset.themeChoice === theme));
            });
        };
        if (!document.startViewTransition || reducedMotion || !origin) { swap(); return; }
        const r = origin.getBoundingClientRect();
        const x = r.left + r.width / 2, y = r.top + r.height / 2;
        const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
        document.startViewTransition(swap).ready.then(function () {
            root.animate({ clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + radius + 'px at ' + x + 'px ' + y + 'px)'] },
                { duration: 650, easing: 'cubic-bezier(.65, 0, .35, 1)', pseudoElement: '::view-transition-new(root)' });
        }).catch(function () {});
    }
    document.querySelectorAll('[data-theme-choice]').forEach(function (choice) {
        choice.addEventListener('click', function () {
            const theme = choice.dataset.themeChoice;
            if (root.getAttribute('data-theme') === theme) return;
            applyTheme(theme, choice);
            const url = choice.closest('[data-theme-url]')?.dataset.themeUrl;
            if (url) fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ theme: theme })
            }).catch(function () { /* the theme still applies for this page */ });
        });
    });

    // Confirmation for destructive or customer-facing actions: <form data-confirm="Are you sure?">
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Prevent double submission of admin forms
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            if (form.dataset.sent === '1') { e.preventDefault(); return; }
            form.dataset.sent = '1';
            setTimeout(function () { form.dataset.sent = ''; }, 4000);
        });
    });

    // Auto-submit filter selects
    document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    // Fallback when the Chart.js CDN cannot load (e.g. offline demo): simple HTML bars.
    function htmlBars(canvas, rows) {
        const max = Math.max.apply(null, rows.map(function (r) { return r.value; }).concat([1]));
        const list = document.createElement('div');
        list.className = 'bar-fallback';
        rows.forEach(function (r) {
            const row = document.createElement('div');
            row.className = 'bar-row';
            const label = document.createElement('span'); label.textContent = r.label;
            const track = document.createElement('span'); track.className = 'bar-track';
            const fill = document.createElement('i'); fill.style.width = (r.value / max * 100) + '%';
            track.appendChild(fill);
            const value = document.createElement('b'); value.textContent = r.value;
            row.append(label, track, value);
            list.appendChild(row);
        });
        canvas.parentElement.replaceWith(list);
    }

    if (!window.Chart) {
        const w = document.getElementById('weeklyChart');
        if (w) htmlBars(w, JSON.parse(w.dataset.chart).map(function (d) { return { label: 'Week of ' + d.label, value: d.count }; }));
        const pop = document.getElementById('popularChart');
        if (pop) htmlBars(pop, JSON.parse(pop.dataset.chart).map(function (d) { return { label: d.name, value: d.count }; }));
    }

    // Dashboard charts (Chart.js is loaded only on the dashboard)
    if (window.Chart) {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        const text = dark ? '#C2B6AA' : '#75695F';
        const grid = dark ? 'rgba(255,255,255,.08)' : 'rgba(58,51,46,.08)';
        Chart.defaults.font.family = 'Figtree, Segoe UI, sans-serif';
        Chart.defaults.color = text;

        const weekly = document.getElementById('weeklyChart');
        if (weekly) {
            const data = JSON.parse(weekly.dataset.chart);
            new Chart(weekly, {
                type: 'bar',
                data: { labels: data.map(function (d) { return d.label; }), datasets: [{ label: 'Appointments', data: data.map(function (d) { return d.count; }), backgroundColor: '#BD7E83', borderRadius: 6, maxBarThickness: 34 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } }, x: { grid: { display: false } } } }
            });
        }
        const popular = document.getElementById('popularChart');
        if (popular) {
            const data = JSON.parse(popular.dataset.chart);
            new Chart(popular, {
                type: 'bar',
                data: { labels: data.map(function (d) { return d.name; }), datasets: [{ label: 'Bookings', data: data.map(function (d) { return d.count; }), backgroundColor: ['#BD7E83', '#89978B', '#B79A62', '#A89582', '#C99A9E', '#A3AFA5'], borderRadius: 6 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } }, y: { grid: { display: false } } } }
            });
        }
    }
})();
