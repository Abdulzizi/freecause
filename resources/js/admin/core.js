import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const notify = (kind, message) => window.toast?.[kind]?.(message);

async function confirmAction(message, title = 'Please confirm') {
    if (window.fcConfirm) {
        return window.fcConfirm(message, title);
    }

    const result = await Swal.fire({
        icon: 'warning',
        title,
        text: message,
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#b91c1c',
    });
    return result.isConfirmed;
}

async function jsonPost(url, payload = {}) {
    if (!url) throw new Error('This action is unavailable. Refresh the page and try again.');

    const response = await fetch(url || '', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(payload),
    });

    const contentType = response.headers.get('content-type') || '';
    if (response.redirected || !/^application\/(?:[\w.-]+\+)?json\b/i.test(contentType)) {
        throw new Error(response.redirected || /text\/html/i.test(contentType)
            ? 'Your session may have expired. Refresh the page and sign in again.'
            : 'The server returned an unexpected response. Please try again.');
    }

    let data;
    try {
        data = await response.json();
    } catch (_) {
        throw new Error('The server returned an invalid response. Please try again.');
    }
    if (!data || typeof data !== 'object' || Array.isArray(data)) {
        throw new Error('The server returned an invalid response. Please try again.');
    }
    if (!response.ok || data.ok !== true) {
        const message = data.error || data.msg || Object.values(data.errors || {}).flat()[0] || 'The action could not be completed.';
        throw new Error(message);
    }
    return data;
}

async function banDetails() {
    const result = await Swal.fire({
        title: 'Ban details',
        html: '<input id="fc-ban-reason" class="swal2-input" maxlength="255" placeholder="Reason (optional)"><input id="fc-ban-days" class="swal2-input" inputmode="numeric" value="0" placeholder="Days (0 = permanent)">',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#b91c1c',
        focusConfirm: false,
        preConfirm: () => {
            const daysText = document.getElementById('fc-ban-days')?.value?.trim() || '0';
            if (!/^\d+$/.test(daysText) || Number(daysText) > 36500) {
                Swal.showValidationMessage('Enter a whole number of days from 0 to 36500.');
                return false;
            }
            return {
                reason: document.getElementById('fc-ban-reason')?.value?.trim() || null,
                days: Number(daysText),
            };
        },
    });
    return result.isConfirmed ? result.value : null;
}

function setLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.dataset.adminLoadingHtml = button.innerHTML;
        button.disabled = true;
        button.style.cursor = 'wait';
        button.style.opacity = '.7';
        return;
    }
    if (button.dataset.adminLoadingHtml) button.innerHTML = button.dataset.adminLoadingHtml;
    button.disabled = false;
    button.style.cursor = '';
    button.style.opacity = '';
}

async function copyText(value) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);
        return;
    }
    const field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.left = '-9999px';
    document.body.appendChild(field);
    field.select();
    const copied = document.execCommand('copy');
    field.remove();
    if (!copied) throw new Error('Copy failed.');
}

export { csrf, notify, confirmAction, jsonPost, banDetails, setLoading };

export function init() {
    document.querySelectorAll('[data-admin-flash]').forEach((node) => {
        notify(node.dataset.adminFlash || 'info', node.dataset.message || '');
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-admin-autosubmit]')) event.target.form?.requestSubmit();
    });

    document.addEventListener('mouseover', (event) => {
        const row = event.target.closest('[data-admin-row-hover]');
        if (row && !row.contains(event.relatedTarget)) row.style.background = '#f7f7f7';
    });
    document.addEventListener('mouseout', (event) => {
        const row = event.target.closest('[data-admin-row-hover]');
        if (row && !row.contains(event.relatedTarget)) row.style.background = '';
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-admin-confirm]');
        if (!form || form.dataset.adminConfirmed === '1') return;
        event.preventDefault();
        if (await confirmAction(form.dataset.adminConfirm || 'Continue?')) {
            form.dataset.adminConfirmed = '1';
            form.requestSubmit();
        }
    });

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        const form = event.target.closest('form');
        if (!form || form.hasAttribute('data-no-pending')) return;
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button[type=submit], input[type=submit]').forEach((control) => {
            control.disabled = true;
            control.setAttribute('aria-busy', 'true');
        });
    });

    document.addEventListener('click', async (event) => {
        const payloadToggle = event.target.closest('[data-payload-toggle]');
        if (payloadToggle) {
            const row = document.getElementById(`payload-${payloadToggle.dataset.payloadToggle}`);
            if (row) row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            return;
        }
        const payloadCopy = event.target.closest('[data-payload-copy]');
        if (payloadCopy) {
            const content = document.getElementById(`payload-content-${payloadCopy.dataset.payloadCopy}`);
            if (!content) return;
            try {
                await copyText(content.textContent || '');
                notify('success', 'Copied!');
            } catch (_) {
                notify('error', 'Copy failed.');
            }
            return;
        }
    });

    document.querySelectorAll('form').forEach((form) => {
        const controls = form.querySelectorAll('[data-loading]');
        if (!controls.length || form.dataset.adminLoadingBound === '1') return;
        form.dataset.adminLoadingBound = '1';
        form.addEventListener('submit', (event) => {
            window.setTimeout(() => {
                if (event.defaultPrevented) return;
                controls.forEach((control) => {
                    if (control.dataset.adminLoadingActive === '1') return;
                    control.dataset.adminLoadingActive = '1';
                    if (typeof control.disabled === 'boolean') control.disabled = true;
                    control.setAttribute('aria-busy', 'true');
                    const label = control.querySelector('.fcui-btn__label');
                    if (label && control.dataset.loadingText) label.textContent = control.dataset.loadingText;
                });
            }, 0);
        });
    });
}
