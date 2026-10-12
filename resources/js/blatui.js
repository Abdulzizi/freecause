import Alpine from 'alpinejs';
import { registerBlatUI } from './blatui-core.js';

document.addEventListener('alpine:init', () => registerBlatUI(window.Alpine));

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
