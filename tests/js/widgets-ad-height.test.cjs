const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const SRC = fs.readFileSync('resources/js/public/widgets.js', 'utf8')
    .replace('export function init', 'function init');

function frame({ fluid }) {
    const contentWindow = {};
    return {
        contentWindow,
        style: {},
        hasAttribute(name) {
            return name === 'data-ad-fluid' && fluid;
        },
    };
}

function setup(frames) {
    let messageHandler = null;
    const document = {
        addEventListener() {},
        querySelectorAll(sel) {
            return sel === '.fc-ad iframe' ? frames : [];
        },
        querySelector() {
            return null;
        },
    };
    const window = {
        addEventListener(type, fn) {
            if (type === 'message') messageHandler = fn;
        },
        requestAnimationFrame() {},
        location: { assign() {} },
    };
    const sandbox = { document, window, Intl };
    vm.createContext(sandbox);
    vm.runInContext(`${SRC}\n;globalThis.__init = init;`, sandbox);
    sandbox.__init({ announce() {}, prefersReducedMotion: true });
    return {
        fire(height) {
            messageHandler({ data: { fcAdHeight: height }, source: frames[0].contentWindow });
        },
    };
}

test('fluid ad frame caps its resized height at 320', () => {
    const fluid = frame({ fluid: true });
    const s = setup([fluid]);
    s.fire(1000);
    assert.equal(fluid.style.height, '320px');
    assert.equal(fluid.style.maxHeight, '320px');
});

test('non-fluid ad frame keeps the 1000 cap', () => {
    const banner = frame({ fluid: false });
    const s = setup([banner]);
    s.fire(5000);
    assert.equal(banner.style.height, '1000px');
    assert.equal(banner.style.maxHeight, '1000px');
});
