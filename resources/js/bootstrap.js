import axios from 'axios';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/client-toast.css';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
    customClass: {
        popup: 'fc-swal-toast',
    },
});

window.toast = {
    success: (msg) => Toast.fire({ icon: 'success', title: msg }),
    error: (msg) => Toast.fire({ icon: 'error', title: msg }),
    warning: (msg) => Toast.fire({ icon: 'warning', title: msg }),
    info: (msg) => Toast.fire({ icon: 'info', title: msg }),
};

// Client-facing toasts — smooth, brand-styled, distinct from the admin Toast above.
const ClientToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4200,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
    customClass: {
        popup: 'fc-client-toast',
        timerProgressBar: 'fc-client-toast-progress',
    },
    showClass: {
        popup: 'fc-toast-show',
    },
    hideClass: {
        popup: 'fc-toast-hide',
    },
});

window.clientToast = {
    success: (msg) => ClientToast.fire({ icon: 'success', title: msg }),
    error: (msg) => ClientToast.fire({ icon: 'error', title: msg }),
    warning: (msg) => ClientToast.fire({ icon: 'warning', title: msg }),
    info: (msg) => ClientToast.fire({ icon: 'info', title: msg }),
};

// Public pages enforce a strict CSP (no inline scripts), so DOM behaviours live
// in the bundle instead of <script> blocks in the views.
document.addEventListener('DOMContentLoaded', function () {
    // Server-flashed toasts (session('toast')) delivered via a data-* carrier.
    const flash = document.getElementById('toast-flash');
    if (flash && window.clientToast) {
        const showToast = window.clientToast?.[flash.dataset.type] ?? window.clientToast?.info;
        showToast?.(flash.dataset.message);
    }

    // Petition sharing: use the device share sheet when available. The link's
    // mailto href remains a no-JavaScript fallback and never exposes contacts
    // to FreeCause.
    document.querySelectorAll('[data-share-petition]').forEach(function (shareLink) {
        shareLink.addEventListener('click', async function (event) {
            if (typeof navigator.share !== 'function') {
                return;
            }

            event.preventDefault();

            try {
                await navigator.share({
                    title: shareLink.dataset.shareTitle,
                    url: shareLink.dataset.shareUrl,
                });
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    window.location.assign(shareLink.href);
                }
            }
        });
    });

    // Profile danger-zone: Google disconnect reveal. The real confirm button and
    // the panel start hidden ('d-none' in markup) so that without JS the form
    // still works; here we add the two-step confirm interaction.
    const disconnectForm = document.querySelector('[data-google-disconnect]');
    if (disconnectForm) {
        const trigger = disconnectForm.querySelector('[data-google-disconnect-trigger]');
        const panel = disconnectForm.querySelector('[data-google-disconnect-panel]');
        const cancel = disconnectForm.querySelector('[data-google-disconnect-cancel]');
        const confirmBtn = disconnectForm.querySelector('[data-google-disconnect-confirm]');

        // The google-only variant has no trigger/panel; skip it safely.
        if (trigger && panel) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                panel.classList.remove('d-none');
                if (confirmBtn) confirmBtn.classList.remove('d-none');
                if (cancel) cancel.classList.remove('d-none');
                trigger.classList.add('d-none');
                if (confirmBtn) confirmBtn.focus();
            });

            if (cancel) {
                cancel.addEventListener('click', function (event) {
                    event.preventDefault();
                    panel.classList.add('d-none');
                    if (confirmBtn) confirmBtn.classList.add('d-none');
                    cancel.classList.add('d-none');
                    trigger.classList.remove('d-none');
                    trigger.focus();
                });
            }
            // confirmBtn is a normal submit button — leave its submit untouched.
        }
    }

    // Profile danger-zone: account-deletion button gating. JS disables the
    // submit until the user explicitly ticks the confirm box, so no-JS users
    // keep a working button.
    const deleteForm = document.querySelector('[data-account-delete-form]');
    if (deleteForm) {
        const checkbox = deleteForm.querySelector('[data-account-delete-confirm]');
        const submitBtn = deleteForm.querySelector('[data-account-delete-submit]');

        if (checkbox && submitBtn) {
            submitBtn.disabled = true;
            checkbox.addEventListener('change', function () {
                submitBtn.disabled = !checkbox.checked;
            });
        }
    }

    // Generic busy-submit guard: disable the button when its form submits to
    // prevent double submission. The delete submit uses a different hook
    // (data-account-delete-submit) so the two mechanisms stay independent.
    document.querySelectorAll('[data-busy-submit]').forEach(function (btn) {
        const form = btn.closest('form');
        if (!form) return;
        form.addEventListener('submit', function () {
            btn.disabled = true;
        });
    });

    // Contact form: submit over AJAX so the sender gets immediate feedback.
    const contactForm = document.getElementById('contactForm');
    if (!contactForm || !window.clientToast) return;

    contactForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = contactForm.querySelector('input[type="submit"]');
        const original = btn.value;
        btn.disabled = true;
        btn.value = contactForm.dataset.sending;

        fetch(contactForm.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(contactForm),
        })
            .then(function (res) {
                if (!res.ok) {
                    return res.json().catch(function () {
                        return {};
                    }).then(function (data) {
                        const msg = data.message
                            || (data.errors && Object.values(data.errors)[0]?.[0])
                            || contactForm.dataset.failed;
                        throw new Error(msg);
                    });
                }
                return res.json();
            })
            .then(function () {
                contactForm.reset();
                window.clientToast.success(contactForm.dataset.sent);
            })
            .catch(function (err) {
                window.clientToast.error(err.message || contactForm.dataset.failed);
            })
            .finally(function () {
                btn.disabled = false;
                btn.value = original;
            });
    });
});
