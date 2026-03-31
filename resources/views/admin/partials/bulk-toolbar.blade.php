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
           data-ban-details="{{ ($banWithDetails ?? false) ? '1' : '0' }}"
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
        const boxes = getBoxes();
        const selected = boxes.filter(cb => cb.checked);

        const banBtn = document.querySelector('[data-bulk="ban"]');
        if (!banBtn) return;

        if (!selected.length) {
            banBtn.style.opacity = '.4';
            banBtn.style.pointerEvents = 'none';
            return;
        }

        const allBanned = selected.every(cb => cb.dataset.level === 'banned');

        if (allBanned) {
            banBtn.style.opacity = '.4';
            banBtn.style.pointerEvents = 'none';
        } else {
            banBtn.style.opacity = '1';
            banBtn.style.pointerEvents = 'auto';
        }
    }

    document.querySelectorAll('[data-bulk]').forEach(el => {
        el.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

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

                const withDetails = this.dataset.banDetails === '1';
                let reason = null;
                let days = 0;

                if (withDetails) {
                    reason = prompt('Ban reason (optional):') || null;
                    const daysInput = prompt('Ban duration in days (0 = permanent):');
                    days = daysInput !== null ? parseInt(daysInput, 10) || 0 : 0;
                }

                fetch(@json($banRoute ?? ''), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids, reason, days })
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
