import Swal from 'sweetalert2';

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

export function init(ctx) {
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

    ctx.announce = announce;

    const seen = new Set();
    function once(kind, message) {
        const key = kind + '|' + (message || '').trim();
        if (!message || seen.has(key)) return false;
        seen.add(key);
        return true;
    }
    function show(kind, message) {
        if (!window.clientToast || !once(kind, message)) return;
        window.clientToast[kind]?.(message);
        announce(message);
    }

    const flash = document.getElementById('toast-flash');
    if (flash) {
        const showToast = window.clientToast?.[flash.dataset.type] ?? window.clientToast?.info;
        const message = flash.dataset.message;
        if (message && once(flash.dataset.type, message)) {
            showToast?.(message);
            announce(message);
        }
    }

    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess) show('success', flashSuccess.dataset.message);

    const flashError = document.getElementById('flash-error');
    if (flashError) show('error', flashError.dataset.message);

    const validationErrors = document.getElementById('validation-errors');
    const hasVisibleErrorSummary = Array.from(document.querySelectorAll('[data-alert-variant="error"]')).some(function (el) {
        return !el.hasAttribute('hidden') && !el.closest('[hidden]');
    });
    if (validationErrors && !hasVisibleErrorSummary) show('error', validationErrors.dataset.message);

    document.querySelectorAll('[data-alert-variant="success"], [data-alert-variant="error"]').forEach(function (el) {
        if (el.hasAttribute('hidden') || el.closest('[hidden]')) return;
        if (el.hasAttribute('data-toast-keep')) return;
        show(el.dataset.alertVariant, el.textContent.trim().replace(/\s+/g, ' '));
        el.setAttribute('hidden', '');
    });

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
}
