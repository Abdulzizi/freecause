const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function run(store, user, description, listeners, timers) {
    const attrs = { 'data-draft-locale': 'en', 'data-draft-id': 'new', 'data-draft-user': user };
    const title = { name: 'title', id: 'title', tagName: 'INPUT', value: '', getAttribute: () => 'text' };
    const desc = { name: 'description', id: 'petition_description', tagName: 'TEXTAREA', value: description || '', getAttribute: () => null };
    const form = {
        getAttribute: (k) => (k in attrs ? attrs[k] : null),
        querySelector: () => null,
        querySelectorAll: () => [title, desc],
        addEventListener(type, fn) { if (listeners) listeners[type] = fn; },
    };
    const localStorage = {
        getItem: (k) => (k in store ? store[k] : null),
        setItem: (k, v) => { store[k] = String(v); },
        removeItem: (k) => { delete store[k]; },
    };
    vm.runInNewContext(fs.readFileSync('public/legacy/js-v2/petition-autosave.js', 'utf8'), {
        document: { querySelector: () => form },
        window: { localStorage, setTimeout: timers ? (fn, ms) => { timers.push(ms); return timers.length; } : setTimeout, clearTimeout },
        JSON,
        Object,
    });
    return { title, desc };
}

test('restores description and scopes draft to the user', () => {
    const store = {
        fc_petition_draft_7_en_new: JSON.stringify({ title: 'T', description: '<p>Body</p>' }),
    };
    const mine = run(store, '7');
    assert.equal(mine.title.value, 'T');
    assert.equal(mine.desc.value, '<p>Body</p>');
    const other = run(store, '8');
    assert.equal(other.title.value, '');
    assert.equal(other.desc.value, '');
});

test('does not overwrite a non-empty description', () => {
    const store = {
        fc_petition_draft_7_en_new: JSON.stringify({ title: 'T', description: '<p>Draft</p>' }),
    };
    const r = run(store, '7', '<p>Server</p>');
    assert.equal(r.desc.value, '<p>Server</p>');
});

test('an input event schedules a debounced draft save', () => {
    const listeners = {};
    const timers = [];
    run({}, '7', '', listeners, timers);
    assert.equal(typeof listeners.input, 'function');
    listeners.input();
    assert.deepEqual(timers, [800]);
});
