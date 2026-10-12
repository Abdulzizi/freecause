import anchor from '@alpinejs/anchor';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import { computePosition, autoUpdate, flip, shift, offset as flOffset, size } from '@floating-ui/dom';

const themeStore = {
    darkMode: 'class',
    mode: localStorage.getItem('theme:mode') || 'light',
    base: localStorage.getItem('theme:base') || 'neutral',
    preset: localStorage.getItem('theme:preset') || 'default',
    radius: localStorage.getItem('theme:radius') || '0.625',
    font: localStorage.getItem('theme:font') || 'sans',
    shadow: localStorage.getItem('theme:shadow') || 'default',
    spacing: localStorage.getItem('theme:spacing') || 'default',
    tracking: localStorage.getItem('theme:tracking') || 'normal',
    inputStyle: localStorage.getItem('theme:inputStyle') || 'outline',
    fontHeading: localStorage.getItem('theme:fontHeading') || 'sans',

    init() {
        if (!localStorage.getItem('theme:mode')) {
            this.mode = this.darkMode === 'system' ? 'system' : 'light';
        }
        this.apply();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.mode === 'system') this.apply();
        });
        const defaults = { mode: this.darkMode === 'system' ? 'system' : 'light', base: 'neutral', preset: 'default', radius: '0.625', font: 'sans', shadow: 'default', spacing: 'default', tracking: 'normal', inputStyle: 'outline', fontHeading: 'sans' };
        window.addEventListener('storage', (e) => {
            if (!e.key || !e.key.startsWith('theme:')) return;
            const key = e.key.slice('theme:'.length);
            if (key in defaults) {
                this[key] = e.newValue ?? defaults[key];
                this.apply();
            }
        });
    },

    get isDark() {
        if (this.darkMode === false) return false;
        return this.mode === 'dark' || (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    },

    set(key, value) {
        this[key] = value;
        localStorage.setItem('theme:' + key, value);
        this.apply();
    },

    setMode(mode) { this.set('mode', mode); },
    setBase(base) { this.set('base', base); },
    setPreset(preset) { this.set('preset', preset); },
    setRadius(radius) { this.set('radius', radius); },
    setFont(font) { this.set('font', font); },
    setShadow(shadow) { this.set('shadow', shadow); },
    setSpacing(spacing) { this.set('spacing', spacing); },
    setTracking(tracking) { this.set('tracking', tracking); },
    setInputStyle(inputStyle) { this.set('inputStyle', inputStyle); },
    setFontHeading(fontHeading) { this.set('fontHeading', fontHeading); },

    toggle() {
        if (this.darkMode === false) return;
        this.setMode(this.isDark ? 'light' : 'dark');
    },

    randomize() {
        const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
        const fonts = ['sans', 'inter', 'geist', 'manrope', 'jakarta', 'space-grotesk', 'dm-sans', 'outfit', 'sora', 'lora', 'source-serif', 'system', 'serif', 'mono'];
        const body = pick(fonts);
        const next = {
            base: pick(['neutral', 'stone', 'zinc', 'slate', 'gray', 'mauve', 'olive', 'mist', 'taupe']),
            preset: pick(['default', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky']),
            radius: pick(['0', '0.3', '0.5', '0.625', '0.75', '1']),
            inputStyle: pick(['outline', 'fill', 'inset']),
            font: body,
            fontHeading: Math.random() < 0.6 ? 'sans' : pick(fonts),
            shadow: pick(['none', 'sm', 'default', 'lg', 'xl']),
            spacing: pick(['compact', 'default', 'comfortable']),
            tracking: pick(['tight', 'normal', 'wide']),
        };
        Object.entries(next).forEach(([k, v]) => {
            this[k] = v;
            localStorage.setItem('theme:' + k, v);
        });
        this.apply();
    },

    reset() {
        ['mode', 'base', 'preset', 'radius', 'font', 'shadow', 'spacing', 'tracking', 'inputStyle', 'fontHeading'].forEach((k) =>
            localStorage.removeItem('theme:' + k),
        );
        this.mode = this.darkMode === 'system' ? 'system' : 'light';
        this.base = 'neutral';
        this.preset = 'default';
        this.radius = '0.625';
        this.font = 'sans';
        this.shadow = 'default';
        this.spacing = 'default';
        this.tracking = 'normal';
        this.inputStyle = 'outline';
        this.fontHeading = 'sans';
        this.apply();
    },

    apply() {
        const root = document.documentElement;
        if (this.darkMode !== false) {
            root.classList.toggle('dark', this.isDark);
        }
        this.attr(root, 'data-base', this.base, 'neutral');
        this.attr(root, 'data-theme', this.preset, 'default');
        this.attr(root, 'data-font', this.font, 'sans');
        this.attr(root, 'data-shadow', this.shadow, 'default');
        this.attr(root, 'data-spacing', this.spacing, 'default');
        this.attr(root, 'data-tracking', this.tracking, 'normal');
        this.attr(root, 'data-input-style', this.inputStyle, 'outline');
        this.attr(root, 'data-font-heading', this.fontHeading, 'sans');
        root.setAttribute('data-radius', this.radius);
    },

    attr(root, name, value, fallback) {
        if (value && value !== fallback) root.setAttribute(name, value);
        else root.removeAttribute(name);
    },
};

window.toast = (opts) => {
    const detail = typeof opts === 'string' ? { title: opts } : (opts || {});
    window.dispatchEvent(new CustomEvent('toast', { detail }));
};
['success', 'error', 'warning', 'info'].forEach((type) => {
    window.toast[type] = (opts) => {
        const detail = typeof opts === 'string' ? { title: opts } : (opts || {});
        window.dispatchEvent(new CustomEvent('toast', { detail: { ...detail, type } }));
    };
});
window.toast.loading = (opts) => {
    const detail = typeof opts === 'string' ? { title: opts } : (opts || {});
    window.dispatchEvent(new CustomEvent('toast', { detail: { duration: Infinity, ...detail, type: 'loading' } }));
};
let _toastPromiseId = 0;
window.toast.promise = (promise, msgs = {}) => {
    const id = 'tp-' + (++_toastPromiseId);
    const text = (m, v) => (typeof m === 'function' ? m(v) : m);
    window.dispatchEvent(new CustomEvent('toast', { detail: { id, type: 'loading', title: text(msgs.loading) || 'Loading…', duration: Infinity } }));
    Promise.resolve(typeof promise === 'function' ? promise() : promise)
        .then((data) => window.dispatchEvent(new CustomEvent('toast-update', { detail: { id, type: 'success', title: text(msgs.success, data) || 'Success', duration: 4000 } })))
        .catch((err) => window.dispatchEvent(new CustomEvent('toast-update', { detail: { id, type: 'error', title: text(msgs.error, err) || 'Error', duration: 4000 } })));
    return promise;
};

function _ymd(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}
function _parse(s) {
    if (!s) return null;
    if (s instanceof Date) return new Date(s.getFullYear(), s.getMonth(), s.getDate());
    const p = String(s).split('-').map(Number);
    return new Date(p[0], (p[1] || 1) - 1, p[2] || 1);
}
function _sameDay(a, b) { return a && b && _ymd(a) === _ymd(b); }
function _addMonths(d, n) { return new Date(d.getFullYear(), d.getMonth() + n, 1); }

const calendar = (cfg = {}) => ({
    calendarId: cfg.calendarId || null,
    mode: cfg.mode || 'single',
    locale: cfg.locale || 'en-US',
    todayLabel: cfg.todayLabel || 'Today, :date',
    selectedLabel: cfg.selectedLabel || 'selected',
    numberOfMonths: cfg.numberOfMonths || 1,
    weekStart: cfg.weekStart || 0,
    captionLayout: cfg.captionLayout || 'label',
    showWeekNumber: !!cfg.showWeekNumber,
    disableNavigation: !!cfg.disableNavigation,
    minDays: cfg.minDays ?? cfg.min ?? null,
    maxDays: cfg.maxDays ?? cfg.max ?? null,
    disabledCfg: cfg.disabled || null,
    minDate: cfg.minDate ? _parse(cfg.minDate) : null,
    maxDate: cfg.maxDate ? _parse(cfg.maxDate) : null,
    outOfRange: cfg.outOfRange || 'disable',
    modifiers: cfg.modifiers || {},
    modifiersClass: cfg.modifiersClass || {},
    startMonth: null,
    endMonth: null,
    view: null,
    weekdays: [],
    single: null,
    multiple: [],
    rangeFrom: null,
    rangeTo: null,
    hover: null,
    focusedDate: null,
    _hooks: null,
    _rootEl: null,

    init() {
        this.startMonth = cfg.startMonth ? _parse(cfg.startMonth) : null;
        this.endMonth = cfg.endMonth ? _parse(cfg.endMonth) : null;
        if (this.mode === 'single') this.single = cfg.value ? _parse(cfg.value) : null;
        else if (this.mode === 'multiple') this.multiple = (cfg.value || []).map(_parse);
        else if (this.mode === 'range') {
            this.rangeFrom = cfg.value && cfg.value.from ? _parse(cfg.value.from) : null;
            this.rangeTo = cfg.value && cfg.value.to ? _parse(cfg.value.to) : null;
        }
        let base = null;
        if (cfg.defaultMonth) base = _parse(cfg.defaultMonth.length === 7 ? cfg.defaultMonth + '-01' : cfg.defaultMonth);
        else base = this.single || this.rangeFrom || (this.multiple && this.multiple[0]) || this.startMonth || new Date();
        this.view = new Date(base.getFullYear(), base.getMonth(), 1);
        this.focusedDate = this.single || this.rangeFrom || (this.multiple && this.multiple[0]) || new Date(base.getFullYear(), base.getMonth(), base.getDate());
        const ref = new Date(2023, 0, 1);
        for (let i = 0; i < 7; i++) {
            const d = new Date(ref);
            d.setDate(ref.getDate() + ((this.weekStart + i + 7 - ref.getDay()) % 7));
            this.weekdays.push(d.toLocaleString(this.locale, { weekday: 'narrow' }));
        }
        const mine = (e) => {
            const d = e.detail;
            const id = d && typeof d === 'object' && !(d instanceof Date) ? d.id : null;
            return !id || id === this.calendarId;
        };
        const payload = (e) => {
            const d = e.detail;
            return d && typeof d === 'object' && !(d instanceof Date) ? (d.date ?? d.month ?? null) : d;
        };
        const onToday = (e) => {
            if (this.mode !== 'single' || !mine(e)) return;
            if (this.setValue(_ymd(new Date()))) this.notify('today');
        };
        const onSet = (e) => {
            if (this.mode !== 'single' || !mine(e)) return;
            const t = _parse(payload(e));
            if (t && this.setValue(_ymd(t))) this.notify('set');
        };
        const onSetRange = (e) => {
            if (this.mode !== 'range' || !mine(e)) return;
            const d = e.detail || {};
            if (this.setValue({ from: d.from ?? null, to: d.to ?? null })) this.notify('set-range');
        };
        const onGoto = (e) => {
            if (!mine(e)) return;
            const raw = payload(e);
            const m = _parse(typeof raw === 'string' && raw.length === 7 ? raw + '-01' : raw);
            if (m) this.view = new Date(m.getFullYear(), m.getMonth(), 1);
        };
        const onClear = (e) => {
            if (!mine(e)) return;
            if (this.setValue(this.mode === 'single' ? null : this.mode === 'multiple' ? [] : { from: null, to: null })) {
                this.notify('clear');
            }
        };
        this._hooks = {
            'calendar:today': onToday,
            'calendar:set': onSet,
            'calendar:set-range': onSetRange,
            'calendar:goto': onGoto,
            'calendar:clear': onClear,
        };
        this._rootEl = this.$root;
        for (const target of [window, this._rootEl]) {
            for (const name in this._hooks) target.addEventListener(name, this._hooks[name]);
        }
    },

    destroy() {
        if (!this._hooks) return;
        for (const target of [window, this._rootEl]) {
            for (const name in this._hooks) target.removeEventListener(name, this._hooks[name]);
        }
    },

    get value() {
        if (this.mode === 'single') return this.single ? _ymd(this.single) : null;
        if (this.mode === 'multiple') return this.multiple.map(_ymd);
        return { from: this.rangeFrom ? _ymd(this.rangeFrom) : null, to: this.rangeTo ? _ymd(this.rangeTo) : null };
    },
    set value(v) {
        if (this.setValue(v)) this.notify('value');
    },
    setValue(v) {
        if (this.mode === 'single') {
            const next = v ? _ymd(_parse(v)) : null;
            const changed = next !== (this.single ? _ymd(this.single) : null);
            this.single = next ? _parse(next) : null;
            if (this.single) this.reveal(this.single);
            return changed;
        }
        if (this.mode === 'multiple') {
            const list = (Array.isArray(v) ? v : v ? [v] : []).filter(Boolean).map(_parse);
            const changed = list.map(_ymd).join(',') !== this.multiple.map(_ymd).join(',');
            this.multiple = list;
            if (list[0]) this.reveal(list[0]);
            return changed;
        }
        const d = v || {};
        const from = d.from ? _parse(d.from) : null;
        const to = d.to ? _parse(d.to) : null;
        const key = (a, b) => this.fmt(a) + '/' + this.fmt(b);
        const changed = key(from, to) !== key(this.rangeFrom, this.rangeTo);
        this.rangeFrom = from;
        this.rangeTo = to;
        this.hover = null;
        if (from) this.reveal(from);
        return changed;
    },
    reveal(d) {
        if (!d) return;
        if (!this._viewContains(d)) this.view = new Date(d.getFullYear(), d.getMonth(), 1);
        this.focusedDate = d;
    },

    get months() {
        return Array.from({ length: this.numberOfMonths }, (_, i) => _addMonths(this.view, i));
    },
    monthLabel(m) { return m.toLocaleString(this.locale, { month: 'long', year: 'numeric' }); },
    weeksFor(m) {
        const year = m.getFullYear(), month = m.getMonth();
        const first = new Date(year, month, 1);
        const offset = (first.getDay() - this.weekStart + 7) % 7;
        const start = new Date(year, month, 1 - offset);
        const weeks = [];
        for (let w = 0; w < 6; w++) {
            const days = [];
            for (let d = 0; d < 7; d++) {
                const day = new Date(start);
                day.setDate(start.getDate() + w * 7 + d);
                day.__outside = day.getMonth() !== month;
                days.push(day);
            }
            weeks.push(days);
        }
        return weeks;
    },
    weekNumber(week) {
        const d = new Date(week[0]);
        d.setDate(d.getDate() + 3 - ((d.getDay() + 6) % 7));
        const firstThu = new Date(d.getFullYear(), 0, 4);
        return 1 + Math.round(((d - firstThu) / 86400000 - 3 + ((firstThu.getDay() + 6) % 7)) / 7);
    },

    isOutside(d, m) { return d.getMonth() !== m.getMonth(); },
    isToday(d) { return _sameDay(d, new Date()); },
    isOutOfRange(d) {
        return !!((this.minDate && d < this.minDate) || (this.maxDate && d > this.maxDate));
    },
    isDisabled(d) {
        if (this.outOfRange !== 'flag' && this.isOutOfRange(d)) return true;
        if (this.startMonth && d < new Date(this.startMonth.getFullYear(), this.startMonth.getMonth(), 1)) return true;
        if (this.endMonth && d > new Date(this.endMonth.getFullYear(), this.endMonth.getMonth() + 1, 0)) return true;
        const c = this.disabledCfg;
        if (!c) return false;
        if (Array.isArray(c)) return c.some((x) => _sameDay(_parse(x), d));
        if (typeof c === 'object') {
            if (c.before && d < _parse(c.before)) return true;
            if (c.after && d > _parse(c.after)) return true;
            if (c.dayOfWeek && c.dayOfWeek.includes(d.getDay())) return true;
        }
        return false;
    },
    isSelected(d) {
        if (this.mode === 'single') return _sameDay(this.single, d);
        if (this.mode === 'multiple') return this.multiple.some((x) => _sameDay(x, d));
        return this.rangeIs(d).selected;
    },
    rangeIs(d) {
        const from = this.rangeFrom, to = this.rangeTo || (this.rangeFrom && this.hover);
        if (!from) return {};
        const lo = to && to < from ? to : from;
        const hi = to && to < from ? from : to;
        const isStart = _sameDay(d, lo);
        const isEnd = hi ? _sameDay(d, hi) : isStart;
        const inMid = hi && d > lo && d < hi;
        return { selected: isStart || isEnd || inMid, start: isStart, end: isEnd, middle: inMid };
    },
    modifierClass(d) {
        let cls = '';
        for (const name in this.modifiers) {
            const list = this.modifiers[name] || [];
            if (list.some((x) => _sameDay(_parse(x), d))) cls += ' ' + (this.modifiersClass[name] || '');
        }
        return cls;
    },

    select(d) {
        if (this.isDisabled(d)) return;
        if (this.mode === 'single') {
            this.single = _sameDay(this.single, d) && !cfg.required ? null : d;
        } else if (this.mode === 'multiple') {
            const i = this.multiple.findIndex((x) => _sameDay(x, d));
            if (i >= 0) this.multiple.splice(i, 1);
            else if (!this.maxDays || this.multiple.length < this.maxDays) this.multiple.push(d);
        } else {
            if (!this.rangeFrom || (this.rangeFrom && this.rangeTo)) {
                this.rangeFrom = d; this.rangeTo = null;
            } else {
                let from = this.rangeFrom, to = d;
                if (to < from) [from, to] = [to, from];
                const span = Math.round((to - from) / 86400000) + 1;
                if (this.minDays && span < this.minDays) { this.rangeFrom = d; this.rangeTo = null; }
                else if (this.maxDays && span > this.maxDays) { this.rangeFrom = d; this.rangeTo = null; }
                else { this.rangeFrom = from; this.rangeTo = to; }
            }
        }
        this.notify('select');
    },

    notify(source) {
        const value = this.value;
        this.$dispatch('calendar:updated', { id: this.calendarId, mode: this.mode, value, source });
        if (source === 'select') this.$dispatch('calendar-change', value);
    },
    emit(value) { this.$dispatch('calendar-change', value); },

    get canPrev() {
        if (this.disableNavigation) return false;
        if (!this.startMonth) return true;
        return _addMonths(this.view, -1) >= new Date(this.startMonth.getFullYear(), this.startMonth.getMonth(), 1);
    },
    get canNext() {
        if (this.disableNavigation) return false;
        if (!this.endMonth) return true;
        return _addMonths(this.view, this.numberOfMonths) <= new Date(this.endMonth.getFullYear(), this.endMonth.getMonth(), 1);
    },
    prev() {
        if (!this.canPrev) return;
        this.view = _addMonths(this.view, -1);
        this.focusedDate = new Date(this.focusedDate.getFullYear(), this.focusedDate.getMonth() - 1, this.focusedDate.getDate());
    },
    next() {
        if (!this.canNext) return;
        this.view = _addMonths(this.view, 1);
        this.focusedDate = new Date(this.focusedDate.getFullYear(), this.focusedDate.getMonth() + 1, this.focusedDate.getDate());
    },

    isFocused(d) { return _sameDay(d, this.focusedDate); },
    dayLabel(d) {
        const base = d.toLocaleDateString(this.locale, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        let label = this.isToday(d) ? this.todayLabel.replace(':date', base) : base;
        if (this.isSelected(d)) label += ', ' + this.selectedLabel;
        return label;
    },
    _viewContains(d) {
        const start = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
        const end = new Date(this.view.getFullYear(), this.view.getMonth() + this.numberOfMonths, 0);
        return d >= start && d <= end;
    },
    _focus(d) {
        this.focusedDate = d;
        if (!this._viewContains(d)) this.view = new Date(d.getFullYear(), d.getMonth(), 1);
        const key = _ymd(d);
        setTimeout(() => {
            const el = this.$root.querySelector('[data-day="' + key + '"]');
            if (el) el.focus();
        }, 0);
    },
    moveFocus(days) {
        const d = new Date(this.focusedDate);
        d.setDate(d.getDate() + days);
        this._focus(d);
    },
    moveFocusMonths(n) {
        const d = new Date(this.focusedDate);
        d.setMonth(d.getMonth() + n);
        this._focus(d);
    },
    focusWeekEdge(end) {
        const d = new Date(this.focusedDate);
        const offset = (d.getDay() - this.weekStart + 7) % 7;
        d.setDate(d.getDate() + (end ? 6 - offset : -offset));
        this._focus(d);
    },
    isRtl() {
        const el = this._rootEl;
        return !!(el && el.nodeType === 1 && typeof getComputedStyle === 'function' && getComputedStyle(el).direction === 'rtl');
    },
    onDayKeydown(e, d) {
        const k = e.key;
        const step = this.isRtl() ? -1 : 1;
        if (k === 'ArrowLeft') this.moveFocus(-step);
        else if (k === 'ArrowRight') this.moveFocus(step);
        else if (k === 'ArrowUp') this.moveFocus(-7);
        else if (k === 'ArrowDown') this.moveFocus(7);
        else if (k === 'Home') this.focusWeekEdge(false);
        else if (k === 'End') this.focusWeekEdge(true);
        else if (k === 'PageUp') this.moveFocusMonths(-1);
        else if (k === 'PageDown') this.moveFocusMonths(1);
        else if (k === 'Enter' || k === ' ') { this.select(d); this.focusedDate = d; }
        else return;
        e.preventDefault();
    },

    get years() {
        const start = this.startMonth ? this.startMonth.getFullYear() : new Date().getFullYear() - 100;
        const end = this.endMonth ? this.endMonth.getFullYear() : new Date().getFullYear() + 10;
        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    },
    get monthNames() {
        return Array.from({ length: 12 }, (_, i) => new Date(2023, i, 1).toLocaleString(this.locale, { month: 'long' }));
    },
    setMonth(m) { this.view = new Date(this.view.getFullYear(), Number(m), 1); },
    setYear(y) { this.view = new Date(Number(y), this.view.getMonth(), 1); },

    fmt(d) { return d ? _ymd(d) : ''; },
    get multipleValue() { return this.multiple.map(_ymd).join(','); },
});

export { calendar };

function decimalsOf(n) {
    if (n === null || n === undefined || !isFinite(n)) return 0;
    const s = String(n);
    const exp = s.indexOf('e-');
    if (exp !== -1) {
        const mantissa = s.slice(0, exp);
        const dot = mantissa.indexOf('.');
        return Number(s.slice(exp + 2)) + (dot === -1 ? 0 : mantissa.length - dot - 1);
    }
    const dot = s.indexOf('.');
    return dot === -1 ? 0 : s.length - dot - 1;
}

function roundTo(v, decimals) {
    if (v === null || v === undefined || !isFinite(v)) return v;
    const d = Math.min(Math.max(decimals, 0), 15);
    const s = String(v);
    if (s.includes('e') || s.includes('E')) {
        const p = Math.pow(10, d);

        return Math.round(v * p) / p;
    }

    return Number(`${Math.round(Number(`${s}e${d}`))}e-${d}`);
}

function stepBy(current, delta, step) {
    const from = Number(current) || 0;
    return roundTo(from + delta, Math.max(decimalsOf(step), decimalsOf(from)));
}

function snapTo(raw, step, origin = 0) {
    if (!isFinite(raw) || !step) return raw;
    return roundTo(Math.round((raw - origin) / step) * step + origin, decimalsOf(step) + decimalsOf(origin));
}

const blatNumber = { decimals: decimalsOf, round: roundTo, step: stepBy, snap: snapTo };

export { blatNumber, decimalsOf, roundTo, stepBy, snapTo };

const THEME_SCAFFOLD = `@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../vendor/mallardduck/blade-lucide-icons/resources/svg/*.svg';
@source '../../storage/framework/views/*.php';
@source '../views';

@custom-variant dark (&:is(.dark *));

@theme inline {
    /* Fonts */
    --font-sans: var(--font-sans);
    --font-serif: var(--font-serif);
    --font-mono: var(--font-mono);
    --font-heading: var(--font-heading);

    /* Radius scale (derived from --radius) */
    --radius-sm: calc(var(--radius) - 4px);
    --radius-md: calc(var(--radius) - 2px);
    --radius-lg: var(--radius);
    --radius-xl: calc(var(--radius) + 4px);

    /* Spacing base (every p-*, gap-*, w-*… derives from this) */
    --spacing: var(--spacing);

    /* Letter-spacing scale (derived from --tracking-normal) */
    --tracking-tighter: calc(var(--tracking-normal) - 0.05em);
    --tracking-tight: calc(var(--tracking-normal) - 0.025em);
    --tracking-normal: var(--tracking-normal);
    --tracking-wide: calc(var(--tracking-normal) + 0.025em);
    --tracking-wider: calc(var(--tracking-normal) + 0.05em);
    --tracking-widest: calc(var(--tracking-normal) + 0.1em);

    /* Box-shadow scale */
    --shadow-2xs: var(--shadow-2xs);
    --shadow-xs: var(--shadow-xs);
    --shadow-sm: var(--shadow-sm);
    --shadow: var(--shadow);
    --shadow-md: var(--shadow-md);
    --shadow-lg: var(--shadow-lg);
    --shadow-xl: var(--shadow-xl);
    --shadow-2xl: var(--shadow-2xl);

    /* Colors */
    --color-background: var(--background);
    --color-foreground: var(--foreground);
    --color-card: var(--card);
    --color-card-foreground: var(--card-foreground);
    --color-popover: var(--popover);
    --color-popover-foreground: var(--popover-foreground);
    --color-primary: var(--primary);
    --color-primary-foreground: var(--primary-foreground);
    --color-secondary: var(--secondary);
    --color-secondary-foreground: var(--secondary-foreground);
    --color-muted: var(--muted);
    --color-muted-foreground: var(--muted-foreground);
    --color-accent: var(--accent);
    --color-accent-foreground: var(--accent-foreground);
    --color-destructive: var(--destructive);
    --color-destructive-foreground: var(--destructive-foreground);
    --color-success: var(--success);
    --color-success-foreground: var(--success-foreground);
    --color-warning: var(--warning);
    --color-warning-foreground: var(--warning-foreground);
    --color-info: var(--info);
    --color-info-foreground: var(--info-foreground);
    --color-border: var(--border);
    --color-input: var(--input);
    --color-ring: var(--ring);
    --color-chart-1: var(--chart-1);
    --color-chart-2: var(--chart-2);
    --color-chart-3: var(--chart-3);
    --color-chart-4: var(--chart-4);
    --color-chart-5: var(--chart-5);
    --color-sidebar: var(--sidebar);
    --color-sidebar-foreground: var(--sidebar-foreground);
    --color-sidebar-primary: var(--sidebar-primary);
    --color-sidebar-primary-foreground: var(--sidebar-primary-foreground);
    --color-sidebar-accent: var(--sidebar-accent);
    --color-sidebar-accent-foreground: var(--sidebar-accent-foreground);
    --color-sidebar-border: var(--sidebar-border);
    --color-sidebar-ring: var(--sidebar-ring);

    --animate-caret-blink: caret-blink 1.25s ease-out infinite;

    @keyframes caret-blink {
        0%,
        70%,
        100% {
            opacity: 1;
        }
        20%,
        50% {
            opacity: 0;
        }
    }

    --animate-progress-indeterminate: progress-indeterminate 1.4s ease-in-out infinite;

    @keyframes progress-indeterminate {
        0% {
            left: -40%;
        }
        100% {
            left: 100%;
        }
    }
}`;

const THEME_TOKENS = [
    '--radius', '--spacing', '--tracking-normal',
    '--font-sans', '--font-serif', '--font-mono', '--font-heading',
    '--shadow-2xs', '--shadow-xs', '--shadow-sm', '--shadow',
    '--shadow-md', '--shadow-lg', '--shadow-xl', '--shadow-2xl',
    '--background', '--foreground',
    '--card', '--card-foreground',
    '--popover', '--popover-foreground',
    '--primary', '--primary-foreground',
    '--secondary', '--secondary-foreground',
    '--muted', '--muted-foreground',
    '--accent', '--accent-foreground',
    '--destructive', '--destructive-foreground',
    '--success', '--success-foreground',
    '--warning', '--warning-foreground',
    '--info', '--info-foreground',
    '--border', '--input', '--ring',
    '--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5',
    '--sidebar', '--sidebar-foreground', '--sidebar-primary', '--sidebar-primary-foreground',
    '--sidebar-accent', '--sidebar-accent-foreground', '--sidebar-border', '--sidebar-ring',
];

function _readTokens() {
    const cs = getComputedStyle(document.documentElement);
    const out = {};
    THEME_TOKENS.forEach((t) => {
        const v = cs.getPropertyValue(t).trim();
        if (v) out[t] = v;
    });
    if (out['--font-heading'] && out['--font-heading'] === out['--font-sans']) {
        out['--font-heading'] = 'var(--font-sans)';
    }
    return out;
}

window.exportTheme = function () {
    const root = document.documentElement;
    const wasDark = root.classList.contains('dark');
    root.classList.remove('dark');
    const light = _readTokens();
    root.classList.add('dark');
    const dark = _readTokens();
    root.classList.toggle('dark', wasDark);
    const fmt = (o, keys) => keys.filter((t) => o[t]).map((t) => `  ${t}: ${o[t]};`).join('\n');
    const darkKeys = THEME_TOKENS.filter((t) => dark[t] && dark[t] !== light[t]);
    return `${THEME_SCAFFOLD}\n\n:root {\n${fmt(light, THEME_TOKENS)}\n}\n\n.dark {\n${fmt(dark, darkKeys)}\n}\n`;
};

function resolveControl(el) {
    const focusableSel = 'button, [href], input, select, textarea, [tabindex]';
    if (el.matches(focusableSel) && el.getAttribute('tabindex') !== '-1') return el;
    return (
        el.querySelector('button:not([tabindex="-1"]), a[href]:not([tabindex="-1"]), input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])') ||
        el.firstElementChild ||
        el
    );
}

let _blatId = 0;
function mintId(prefix = 'blat') {
    return `${prefix}-${++_blatId}-${Math.random().toString(36).slice(2, 7)}`;
}

function ensureId(node, prefix = 'blat') {
    if (!node.id) node.id = mintId(prefix);
    return node.id;
}

function stableIds(owner) {
    const minted = (owner._blatIds ??= {});

    return (node, role, prefix) => {
        if (node.id) return (minted[role] = node.id);
        node.id = minted[role] || mintId(prefix);

        return (minted[role] = node.id);
    };
}

function setAttr(node, name, value) {
    if (value === null || value === undefined || value === '') {
        if (node.hasAttribute(name)) node.removeAttribute(name);
    } else if (node.getAttribute(name) !== value) {
        node.setAttribute(name, String(value));
    }
}

const WIRED_ATTRS = ['id', 'for', 'aria-describedby', 'aria-labelledby', 'aria-invalid', 'aria-controls', 'aria-haspopup', 'aria-expanded', 'data-invalid', 'data-state'];

function keepWired(el, sync, cleanup) {
    sync();
    const observer = new MutationObserver(sync);
    observer.observe(el, { childList: true, subtree: true, attributes: true, attributeFilter: WIRED_ATTRS });
    cleanup?.(() => observer.disconnect());
}

function blatTriggerDirective(el, { expression }, { evaluate, effect, cleanup }) {
    const cfg = expression ? evaluate(expression) : {};
    const tracksState = cfg.state !== null;
    const stateExpr = cfg.state || 'open';
    let open = false;

    const sync = () => {
        const control = resolveControl(el);
        if (!control) return;
        if (cfg.focusable && !control.matches('button, a[href], input, select, textarea, [tabindex]')) {
            control.tabIndex = 0;
        }
        if (cfg.id && !control.id) control.id = cfg.id;
        if (cfg.haspopup) setAttr(control, 'aria-haspopup', cfg.haspopup === true ? 'true' : cfg.haspopup);
        if (cfg.controls) setAttr(control, 'aria-controls', cfg.controls);
        if (cfg.labelledby) setAttr(control, 'aria-labelledby', cfg.labelledby);
        if (cfg.describedby) setAttr(control, 'aria-describedby', cfg.describedby);
        if (!tracksState) return;
        setAttr(control, 'aria-expanded', open ? 'true' : 'false');
        setAttr(control, 'data-state', open ? 'open' : 'closed');
    };

    if (tracksState) {
        effect(() => {
            try {
                open = !!evaluate(stateExpr);
            } catch (e) {
            }
            sync();
        });
    }

    keepWired(el, sync, cleanup);
}

function blatLabelledByDirective(el, { expression }, { evaluate, cleanup }) {
    const cfg = expression ? evaluate(expression) : {};
    const ours = {};
    const idFor = stableIds(el);

    const wire = (sel, attr) => {
        if (!sel) return;
        const node = el.querySelector(sel);
        if (node) {
            setAttr(el, attr, (ours[attr] = idFor(node, attr, 'blat-label')));
        } else if (ours[attr] && el.getAttribute(attr) === ours[attr]) {
            setAttr(el, attr, null);
            delete ours[attr];
        }
    };

    keepWired(
        el,
        () => {
            wire(cfg.label, 'aria-labelledby');
            wire(cfg.description, 'aria-describedby');
        },
        cleanup,
    );
}

const BLAT_ANCHOR_PLACEMENTS = ['top', 'top-start', 'top-end', 'right', 'right-start', 'right-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'left-start', 'left-end'];
function blatAnchorDirective(el, { modifiers, expression }, { evaluateLater, cleanup }) {
    const placement = BLAT_ANCHOR_PLACEMENTS.find((p) => modifiers.includes(p)) || 'bottom';
    let offsetValue = 0;
    if (modifiers.includes('offset')) {
        const i = modifiers.indexOf('offset');
        offsetValue = modifiers[i + 1] !== undefined ? Number(modifiers[i + 1]) : 0;
    }
    const allowFlip = !modifiers.includes('no-flip');
    const matchWidth = modifiers.includes('match-width');
    const capHeight = !modifiers.includes('no-size');
    const strategy = modifiers.includes('absolute') ? 'absolute' : 'fixed';
    const PAD = modifiers.includes('pad') ? Number(modifiers[modifiers.indexOf('pad') + 1]) || 8 : 8;
    const designMax = parseFloat(getComputedStyle(el).maxHeight) || Infinity;
    const getReference = evaluateLater(expression);

    let stop = null;
    let reference = null;

    const rebind = (next) => {
        if (!next) return;
        if (stop) stop();
        reference = next;
        stop = autoUpdate(reference, el, update);
    };

    function update() {
        if (reference?.isConnected === false) {
            let fresh = null;
            getReference((r) => (fresh = r));
            if (fresh && fresh !== reference) return rebind(fresh);
        }

        return position();
    }

    const position = () =>
    computePosition(reference, el, {
        strategy,
        placement,
        middleware: [
            flOffset(offsetValue),
            allowFlip && flip({ padding: PAD }),
            shift({ padding: PAD }),
            size({
                padding: PAD,
                apply({ availableHeight, rects }) {
                    if (capHeight) {
                        const h = Math.min(designMax, Math.max(140, Math.floor(availableHeight)));
                        el.style.maxHeight = Number.isFinite(h) ? `${h}px` : '';
                    }
                    if (matchWidth && rects.reference.width > 0) {
                        const w = `${Math.round(rects.reference.width)}px`;
                        if (el.style.minWidth !== w) el.style.minWidth = w;
                    }
                },
            }),
        ].filter(Boolean),
    }).then(({ x, y }) => {
        Object.assign(el.style, { position: strategy, left: `${x}px`, top: `${y}px` });
    });

    getReference((initial) => rebind(initial));

    cleanup(() => stop && stop());
}

function blatDialogLayerDirective(el) {
    queueMicrotask(() => {
        const home = el._x_teleportBack;
        if (!home) return;
        const target = home.closest('dialog') || document.body;
        if (el.parentElement !== target) {
            target.appendChild(el);
            requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
        }
    });
}

function blatClosest(el, selector) {
    let node = el;
    while (node) {
        if (node.matches && node.matches(selector)) return node;
        node = node._x_teleportBack || node.parentElement;
    }

    return null;
}

function blatWire(el) {
    if (! window.Livewire) return null;
    const root = blatClosest(el, '[wire\\:id]');

    return (root && window.Livewire.find(root.getAttribute('wire:id'))) || null;
}

function blatModelMagic(el) {
    return (fallback = null) => ({
        local: fallback,

        get path() { return el.dataset.blatModel || null; },

        get value() {
            const wire = this.path ? blatWire(el) : null;
            if (! wire) return this.local;
            const value = wire.$get(this.path);

            return value === undefined ? this.local : value;
        },

        set value(next) {
            this.write(next);
            this.commit();
        },

        write(next) {
            const wire = this.path ? blatWire(el) : null;
            if (! wire) { this.local = next; return; }
            wire.$set(this.path, next, false);
        },

        commit() {
            const wire = this.path && el.dataset.blatModelLive === '1' ? blatWire(el) : null;
            if (wire) wire.$set(this.path, wire.$get(this.path), true);
        },
    });
}

function treeBlockEnd(rows, i) {
    let j = i + 1;
    while (j < rows.length && rows[j].depth > rows[i].depth) j++;

    return j;
}

function treeMove(rows, moved, target, where) {
    const from = rows.findIndex((r) => r.key === moved);
    if (from === -1 || moved === target) return null;
    const end = treeBlockEnd(rows, from);
    const at = rows.findIndex((r) => r.key === target);
    if (at === -1 || (at > from && at < end)) return null;

    const block = rows.slice(from, end);
    const rest = [...rows.slice(0, from), ...rows.slice(end)];
    const t = rest.findIndex((r) => r.key === target);
    const anchor = rest[t];
    const parent = where === 'inside' ? anchor.key : anchor.parent;
    const delta = (where === 'inside' ? anchor.depth + 1 : anchor.depth) - block[0].depth;
    const index = where === 'before' ? t : treeBlockEnd(rest, t);
    const placed = block.map((r, i) => ({ ...r, depth: r.depth + delta, parent: i === 0 ? parent : r.parent }));

    return [...rest.slice(0, index), ...placed, ...rest.slice(index)];
}

function treeSiblings(rows, parent) {
    return rows.filter((r) => r.parent === parent).map((r) => r.key);
}

function treeCastKey(key) {
    if (key === null || key === undefined || key === '') return null;

    return /^-?\d{1,15}$/.test(key) ? Number(key) : key;
}

export { treeMove, treeSiblings, treeCastKey };

const blatTreeGrid = (config = {}) => ({
    method: config.method || null,
    reparent: !! config.reparent,
    reorderable: !! config.reorderable,
    labels: config.labels || {},
    version: 0,
    focusKey: null,
    grabbed: null,
    dragging: null,
    announcement: '',
    _open: null,
    _observer: null,

    init() {
        this._open = this.$blatModel((config.expanded || []).map(String));
        this._observer = new MutationObserver(() => this.version++);
        this._observer.observe(this.$refs.body, { childList: true });
        this._observer.observe(this.$root, { attributes: true, subtree: true, attributeFilter: ['data-tree-parent', 'data-expand-all'] });
    },

    destroy() {
        this._observer?.disconnect();
    },

    rowEls() {
        return [...this.$refs.body.children].filter((el) => el.dataset.treeKey !== undefined);
    },

    rows() {
        return this.rowEls().map((el) => ({
            key: el.dataset.treeKey,
            parent: el.dataset.treeParent || null,
            depth: Number(el.dataset.treeDepth) || 0,
            el,
        }));
    },

    rowEl(key) {
        return this.rowEls().find((el) => el.dataset.treeKey === key) || null;
    },

    isOpen(key) {
        return (this._open.value || []).map(String).includes(String(key));
    },

    setOpen(key, open) {
        const keys = (this._open.value || []).map(String).filter((k) => k !== String(key));
        this._open.value = open ? [...keys, String(key)] : keys;
    },

    toggle(key) {
        this.setOpen(key, ! this.isOpen(key));
    },

    shown(el) {
        this.version;
        if (this.$root.dataset.expandAll === '1') return true;
        const byKey = new Map(this.rowEls().map((r) => [r.dataset.treeKey, r]));
        let parent = el.dataset.treeParent;
        const seen = new Set();
        while (parent && ! seen.has(parent)) {
            if (! this.isOpen(parent)) return false;
            seen.add(parent);
            parent = byKey.get(parent)?.dataset.treeParent;
        }

        return true;
    },

    expandedAttr(el) {
        this.version;
        return el.dataset.treeChildren === '1' ? String(this.$root.dataset.expandAll === '1' || this.isOpen(el.dataset.treeKey)) : null;
    },

    tabFor(el) {
        this.version;
        const current = this.focusKey !== null && this.rowEl(this.focusKey);
        if (current) return el === current ? 0 : -1;

        return el === this.rowEls()[0] ? 0 : -1;
    },

    focusRow(el) {
        if (! el) return;
        this.focusKey = el.dataset.treeKey;
        el.focus();
    },

    visibleRows() {
        return this.rowEls().filter((el) => this.shown(el));
    },

    onKeydown(e) {
        const row = e.target.closest?.('tr[data-tree-key]');
        if (! row || e.target !== row) return;
        if (this.grabbed) return this.onGrabbedKey(e, row);

        const rows = this.visibleRows();
        const i = rows.indexOf(row);
        const key = row.dataset.treeKey;
        const hasChildren = row.dataset.treeChildren === '1';

        switch (e.key) {
            case 'ArrowDown': this.focusRow(rows[i + 1]); break;
            case 'ArrowUp': this.focusRow(rows[i - 1]); break;
            case 'Home': this.focusRow(rows[0]); break;
            case 'End': this.focusRow(rows[rows.length - 1]); break;
            case 'ArrowRight':
                if (hasChildren && ! this.isOpen(key)) this.setOpen(key, true);
                else if (hasChildren) this.focusRow(rows[i + 1]);
                break;
            case 'ArrowLeft':
                if (hasChildren && this.isOpen(key)) this.setOpen(key, false);
                else this.focusRow(this.rowEl(row.dataset.treeParent));
                break;
            case ' ':
                if (! this.reorderable) return;
                this.grabbed = { key, snapshot: this.rows() };
                row.setAttribute('data-dragging', '');
                this.say(this.labels.grabbed, row);
                break;
            default: return;
        }
        e.preventDefault();
    },

    onGrabbedKey(e, row) {
        const rows = this.rows();
        const me = rows.find((r) => r.key === this.grabbed.key);
        const siblings = rows.filter((r) => r.parent === me.parent);
        const at = siblings.findIndex((r) => r.key === me.key);
        let next = null;

        switch (e.key) {
            case 'ArrowUp': if (at > 0) next = treeMove(rows, me.key, siblings[at - 1].key, 'before'); break;
            case 'ArrowDown': if (at < siblings.length - 1) next = treeMove(rows, me.key, siblings[at + 1].key, 'after'); break;
            case 'ArrowRight':
                if (this.reparent && at > 0) {
                    next = treeMove(rows, me.key, siblings[at - 1].key, 'inside');
                    if (next) this.setOpen(siblings[at - 1].key, true);
                }
                break;
            case 'ArrowLeft': if (this.reparent && me.parent) next = treeMove(rows, me.key, me.parent, 'after'); break;
            case ' ':
            case 'Enter': {
                const { key, snapshot } = this.grabbed;
                this.grabbed = null;
                row.removeAttribute('data-dragging');
                this.commit(key, snapshot);
                this.say(this.labels.dropped, row);
                break;
            }
            case 'Escape':
            case 'Tab': {
                this.apply(this.grabbed.snapshot);
                this.grabbed = null;
                row.removeAttribute('data-dragging');
                this.say(this.labels.cancelled, row);
                if (e.key === 'Tab') return;
                break;
            }
            default: return;
        }
        e.preventDefault();
        if (next) {
            this.apply(next);
            this.focusRow(this.rowEl(me.key));
            this.say(this.labels.moved, this.rowEl(me.key));
        }
    },

    onHandleDown(e, key) {
        if (! this.reorderable || (e.pointerType === 'mouse' && e.button !== 0)) return;
        e.preventDefault();
        const handle = e.currentTarget;
        handle.setPointerCapture?.(e.pointerId);
        this.dragging = { key, target: null, where: null };
        this.rowEl(key)?.setAttribute('data-dragging', '');

        const move = (ev) => this.onDragMove(ev);
        const end = (ev) => {
            handle.removeEventListener('pointermove', move);
            handle.removeEventListener('pointerup', end);
            handle.removeEventListener('pointercancel', end);
            this.onDragEnd(ev.type === 'pointerup');
        };
        handle.addEventListener('pointermove', move);
        handle.addEventListener('pointerup', end);
        handle.addEventListener('pointercancel', end);
    },

    onDragMove(e) {
        const drag = this.dragging;
        if (! drag) return;
        const over = document.elementFromPoint(e.clientX, e.clientY)?.closest('tr[data-tree-key]');
        let target = null;
        let where = null;

        if (over && this.$refs.body.contains(over) && over.dataset.treeKey !== drag.key) {
            const box = over.getBoundingClientRect();
            const f = (e.clientY - box.top) / (box.height || 1);
            where = this.reparent && f > 0.25 && f < 0.75 ? 'inside' : (f < 0.5 ? 'before' : 'after');
            const rows = this.rows();
            const me = rows.find((r) => r.key === drag.key);
            const sameParent = (over.dataset.treeParent || null) === me.parent;
            if ((this.reparent || (sameParent && where !== 'inside')) && treeMove(rows, drag.key, over.dataset.treeKey, where)) {
                target = over;
            }
        }

        if (drag.target && (drag.target !== target || drag.where !== where)) drag.target.removeAttribute('data-drop');
        drag.target = target;
        drag.where = target ? where : null;
        if (target) target.setAttribute('data-drop', where);
    },

    onDragEnd(drop) {
        const drag = this.dragging;
        this.dragging = null;
        if (! drag) return;
        this.rowEl(drag.key)?.removeAttribute('data-dragging');
        drag.target?.removeAttribute('data-drop');
        if (! drop || ! drag.target) return;

        const snapshot = this.rows();
        const next = treeMove(snapshot, drag.key, drag.target.dataset.treeKey, drag.where);
        if (! next) return;
        if (drag.where === 'inside') this.setOpen(drag.target.dataset.treeKey, true);
        this.apply(next);
        this.commit(drag.key, snapshot);
    },

    apply(list) {
        const body = this.$refs.body;
        const rowEls = this.rowEls();
        const anchor = rowEls.length ? rowEls[rowEls.length - 1].nextSibling : null;
        const withLeadingSpace = (el) => {
            const nodes = [el];
            for (let n = el.previousSibling; n && n.nodeType === Node.TEXT_NODE && ! n.textContent.trim(); n = n.previousSibling) nodes.unshift(n);
            return nodes;
        };
        const units = list.map((r) => withLeadingSpace(r.el));
        const focused = document.activeElement;
        for (const [i, r] of list.entries()) {
            for (const node of units[i]) body.insertBefore(node, anchor);
            r.el.dataset.treeParent = r.parent ?? '';
            r.el.dataset.treeDepth = String(r.depth);
            r.el.style.setProperty('--depth', String(r.depth));
            r.el.setAttribute('aria-level', String(r.depth + 1));
        }
        if (focused && body.contains(focused)) focused.focus();
        this.version++;
    },

    commit(key, before) {
        const after = this.rows();
        const was = before.find((r) => r.key === key);
        const now = after.find((r) => r.key === key);
        if (! was || ! now) return;
        const unchanged = was.parent === now.parent &&
            JSON.stringify(treeSiblings(before, was.parent)) === JSON.stringify(treeSiblings(after, now.parent));
        if (unchanged) return;

        const detail = {
            parent: treeCastKey(now.parent),
            ids: treeSiblings(after, now.parent).map(treeCastKey),
            moved: treeCastKey(key),
            from: treeCastKey(was.parent),
        };
        this.$dispatch('tree-reorder', detail);

        const wire = this.method ? this.$blatWire : null;
        if (wire) wire.$call(this.method, detail.parent, detail.ids, detail.moved);
    },

    say(template, row) {
        if (! template || ! row) return;
        const name = row.querySelector('[data-tree-label]')?.textContent.trim() || row.dataset.treeKey;
        const rows = this.rows();
        const me = rows.find((r) => r.el === row);
        const siblings = me ? rows.filter((r) => r.parent === me.parent) : [];
        this.announcement = template
            .replace(':name', name)
            .replace(':position', String(siblings.findIndex((r) => r.el === row) + 1))
            .replace(':count', String(siblings.length))
            .replace(':level', String((me?.depth ?? 0) + 1));
    },
});

const FIELD_CONTROL =
    'input:not([type=hidden]), textarea, select, [role="checkbox"], [role="switch"], [role="radiogroup"], [role="combobox"], [role="slider"], [role="spinbutton"]';

function blatFieldDirective(el, _directive, { cleanup }) {
    let ourIds = [];
    let ourInvalid = false;
    const idFor = stableIds(el);

    const sync = () => {
        const control = el.querySelector(FIELD_CONTROL);
        if (!control) return;

        const desc = el.querySelector('[data-slot="field-description"]');
        const err = el.querySelector('[data-slot="field-error"]');

        const ids = [];
        if (desc) ids.push(idFor(desc, 'desc', 'field-desc'));
        if (err) ids.push(idFor(err, 'err', 'field-err'));

        const present = (control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        const theirs = present.filter((id) => !ourIds.includes(id) && !ids.includes(id));
        setAttr(control, 'aria-describedby', [...theirs, ...ids].join(' '));
        ourIds = ids;

        if (err) {
            setAttr(control, 'aria-invalid', 'true');
            ourInvalid = true;
        } else if (ourInvalid) {
            setAttr(control, 'aria-invalid', null);
            ourInvalid = false;
        }

        setAttr(el, 'data-invalid', err ? 'true' : null);

        const label = el.querySelector('[data-slot="field-label"]');
        if (label && !label.getAttribute('for')) {
            setAttr(label, 'for', idFor(control, 'control', 'field-control'));
        }
    };

    keepWired(el, sync, cleanup);
}

function blatNavMagic(el) {
    return (e, opts = {}) => {
        if (!e || !e.key) return;
        const selector = opts.selector || '[role^="menuitem"], [role="option"]';
        const orientation = opts.orientation || 'vertical';
        const loop = opts.loop !== false;
        const horiz = orientation === 'horizontal' || orientation === 'both';
        const vert = orientation === 'vertical' || orientation === 'both';
        const items = Array.from(el.querySelectorAll(selector)).filter(
            (i) => i.offsetParent !== null && i.getAttribute('aria-disabled') !== 'true' && !i.hasAttribute('disabled'),
        );
        if (!items.length) return;
        const cur = items.indexOf(document.activeElement);
        if (opts.requireMatch && cur < 0) return;
        let idx = null;
        if ((vert && e.key === 'ArrowDown') || (horiz && e.key === 'ArrowRight')) idx = cur < 0 ? 0 : cur + 1;
        else if ((vert && e.key === 'ArrowUp') || (horiz && e.key === 'ArrowLeft')) idx = cur < 0 ? items.length - 1 : cur - 1;
        else if (e.key === 'Home' || e.key === 'PageUp') idx = 0;
        else if (e.key === 'End' || e.key === 'PageDown') idx = items.length - 1;
        else return;
        if (loop) idx = (idx + items.length) % items.length;
        else idx = Math.max(0, Math.min(items.length - 1, idx));
        const target = items[idx];
        if (target) {
            target.focus();
            e.preventDefault();
            e.stopPropagation();
        }
    };
}

function blatTypeMagic(el) {
    const DEFAULT = '[role^="menuitem"], [role="option"], [role="menuitemradio"], [role="menuitemcheckbox"]';
    return (e, selector = DEFAULT) => {
        if (!e || e.key == null || e.key.length !== 1 || e.ctrlKey || e.metaKey || e.altKey) return;
        const items = Array.from(el.querySelectorAll(selector)).filter(
            (i) => !i.hasAttribute('disabled') && i.getAttribute('aria-disabled') !== 'true' && i.offsetParent !== null,
        );
        if (!items.length) return;
        el._blatBuf = (el._blatBuf || '') + e.key.toLowerCase();
        clearTimeout(el._blatBufT);
        el._blatBufT = setTimeout(() => (el._blatBuf = ''), 500);
        const start = items.indexOf(document.activeElement);
        const ordered = items.slice(start + 1).concat(items.slice(0, start + 1));
        const match = ordered.find((i) => (i.textContent || '').trim().toLowerCase().startsWith(el._blatBuf));
        if (match) {
            match.focus();
            e.preventDefault();
        }
    };
}

const blatMenu = (config = {}) => ({
    open: config.open ?? false,
    x: 0,
    y: 0,
    _menu: null,
    _trigger: null,
    _closeTimer: null,
    openAt(ev) {
        if (ev) {
            ev.preventDefault();
            this.x = ev.clientX;
            this.y = ev.clientY;
            this._trigger = ev.currentTarget || this._trigger;
        }
        this.openMenu('first');
    },
    get _items() {
        if (!this._menu) return [];
        return Array.from(this._menu.querySelectorAll('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]')).filter(
            (i) => i.getAttribute('aria-disabled') !== 'true' && !i.hasAttribute('disabled') && i.offsetParent !== null,
        );
    },
    openMenu(focus) {
        this.cancelClose();
        this.open = true;
        this.$nextTick(() => {
            if (!this._menu) return;
            const items = this._items;
            if (focus === 'first') (items[0] || this._menu).focus();
            else if (focus === 'last') (items[items.length - 1] || this._menu).focus();
            else this._menu.focus();
        });
    },
    toggleMenu() {
        this.open ? this.closeMenu(false) : this.openMenu();
    },
    closeMenu(returnFocus = true) {
        this.cancelClose();
        if (!this.open) return;
        this.open = false;
        if (returnFocus && this._trigger) this.$nextTick(() => this._trigger.focus());
    },
    closeSoon(delay = 120) {
        clearTimeout(this._closeTimer);
        this._closeTimer = setTimeout(() => this.closeMenu(false), delay);
    },
    cancelClose() {
        clearTimeout(this._closeTimer);
    },
});

const blatMenubar = () => ({
    active: null,
    rovingId: null,
    triggers: [],
    register(id, el) {
        this.triggers.push({ id, el });
        if (this.rovingId === null) this.rovingId = id;
    },
    _idx(id) {
        return this.triggers.findIndex((t) => t.id === id);
    },
    _items(id) {
        const m = document.getElementById(id);
        return m
            ? Array.from(m.querySelectorAll('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]')).filter(
                  (x) => x.getAttribute('aria-disabled') !== 'true' && x.offsetParent !== null,
              )
            : [];
    },
    focusTrigger(id) {
        const t = this.triggers.find((x) => x.id === id);
        if (t) {
            this.rovingId = id;
            t.el.focus();
        }
    },
    openMenu(id, focusFirst = true) {
        this.active = id;
        this.rovingId = id;
        if (focusFirst) this.$nextTick(() => (this._items(id)[0] || document.getElementById(id))?.focus());
    },
    toggleMenu(id) {
        this.active === id ? this.closeMenu(false) : this.openMenu(id);
    },
    moveTrigger(dir, fromId) {
        if (!this.triggers.length) return;
        const i = this._idx(fromId);
        if (i < 0) return;
        const next = this.triggers[(i + dir + this.triggers.length) % this.triggers.length].id;
        this.active !== null ? this.openMenu(next) : this.focusTrigger(next);
    },
    closeMenu(returnFocus = true) {
        const id = this.active;
        this.active = null;
        if (returnFocus && id) this.focusTrigger(id);
    },
});

function normaliseSelectValue(model, multiple) {
    const v = model.value;
    model.value = multiple
        ? (Array.isArray(v) ? v.map(String) : v != null && v !== '' ? [String(v)] : [])
        : (v != null ? String(v) : '');

    return model;
}

const blatSelect = (config = {}) => ({
    multiple: !!config.multiple,
    open: false,
    _model: config.wired ? config.model : normaliseSelectValue(config.model, !!config.multiple),
    get value() { return this._model.value; },
    set value(v) { this._model.value = v; },
    _labels: {},
    get label() {
        if (this.multiple) return '';
        const v = this.value;

        return v === null || v === undefined || v === '' ? '' : (this._labels[String(v)] ?? '');
    },
    get selected() {
        const vs = this.multiple && Array.isArray(this.value) ? this.value : [];

        return vs.map((v) => ({ value: String(v), label: this._labels[String(v)] ?? String(v) }));
    },
    _list: null,
    _trigger: null,
    get _options() {
        return this._list
            ? Array.from(this._list.querySelectorAll('[role="option"]')).filter(
                  (o) => o.getAttribute('aria-disabled') !== 'true' && o.offsetParent !== null,
              )
            : [];
    },
    isSelected(val) {
        val = String(val);
        return this.multiple
            ? (Array.isArray(this.value) ? this.value : []).map(String).includes(val)
            : String(this.value ?? '') === val;
    },
    openList() {
        this.open = true;
        this.$nextTick(() => {
            if (!this._list) return;
            const opts = this._options;
            (opts.find((o) => this.isSelected(o.dataset.value)) || opts[0] || this._list).focus();
        });
    },
    toggleList() {
        this.open ? this.close(false) : this.openList();
    },
    close(returnFocus = true) {
        if (!this.open) return;
        this.open = false;
        if (returnFocus && this._trigger) this.$nextTick(() => this._trigger.focus());
    },
    selectOption(val, lbl) {
        val = String(val);
        this._labels[val] = lbl;
        if (this.multiple) {
            const current = (Array.isArray(this.value) ? this.value : []).map(String);
            this.value = current.includes(val) ? current.filter((v) => v !== val) : [...current, val];

            return;
        }
        this.value = val;
        this.close();
    },
    seedSelected(val, lbl) {
        this._labels[String(val)] = lbl;
    },
    remove(val) {
        val = String(val);
        this.value = (Array.isArray(this.value) ? this.value : []).map(String).filter((v) => v !== val);
    },
});

const blatCommand = () => ({
    query: '',
    activeId: null,
    _entries: [],
    registerItem(el, keyword, disabled) {
        const id = ensureId(el, 'blat-cmd-item');
        this._entries.push({ id, el, keyword: (keyword || '').toLowerCase(), disabled: !!disabled });
        return id;
    },
    matches(kw) {
        return (kw || '').toLowerCase().includes(this.query.toLowerCase());
    },
    get _visible() {
        return this._entries.filter((i) => !i.disabled && this.matches(i.keyword) && i.el.offsetParent !== null);
    },
    get visibleCount() {
        return this._entries.filter((i) => this.matches(i.keyword)).length;
    },
    ensureActive() {
        const vis = this._visible;
        if (!vis.length) {
            this.activeId = null;
        } else if (!vis.some((i) => i.id === this.activeId)) {
            this.activeId = vis[0].id;
        }
    },
    move(dir) {
        const vis = this._visible;
        if (!vis.length) return;
        let idx = vis.findIndex((i) => i.id === this.activeId);
        idx = idx < 0 ? (dir > 0 ? 0 : vis.length - 1) : (idx + dir + vis.length) % vis.length;
        this.activeId = vis[idx].id;
        vis[idx].el.scrollIntoView({ block: 'nearest' });
    },
    edge(pos) {
        const vis = this._visible;
        if (!vis.length) return;
        const it = pos === 'last' ? vis[vis.length - 1] : vis[0];
        this.activeId = it.id;
        it.el.scrollIntoView({ block: 'nearest' });
    },
    selectActive() {
        const it = this._entries.find((i) => i.id === this.activeId);
        if (it && !it.disabled) it.el.click();
    },
});

const blatListbox = (config = {}) => ({
    trigger: config.trigger || 'button',
    open: false,
    filtering: false,
    multiple: !!config.multiple,
    _model: config.model ?? { value: config.value ?? (config.multiple ? [] : '') },
    get value() { const v = this._model.value; return v ?? (this.multiple ? [] : ''); },
    set value(v) { this._model.value = v; },
    activeValue: null,
    options: config.options || [],
    query: config.query ?? '',

    isSelected(v) { return this.multiple ? this.value.includes(v) : this.value === v; },
    get selected() { return this.options.filter((o) => this.isSelected(o.value)); },
    get label() { const o = this.options.find((o) => o.value === this.value); return o ? o.label : ''; },
    matches(label) { return label.toLowerCase().includes(this.query.toLowerCase()); },
    get visible() {
        if (this.trigger === 'input' && !this.filtering) return this.options;
        return this.options.filter((o) => this.matches(o.label));
    },
    get visibleCount() { return this.visible.length; },
    ensureActive() {
        const v = this.visible;
        if (!v.length) { this.activeValue = null; return; }
        if (!v.some((o) => o.value === this.activeValue)) {
            this.activeValue = (v.find((o) => this.isSelected(o.value)) || v[0]).value;
        }
    },
    move(dir) {
        if (this.trigger === 'input' && !this.open) { this.openList(); return; }
        const v = this.visible;
        if (!v.length) return;
        let i = v.findIndex((o) => o.value === this.activeValue);
        i = i < 0 ? 0 : (i + dir + v.length) % v.length;
        this.activeValue = v[i].value;
    },
    edge(pos) {
        const v = this.visible;
        if (!v.length) return;
        this.activeValue = (pos === 'last' ? v[v.length - 1] : v[0]).value;
    },
    openList() {
        this.open = true;
        if (this.trigger === 'button') {
            this.query = '';
            this.$nextTick(() => { this.ensureActive(); (this.$refs.search || this.$refs.list)?.focus(); });
        } else {
            this.filtering = false;
            this.$nextTick(() => this.ensureActive());
        }
    },
    onInput() {
        if (!this.multiple) this.value = '';
        this.open = true;
        this.filtering = true;
        this.$nextTick(() => this.ensureActive());
    },
    toggle() { this.open ? this.close(false) : this.openList(); },
    close(returnFocus = true) {
        if (!this.open) return;
        this.open = false;
        this.filtering = false;
        if (this.trigger === 'button' && returnFocus) this.$nextTick(() => this.$refs.trigger?.focus());
    },
    selectActive() { if (this.activeValue != null) this.select(this.activeValue); },
    select(v) {
        if (this.multiple) {
            const current = this.value;
            this.value = current.includes(v) ? current.filter((x) => x !== v) : [...current, v];
            if (this.trigger === 'input') {
                this.query = '';
                this.filtering = false;
                this.$nextTick(() => this.$refs.input?.focus());
            }
            return;
        }
        if (this.trigger === 'input') {
            const o = this.options.find((x) => x.value === v);
            if (o) { this.value = o.value; this.query = o.label; }
            this.close();
            return;
        }
        this.value = this.value === v ? '' : v;
        this.close();
        this.query = '';
    },
    remove(v) { this.value = this.value.filter((x) => x !== v); },
    backspace() { if (this.multiple && this.query === '' && this.value.length) this.value = this.value.slice(0, -1); },
});

export function registerBlatUI(Alpine, options = {}) {
    if (options.darkMode !== undefined) themeStore.darkMode = options.darkMode;
    Alpine.plugin(anchor);
    Alpine.plugin(focus);
    Alpine.plugin(collapse);
    Alpine.store('theme', themeStore);
    Alpine.data('calendar', calendar);
    Alpine.data('blatMenu', blatMenu);
    Alpine.data('blatMenubar', blatMenubar);
    Alpine.data('blatSelect', blatSelect);
    Alpine.data('blatListbox', blatListbox);
    Alpine.data('blatCommand', blatCommand);
    Alpine.data('blatTreeGrid', blatTreeGrid);
    Alpine.directive('blat-trigger', blatTriggerDirective);
    Alpine.directive('blat-labelledby', blatLabelledByDirective);
    Alpine.directive('blat-anchor', blatAnchorDirective);
    Alpine.directive('blat-dialog-layer', blatDialogLayerDirective);
    Alpine.directive('blat-field', blatFieldDirective);
    Alpine.magic('blatModel', blatModelMagic);
    Alpine.magic('blatWire', blatWire);
    Alpine.magic('blatNumber', () => blatNumber);
    Alpine.magic('blatNav', blatNavMagic);
    Alpine.magic('blatType', blatTypeMagic);
}
