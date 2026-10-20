const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const sourcePath = path.join(process.cwd(), 'resources/js/public/forms.js');

function makeError() {
    return { textContent: '', hidden: true };
}

function makeField(form, name, message) {
    const attrs = {};
    const field = {
        form,
        validity: { valid: false },
        validationMessage: message,
        _cls: false,
        _focused: false,
        _attrs: attrs,
        classList: {
            add() { field._cls = true; },
            remove() { field._cls = false; },
        },
        getAttribute(k) {
            if (k === 'name') return name;
            if (k === 'type') return field._type || null;
            return null;
        },
        setAttribute(k, v) { attrs[k] = v; },
        removeAttribute(k) { delete attrs[k]; },
        focus() { field._focused = true; },
    };
    return field;
}

function invalidEvent(target) {
    let prevented = false;
    return {
        target,
        preventDefault() { prevented = true; },
        get defaultPrevented() { return prevented; },
    };
}

async function setup(options = {}) {
    const handlers = {};
    const toastCalls = [];
    const successCalls = [];
    const forms = options.forms || [];
    const byId = options.byId || {};
    for (const form of forms) {
        if (!form.dataset) form.dataset = {};
        if (!form.querySelector) form.querySelector = () => null;
        if (!form.querySelectorAll) form.querySelectorAll = () => [];
        if (!form.addEventListener) {
            form._handlers = {};
            form.addEventListener = (type, fn) => { form._handlers[type] = fn; };
        }
    }
    const html = { dataset: {}, addEventListener(type, fn) { handlers['html:' + type] = fn; } };
    const fakeDocument = {
        documentElement: html,
        activeElement: null,
        addEventListener(type, fn) { handlers[type] = fn; },
        querySelectorAll(sel) {
            if (options.documentForms && String(sel) === 'form[data-validate]') return options.documentForms;
            return [];
        },
        querySelector() { return null; },
        getElementById(id) { return byId[id] || null; },
    };
    const fakeWindow = {
        setTimeout,
        clientToast: { error(msg) { toastCalls.push(msg); }, success(msg) { successCalls.push(msg); } },
    };
    const origDoc = globalThis.document;
    const origWin = globalThis.window;
    const origFormData = globalThis.FormData;
    globalThis.document = fakeDocument;
    globalThis.window = fakeWindow;
    if (options.stubFormData) globalThis.FormData = function () {};
    delete require.cache[require.resolve(sourcePath)];
    const mod = await import(pathToFileURL(sourcePath).href + '?t=' + Date.now());
    mod.init();
    return {
        handlers, handlersById(id) { return byId[id] && byId[id]._handlers; },
        forms, toastCalls, successCalls,
        restore() {
            if (origDoc === undefined) delete globalThis.document;
            else globalThis.document = origDoc;
            if (origWin === undefined) delete globalThis.window;
            else globalThis.window = origWin;
            if (origFormData === undefined) delete globalThis.FormData;
            else globalThis.FormData = origFormData;
        },
    };
}

test('native invalid batch: one toast, all fields get inline error/aria-invalid, retry re-toasts', async (t) => {
    const errorEls = { name: makeError(), email: makeError() };
    const form = {
        dataset: {},
        querySelector(sel) {
            const m = String(sel).match(/data-error-for="([^"]+)"/);
            return m ? errorEls[m[1]] || null : null;
        },
    };
    const s = await setup({ forms: [form] });
    t.after(s.restore);

    const fields = {
        name: makeField(form, 'name', 'Fill in name'),
        email: makeField(form, 'email', 'Bad email'),
    };

    assert.ok(s.handlers.invalid, 'invalid handler registered');
    assert.equal(form.noValidate, undefined, 'native validation stays enabled');

    const src = fs.readFileSync(sourcePath, 'utf8');
    assert.ok(!/noValidate\s*=\s*true/.test(src), 'no runtime noValidate assignment');

    const e1 = invalidEvent(fields.name);
    s.handlers.invalid(e1);
    const e2 = invalidEvent(fields.email);
    s.handlers.invalid(e2);

    assert.equal(e1.defaultPrevented, true);
    assert.equal(e2.defaultPrevented, true);
    assert.equal(s.toastCalls.length, 1, 'one toast per synchronous batch');
    assert.equal(s.toastCalls[0], 'Fill in name');
    assert.equal(fields.name._focused, true, 'first invalid control focused');
    assert.equal(fields.email._focused, false, 'deduped event does not steal focus');

    for (const key of ['name', 'email']) {
        assert.equal(errorEls[key].hidden, false, key + ' inline error shown');
        assert.ok(errorEls[key].textContent.length > 0, key + ' inline error text set');
        assert.equal(fields[key]._attrs['aria-invalid'], 'true', key + ' aria-invalid set');
        assert.equal(fields[key]._cls, true, key + ' invalid class set');
    }

    await new Promise((r) => setTimeout(r, 10));
    assert.equal(form.dataset.fcInvalidShown, undefined, 'dedupe resets after batch');

    s.handlers.invalid(invalidEvent(fields.email));
    assert.equal(s.toastCalls.length, 2, 'retry without edits toasts again');
    assert.equal(s.toastCalls[1], 'Bad email');
});

