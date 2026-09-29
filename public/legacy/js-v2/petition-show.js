document.addEventListener('DOMContentLoaded', () => {
    const copyButton = document.getElementById('copyPetitionLink');

    copyButton?.addEventListener('click', async () => {
        const url = copyButton.dataset.url;

        try {
            await navigator.clipboard.writeText(url);
        } catch (error) {
            const input = document.createElement('textarea');
            input.value = url;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            input.remove();
        }

        copyButton.innerHTML = `<i class="fa fa-check"></i> ${copyButton.dataset.copiedLabel}`;
        copyButton.classList.add('fc-share-copy--copied');
    });

    const toggle = document.getElementById('widgetsToggle');
    const body = document.getElementById('widgetsBody');
    if (toggle && body && window.bootstrap) {
        body.addEventListener('shown.bs.collapse', () => {
            toggle.removeAttribute('data-bs-toggle');
            toggle.removeAttribute('data-bs-target');
            toggle.removeAttribute('aria-controls');
            toggle.style.cursor = 'default';
        }, { once: true });
    }

    // Guest sign-form progressive disclosure + client-side validation
    document.querySelectorAll('.fc-sign-form').forEach(form => {
        const emailInput = form.querySelector('input[name="email"]');
        const passwordInput = form.querySelector('input[name="password"]');
        if (!emailInput || passwordInput) return; // authed forms lack email; guest forms lack password

        const advanced = form.querySelector('.fc-sign-advanced');
        if (advanced) {
            emailInput.addEventListener('input', () => {
                if (emailInput.value.includes('@')) {
                    advanced.classList.remove('d-none');
                } else {
                    advanced.classList.add('d-none');
                }
            });
        }

        form.addEventListener('submit', event => {
            const name = form.querySelector('input[name="name"]')?.value.trim() || '';
            const email = emailInput.value.trim();
            const agree1 = form.querySelector('input[name="agree1"]')?.checked;
            const agree2 = form.querySelector('input[name="agree2"]')?.checked;
            const agree3 = form.querySelector('input[name="agree3"]')?.checked;

            const messages = [];
            let firstInvalid = null;

            if (!name) {
                messages.push('Please fill your name');
                if (!firstInvalid) firstInvalid = form.querySelector('input[name="name"]');
            }
            if (!/.+@.+\..+/.test(email)) {
                messages.push('Please enter a valid email');
                if (!firstInvalid) firstInvalid = emailInput;
            }
            if (!agree1 || !agree2 || !agree3) {
                messages.push('You must tick all three consent boxes');
                if (!firstInvalid) firstInvalid = form.querySelector('input[name="agree1"]');
            }

            if (messages.length === 0) return;

            event.preventDefault();
            let alert = form.querySelector('.fc-sign-error');
            if (!alert) {
                alert = document.createElement('div');
                alert.className = 'alert alert-danger mt-2 fc-sign-error';
                alert.setAttribute('role', 'alert');
                form.insertBefore(alert, form.firstChild);
            }
            alert.innerHTML = '<ul class="mb-0">' + messages.map(m => `<li>${m}</li>`).join('') + '</ul>';
            firstInvalid?.focus();
        });
    });
});
