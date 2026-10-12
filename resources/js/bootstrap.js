import axios from 'axios';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/client-toast.css';

import * as toast from './public/toast.js';
import * as util from './public/util.js';
import * as widgets from './public/widgets.js';
import * as navigation from './public/navigation.js';
import * as forms from './public/forms.js';
import * as account from './public/account.js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

document.addEventListener('DOMContentLoaded', function () {
    const ctx = {
        prefersReducedMotion: window.matchMedia
            ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
            : false,
    };

    toast.init(ctx);
    util.init(ctx);
    widgets.init(ctx);
    navigation.init(ctx);
    forms.init();
    account.init();
});
