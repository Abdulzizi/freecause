/** Busy feedback for native petition signing POSTs. */
(function () {
    'use strict';

    var pending = new Map();

    function restore() {
        pending.forEach(function (state, form) {
            state.button.disabled = false;
            state.button.removeAttribute('aria-busy');
            state.button.style.opacity = state.opacity;
            state.spinner.remove();
            form.removeAttribute('aria-busy');
        });
        pending.clear();
    }

    // Bubble after the form's guest validation handler has had a chance to cancel.
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || !form.hasAttribute('data-sign-busy') || event.defaultPrevented) {
            return;
        }
        if (pending.has(form)) {
            event.preventDefault();
            return;
        }

        var button = event.submitter || form.querySelector('button[type="submit"]');
        if (!button || button.form !== form || button.disabled) {
            return;
        }

        var error = form.querySelector('[data-sign-network-error]');
        if (navigator.onLine === false) {
            event.preventDefault();
            if (error) error.hidden = false;
            return;
        }
        if (error) error.hidden = true;

        var spinner = document.createElement('span');
        spinner.className = 'fc-sign-spinner';
        spinner.setAttribute('aria-hidden', 'true');
        pending.set(form, { button: button, spinner: spinner, opacity: button.style.opacity });
        button.prepend(spinner);
        button.style.opacity = '0.65';
        button.setAttribute('aria-busy', 'true');
        form.setAttribute('aria-busy', 'true');
        button.disabled = true;
        // Leave the native POST intact. Do not unlock on a timer: a slow request
        // could still succeed, making an automatic retry a duplicate signature.
    });

    // Back/forward cache restores the original document, including disabled buttons.
    window.addEventListener('pageshow', restore);
})();
