<div class="flex! items-center! gap-2.5! flex-wrap! max-md:gap-1.5!">
    <div class="flex! items-center! gap-1!">
        <a href="#" data-bulk="all" title="Select all" aria-label="Select all" class="inline-flex! items-center! gap-1! no-underline! text-[#555]! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
            <span class="max-md:hidden!">All</span>
        </a>

        <a href="#" data-bulk="none" title="Deselect all" aria-label="Deselect all" class="inline-flex! items-center! gap-1! no-underline! text-[#555]! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18"></rect></svg></span>
            <span class="max-md:hidden!">None</span>
        </a>

        <a href="#" data-bulk="invert" title="Invert selection" aria-label="Invert selection" class="inline-flex! items-center! gap-1! no-underline! text-[#555]! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 3L3 21"></path><path d="M3 3L21 21"></path></svg></span>
            <span class="max-md:hidden!">Invert</span>
        </a>
    </div>

    <span class="fc-bulk-count items-center! py-0.5! px-2! bg-[#e8f0fe]! text-[#1a5fb4]! rounded-[10px]! text-[11px]! font-semibold! whitespace-nowrap! leading-[1.6]! ml-2!" id="fc-bulk-count" aria-live="polite"></span>

    @isset($banRoute)
        <div class="flex! items-center! gap-1! ml-1! pl-2.5! border-l! border-[#ddd]!">
            <a href="#" data-bulk="ban"
               data-confirm="{{ $banConfirm ?? 'Apply action to selected items?' }}"
               data-ban-details="{{ ($banWithDetails ?? false) ? '1' : '0' }}"
               title="{{ $banLabel ?? 'Ban' }}"
               aria-label="{{ $banLabel ?? 'Ban' }}"
               class="inline-flex! items-center! gap-1! no-underline! text-[#900]! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#fff0f0]!">
                <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></span>
                <span class="max-md:hidden!">{{ $banLabel ?? 'Banned' }}</span>
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
