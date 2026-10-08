function isEditableTarget(node) {
    if (!(node instanceof Element)) return false;
    return !!node.closest('input, textarea, select, [contenteditable=""], [contenteditable="true"]');
}

function openAdminModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.dataset.open = '1';
    modal.setAttribute('aria-hidden', 'false');
    const form = modal.querySelector('form');
    if (form) form.style.display = '';
    modal.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
}

function closeAdminModal(modal) {
    if (!modal) return;
    modal.dataset.open = '0';
    modal.setAttribute('aria-hidden', 'true');
}

function loadRichEditors() {
    const areas = document.querySelectorAll('textarea.fc-editor[data-ckeditor="rich"]');
    if (!areas.length) return;
    const activate = () => areas.forEach((area, index) => {
        try {
            if (!area.id) area.id = `fc-editor-${index}`;
            window.CKEDITOR.replace(area.id, {
                toolbar: [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'styles', items: ['Format'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table'] },
                    { name: 'tools', items: ['Source'] },
                ],
                height: 350,
            });
        } catch (_) { /* Leave the textarea usable if CKEditor cannot initialize. */ }
    });
    if (window.CKEDITOR) return activate();
    const script = document.createElement('script');
    script.src = 'https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js';
    script.onload = activate;
    document.head.appendChild(script);
}

function handleSmtpToggle() {
    const input = document.getElementById('smtp_pass_input');
    const toggle = document.getElementById('toggle_smtp_pass');
    if (!input || !toggle) return;
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    toggle.innerHTML = visible
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" fill="none"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>';
}

export function init() {
    loadRichEditors();

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const modal = document.querySelector('.fc-modal[data-open="1"]');
            if (modal) {
                closeAdminModal(modal);
                return;
            }
            const drawer = document.querySelector('.fc-drawer[data-open="1"]');
            if (drawer) {
                drawer.querySelector('.fc-drawer__close')?.click();
            }
            return;
        }
        if ((event.key === 's' || event.key === 'S') && !event.ctrlKey && !event.metaKey && !event.altKey && !isEditableTarget(event.target)) {
            const drawer = document.querySelector('.fc-drawer[data-open="1"]');
            if (!drawer) return;
            const submit = Array.from(drawer.querySelectorAll('button[type="submit"], input[type="submit"]'))
                .find((control) => control.offsetParent !== null && !control.disabled);
            if (!submit) return;
            event.preventDefault();
            submit.click();
        }
    });

    document.addEventListener('click', async (event) => {
        if (event.target.closest('#toggle_smtp_pass')) {
            handleSmtpToggle();
            return;
        }
        const createToggle = event.target.closest('[data-petition-create-toggle]');
        if (createToggle) {
            event.preventDefault();
            return openAdminModal('petition-create-modal');
        }
        const modalClose = event.target.closest('[data-admin-modal-close]');
        if (modalClose) {
            event.preventDefault();
            const modal = modalClose.closest('.fc-modal');
            if (modal) closeAdminModal(modal);
        }
    });
}
