<div class="fc-bulk-bar">
    <div class="fc-bulk-select">
        <a href="#" data-bulk="all" title="Select all" aria-label="Select all" class="fc-bulk-link">
            <span class="fc-bulk-ico">✓</span>
            <span class="bulk-label">All</span>
        </a>

        <a href="#" data-bulk="none" title="Deselect all" aria-label="Deselect all" class="fc-bulk-link">
            <span class="fc-bulk-ico">□</span>
            <span class="bulk-label">None</span>
        </a>

        <a href="#" data-bulk="invert" title="Invert selection" aria-label="Invert selection" class="fc-bulk-link">
            <span class="fc-bulk-ico">↔</span>
            <span class="bulk-label">Invert</span>
        </a>
    </div>

    <span class="fc-bulk-count" id="fc-bulk-count" aria-live="polite"></span>

    @isset($banRoute)
        <div class="fc-bulk-actions">
            <a href="#" data-bulk="ban"
               data-confirm="{{ $banConfirm ?? 'Apply action to selected items?' }}"
               data-ban-details="{{ ($banWithDetails ?? false) ? '1' : '0' }}"
               title="{{ $banLabel ?? 'Ban' }}"
               aria-label="{{ $banLabel ?? 'Ban' }}"
               class="fc-bulk-link fc-bulk-danger">
                <span class="fc-bulk-ico">✖</span>
                <span class="bulk-label">{{ $banLabel ?? 'Banned' }}</span>
            </a>
        </div>
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
        const count = selected.length;
        const countEl = document.getElementById('fc-bulk-count');

        /* count badge */
        if (countEl) {
            countEl.textContent = count ? count + ' selected' : '';
            countEl.classList.toggle('show', count > 0);
        }

        /* disable all action buttons when nothing selected */
        document.querySelectorAll('.fc-toolbar-right .fc-icon-btn').forEach(btn => {
            btn.disabled = count === 0;
            btn.style.opacity = count === 0 ? '.35' : '1';
            btn.style.pointerEvents = count === 0 ? 'none' : 'auto';
        });

        /* ban-specific: also disable if all selected are already banned */
        const banBtn = document.querySelector('[data-bulk="ban"]');
        if (banBtn) {
            if (!count || selected.every(cb => cb.dataset.level === 'banned')) {
                banBtn.style.opacity = '.35';
                banBtn.style.pointerEvents = 'none';
            } else {
                banBtn.style.opacity = '1';
                banBtn.style.pointerEvents = 'auto';
            }
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
