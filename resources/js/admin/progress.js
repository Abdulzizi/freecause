const SHOW_DELAY = 120;
const MIN_VISIBLE = 150;

let pending = 0;
let visible = false;
let showTimer = null;
let hideTimer = null;
let shownAt = 0;
let bound = false;

const bar = () => document.getElementById('fc-nav-progress');

function show() {
    const el = bar();
    if (!el) return;
    visible = true;
    shownAt = Date.now();
    el.hidden = false;
}

function start() {
    pending += 1;
    if (hideTimer) {
        window.clearTimeout(hideTimer);
        hideTimer = null;
    }
    if (visible || showTimer) return;
    showTimer = window.setTimeout(() => {
        showTimer = null;
        if (pending > 0) show();
    }, SHOW_DELAY);
}

function done() {
    pending = Math.max(0, pending - 1);
    if (pending > 0) return;
    if (showTimer) {
        window.clearTimeout(showTimer);
        showTimer = null;
    }
    if (!visible) return;
    hideTimer = window.setTimeout(() => {
        hideTimer = null;
        visible = false;
        const el = bar();
        if (el) el.hidden = true;
    }, Math.max(0, MIN_VISIBLE - (Date.now() - shownAt)));
}

function reset() {
    pending = 0;
    visible = false;
    if (showTimer) window.clearTimeout(showTimer);
    if (hideTimer) window.clearTimeout(hideTimer);
    showTimer = null;
    hideTimer = null;
    const el = bar();
    if (el) el.hidden = true;
}

function sameOrigin(href) {
    try {
        return new URL(href, window.location.href).origin === window.location.origin;
    } catch (_) {
        return false;
    }
}

function onDocumentClick(event) {
    if (event.defaultPrevented || event.button !== 0) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || link.closest('[data-progress-off]')) return;
    if (link.hasAttribute('download')) return;
    if (link.target && link.target.toLowerCase() !== '_self') return;
    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#')) return;
    if (!sameOrigin(href)) return;
    start();
}

function onDocumentSubmit(event) {
    if (event.defaultPrevented) return;
    const form = event.target instanceof Element ? event.target.closest('form') : null;
    if (!form || form.closest('[data-progress-off]')) return;
    if (!sameOrigin(form.getAttribute('action') || window.location.href)) return;
    start();
}

export function init() {
    if (bound) return;
    bound = true;

    window.fcProgress = { start, done };

    window.addEventListener('pageshow', reset);
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('submit', onDocumentSubmit);

    const nativeFetch = window.fetch;
    if (typeof nativeFetch === 'function') {
        window.fetch = function (...args) {
            const input = args[0];
            let origin = true;
            try {
                const url = input instanceof Request ? input.url : String(input);
                origin = sameOrigin(url);
            } catch (_) {
                origin = true;
            }
            if (!origin) return nativeFetch.apply(this, args);
            start();
            return nativeFetch.apply(this, args).finally(done);
        };
    }
}
