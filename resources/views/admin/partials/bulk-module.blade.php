{{-- Shared B0 admin bulk-action behavior used by both legacy partial adapters and x-admin.bulk-toolbar. --}}
<script>
(function () {
    const initialConfig = @json($bulkConfig ?? []);
    const api = window.FreecauseAdminBulk || (window.FreecauseAdminBulk = (function () {
        const config = {};
        let initialized = false;

        function getBoxes() {
            const selector = config.checkboxSelector || '.bulk-checkbox';
            return Array.from(document.querySelectorAll(selector));
        }

        function selectedIds(selector) {
            return Array.from(document.querySelectorAll(selector || config.checkboxSelector || '.bulk-checkbox'))
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.value);
        }

        function csrfToken() {
            return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
        }

        function updateState() {
            const boxes = getBoxes();
            const selected = boxes.filter((checkbox) => checkbox.checked);
            const count = selected.length;
            const countEl = document.getElementById('fc-bulk-count');

            if (countEl) {
                countEl.textContent = count ? count + ' selected' : '';
                countEl.classList.toggle('show', count > 0);
            }

            document.querySelectorAll('.bulk-action').forEach((button) => {
                if (button.disabled && button.style.cursor === 'wait') return;
                button.disabled = count === 0;
                button.style.opacity = count === 0 ? '.4' : '1';
                button.style.pointerEvents = count === 0 ? 'none' : 'auto';
            });

            const banButton = document.querySelector('[data-bulk="ban"]');
            if (banButton) {
                const disabled = !count || selected.every((checkbox) => checkbox.dataset.level === 'banned');
                banButton.style.opacity = disabled ? '.35' : '1';
                banButton.style.pointerEvents = disabled ? 'none' : 'auto';
            }
        }

        function setLoading(button, loading) {
            if (loading) {
                button.dataset.origHtml = button.innerHTML;
                const label = button.querySelector('.fc-btn-label');
                const icon = button.querySelector('svg, .fc-bulk-ico, i');
                if (label) label.textContent = '...';
                if (icon) icon.style.opacity = '0.3';
                button.disabled = true;
                button.style.opacity = '0.7';
                button.style.cursor = 'wait';
                return;
            }

            if (button.dataset.origHtml) button.innerHTML = button.dataset.origHtml;
            button.disabled = false;
            button.style.opacity = '';
            button.style.cursor = '';
        }

        function postJson(url, body) {
            return fetch(url || '', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(body),
            }).then((response) => response.json());
        }

        function applySelection(action) {
            const boxes = getBoxes();
            if (action === 'all') boxes.forEach((checkbox) => { checkbox.checked = true; });
            if (action === 'none') boxes.forEach((checkbox) => { checkbox.checked = false; });
            if (action === 'invert') boxes.forEach((checkbox) => { checkbox.checked = !checkbox.checked; });
            updateState();
        }

        function init() {
            if (initialized) return;
            initialized = true;

            document.addEventListener('click', function (event) {
                const toolbarAction = event.target.closest('[data-bulk]');
                if (toolbarAction) {
                    const action = toolbarAction.dataset.bulk;
                    if (['all', 'none', 'invert'].includes(action)) {
                        event.preventDefault();
                        event.stopPropagation();
                        applySelection(action);
                        return;
                    }

                    if (action === 'ban') {
                        event.preventDefault();
                        event.stopPropagation();
                        const ids = selectedIds();
                        if (!ids.length) {
                            window.alert(config.emptyMsg || 'No items selected');
                            return;
                        }

                        const confirmText = (toolbarAction.dataset.confirm || config.banConfirm || 'Apply action?')
                            .replace('?', ' (' + ids.length + ')?');
                        if (!window.confirm(confirmText)) return;

                        let reason = null;
                        let days = 0;
                        if (toolbarAction.dataset.banDetails === '1' || config.banWithDetails) {
                            reason = window.prompt('Ban reason (optional):') || null;
                            const daysInput = window.prompt('Ban duration in days (0 = permanent):');
                            days = daysInput !== null ? parseInt(daysInput, 10) || 0 : 0;
                        }

                        postJson(config.banRoute || toolbarAction.dataset.route, { ids, reason, days })
                            .then((result) => {
                                if (result && result.ok) window.location.reload();
                                else window.alert('Operation failed');
                            })
                            .catch(() => window.alert('Network error'));
                        return;
                    }
                }

                const legacySelection = event.target.closest('#bulk-all, #bulk-none, #bulk-invert');
                if (legacySelection) {
                    event.preventDefault();
                    applySelection(legacySelection.id.replace('bulk-', ''));
                    return;
                }

                const bulkAction = event.target.closest('.bulk-action');
                if (bulkAction) {
                    const ids = selectedIds(config.checkboxSelector);
                    if (!ids.length) {
                        window.alert(config.emptyMsg || 'No items selected');
                        return;
                    }
                    const action = bulkAction.dataset.action || '';
                    if (!action) return;
                    const noun = config.noun || 'items';
                    if (!window.confirm('Run "' + action + '" on ' + ids.length + ' selected ' + noun + '?')) return;

                    setLoading(bulkAction, true);
                    postJson(config.actionRoute, { action, ids })
                        .then((result) => {
                            if (result && result.ok) window.location.reload();
                            else {
                                window.alert(result && result.msg ? result.msg : 'Operation failed');
                                setLoading(bulkAction, false);
                                updateState();
                            }
                        })
                        .catch(() => {
                            window.alert('Network error');
                            setLoading(bulkAction, false);
                            updateState();
                        });
                    return;
                }

                const deleteButton = event.target.closest('#bulk-delete');
                if (deleteButton) {
                    const ids = selectedIds(config.checkboxSelector);
                    if (!ids.length) {
                        window.alert(config.emptyMsg || 'No items selected');
                        return;
                    }
                    if (!window.confirm('Delete selected ' + (config.noun || 'items') + '?')) return;
                    setLoading(deleteButton, true);
                    postJson(config.deleteRoute, { ids })
                        .then(() => window.location.reload())
                        .catch(() => {
                            window.alert('Network error');
                            setLoading(deleteButton, false);
                        });
                    return;
                }

                const unbanButton = event.target.closest('#bulk-unban');
                if (unbanButton) {
                    const ids = selectedIds(config.checkboxSelector);
                    if (!ids.length) {
                        window.alert(config.emptyMsg || 'No items selected');
                        return;
                    }
                    if (!window.confirm('Unban selected ' + (config.noun || 'items') + '?')) return;
                    setLoading(unbanButton, true);
                    postJson(config.unbanRoute, { ids })
                        .then(() => window.location.reload())
                        .catch(() => {
                            window.alert('Network error');
                            setLoading(unbanButton, false);
                        });
                }
            });

            document.addEventListener('change', function (event) {
                if (event.target.matches(config.checkboxSelector || '.bulk-checkbox')) updateState();
            });

            updateState();
        }

        return {
            register(nextConfig) {
                Object.keys(nextConfig || {}).forEach((key) => {
                    if (nextConfig[key] !== null && nextConfig[key] !== undefined) config[key] = nextConfig[key];
                });
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
                else init();
            },
        };
    })());

    api.register(initialConfig);
})();
</script>
