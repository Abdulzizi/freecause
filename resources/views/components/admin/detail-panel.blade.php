{{--
    Right-side editing sheet for admin detail panels.
    Opens from the ?select= param and closes by navigating to closeRoute (dropping ?select=).
    Dirty forms are guarded through the shared BlatUI confirm dialog.
--}}
@props([
    'title' => 'detail',
    'closeRoute' => null,
    'drawerId' => 'fc-detail-drawer',
])

@php
    $open = filled(request()->query('select'));
@endphp

@if ($open)
    <div
        x-data="{
            open: true,
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                const proceed = (ok) => {
                    if (!ok) { this.closing = false; this.open = true; return; }
                    window.fcDrawer?.clearDirty?.();
                    window.location.href = @js($closeRoute);
                };
                if (window.fcDrawer?.hasDirty?.() && window.fcConfirm) {
                    window.fcConfirm('Discard unsaved changes?', 'Unsaved changes').then(proceed);
                } else {
                    proceed(true);
                }
            },
        }"
        x-effect="if (!open) close()"
        class="contents"
    >
        <x-blatui.sheet-content :showClose="false" class="fc-drawer gap-0! p-0!" id="{{ $drawerId }}" data-open="1">
            <div class="fc-drawer__head">
                <span>{{ $title }}</span>
                @if ($closeRoute)
                    <a href="{{ $closeRoute }}" class="fc-drawer__close" title="Close panel" aria-label="Close panel">&times;</a>
                @endif
            </div>
            <div class="fc-drawer__body">
                {{ $slot }}
            </div>
        </x-blatui.sheet-content>
    </div>
@endif
