export function init(ctx) {
    const announce = ctx.announce;

    window.fcDebounce = function (fn, wait) {
        let timer = null;
        return function () {
            const ctx = this;
            const args = arguments;
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                fn.apply(ctx, args);
            }, wait);
        };
    };

    window.fcAbbreviate = function (n) {
        const num = Number(n);
        if (!Number.isFinite(num)) return String(n);
        const abs = Math.abs(num);
        const sign = num < 0 ? '-' : '';
        const units = [
            { v: 1e9, s: 'B' },
            { v: 1e6, s: 'M' },
            { v: 1e3, s: 'K' },
        ];
        for (let i = 0; i < units.length; i++) {
            if (abs >= units[i].v) {
                let str = (abs / units[i].v).toFixed(1);
                if (str.slice(-2) === '.0') str = str.slice(0, -2);
                return sign + str + units[i].s;
            }
        }
        return sign + String(abs);
    };

    document.querySelectorAll('[data-abbr]').forEach(function (el) {
        const value = Number(el.dataset.abbrValue);
        if (!Number.isFinite(value)) return;
        el.textContent = window.fcAbbreviate(value);
        el.setAttribute('title', new Intl.NumberFormat().format(value));
        if (el.hasAttribute('data-aria-announce')) {
            announce(el.textContent);
        }
    });
}