test('invalid field without data-error-for still gets invalid styling and aria', async (t) => {
    const form = { dataset: {}, querySelector() { return null; } };
    const s = await setup({ forms: [form] });
    t.after(s.restore);

    const field = makeField(form, 'lonely', 'Pick something');
    const e = invalidEvent(field);
    s.handlers.invalid(e);

    assert.equal(e.defaultPrevented, true);
    assert.equal(field._cls, true, 'invalid class set without error element');
    assert.equal(field._attrs['aria-invalid'], 'true', 'aria-invalid set');
    assert.deepEqual(s.toastCalls, ['Pick something']);
    assert.equal(field._focused, true, 'focus moves to invalid control');
});

test('empty validationMessage keeps state but skips toast and inline text', async (t) => {
    const errorEl = makeError();
    const form = {
        dataset: {},
        querySelector(sel) {
            return String(sel).includes('data-error-for="blank"') ? errorEl : null;
        },
    };
    const s = await setup({ forms: [form] });
    t.after(s.restore);

    const field = makeField(form, 'blank', '');
    const e = invalidEvent(field);
    s.handlers.invalid(e);

    assert.equal(e.defaultPrevented, true);
    assert.equal(field._cls, true, 'invalid class set');
    assert.equal(field._attrs['aria-invalid'], 'true', 'aria-invalid set');
    assert.equal(errorEl.textContent, '', 'no empty inline message written');
    assert.equal(errorEl.hidden, true, 'inline error stays hidden');
    assert.equal(s.toastCalls.length, 0, 'no toast for empty message');
    assert.equal(field._focused, true, 'first invalid control still focused when message empty');
});

test('data-validate form has no custom submit listener; blur uses native validity', async (t) => {
    function makeValidateField(form, name, kind) {
        const listeners = {};
        const field = {
            form,
            value: kind === 'bad-email' ? 'not-an-email' : '',
            validity: kind === 'bad-email'
                ? { valid: false, valueMissing: false, typeMismatch: true }
                : kind === 'missing'
                    ? { valid: false, valueMissing: true, typeMismatch: false }
                    : { valid: true, valueMissing: false, typeMismatch: false },
            validationMessage: kind === 'bad-email' ? 'Enter a valid email address' : 'Fill out this field',
            _cls: false,
            classList: {
                add() { field._cls = true; },
                remove() { field._cls = false; },
            },
            getAttribute(k) {
                if (k === 'name') return name;
                if (k === 'type') return kind === 'bad-email' ? 'email' : 'text';
                return null;
            },
            hasAttribute(k) { return k === 'required' && kind === 'missing'; },
            addEventListener(type, fn) { listeners[type] = fn; },
        };
        return { field, listeners };
    }

    const errorEl = makeError();
    const entries = [];
    const form = {
        dataset: { fcValidateBound: undefined, msgEmail: 'Localized email msg' },
        querySelector(sel) {
            return String(sel).includes('data-error-for="email"') ? errorEl : null;
        },
        querySelectorAll(sel) {
            if (String(sel) === 'input[name], textarea[name], select[name]') return entries.map((e) => e.field);
            return [];
        },
    };
    entries.push(makeValidateField(form, 'email', 'bad-email'));

    const s = await setup({ forms: [form], documentForms: [form] });
    t.after(s.restore);

    assert.equal(form.dataset.fcValidateBound, '1', 'validator block bound');
    assert.equal(form._handlers.submit, undefined, 'no custom submit listener');
    assert.ok(!/validators\.push|emailPattern/.test(fs.readFileSync(sourcePath, 'utf8')), 'no custom email regex or validator queue');

    const { field, listeners } = entries[0];
    assert.ok(listeners.blur && listeners.input, 'blur/input inline feedback kept');
    listeners.blur();
    assert.equal(field._cls, true, 'invalid class on blur');
    assert.equal(errorEl.textContent, 'Localized email msg', 'translated msg wins over browser text');
    assert.equal(errorEl.hidden, false);
});

