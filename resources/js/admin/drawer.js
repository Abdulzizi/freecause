import { confirmAction } from './core.js';

const liveSearchTimers = new WeakMap();

const slugTranslit = {
    'ø': 'o', 'æ': 'ae', 'œ': 'oe', 'ß': 'ss', 'đ': 'd', 'ð': 'd', 'þ': 'th',
    'ł': 'l', 'ħ': 'h', 'ı': 'i', 'ĸ': 'k', 'ŋ': 'n', 'ſ': 's',
};

function dirtyForm(node) {
    if (!(node instanceof Element)) return null;
    return node.closest('.fc-drawer form');
}

function markDrawerDirty(form) {
    if (form) form.dataset.fcDirty = '1';
}

function clearDrawerDirty(form) {
    if (form) delete form.dataset.fcDirty;
}

function clearDirtyDrawerForms() {
    document.querySelectorAll('.fc-drawer form[data-fc-dirty]').forEach((form) => {
        delete form.dataset.fcDirty;
    });
}

function hasDirtyDrawerForm() {
    return !!document.querySelector('.fc-drawer form[data-fc-dirty]');
}

function setFieldError(field, message) {
    if (!field) return;
    field.classList.add('fc-input-error');
    const host = field.parentElement;
    if (!host) return;
    let note = host.querySelector('[data-fc-field-error]');
    if (!note) {
        note = document.createElement('div');
        note.setAttribute('data-fc-field-error', '');
        note.className = 'fc-field-error';
        note.style.color = '#b00020';
        note.style.fontSize = '12px';
        note.style.marginTop = '4px';
        field.insertAdjacentElement('afterend', note);
    }
    note.textContent = message;
}

function clearFieldError(field) {
    if (!field) return;
    field.classList.remove('fc-input-error');
    field.parentElement?.querySelector('[data-fc-field-error]')?.remove();
}

function slugify(value) {
    const normalized = String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    return normalized
        .replace(/[øæœßđðþłħıĸŋſ]/gi, (char) => slugTranslit[char.toLowerCase()] || char)
        .toLowerCase()
        .replace(/['’`]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function slugTarget(source) {
    const scope = source.closest('form') || source.closest('.fc-drawer') || document;
    return scope.querySelector('input[data-slug-target]') || document.querySelector('input[data-slug-target]');
}

function applySlugConflict(target, slug) {
    let list = [];
    try {
        list = JSON.parse(document.getElementById('existing-slugs')?.textContent || '[]');
    } catch (_) {
        list = [];
    }
    if (Array.isArray(list) && list.map(String).includes(slug)) {
        target.classList.add('fc-input-error');
        target.title = 'This slug is already in use.';
        return;
    }
    target.classList.remove('fc-input-error');
    target.removeAttribute('title');
}

function markRowOpen(row) {
    document.querySelectorAll('tr[data-detail-row].fc-row-open').forEach((open) => {
        open.classList.remove('fc-row-open');
    });
    row?.classList.add('fc-row-open');
}

function markRowOpenFromUrl() {
    const current = new URL(window.location.href);
    const select = current.searchParams.get('select');
    if (!select) return;
    const locale = current.searchParams.get('locale');
    const row = Array.from(document.querySelectorAll('tr[data-detail-row]')).find((candidate) => {
        if (!candidate.dataset.detailHref) return false;
        let url;
        try {
            url = new URL(candidate.dataset.detailHref, window.location.href);
        } catch (_) {
            return false;
        }
        if (url.searchParams.get('select') !== select) return false;
        return !locale || url.searchParams.get('locale') === locale;
    });
    if (row) markRowOpen(row);
}

function handleDrawerInput(event) {
    if (!(event.target instanceof Element)) return;
    const form = dirtyForm(event.target);
    if (form) markDrawerDirty(form);

    const liveField = event.target.closest('[data-live-validate]');
    if (liveField?.classList.contains('fc-input-error')) clearFieldError(liveField);

    const touchedSlug = event.target.closest('input[data-slug-target]');
    if (touchedSlug) {
        touchedSlug.dataset.slugTouched = '1';
        touchedSlug.classList.remove('fc-input-error');
        touchedSlug.removeAttribute('title');
        return;
    }

    const source = event.target.closest('[data-slug-source]');
    if (source) {
        const output = slugTarget(source);
        if (!output || output.dataset.slugTouched === '1') return;
        const slug = slugify(source.value);
        if (!slug) return;
        output.value = slug;
        applySlugConflict(output, slug);
        return;
    }

    const liveInput = event.target.closest('.fc-filter-form input');
    if (!liveInput) return;
    const type = liveInput.getAttribute('type') || 'text';
    if (['hidden', 'checkbox', 'date'].includes(type)) return;
    const filterForm = liveInput.closest('form.fc-filter-form');
    if (!filterForm || !filterForm.hasAttribute('data-live-search')) return;
    const pending = liveSearchTimers.get(filterForm);
    if (pending) window.clearTimeout(pending);
    const value = liveInput.value;
    const timer = window.setTimeout(() => {
        if (filterForm.dataset.fcLastSearch === value) return;
        filterForm.dataset.fcLastSearch = value;
        filterForm.requestSubmit();
    }, 450);
    liveSearchTimers.set(filterForm, timer);
}

function handleDrawerChange(event) {
    if (!(event.target instanceof Element)) return;
    const form = dirtyForm(event.target);
    if (form) markDrawerDirty(form);
}

function handleFieldBlur(event) {
    if (!(event.target instanceof Element)) return;
    const field = event.target.closest('input[data-live-validate][name="email"]');
    if (!field) return;
    const value = field.value.trim();
    if (value === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        clearFieldError(field);
        return;
    }
    setFieldError(field, 'Enter a valid email address.');
}

export function init() {
    /* Any click on a list row opens that row's detail drawer, the standard
       ?select= navigation. Interactive elements inside the row keep their own
       behavior (checkboxes, buttons, links, selects, labels). */
    document.addEventListener('click', async function (event) {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.target.closest('a, button, input, select, textarea, label')) return;
        if (event.target.closest('[data-detail-row-ignore]')) return;
        const row = event.target.closest('[data-detail-row]');
        if (!row || !row.dataset.detailHref) return;
        event.preventDefault();
        if (hasDirtyDrawerForm() && !(await confirmAction('Discard unsaved changes?', 'Unsaved changes'))) {
            event.stopPropagation();
            return;
        }
        clearDirtyDrawerForms();
        markRowOpen(row);
        window.location.href = row.dataset.detailHref;
    });

    document.addEventListener('input', handleDrawerInput, true);
    document.addEventListener('change', handleDrawerChange, true);
    document.addEventListener('focusout', handleFieldBlur);
    document.addEventListener('submit', (event) => {
        const form = dirtyForm(event.target);
        if (form) clearDrawerDirty(form);
    }, true);
    document.addEventListener('reset', (event) => {
        const form = dirtyForm(event.target);
        if (form) clearDrawerDirty(form);
    }, true);

    document.addEventListener('click', async (event) => {
        if (!(event.target instanceof Element)) return;
        const closeLink = event.target.closest('a.fc-drawer__close');
        if (!closeLink || !hasDirtyDrawerForm()) return;
        event.preventDefault();
        event.stopPropagation();
        if (!(await confirmAction('Discard unsaved changes?', 'Unsaved changes'))) return;
        clearDirtyDrawerForms();
        window.location.href = closeLink.href;
    }, true);

    window.addEventListener('beforeunload', (event) => {
        if (!hasDirtyDrawerForm()) return;
        event.preventDefault();
        event.returnValue = '';
    });

    markRowOpenFromUrl();
}
