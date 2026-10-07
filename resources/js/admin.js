import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

window.toast = {
    success: (message) => Toast.fire({ icon: 'success', title: message }),
    error: (message) => Toast.fire({ icon: 'error', title: message }),
    warning: (message) => Toast.fire({ icon: 'warning', title: message }),
    info: (message) => Toast.fire({ icon: 'info', title: message }),
};

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const notify = (kind, message) => window.toast?.[kind]?.(message);

async function confirmAction(message, title = 'Please confirm') {
    const result = await Swal.fire({
        icon: 'warning',
        title,
        text: message,
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#b91c1c',
    });
    return result.isConfirmed;
}

async function jsonPost(url, payload = {}) {
    if (!url) throw new Error('This action is unavailable. Refresh the page and try again.');

    const response = await fetch(url || '', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(payload),
    });

    const contentType = response.headers.get('content-type') || '';
    if (response.redirected || !/^application\/(?:[\w.-]+\+)?json\b/i.test(contentType)) {
        throw new Error(response.redirected || /text\/html/i.test(contentType)
            ? 'Your session may have expired. Refresh the page and sign in again.'
            : 'The server returned an unexpected response. Please try again.');
    }

    let data;
    try {
        data = await response.json();
    } catch (_) {
        throw new Error('The server returned an invalid response. Please try again.');
    }
    if (!data || typeof data !== 'object' || Array.isArray(data)) {
        throw new Error('The server returned an invalid response. Please try again.');
    }
    if (!response.ok || data.ok !== true) {
        const message = data.error || data.msg || Object.values(data.errors || {}).flat()[0] || 'The action could not be completed.';
        throw new Error(message);
    }
    return data;
}

async function banDetails() {
    const result = await Swal.fire({
        title: 'Ban details',
        html: '<input id="fc-ban-reason" class="swal2-input" maxlength="255" placeholder="Reason (optional)"><input id="fc-ban-days" class="swal2-input" inputmode="numeric" value="0" placeholder="Days (0 = permanent)">',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#b91c1c',
        focusConfirm: false,
        preConfirm: () => {
            const daysText = document.getElementById('fc-ban-days')?.value?.trim() || '0';
            if (!/^\d+$/.test(daysText) || Number(daysText) > 36500) {
                Swal.showValidationMessage('Enter a whole number of days from 0 to 36500.');
                return false;
            }
            return {
                reason: document.getElementById('fc-ban-reason')?.value?.trim() || null,
                days: Number(daysText),
            };
        },
    });
    return result.isConfirmed ? result.value : null;
}

const bulkConfigs = () => Array.from(document.querySelectorAll('[data-admin-bulk-config]')).reduce((result, node) => {
    try {
        const config = JSON.parse(node.textContent || '{}');
        Object.keys(config).forEach((key) => {
            if (config[key] !== null && config[key] !== undefined) result[key] = config[key];
        });
    } catch (error) {
        console.error('Invalid admin bulk configuration.', error);
    }
    return result;
}, {});

function selectedBoxes(selector = '.bulk-checkbox') {
    return Array.from(document.querySelectorAll(selector)).filter((box) => box.checked && !box.disabled);
}

function updateBulkState(config) {
    const boxes = Array.from(document.querySelectorAll(config.checkboxSelector || '.bulk-checkbox'));
    const selected = boxes.filter((box) => box.checked && !box.disabled);
    document.querySelectorAll('.fc-bulk-count').forEach((count) => {
        count.textContent = selected.length ? `${selected.length} selected` : '';
        count.classList.toggle('show', selected.length > 0);
    });
    document.querySelectorAll('.bulk-action, #bulk-delete, #bulk-unban').forEach((button) => {
        button.disabled = selected.length === 0;
        button.setAttribute('aria-disabled', selected.length === 0 ? 'true' : 'false');
        button.style.opacity = selected.length === 0 ? '.4' : '1';
        button.style.pointerEvents = selected.length === 0 ? 'none' : 'auto';
    });
    document.querySelectorAll('[data-bulk="ban"], [data-petition-bulk-ban]').forEach((banButton) => {
        const disabled = !selected.length || selected.every((box) => box.dataset.level === 'banned');
        banButton.disabled = disabled;
        banButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        banButton.style.opacity = disabled ? '.35' : '1';
        banButton.style.pointerEvents = disabled ? 'none' : 'auto';
    });
}

function setLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.dataset.adminLoadingHtml = button.innerHTML;
        button.disabled = true;
        button.style.cursor = 'wait';
        button.style.opacity = '.7';
        const label = button.querySelector('.fc-btn-label');
        if (label) label.textContent = '...';
        return;
    }
    if (button.dataset.adminLoadingHtml) button.innerHTML = button.dataset.adminLoadingHtml;
    button.disabled = false;
    button.style.cursor = '';
    button.style.opacity = '';
}

const liveSearchTimers = new WeakMap();

const slugTranslit = {
    'ø': 'o', 'æ': 'ae', 'œ': 'oe', 'ß': 'ss', 'đ': 'd', 'ð': 'd', 'þ': 'th',
    'ł': 'l', 'ħ': 'h', 'ı': 'i', 'ĸ': 'k', 'ŋ': 'n', 'ſ': 's',
};

function isEditableTarget(node) {
    if (!(node instanceof Element)) return false;
    return !!node.closest('input, textarea, select, [contenteditable=""], [contenteditable="true"]');
}

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

async function notifyUndoDelete(message, route, ids) {
    const result = await Swal.fire({
        toast: true,
        position: 'top',
        icon: 'success',
        title: message,
        showConfirmButton: true,
        confirmButtonText: 'Undo',
        confirmButtonColor: '#1a57c8',
        timer: 6000,
        timerProgressBar: true,
    });
    if (!result.isConfirmed) return false;

    const restored = await jsonPost(route, { ids });
    const count = Number(restored.restored) || 0;
    if (count < 1) throw new Error('The selected records could not be restored.');
    notify('success', `Restored ${count} item${count === 1 ? '' : 's'}.`);
    return true;
}

async function runBulkAction(button, config) {
    const ids = selectedBoxes(config.checkboxSelector).map((box) => box.value);
    if (!ids.length) {
        notify('warning', config.emptyMsg || 'No items selected.');
        return;
    }
    const action = button.dataset.action || '';
    const noun = config.noun || 'items';
    if (!action || !(await confirmAction(`Run “${action}” on ${ids.length} selected ${noun}?`))) return;
    setLoading(button, true);
    try {
        await jsonPost(config.actionRoute, { action, ids });
        window.location.reload();
    } catch (error) {
        notify('error', error.message);
        setLoading(button, false);
        updateBulkState(config);
    }
}

async function runBulkNamedAction(button, config, action) {
    const ids = selectedBoxes(config.checkboxSelector).map((box) => box.value);
    if (!ids.length) {
        notify('warning', config.emptyMsg || 'No items selected.');
        return;
    }
    const noun = config.noun || 'items';
    if (!(await confirmAction(`${action === 'delete' ? 'Delete' : 'Unban'} ${ids.length} selected ${noun}?`))) return;
    setLoading(button, true);
    try {
        const result = await jsonPost(action === 'delete' ? config.deleteRoute : config.unbanRoute, { ids });
        if (action === 'delete' && [true, 1, '1'].includes(result.restorable)) {
            setLoading(button, false);
            try {
                await notifyUndoDelete(result.msg || `${result.deleted ?? ids.length} deleted.`, result.restorable_route || result.restoreRoute || config.restoreRoute, ids);
            } catch (error) {
                notify('error', `Deleted, but restore failed: ${error.message}`);
            }
        } else {
            notify('success', result.msg || (action === 'delete' ? 'Deleted.' : 'Unbanned.'));
        }
        window.location.reload();
    } catch (error) {
        notify('error', error.message);
        setLoading(button, false);
    }
}

