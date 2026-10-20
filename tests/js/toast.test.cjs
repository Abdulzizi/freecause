const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const SRC = fs.readFileSync('resources/js/public/toast.js', 'utf8')
    .replace(/^import\s+.*$/m, '')
    .replace('export function init', 'function init');

function makeAlert({ variant, text = '', hidden = false, keep = false, hiddenAncestor = false, signError = false }) {
    const el = {
        dataset: { alertVariant: variant },
        textContent: text,
        _hidden: hidden,
        _keep: keep,
        hasAttribute(name) {
            if (name === 'hidden') return el._hidden;
            if (name === 'data-toast-keep') return el._keep;
            if (name === 'data-sign-network-error') return signError;
            return false;
        },
        setAttribute(k) { if (k === 'hidden') el._hidden = true; },
        closest(sel) { return sel === '[hidden]' && hiddenAncestor ? {} : null; },
    };
    return el;
}

function setup({ alerts = [], validationMessage = null } = {}) {
    const toastCalls = [];
    const byId = {};
    if (validationMessage !== null) byId['validation-errors'] = { dataset: { message: validationMessage } };
    let live = null;
    const document = {
        getElementById(id) {
            if (id === 'fc-live') return live;
            return byId[id] || null;
        },
        createElement() {
            return { id: '', className: '', textContent: '', setAttribute() {} };
        },
        body: { appendChild(node) { live = node; } },
        querySelectorAll(sel) {
            if (sel.includes('[data-alert-variant="success"]')) return alerts.filter((el) => ['success', 'error'].includes(el.dataset.alertVariant));
            if (sel.includes('[data-alert-variant="error"]')) return alerts.filter((el) => el.dataset.alertVariant === 'error');
            return [];
        },
    };
    const window = {
        setTimeout,
        clientToast: undefined,
    };
    const sandbox = { document, window, Swal: { mixin() { return { fire() {} }; }, stopTimer() {}, resumeTimer() {} }, setTimeout, Array };
    vm.createContext(sandbox);
    vm.runInContext(`${SRC}\n;globalThis.__toastInit = init;`, sandbox);
    window.clientToast = {
        success(msg) { toastCalls.push(['success', msg]); },
        error(msg) { toastCalls.push(['error', msg]); },
        warning(msg) { toastCalls.push(['warning', msg]); },
        info(msg) { toastCalls.push(['info', msg]); },
    };
    sandbox.window = window;
    sandbox.document = document;
    sandbox.__toastInit.call(sandbox, {});
    return { toastCalls, document, window };
}

test('hidden sign network error does not suppress validation-errors toast', () => {
    const hiddenNet = makeAlert({ variant: 'error', text: 'connection error', hidden: true, signError: true });
    const s = setup({ alerts: [hiddenNet], validationMessage: 'Name is required.' });
    assert.deepEqual(s.toastCalls, [['error', 'Name is required.']]);
    assert.equal(hiddenNet._hidden, true);
});

test('visible success and error alerts toast matching kind, normalized, then hidden', () => {
    const ok = makeAlert({ variant: 'success', text: '  Saved\n   OK  ' });
    const bad = makeAlert({ variant: 'error', text: '\n Failed \t again\n' });
    const s = setup({ alerts: [ok, bad] });
    assert.deepEqual(s.toastCalls, [['success', 'Saved OK'], ['error', 'Failed again']]);
    assert.equal(ok._hidden, true);
    assert.equal(bad._hidden, true);
});

test('data-toast-keep success alert stays visible and does not toast', () => {
    const kept = makeAlert({ variant: 'success', text: 'Kept inline', keep: true });
    const s = setup({ alerts: [kept] });
    assert.deepEqual(s.toastCalls, []);
    assert.equal(kept._hidden, false);
});

test('warning alerts remain inline without toast', () => {
    const warn = makeAlert({ variant: 'warning', text: 'Check this' });
    const s = setup({ alerts: [warn] });
    assert.deepEqual(s.toastCalls, []);
    assert.equal(warn._hidden, false);
});
