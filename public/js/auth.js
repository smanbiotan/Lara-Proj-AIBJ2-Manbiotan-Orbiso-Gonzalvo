/* Show / hide password buttons on the Sign In and Register pages. */
(function () {
    'use strict';
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        const input = btn.parentElement.querySelector('input');
        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
})();
