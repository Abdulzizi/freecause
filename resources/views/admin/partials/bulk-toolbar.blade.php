<div style="font-size:11px; color:#555;">
    <a href="#" data-bulk="all" style="margin-right:12px; text-decoration:none; color:#555;">
        <span style="display:inline-block; width:14px; text-align:center;">✓</span>
        <span>All</span>
    </a>

    <a href="#" data-bulk="none" style="margin-right:12px; text-decoration:none; color:#555;">
        <span style="display:inline-block; width:14px; text-align:center;">□</span>
        <span>None</span>
    </a>

    <a href="#" data-bulk="invert" style="margin-right:12px; text-decoration:none; color:#555;">
        <span style="display:inline-block; width:14px; text-align:center;">↔</span>
        <span>Invert</span>
    </a>

    @isset($banRoute)
        <a href="#" data-bulk="ban"
           data-confirm="{{ $banConfirm ?? 'Apply action to selected items?' }}"
           style="text-decoration:none; color:#900;">
            <span style="display:inline-block; width:14px; text-align:center;">✖</span>
            <span>{{ $banLabel ?? 'Banned' }}</span>
        </a>
    @endisset
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function getBoxes() {
        return Array.from(document.querySelectorAll('.bulk-checkbox'));
    }

    function updateState() {
        const anyChecked = getBoxes().some(cb => cb.checked);
        const banBtn = document.querySelector('[data-bulk="ban"]');

        if (banBtn) {
            banBtn.style.opacity = anyChecked ? '1' : '.4';
            banBtn.style.pointerEvents = anyChecked ? 'auto' : 'none';
        }
    }

    document.querySelectorAll('[data-bulk]').forEach(el => {
        el.addEventListener('click', function (e) {

            e.preventDefault();      // 🔥 critical
            e.stopPropagation();     // 🔥 prevents form submission

            const action = this.dataset.bulk;
            const boxes = getBoxes();
            const confirmText = this.dataset.confirm || 'Apply action?';

            if (action === 'all') {
                boxes.forEach(cb => cb.checked = true);
            }

            if (action === 'none') {
                boxes.forEach(cb => cb.checked = false);
            }

            if (action === 'invert') {
                boxes.forEach(cb => cb.checked = !cb.checked);
            }

            if (action === 'ban') {
                const ids = boxes.filter(cb => cb.checked).map(cb => cb.value);

                if (!ids.length) {
                    alert('No items selected');
                    return;
                }

                if (!confirm(confirmText.replace('?', ' (' + ids.length + ')?'))) {
                    return;
                }

                fetch(@json($banRoute ?? ''), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.ok) location.reload();
                    else alert('Operation failed');
                })
                .catch(() => alert('Network error'));
            }

            updateState();
        });
    });

    getBoxes().forEach(cb => cb.addEventListener('change', updateState));
    updateState();
});
</script>