async function runBulkBan(button, config, url = config.banRoute) {
    const ids = selectedBoxes(config.checkboxSelector).map((box) => box.value);
    if (!ids.length) {
        notify('warning', config.emptyMsg || 'No items selected.');
        return;
    }
    let details = { reason: null, days: 0 };
    if (button.dataset.banDetails === '1' || config.banWithDetails) {
        details = await banDetails();
        if (!details) return;
    } else if (!(await confirmAction((button.dataset.confirm || config.banConfirm || 'Ban selected items?').replace('?', ` (${ids.length})?`)))) {
        return;
    }
    setLoading(button, true);
    try {
        await jsonPost(url, { action: 'ban', ids, ...details });
        window.location.reload();
    } catch (error) {
        notify('error', error.message);
        setLoading(button, false);
        updateBulkState(config);
    }
}

async function handlePetitionToggle(button) {
    if (button.disabled) return;
    button.disabled = true;
    try {
        const action = button.dataset.action;
        const result = await jsonPost(button.dataset.url, { action, ids: [button.dataset.id] });
        const enabled = ['activate', 'feature'].includes(action);
        const isActive = button.dataset.kind === 'active';
        button.dataset.action = isActive ? (enabled ? 'deactivate' : 'activate') : (enabled ? 'unfeature' : 'feature');
        button.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        button.textContent = isActive ? (enabled ? 'Active' : 'Inactive') : (enabled ? 'Featured' : 'Feature');
        button.classList.toggle('border-[#b7d7b7]!', enabled);
        button.classList.toggle('bg-[#e8f5e8]!', enabled);
        button.classList.toggle('text-[#2f6f2f]!', enabled);
        button.classList.toggle('border-[#e2baba]!', !enabled);
        button.classList.toggle('bg-[#fff0f0]!', !enabled);
        button.classList.toggle('text-[#900]!', !enabled);
        notify('success', result.msg || 'Petition updated.');
    } catch (error) {
        notify('error', error.message || 'Could not update petition.');
    } finally {
        button.disabled = false;
    }
}

async function handlePetitionBan(button, config) {
    const ids = selectedBoxes(config.checkboxSelector).map((box) => box.value);
    if (!ids.length) {
        notify('warning', 'Select at least one petition to ban.');
        return;
    }
    if (!(await confirmAction(`Ban ${ids.length} selected petition(s)? This will unpublish and deactivate them.`))) return;
    setLoading(button, true);
    try {
        await jsonPost(button.dataset.url, { action: 'ban', ids });
        notify('success', 'Selected petitions were unpublished and deactivated.');
        window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        notify('error', error.message || 'Could not ban selected petitions.');
        setLoading(button, false);
    }
}

async function handleReconcile(button) {
    if (!(await confirmAction('Recalculate the confirmed signature count for this petition?'))) return;
    button.disabled = true;
    try {
        const result = await jsonPost(button.dataset.url, { id: button.dataset.id });
        notify('success', `Signature count reconciled: ${result.actual_count}.`);
    } catch (error) {
        notify('error', error.message || 'Could not reconcile petition count.');
    } finally {
        button.disabled = false;
    }
}

async function handleTranslateMissing(button) {
    const message = `Create draft translations for all ${button.dataset.count || ''} missing active locale(s), seeded from the source translation?`;
    if (!(await confirmAction(message, 'Translate to missing locales'))) return;
    button.disabled = true;
    try {
        const result = await jsonPost(button.dataset.url, {
            id: button.dataset.id,
            locale: button.dataset.source || undefined,
        });
        notify('success', `Created ${result.created} draft translation(s).`);
        window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        notify('error', error.message || 'Could not create missing translations.');
        button.disabled = false;
    }
}

function openAdminModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.dataset.open = '1';
    modal.setAttribute('aria-hidden', 'false');
    const form = modal.querySelector('form');
    if (form) form.style.display = '';
    modal.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
}

