window.fcConfirm = (message, title = 'Please confirm') => new Promise((resolve) => {
    if (!window.__fcConfirmReady) {
        resolve(window.confirm(message));
        return;
    }

    window.dispatchEvent(new CustomEvent('fc-confirm', { detail: { message, title, resolve } }));
});
