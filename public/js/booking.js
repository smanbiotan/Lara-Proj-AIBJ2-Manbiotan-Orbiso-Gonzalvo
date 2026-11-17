/* =========================================================================
   Book Now page
   - Step-by-step wizard (Service → Date & time → Your details)
   - Month calendar: past days, days beyond 6 months, and closed days are disabled
   - Loads REAL available times from Laravel for the chosen service + date
   - Live booking summary, client-side checks, duplicate-submit guard
   - "Sign in to continue" dialog for guests
   ========================================================================= */
(function () {
    'use strict';

    const form = document.getElementById('bookingForm');
    if (!form) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const dateInput = form.querySelector('#appointment_date');
    const slotGrid = document.getElementById('slotGrid');
    const timeDateLabel = document.getElementById('timeDateLabel');
    const submitBtn = document.getElementById('bookingSubmit');
    const url = form.dataset.availabilityUrl;
    let selectedTime = slotGrid.dataset.old || '';
    let requestId = 0;

    const sum = {
        service: document.getElementById('sumService'), price: document.getElementById('sumPrice'),
        duration: document.getElementById('sumDuration'), date: document.getElementById('sumDate'), time: document.getElementById('sumTime')
    };

    const selectedService = () => form.querySelector('input[name="service"]:checked');
    const parseISO = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const toISO = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const prettyDate = (iso, opts) => parseISO(iso).toLocaleDateString(undefined, opts || { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

    /* ---------------- Summary ---------------- */
    function updateSummary() {
        const svc = selectedService();
        sum.service.textContent = svc ? svc.dataset.name : 'Not selected';
        sum.price.textContent = svc ? svc.dataset.price : '—';
        sum.duration.textContent = svc ? svc.dataset.duration : '—';
        sum.date.textContent = dateInput.value ? prettyDate(dateInput.value) : '—';
        const checked = slotGrid.querySelector('input:checked');
        sum.time.textContent = checked ? checked.dataset.label : '—';
    }

    /* ---------------- Calendar ---------------- */
    const cal = document.getElementById('bookingCalendar');
    const calDays = cal.querySelector('[data-cal-days]');
    const calTitle = cal.querySelector('[data-cal-title]');
    const prevBtn = cal.querySelector('[data-cal-prev]');
    const nextBtn = cal.querySelector('[data-cal-next]');
    const minDate = parseISO(cal.dataset.min);
    const maxDate = parseISO(cal.dataset.max);
    const openDays = (cal.dataset.openDays || '').split(',').filter(Boolean).map(Number);
    const todayISO = cal.dataset.min;
    let viewMonth = dateInput.value ? parseISO(dateInput.value) : new Date(minDate);
    viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth(), 1);

    function isSelectable(d) {
        return d >= minDate && d <= maxDate && openDays.includes(d.getDay());
    }

    function renderCalendar(focusISO) {
        const year = viewMonth.getFullYear();
        const month = viewMonth.getMonth();
        calTitle.textContent = viewMonth.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        prevBtn.disabled = year === minDate.getFullYear() && month === minDate.getMonth();
        nextBtn.disabled = year === maxDate.getFullYear() && month === maxDate.getMonth();

        const first = new Date(year, month, 1);
        const start = new Date(year, month, 1 - first.getDay());
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const cells = Math.ceil((first.getDay() + daysInMonth) / 7) * 7;

        calDays.innerHTML = '';
        for (let i = 0; i < cells; i++) {
            const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
            const iso = toISO(d);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'calendar-day';
            btn.textContent = d.getDate();
            btn.dataset.date = iso;
            const outside = d.getMonth() !== month;
            const selectable = !outside && isSelectable(d);
            if (outside) btn.classList.add('is-outside');
            if (iso === todayISO) btn.classList.add('is-today');
            if (iso === dateInput.value) { btn.classList.add('is-selected'); btn.setAttribute('aria-pressed', 'true'); }
            if (!selectable) {
                btn.disabled = true;
                btn.tabIndex = -1;
                if (outside) btn.setAttribute('aria-hidden', 'true');
                else btn.setAttribute('aria-label', prettyDate(iso, { weekday: 'long', month: 'long', day: 'numeric' }) + (openDays.includes(d.getDay()) ? ' (unavailable)' : ' (closed)'));
            } else {
                btn.setAttribute('aria-label', prettyDate(iso, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }));
            }
            calDays.appendChild(btn);
        }
        if (focusISO) calDays.querySelector('[data-date="' + focusISO + '"]:not(:disabled)')?.focus();
    }

    function selectDate(iso) {
        dateInput.value = iso;
        selectedTime = '';
        setError('appointment_date', false);
        renderCalendar(iso);
        loadSlots();
    }

    calDays.addEventListener('click', (e) => {
        const btn = e.target.closest('.calendar-day');
        if (btn && !btn.disabled) selectDate(btn.dataset.date);
    });
    calDays.addEventListener('keydown', (e) => {
        const btn = e.target.closest('.calendar-day');
        if (!btn) return;
        const steps = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
        if (!(e.key in steps)) return;
        e.preventDefault();
        let d = parseISO(btn.dataset.date);
        for (let n = 0; n < 60; n++) {
            d = new Date(d.getFullYear(), d.getMonth(), d.getDate() + steps[e.key]);
            if (d < minDate || d > maxDate) return;
            if (isSelectable(d)) break;
        }
        if (d.getMonth() !== viewMonth.getMonth()) viewMonth = new Date(d.getFullYear(), d.getMonth(), 1);
        renderCalendar(toISO(d));
    });
    prevBtn.addEventListener('click', () => { viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() - 1, 1); renderCalendar(); });
    nextBtn.addEventListener('click', () => { viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 1); renderCalendar(); });

    /* ---------------- Time slots ---------------- */
    function message(text) {
        slotGrid.innerHTML = '';
        const p = document.createElement('p');
        p.className = 'slot-message';
        p.textContent = text;
        slotGrid.appendChild(p);
    }

    async function loadSlots() {
        updateSummary();
        const svc = selectedService();
        timeDateLabel.textContent = dateInput.value ? prettyDate(dateInput.value, { weekday: 'long', month: 'long', day: 'numeric' }) : 'Pick a date to see open times.';
        if (!svc || !dateInput.value) {
            message(!svc ? 'Choose a service first.' : 'Choose a date on the calendar.');
            return;
        }
        const id = ++requestId;
        slotGrid.innerHTML = '<div class="slot-loading"><span class="spinner"></span> Checking availability…</div>';

        try {
            const params = new URLSearchParams({ service: svc.value, date: dateInput.value });
            const response = await fetch(url + '?' + params.toString(), { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (id !== requestId) return;

            if (!response.ok) {
                message(data.errors ? Object.values(data.errors)[0][0] : 'Could not load times for that date.');
                return;
            }
            if (!data.open) { message('The salon is closed on this day. Please choose another date.'); return; }
            if (!data.slots.length || data.available_count === 0) { message('No times are available on this date. Please try another day.'); return; }

            slotGrid.innerHTML = '';
            data.slots.forEach(function (slot) {
                const label = document.createElement('label');
                label.className = 'slot';
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'appointment_time';
                input.value = slot.time;
                input.dataset.label = slot.label;
                input.disabled = !slot.available;
                if (slot.available && slot.time === selectedTime) input.checked = true;
                input.setAttribute('aria-label', slot.label + (slot.available ? '' : ' (unavailable)'));
                const span = document.createElement('span');
                span.textContent = slot.label;
                label.append(input, span);
                slotGrid.appendChild(label);
            });
            updateSummary();
        } catch (error) {
            if (id === requestId) message('We could not check availability right now. Please refresh the page and try again.');
        }
    }

    slotGrid.addEventListener('change', (e) => {
        if (e.target.name === 'appointment_time') { selectedTime = e.target.value; setError('appointment_time', false); updateSummary(); }
    });
    form.addEventListener('change', (e) => {
        if (e.target.name === 'service') { selectedTime = ''; setError('service', false); loadSlots(); }
    });

    /* ---------------- Validation helpers ---------------- */
    function setError(name, on) {
        const msg = form.querySelector('[data-error-for="' + name + '"]');
        if (msg) { msg.hidden = !on; msg.closest('.field')?.classList.toggle('has-error', on); }
    }

    function validateStep(step) {
        let firstInvalid = null;
        if (step === 1) {
            const ok = !!selectedService();
            setError('service', !ok);
            if (!ok) firstInvalid = form.querySelector('input[name="service"]');
        }
        if (step === 2) {
            const hasDate = !!dateInput.value;
            const hasTime = !!slotGrid.querySelector('input:checked');
            setError('appointment_date', !hasDate);
            setError('appointment_time', hasDate && !hasTime);
            if (!hasDate) firstInvalid = calDays.querySelector('.calendar-day:not(:disabled)') || cal;
            else if (!hasTime) firstInvalid = slotGrid.querySelector('input:not(:disabled)') || slotGrid;
        }
        if (step === 3) {
            ['name', 'email', 'phone'].forEach((name) => {
                const el = form.elements[name];
                const v = el.value.trim();
                const invalid = !v
                    || (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v))
                    || (name === 'phone' && !/^[0-9+()\-\s]{7,30}$/.test(v))
                    || (name === 'name' && v.length < 2);
                el.closest('.field').classList.toggle('has-error', invalid);
                if (invalid && !firstInvalid) firstInvalid = el;
            });
        }
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
            firstInvalid.focus?.({ preventScroll: true });
        }
        return !firstInvalid;
    }

    /* ---------------- Wizard ---------------- */
    const panels = Array.from(form.querySelectorAll('.wizard-panel'));
    const indicators = Array.from(form.querySelectorAll('[data-step-indicator]'));
    let current = 1;

    function showStep(step, focus) {
        current = step;
        panels.forEach((p) => {
            const active = Number(p.dataset.step) === step;
            p.classList.toggle('is-active', active);
            p.toggleAttribute('hidden', !active);
        });
        indicators.forEach((li) => {
            const n = Number(li.dataset.stepIndicator);
            li.classList.toggle('is-active', n === step);
            li.classList.toggle('is-done', n < step);
            if (n === step) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        });
        if (focus) {
            const panel = panels[step - 1];
            panel.querySelector('legend')?.setAttribute('tabindex', '-1');
            panel.querySelector('legend')?.focus({ preventScroll: true });
            form.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
        }
    }

    form.addEventListener('click', (e) => {
        if (e.target.closest('[data-next]')) { if (validateStep(current)) showStep(Math.min(current + 1, panels.length), true); }
        if (e.target.closest('[data-back]')) showStep(Math.max(current - 1, 1), true);
    });
    // Let customers jump back to a completed step from the stepper.
    indicators.forEach((li) => li.addEventListener('click', () => {
        const n = Number(li.dataset.stepIndicator);
        if (n < current) showStep(n, true);
    }));

    form.addEventListener('submit', (e) => {
        for (let s = 1; s <= 3; s++) {
            if (!validateStep(s)) { e.preventDefault(); showStep(s, false); validateStep(s); return; }
        }
        if (form.dataset.submitting === 'true') { e.preventDefault(); return; }
        form.dataset.submitting = 'true';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Please wait…';
    });

    /* ---------------- Sign-in dialog (guests) ---------------- */
    const gate = document.getElementById('authGate');
    let lastFocus = null;
    function openGate() {
        if (!gate) return;
        lastFocus = document.activeElement;
        gate.hidden = false;
        requestAnimationFrame(() => gate.classList.add('is-open'));
        document.body.classList.add('no-scroll');
        gate.querySelector('.btn-google:not(.is-disabled), .auth-gate-btn')?.focus();
    }
    function closeGate() {
        if (!gate) return;
        gate.classList.remove('is-open');
        document.body.classList.remove('no-scroll');
        setTimeout(() => { gate.hidden = true; }, reducedMotion ? 0 : 250);
        lastFocus?.focus?.();
    }
    if (gate) {
        gate.addEventListener('click', (e) => { if (e.target.closest('[data-close-auth-gate]')) closeGate(); });
        document.addEventListener('keydown', (e) => {
            if (gate.hidden) return;
            if (e.key === 'Escape') closeGate();
            if (e.key === 'Tab') {
                const f = Array.from(gate.querySelectorAll('a[href], button:not([disabled])'));
                if (!f.length) return;
                const first = f[0], last = f[f.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
        document.querySelectorAll('[data-open-auth-gate]').forEach((b) => b.addEventListener('click', openGate));
    }

    /* ---------------- Start ---------------- */
    form.classList.add('is-wizard');
    showStep(Number(form.dataset.startStep) || 1, false);
    renderCalendar();
    loadSlots();
    if (gate && gate.dataset.open === 'true') openGate();
})();