function closeAdminModal(modal) {
    if (!modal) return;
    modal.dataset.open = '0';
    modal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modal = document.querySelector('.fc-modal[data-open="1"]');
        if (modal) {
            closeAdminModal(modal);
            return;
        }
        const drawer = document.querySelector('.fc-drawer[data-open="1"]');
        if (drawer) {
            drawer.querySelector('.fc-drawer__close')?.click();
        }
        return;
    }
    if ((event.key === 's' || event.key === 'S') && !event.ctrlKey && !event.metaKey && !event.altKey && !isEditableTarget(event.target)) {
        const drawer = document.querySelector('.fc-drawer[data-open="1"]');
        if (!drawer) return;
        const submit = Array.from(drawer.querySelectorAll('button[type="submit"], input[type="submit"]'))
            .find((control) => control.offsetParent !== null && !control.disabled);
        if (!submit) return;
        event.preventDefault();
        submit.click();
    }
});

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

async function handleSignature(button) {
    if (!button || button.disabled) return;
    const action = button.dataset.signatureAction;
    if (action === 'reject' && !(await confirmAction('Reject this signature?'))) return;
    const group = button.closest('[data-signature-actions]');
    button.disabled = true;
    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ action }),
        });
        const result = await response.json().catch(() => ({}));
        if (response.status === 409) {
            notify('warning', result.msg || (action === 'approve' ? 'Already confirmed.' : 'Already rejected.'));
            button.disabled = false;
            window.setTimeout(() => window.location.reload(), 700);
            return;
        }
        if (!response.ok || !result.ok) throw new Error(result.error || 'Could not update signature.');
        const confirmed = action === 'approve';
        group.dataset.confirmed = confirmed ? '1' : '0';
        group.querySelectorAll('[data-signature-action]').forEach((control) => {
            const isCurrent = (control.dataset.signatureAction === 'approve') === confirmed;
            control.disabled = isCurrent;
            control.title = isCurrent ? (confirmed ? 'Already confirmed' : 'Already rejected') : (confirmed ? 'Reject signature' : 'Approve signature');
        });
        const status = group.closest('tr')?.querySelector('[data-signature-status]');
        if (status) {
            status.innerHTML = confirmed
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Confirmed"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round" title="Rejected"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
        }
        notify('success', confirmed ? 'Signature approved.' : 'Signature rejected.');
    } catch (error) {
        notify('error', error.message || 'Could not update signature.');
        button.disabled = false;
    }
}

async function handleUserAction(button) {
    const action = button.dataset.userSingleAction;
    let payload = {};
    if (action === 'ban') {
        payload = await banDetails();
        if (!payload) return;
        if (!(await confirmAction('Ban this user?'))) return;
    } else if (action === 'unban') {
        if (!(await confirmAction('Unban this user?'))) return;
    } else return;

    button.disabled = true;
    try {
        await jsonPost(button.dataset.url, payload);
        notify('success', action === 'ban' ? 'User banned.' : 'User unbanned.');
        window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        notify('error', error.message || 'Could not update user status.');
        button.disabled = false;
    }
}

