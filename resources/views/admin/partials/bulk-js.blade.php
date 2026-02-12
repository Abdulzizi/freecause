<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxSelector = @json($checkboxSelector ?? '.bulk-checkbox');

        function getBoxes() {
            return Array.from(document.querySelectorAll(checkboxSelector));
        }

        function selectedIds() {
            return getBoxes().filter(cb => cb.checked).map(cb => cb.value);
        }

        function setBulkEnabled() {
            const anyChecked = selectedIds().length > 0;
            const btn = document.getElementById('bulk-banned');
            if (!btn) return;

            btn.style.opacity = anyChecked ? '1' : '.4';
            btn.style.pointerEvents = anyChecked ? 'auto' : 'none';
        }

        document.getElementById('bulk-all')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = true);
            setBulkEnabled();
        });

        document.getElementById('bulk-none')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = false);
            setBulkEnabled();
        });

        document.getElementById('bulk-invert')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = !cb.checked);
            setBulkEnabled();
        });

        document.querySelectorAll('.bulk-action').forEach(btn => {
            btn.style.opacity = anyChecked ? '1' : '.4';
            btn.style.pointerEvents = anyChecked ? 'auto' : 'none';
        });

        document.querySelectorAll('.bulk-action').forEach(btn => {
            btn.addEventListener('click', function () {
                const ids = selectedIds();
                if (!ids.length) {
                    alert(@json($emptyMsg ?? 'No items selected'));
                    return;
                }

                const action = btn.dataset.action || '';
                if (!action) return;

                const msg = 'Run "' + action + '" on ' + ids.length + ' selected ' + @json($noun ?? 'items') + '?';
                if (!confirm(msg)) return;

                fetch(@json($actionRoute), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body: JSON.stringify({ action, ids })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res && res.ok) location.reload();
                        else alert((res && res.msg) ? res.msg : 'Operation failed');
                    })
                    .catch(() => alert('Network error'));
            });
        });

        getBoxes().forEach(cb => cb.addEventListener('change', setBulkEnabled));
        setBulkEnabled();

        document.getElementById('bulk-banned')?.addEventListener('click', function () {
            const ids = selectedIds();

            if (!ids.length) {
                alert(@json($emptyMsg ?? 'No items selected'));
                return;
            }

            const noun = @json($noun ?? 'items');
            const msg = ids.length === 1
                ? ('Ban 1 selected ' + noun + '?')
                : ('Ban ' + ids.length + ' selected ' + noun + '?');

            if (!confirm(msg)) return;

            fetch(@json($banRoute), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify({ ids })
            })
                .then(r => r.json())
                .then(res => {
                    if (res && res.ok) location.reload();
                    else alert('Operation failed');
                })
                .catch(() => alert('Network error'));
        });
    });
</script>
