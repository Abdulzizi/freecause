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

    const liveRegion = (function () {
        let region = document.getElementById('fc-live');
        if (!region) {
            region = document.createElement('div');
            region.id = 'fc-live';
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('aria-atomic', 'true');
            region.className = 'fcui-sr-only';
            document.body.appendChild(region);
        }
        return region;
    })();

    function announce(message) {
        if (!message) return;
        liveRegion.textContent = '';
        window.setTimeout(function () {
            liveRegion.textContent = message;
        }, 50);
    }

    if (window.clientToast && !window.clientToast.__fcAnnounceWrapped) {
        ['success', 'error', 'warning', 'info'].forEach(function (kind) {
            const original = window.clientToast[kind];
            if (typeof original !== 'function') return;
            window.clientToast[kind] = function (msg) {
                announce(msg);
                return original(msg);
            };
        });
        window.clientToast.__fcAnnounceWrapped = true;
    }

    if (flash && window.clientToast) {
        announce(flash.dataset.message);
    }

    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess && window.clientToast?.success) {
        window.clientToast.success(flashSuccess.dataset.message);
    }

    const flashError = document.getElementById('flash-error');
    if (flashError && window.clientToast?.error) {
        window.clientToast.error(flashError.dataset.message);
    }

    document.querySelectorAll('ins.adsbygoogle[data-adsense]').forEach(function (slot) {
        if (slot.dataset.fcAdsPushed === '1') return;
        slot.dataset.fcAdsPushed = '1';
        try {
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        } catch (error) {
            slot.dataset.fcAdsPushed = '';
        }
    });

    document.querySelectorAll('.fc-sign-form a[href]').forEach(function (link) {
        if (link.dataset.fcPolicyBound === '1') return;
        const href = link.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || href.toLowerCase().indexOf('javascript:') === 0) return;
        link.dataset.fcPolicyBound = '1';
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener noreferrer');
        link.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            window.open(link.href, '_blank', 'noopener,noreferrer');
        });
    });

    const prefersReducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;

    document.querySelectorAll('form').forEach(function (form) {
        if (form.dataset.fcLoadingBound === '1') return;
        if (!form.querySelector('[data-loading]')) return;
        form.dataset.fcLoadingBound = '1';

        form.addEventListener('submit', function (event) {
            window.setTimeout(function () {
                if (event.defaultPrevented) return;

                form.querySelectorAll('[data-loading]').forEach(function (el) {
                    if (el.dataset.fcLoadingActive === '1') return;
                    el.dataset.fcLoadingActive = '1';

                    if (typeof el.disabled === 'boolean') {
                        el.disabled = true;
                    }
                    el.setAttribute('aria-busy', 'true');

                    const label = el.querySelector('.fcui-btn__label');
                    const busyText = el.dataset.loadingText;
                    if (label && busyText) {
                        label.textContent = busyText;
                    }
                });
            }, 0);
        });
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-alert-dismiss]');
        if (!trigger) return;

        const alertBox = trigger.closest('[data-alert]');
        if (!alertBox) return;

        event.preventDefault();

        if (prefersReducedMotion) {
            alertBox.remove();
            return;
        }

        alertBox.setAttribute('data-alert-hiding', '');
        let removed = false;
        const finish = function () {
            if (removed) return;
            removed = true;
            alertBox.remove();
        };
        alertBox.addEventListener('transitionend', finish, { once: true });
        window.setTimeout(finish, 300);
    });

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

    const disconnectForm = document.querySelector('[data-google-disconnect]');
    if (disconnectForm) {
        const trigger = disconnectForm.querySelector('[data-google-disconnect-trigger]');
        const needsPassword = disconnectForm.hasAttribute('data-google-disconnect-needs-password');

        if (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                const d = disconnectForm.dataset;

                if (needsPassword) {
                    Swal.fire({
                        icon: 'info',
                        title: d.needTitle || 'Set a password first',
                        text: d.needText || 'Please set an account password before disconnecting Google, so you can still sign in.',
                        confirmButtonText: d.needOk || 'OK',
                    });
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: d.confirmTitle || 'Disconnect Google?',
                    text: d.confirmText || 'You will no longer be able to sign in with Google.',
                    showCancelButton: true,
                    confirmButtonText: d.confirmOk || 'Disconnect',
                    cancelButtonText: d.confirmCancel || 'Cancel',
                    confirmButtonColor: '#cc1f1f',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        disconnectForm.submit();
                    }
                });
            });
        }
    }

    const deleteForm = document.querySelector('[data-account-delete-form]');
    if (deleteForm) {
        const checkbox = deleteForm.querySelector('[data-account-delete-confirm]');
        const submitBtn = deleteForm.querySelector('[data-account-delete-submit]');

        if (checkbox && submitBtn) {
            submitBtn.disabled = true;
            checkbox.addEventListener('change', function () {
                submitBtn.disabled = !checkbox.checked;
            });

            submitBtn.addEventListener('click', function (event) {
                event.preventDefault();
                if (!checkbox.checked) {
                    return;
                }

                const d = deleteForm.dataset;
                Swal.fire({
                    icon: 'warning',
                    title: d.confirmTitle || 'Delete your account?',
                    text: d.confirmText || 'This permanently deletes your account and cannot be undone.',
                    showCancelButton: true,
                    confirmButtonText: d.confirmOk || 'Yes, delete my account',
                    cancelButtonText: d.confirmCancel || 'Cancel',
                    confirmButtonColor: '#b91c1c',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            });
        }
    }

    const scrollToPassword = document.querySelector('[data-scroll-to-password]');
    const passwordSection = document.getElementById('password-section');
    if (scrollToPassword && passwordSection) {
        scrollToPassword.addEventListener('click', function (event) {
            event.preventDefault();
            passwordSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const firstField = passwordSection.querySelector('input[name="new_password"]');
            if (firstField) {
                window.setTimeout(function () {
                    firstField.focus();
                }, 400);
            }
        });
    }

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
