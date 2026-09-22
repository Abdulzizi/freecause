@php
    $actionRoute = $actionRoute ?? null;
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const route = @json($actionRoute);
        const checkboxSelector = @json($checkboxSelector ?? '.bulk-checkbox');

        function getBoxes() {
            return Array.from(document.querySelectorAll(checkboxSelector));
        }

        function selectedIds() {
            return getBoxes().filter(cb => cb.checked).map(cb => cb.value);
        }

        function setLoading(btn, on) {
            if (on) {
                btn.dataset.origHtml = btn.innerHTML;
                const label = btn.querySelector('.fc-btn-label');
                const ico = btn.querySelector('svg, .fc-bulk-ico, i');
                if (label) label.textContent = '...';
                if (ico) ico.style.opacity = '0.3';
                btn.disabled = true;
                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';
            } else {
                if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
                btn.disabled = false;
                btn.style.opacity = '';
                btn.style.cursor = '';
            }
        }

        function updateButtons() {
            const anyChecked = selectedIds().length > 0;

            document.querySelectorAll('.bulk-action').forEach(btn => {
                if (btn.disabled && btn.style.cursor === 'wait') return;
                btn.style.opacity = anyChecked ? '1' : '.4';
                btn.style.pointerEvents = anyChecked ? 'auto' : 'none';
            });
        }

        // ALL
        document.getElementById('bulk-all')?.addEventListener('click', function() {
            getBoxes().forEach(cb => cb.checked = true);
            updateButtons();
        });

        // NONE
        document.getElementById('bulk-none')?.addEventListener('click', function() {
            getBoxes().forEach(cb => cb.checked = false);
            updateButtons();
        });

        // INVERT
        document.getElementById('bulk-invert')?.addEventListener('click', function() {
            getBoxes().forEach(cb => cb.checked = !cb.checked);
            updateButtons();
        });

        // GENERIC BULK ACTION (publish, activate, feature, ban, etc)
        document.querySelectorAll('.bulk-action').forEach(btn => {
            btn.addEventListener('click', function() {

                const ids = selectedIds();
                if (!ids.length) {
                    alert(@json($emptyMsg ?? 'No items selected'));
                    return;
                }

                const action = btn.dataset.action || '';
                if (!action) return;

                const msg = 'Run "' + action + '" on ' + ids.length + ' selected ' +
                    @json($noun ?? 'items') + '?';
                if (!confirm(msg)) return;

                setLoading(btn, true);

                fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: JSON.stringify({
                            action,
                            ids
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res && res.ok) location.reload();
                        else {
                            alert((res && res.msg) ? res.msg : 'Operation failed');
                            setLoading(btn, false);
                            updateButtons();
                        }
                    })
                    .catch(() => {
                        alert('Network error');
                        setLoading(btn, false);
                        updateButtons();
                    });
            });
        });

        document.getElementById('bulk-delete')?.addEventListener('click', function() {
            const btn = this;
            const ids = Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
                .map(cb => cb.value);

            if (!ids.length) {
                alert("{{ $emptyMsg }}");
                return;
            }

            if (!confirm("Delete selected {{ $noun }}?")) return;

            setLoading(btn, true);

            fetch("{{ $deleteRoute ?? '' }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(() => window.location.reload())
                .catch(() => {
                    alert('Network error');
                    setLoading(btn, false);
                });
        });

        document.getElementById('bulk-unban')?.addEventListener('click', function() {
            const btn = this;
            const ids = Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
                .map(cb => cb.value);

            if (!ids.length) {
                alert("{{ $emptyMsg }}");
                return;
            }

            if (!confirm("Unban selected {{ $noun }}?")) return;

            setLoading(btn, true);

            fetch("{{ $unbanRoute ?? '' }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(() => window.location.reload())
                .catch(() => {
                    alert('Network error');
                    setLoading(btn, false);
                });
        });

        getBoxes().forEach(cb => cb.addEventListener('change', updateButtons));
        updateButtons();

    });
</script>
