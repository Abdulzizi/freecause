import './blatui.js';
import './admin/confirm.js';
import { init as initCore } from './admin/core.js';
import { init as initProgress } from './admin/progress.js';
import { init as initBulk } from './admin/bulk.js';
import { init as initDrawer } from './admin/drawer.js';
import { init as initEditors } from './admin/editors.js';

document.addEventListener('DOMContentLoaded', () => {
    initCore();
    initProgress();
    initBulk();
    initDrawer();
    initEditors();
});
