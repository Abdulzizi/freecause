import Swal from 'sweetalert2';

// Client-facing toasts — smooth, brand-styled.
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

    // Server-flashed toasts delivered via data-* carriers: a single path for the
    // session('toast') carrier plus session('success') and session('error').
    const flash = document.getElementById('toast-flash');
    if (flash && window.clientToast) {
        const showToast = window.clientToast?.[flash.dataset.type] ?? window.clientToast?.info;
        showToast?.(flash.dataset.message);
        announce(flash.dataset.message);
    }

    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess && window.clientToast?.success) {
        window.clientToast.success(flashSuccess.dataset.message);
        announce(flashSuccess.dataset.message);
    }

    const flashError = document.getElementById('flash-error');
    if (flashError && window.clientToast?.error) {
        window.clientToast.error(flashError.dataset.message);
        announce(flashError.dataset.message);
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
}
