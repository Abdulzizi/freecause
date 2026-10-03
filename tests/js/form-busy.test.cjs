const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup() {
    const handlers = {};
    const error = { hidden: true };
    const button = { disabled: false, style: { opacity: '' }, attrs: {}, children: [],
        setAttribute(k, v) { this.attrs[k] = v; },
        removeAttribute(k) { delete this.attrs[k]; },
        prepend(node) { this.children.push(node); } };
    const form = { tagName: 'FORM', enabled: true, attrs: {},
        hasAttribute() { return this.enabled; },
        querySelector(selector) { return selector.includes('network-error') ? error : button; },
        setAttribute(k, v) { this.attrs[k] = v; },
        removeAttribute(k) { delete this.attrs[k]; } };
    button.form = form;
    const navigator = { onLine: true };
    vm.runInNewContext(fs.readFileSync('public/legacy/js-v2/form-busy.js', 'utf8'), {
        Map, navigator,
        document: { addEventListener(k, fn) { handlers[k] = fn; },
            createElement() { return { setAttribute() {}, remove() { button.children.pop(); } }; } },
        window: { addEventListener(k, fn) { handlers[k] = fn; } },
    });
    function submit(defaultPrevented = false, submitter = button) {
        const event = { target: form, submitter, defaultPrevented,
            preventDefault() { this.defaultPrevented = true; } };
        handlers.submit(event);
        return event;
    }
    return { handlers, form, button, navigator, error, submit };
}

test('native POST proceeds once with spinner, dimming and busy semantics', () => {
    const s = setup();
    assert.equal(s.submit().defaultPrevented, false);
    assert.equal(s.button.disabled, true);
    assert.equal(s.button.children.length, 1);
    assert.equal(s.button.children[0].className, 'fc-sign-spinner');
    assert.equal(s.button.attrs['aria-busy'], 'true');
    assert.equal(s.button.style.opacity, '0.65');
    assert.equal(s.submit().defaultPrevented, true);
});
test('guest validation cancellation leaves the sign button usable', () => {
    const s = setup(); s.submit(true);
    assert.equal(s.button.disabled, false);
    assert.equal(s.button.children.length, 0);
});
test('unrelated forms and foreign submitters are unchanged', () => {
    const s = setup(); s.form.enabled = false; s.submit();
    assert.equal(s.button.disabled, false);
    s.form.enabled = true; s.submit(false, { form: {} });
    assert.equal(s.button.disabled, false);
});
test('offline request stays on the form with error and can retry online', () => {
    const s = setup(); s.navigator.onLine = false;
    assert.equal(s.submit().defaultPrevented, true);
    assert.equal(s.button.disabled, false);
    assert.equal(s.error.hidden, false);
    s.navigator.onLine = true;
    assert.equal(s.submit().defaultPrevented, false);
    assert.equal(s.error.hidden, true);
});
test('back navigation restores busy button and supports keyboard submit', () => {
    const s = setup(); s.submit(); s.handlers.pageshow();
    assert.equal(s.button.disabled, false);
    assert.equal(s.button.children.length, 0);
    assert.equal(s.button.style.opacity, '');
    assert.equal(s.button.attrs['aria-busy'], undefined);
    assert.equal(s.submit(false, null).defaultPrevented, false);
    assert.equal(s.button.disabled, true);
});
