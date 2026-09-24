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
