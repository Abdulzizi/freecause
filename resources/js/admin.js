import '@hotwired/turbo';
import './blatui.js';
import './admin/confirm.js';
import { init as initCore } from './admin/core.js';
import { init as initProgress } from './admin/progress.js';
import { init as initBulk } from './admin/bulk.js';
import { init as initDrawer } from './admin/drawer.js';
import { init as initEditors } from './admin/editors.js';

let bootedBody = null;

function bootPage() {
    if (bootedBody === document.body) return;
    bootedBody = document.body;
    initCore();
    initProgress();
    initBulk();
    initDrawer();
    initEditors();
}

window.fcVisit = (url, options = {}) => {
    if (window.Turbo) {
        window.Turbo.visit(url, options);
        return;
    }
    window.location.href = url;
};

window.fcRefresh = () => {
    const scrollY = window.scrollY;
    if (!window.Turbo) {
        window.location.reload();
        return;
    }
    const restore = () => {
        window.scrollTo(0, scrollY);
        document.removeEventListener('turbo:load', restore);
    };
    document.addEventListener('turbo:load', restore);
    window.Turbo.visit(window.location.href, { action: 'replace' });
};

document.addEventListener('DOMContentLoaded', bootPage);

document.addEventListener('turbo:load', () => {
    if (bootedBody === document.body) return;
    if (window.Alpine) window.Alpine.initTree(document.body);
    bootPage();

    const inset = document.querySelector('[data-slot="sidebar-inset"]');
    if (inset) {
        inset.classList.remove('fc-page-enter');
        void inset.offsetWidth;
        inset.classList.add('fc-page-enter');
    }
});

document.addEventListener('turbo:before-render', () => {
    if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
        window.Alpine.destroyTree(document.body);
    }
});

document.addEventListener('turbo:before-cache', () => {
    if (window.fcProgress) window.fcProgress.reset();
    document.querySelectorAll(
        'body > [data-slot="sheet-content"], body > [data-slot="dialog-content"], ' +
        'body > [data-slot="alert-dialog-content"], body > [data-slot="dropdown-menu-content"], ' +
        'body > .swal2-container'
    ).forEach((node) => node.remove());
    document.body.style.overflow = '';
});
