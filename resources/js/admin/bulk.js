import Swal from 'sweetalert2';
import { csrf, notify, confirmAction, jsonPost, banDetails, setLoading } from './core.js';

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

export function init() {
    const config = bulkConfigs();
    updateBulkState(config);

    document.addEventListener('change', (event) => {
        if (event.target.matches(config.checkboxSelector || '.bulk-checkbox')) updateBulkState(config);
    });

    document.addEventListener('click', async (event) => {
        const petitionToggle = event.target.closest('[data-petition-toggle]');
        if (petitionToggle) return handlePetitionToggle(petitionToggle);
        const petitionBan = event.target.closest('[data-petition-bulk-ban]');
        if (petitionBan) return handlePetitionBan(petitionBan, config);
        const reconcile = event.target.closest('[data-reconcile-one]');
        if (reconcile) return handleReconcile(reconcile);
        const translateMissing = event.target.closest('[data-translate-missing]');
        if (translateMissing) return handleTranslateMissing(translateMissing);
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
}