test('corrected control clears style, aria flag, and inline message', async (t) => {
    const errorEl = makeError();
    const members = [];
    const form = {
        dataset: {},
        querySelector(sel) {
            return String(sel).includes('data-error-for="choice"') ? errorEl : null;
        },
        querySelectorAll(sel) {
            return String(sel).includes('choice') ? members : [];
        },
    };
    const s = await setup({ forms: [form] });
    t.after(s.restore);

    const field = makeField(form, 'choice', 'Pick one');
    field._type = 'radio';
    members.push(field);
    const other = makeField(form, 'choice', '');
    other._type = 'radio';
    other._cls = true;
    other._attrs['aria-invalid'] = 'true';
    members.push(other);

    s.handlers.invalid(invalidEvent(field));
    assert.equal(field._cls, true);
    assert.equal(errorEl.hidden, false);

    field.validity.valid = true;
    s.handlers.input({ target: field });
    assert.equal(field._cls, false, 'invalid class cleared');
    assert.equal(field._attrs['aria-invalid'], undefined, 'aria-invalid cleared');
    assert.equal(errorEl.textContent, '', 'inline message cleared');
    assert.equal(errorEl.hidden, true, 'inline error hidden');
    assert.equal(other._cls, false, 'radio group member cleared');
    assert.equal(other._attrs['aria-invalid'], undefined, 'radio member aria cleared');
    assert.equal(form.dataset.fcInvalidShown, undefined, 'dedupe reset on input');
});

test('contact AJAX: success uses translated dataset.sent and reveals panel after server response', async (t) => {
    let resolveFetch;
    const pending = new Promise((r) => { resolveFetch = r; });
    const doc = { createElement: () => ({ appendChild() {}, dataset: {}, attrs: {} }) };
    const contactForm = doc.createElement('form');
    Object.assign(contactForm.dataset, { sending: 'Sending…', sent: 'Thanks!', failed: 'Failed' });
    contactForm._redirect = '';
    Object.defineProperty(contactForm, 'action', { get: () => contactForm._redirect, set: (v) => { contactForm._redirect = v; } });
    contactForm.action = '/en/contacts';
    contactForm.reset = function () { contactForm._reset = true; };
    const submitBtn = { value: 'Send', disabled: false };
    contactForm.appendChild(doc.createElement('input'));
    contactForm.querySelector = function () { return submitBtn; };
    const successPanel = { hidden: true, resetBtn: { focus() { successPanel._resetFocused = true; } }, querySelector() { return successPanel.resetBtn; } };
    const origFetch = globalThis.fetch;
    globalThis.fetch = () => pending;
    t.after(() => {
        if (origFetch === undefined) delete globalThis.fetch;
        else globalThis.fetch = origFetch;
    });

    const s = await setup({ forms: [contactForm], byId: { contactForm, contactSuccess: successPanel }, stubFormData: true });
    t.after(s.restore);

    const submit = s.handlersById('contactForm').submit;
    assert.ok(submit, 'contact submit handler registered');

    submit({ preventDefault() {} });

    assert.equal(contactForm._reset, undefined, 'form not reset before server responds');
    assert.equal(successPanel.hidden, true, 'panel stays hidden before server responds');
    assert.equal(s.successCalls.length, 0, 'no success toast before server responds');

    resolveFetch({
        ok: true,
        json: () => Promise.resolve({ status: 'ok' }),
    });
    await pending;
    await new Promise((r) => setTimeout(r, 0));

    assert.deepEqual(s.successCalls, ['Thanks!'], 'uses translated dataset.sent');
    assert.equal(successPanel.hidden, false, 'panel shown after response');
    assert.equal(contactForm.hidden, true, 'form hidden after response');
    assert.equal(successPanel._resetFocused, true, 'reset action receives focus');
});
