import { init as initCore } from './admin/core.js';
import { init as initBulk } from './admin/bulk.js';
import { init as initDrawer } from './admin/drawer.js';
import { init as initSidebar } from './admin/sidebar.js';
import { init as initEditors } from './admin/editors.js';

document.addEventListener('DOMContentLoaded', () => {
    initCore();
    initBulk();
    initDrawer();
    initSidebar();
    initEditors();
});
