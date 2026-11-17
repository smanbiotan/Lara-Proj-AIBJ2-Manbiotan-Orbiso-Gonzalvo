/* =========================================================================
   CONTACT PAGE — Member 3: Anthony Josh Orbiso
   Features: CLIENT-SIDE FORM VALIDATION
   - Shows an error for every empty field
   - Checks the email format
   - Shows a success message when the form is valid and saved
   The server (Laravel) validates again and saves the inquiry to MySQL.
   ========================================================================= */
(function () {
    'use strict';

    const form = document.getElementById('contactForm');
    if (!form) return;

    const successBox = document.getElementById('contactSuccess');
    const alertBox = document.getElementById('contactAlert');
    const submitBtn = form.querySelector('button[type="submit"]');
    const submitLabel = form.querySelector('[data-submit-label]');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    const fields = {
        name: { label: 'Name', min: 2 },
        email: { label: 'Email address', email: true },
        subject: { label: 'Subject', min: 3 },
        message: { label: 'Message', min: 10 }
    };

    function setError(name, message) {
        const input = form.elements[name];
        const wrapper = input.closest('.field');
        const errorEl = form.querySelector('[data-error-for="' + name + '"]');
        wrapper.classList.toggle('has-error', Boolean(message));
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        errorEl.textContent = message || '';
    }

    function validateField(name) {
        const rules = fields[name];
        const value = form.elements[name].value.trim();
        let message = '';

        if (value === '') {
            message = rules.label + ' is required.';
        } else if (rules.email && !emailPattern.test(value)) {
            message = 'Please enter a valid email address (example: name@example.com).';
        } else if (rules.min && value.length < rules.min) {
            message = rules.label + ' must be at least ' + rules.min + ' characters.';
        }

        setError(name, message);
        return message === '';
    }

    function validateAll() {
        let firstInvalid = null;
        Object.keys(fields).forEach(function (name) {
            if (!validateField(name) && !firstInvalid) firstInvalid = form.elements[name];
        });
        if (firstInvalid) firstInvalid.focus();
        return firstInvalid === null;
    }

    // Live feedback: validate a field when the user leaves it, and re-check while typing once it has an error.
    Object.keys(fields).forEach(function (name) {
        const input = form.elements[name];
        input.addEventListener('blur', function () { validateField(name); });
        input.addEventListener('input', function () {
            if (input.closest('.field').classList.contains('has-error')) validateField(name);
            successBox.hidden = true;
        });
    });

    function showSuccess(text) {
        successBox.querySelector('[data-success-text]').textContent = text;
        successBox.hidden = false;
        successBox.focus();
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        alertBox.hidden = true;
        successBox.hidden = true;

        if (!validateAll()) {
            alertBox.textContent = 'Please correct the highlighted fields and try again.';
            alertBox.hidden = false;
            return;
        }

        submitBtn.disabled = true;
        submitLabel.textContent = 'Sending…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });
            const data = await response.json().catch(function () { return {}; });

            if (response.ok) {
                form.reset();
                showSuccess(data.message || 'Thank you! Your message has been sent.');
            } else if (response.status === 422 && data.errors) {
                // Show Laravel's server-side validation messages under each field.
                Object.keys(data.errors).forEach(function (name) {
                    if (fields[name]) setError(name, data.errors[name][0]);
                });
                alertBox.textContent = data.message || 'Please check the form and try again.';
                alertBox.hidden = false;
            } else if (response.status === 419) {
                alertBox.textContent = 'Your session expired. Please refresh the page and try again.';
                alertBox.hidden = false;
            } else if (response.status === 429) {
                alertBox.textContent = 'Too many messages sent. Please wait a minute and try again.';
                alertBox.hidden = false;
            } else {
                throw new Error('Request failed');
            }
        } catch (error) {
            alertBox.textContent = 'Sorry, your message could not be sent right now. Please try again later.';
            alertBox.hidden = false;
        } finally {
            submitBtn.disabled = false;
            submitLabel.textContent = 'Send Message';
        }
    });
})();