function setupSidebar() {
    const sidebar = document.getElementById('fc-menu');
    const backdrop = document.getElementById('fc-menu-backdrop');
    const toggle = document.querySelector('.fc-menu-toggle');
    let lastFocused = null;
    function close() {
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
        toggle?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        lastFocused?.focus?.();
    }
    function open() {
        if (!sidebar || !backdrop) return;
        lastFocused = document.activeElement;
        sidebar.classList.add('show');
        backdrop.classList.add('show');
        toggle?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        sidebar.focus();
    }
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-admin-sidebar-toggle]')) {
            if (sidebar?.classList.contains('show')) close(); else open();
        }
        if (event.target.closest('[data-admin-sidebar-close]')) close();
        const groupTitle = event.target.closest('.fc-group .group-title');
        if (groupTitle) {
            const group = groupTitle.closest('.fc-group');
            const collapsed = !group.classList.contains('collapsed');
            group.classList.toggle('collapsed', collapsed);
            group.querySelectorAll(':scope > a').forEach((link) => {
                if (collapsed) link.style.setProperty('display', 'none', 'important');
                else link.style.removeProperty('display');
            });
            const label = (groupTitle.textContent || '').trim().toLowerCase().replace(/\s+/g, '-');
            try { localStorage.setItem(`fc-group-${label}`, collapsed ? '1' : '0'); } catch (_) {}
        }
        if (event.target.closest('#fc-back-top')) window.scrollTo({ top: 0, behavior: 'smooth' });
        if (event.target.closest('.fc-menu a') && window.innerWidth <= 768) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('show')) close();
    });
    if (sidebar) {
        sidebar.querySelectorAll('.fc-group').forEach((group, index) => {
            const title = group.querySelector('.group-title');
            if (!title) return;
            const label = (title.textContent || '').trim().toLowerCase().replace(/\s+/g, '-');
            try {
                if (localStorage.getItem(`fc-group-${label || index}`) === '1') {
                    group.classList.add('collapsed');
                    group.querySelectorAll(':scope > a').forEach((link) => link.style.setProperty('display', 'none', 'important'));
                }
            } catch (_) {}
        });
    }
    const backTop = document.getElementById('fc-back-top');
    if (backTop) window.addEventListener('scroll', () => { backTop.style.display = window.scrollY > 400 ? 'flex' : 'none'; });
}

function loadRichEditors() {
    const areas = document.querySelectorAll('textarea.fc-editor[data-ckeditor="rich"]');
    if (!areas.length) return;
    const activate = () => areas.forEach((area, index) => {
        try {
            if (!area.id) area.id = `fc-editor-${index}`;
            window.CKEDITOR.replace(area.id, {
                toolbar: [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'styles', items: ['Format'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table'] },
                    { name: 'tools', items: ['Source'] },
                ],
                height: 350,
            });
        } catch (_) { /* Leave the textarea usable if CKEditor cannot initialize. */ }
    });
    if (window.CKEDITOR) return activate();
    const script = document.createElement('script');
    script.src = 'https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js';
    script.onload = activate;
    document.head.appendChild(script);
}

async function copyText(value) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);
        return;
    }
    const field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.left = '-9999px';
    document.body.appendChild(field);
    field.select();
    const copied = document.execCommand('copy');
    field.remove();
    if (!copied) throw new Error('Copy failed.');
}

function handleSmtpToggle() {
    const input = document.getElementById('smtp_pass_input');
    const toggle = document.getElementById('toggle_smtp_pass');
    if (!input || !toggle) return;
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    toggle.innerHTML = visible
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" fill="none"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>';
}

