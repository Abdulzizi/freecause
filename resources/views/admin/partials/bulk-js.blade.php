@php
    $actionRoute = $actionRoute ?? null;
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {

    const route = @json($actionRoute);
    const checkboxSelector = @json($checkboxSelector ?? '.bulk-checkbox');

    function getBoxes() {
        return Array.from(document.querySelectorAll(checkboxSelector));
    }

    function selectedIds() {
        return getBoxes().filter(cb => cb.checked).map(cb => cb.value);
    }

    function updateButtons() {
        const anyChecked = selectedIds().length > 0;

        document.querySelectorAll('.bulk-action').forEach(btn => {
            btn.style.opacity = anyChecked ? '1' : '.4';
            btn.style.pointerEvents = anyChecked ? 'auto' : 'none';
        });
    }

    // ALL
    document.getElementById('bulk-all')?.addEventListener('click', function () {
        getBoxes().forEach(cb => cb.checked = true);
        updateButtons();
    });

    // NONE
    document.getElementById('bulk-none')?.addEventListener('click', function () {
        getBoxes().forEach(cb => cb.checked = false);
        updateButtons();
    });

    // INVERT
    document.getElementById('bulk-invert')?.addEventListener('click', function () {
        getBoxes().forEach(cb => cb.checked = !cb.checked);
        updateButtons();
    });

    // GENERIC BULK ACTION (publish, activate, feature, ban, etc)
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

            fetch(route, {
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

    getBoxes().forEach(cb => cb.addEventListener('change', updateButtons));
    updateButtons();

});
</script>
