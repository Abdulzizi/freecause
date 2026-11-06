const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function loadBulk(confirmed = true) {
    const calls = { confirm: [], notify: [], post: [] };
    const source = fs.readFileSync('resources/js/admin/bulk.js', 'utf8')
        .replace(/^import\s+.*$/gm, '')
        .replace('export function init', 'function init');

    const handlers = {};
    const button = {
        dataset: { count: '3', id: '7', source: 'en', url: '/admin/petitions/translate-missing' },
        disabled: false,
    };
    const sandbox = {
        console,
        document: {
            addEventListener(name, fn) { handlers[name] = fn; },
            querySelectorAll() { return []; },
        },
        window: { setTimeout() { return 0; } },
        confirmAction(message, title) {
            calls.confirm.push({ message, title });
            return Promise.resolve(confirmed);
        },
        jsonPost(url, payload) {
            calls.post.push({ url, payload });
            return Promise.resolve({ created: 3 });
        },
        notify(kind, message) { calls.notify.push({ kind, message }); },
        csrf: () => '',
        banDetails: async () => null,
        setLoading() {},
        Swal: {},
    };
    vm.runInNewContext(source, sandbox);
    sandbox.init();

    const event = { target: { closest: (sel) => (sel === '[data-translate-missing]' ? button : null) } };
    return { calls, button, click: () => handlers.click(event) };
}

test('translate-missing copy is described as public and in the source language', async () => {
    const s = loadBulk();
    await s.click();

    assert.equal(s.calls.confirm.length, 1);
    const { message, title } = s.calls.confirm[0];
    assert.equal(title, 'Translate to missing locales');
    assert.match(message, /3 missing active locale/);
    assert.match(message, /go live immediately/i);
    assert.match(message, /source language/i);
    assert.doesNotMatch(message, /draft/i);

    assert.equal(s.calls.post.length, 1);
    assert.equal(s.calls.post[0].url, '/admin/petitions/translate-missing');
    assert.equal(s.calls.post[0].payload.id, '7');
    assert.equal(s.calls.post[0].payload.locale, 'en');

    const success = s.calls.notify.find((entry) => entry.kind === 'success');
    assert.ok(success);
    assert.match(success.message, /3/);
    assert.match(success.message, /source language/i);
    assert.doesNotMatch(success.message, /draft/i);
});

test('cancelling the confirm sends nothing', async () => {
    const s = loadBulk(false);
    await s.click();

    assert.deepEqual(s.calls.post, []);
    assert.deepEqual(s.calls.notify, []);
    assert.equal(s.button.disabled, false);
});