document.addEventListener('DOMContentLoaded', () => {
    const config = bulkConfigs();
    document.querySelectorAll('[data-admin-flash]').forEach((node) => {
        notify(node.dataset.adminFlash || 'info', node.dataset.message || '');
    });
    setupSidebar();
    loadRichEditors();
    markRowOpenFromUrl();
    const backTop = document.getElementById('fc-back-top');
    if (backTop) backTop.style.display = window.scrollY > 400 ? 'flex' : 'none';
    updateBulkState(config);

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-admin-autosubmit]')) event.target.form?.requestSubmit();
        if (event.target.matches(config.checkboxSelector || '.bulk-checkbox')) updateBulkState(config);
    });

    document.addEventListener('mouseover', (event) => {
        const row = event.target.closest('[data-admin-row-hover]');
        if (row && !row.contains(event.relatedTarget)) row.style.background = '#f7f7f7';
    });
    document.addEventListener('mouseout', (event) => {
        const row = event.target.closest('[data-admin-row-hover]');
        if (row && !row.contains(event.relatedTarget)) row.style.background = '';
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-admin-confirm]');
        if (!form || form.dataset.adminConfirmed === '1') return;
        event.preventDefault();
        if (await confirmAction(form.dataset.adminConfirm || 'Continue?')) {
            form.dataset.adminConfirmed = '1';
            form.requestSubmit();
        }
    });

    document.addEventListener('click', async (event) => {
        const payloadToggle = event.target.closest('[data-payload-toggle]');
        if (payloadToggle) {
            const row = document.getElementById(`payload-${payloadToggle.dataset.payloadToggle}`);
            if (row) row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            return;
        }
        const payloadCopy = event.target.closest('[data-payload-copy]');
        if (payloadCopy) {
            const content = document.getElementById(`payload-content-${payloadCopy.dataset.payloadCopy}`);
            if (!content) return;
            try {
                await copyText(content.textContent || '');
                notify('success', 'Copied!');
            } catch (_) {
                notify('error', 'Copy failed.');
            }
            return;
        }
        if (event.target.closest('#toggle_smtp_pass')) {
            handleSmtpToggle();
            return;
        }
        const petitionToggle = event.target.closest('[data-petition-toggle]');
        if (petitionToggle) return handlePetitionToggle(petitionToggle);
        const petitionBan = event.target.closest('[data-petition-bulk-ban]');
        if (petitionBan) return handlePetitionBan(petitionBan, config);
        const reconcile = event.target.closest('[data-reconcile-one]');
        if (reconcile) return handleReconcile(reconcile);
        const translateMissing = event.target.closest('[data-translate-missing]');
        if (translateMissing) return handleTranslateMissing(translateMissing);
        const createToggle = event.target.closest('[data-petition-create-toggle]');
        if (createToggle) {
            event.preventDefault();
            return openAdminModal('petition-create-modal');
        }

        const modalClose = event.target.closest('[data-admin-modal-close]');
        if (modalClose) {
            event.preventDefault();
            const modal = modalClose.closest('.fc-modal');
            if (modal) closeAdminModal(modal);
            return;
        }
        const signature = event.target.closest('[data-signature-action]');
        if (signature) return handleSignature(signature);
        const userAction = event.target.closest('[data-user-single-action]');
        if (userAction) return handleUserAction(userAction);

        const selection = event.target.closest('[data-bulk], #bulk-all, #bulk-none, #bulk-invert');
        if (selection) {
            if (selection.matches('[aria-disabled="true"], :disabled')) {
                event.preventDefault();
                return;
            }
            const action = selection.dataset.bulk || selection.id.replace('bulk-', '');
            if (['all', 'none', 'invert'].includes(action)) {
                event.preventDefault();
                const boxes = Array.from(document.querySelectorAll(config.checkboxSelector || '.bulk-checkbox'));
                boxes.forEach((box) => {
                    if (box.disabled) return;
                    if (action === 'all') box.checked = true;
                    else if (action === 'none') box.checked = false;
                    else box.checked = !box.checked;
                });
                updateBulkState(config);
                return;
            }
            if (action === 'ban') {
                event.preventDefault();
                if (selection.dataset.url) return handlePetitionBan(selection, config);
                return runBulkBan(selection, config);
            }
        }

        const actionButton = event.target.closest('.bulk-action');
        if (actionButton) return runBulkAction(actionButton, config);
        const deleteButton = event.target.closest('#bulk-delete');
        if (deleteButton) return runBulkNamedAction(deleteButton, config, 'delete');
        const unbanButton = event.target.closest('#bulk-unban');
        if (unbanButton) return runBulkNamedAction(unbanButton, config, 'unban');
    });

    document.querySelectorAll('form').forEach((form) => {
        const controls = form.querySelectorAll('[data-loading]');
        if (!controls.length || form.dataset.adminLoadingBound === '1') return;
        form.dataset.adminLoadingBound = '1';
        form.addEventListener('submit', (event) => {
            window.setTimeout(() => {
                if (event.defaultPrevented) return;
                controls.forEach((control) => {
                    if (control.dataset.adminLoadingActive === '1') return;
                    control.dataset.adminLoadingActive = '1';
                    if (typeof control.disabled === 'boolean') control.disabled = true;
                    control.setAttribute('aria-busy', 'true');
                    const label = control.querySelector('.fcui-btn__label');
                    if (label && control.dataset.loadingText) label.textContent = control.dataset.loadingText;
                });
            }, 0);
        });
    });
});
