{{--
    Admin confirmation-style modal. Hidden by default; opened via
    [data-admin-modal="id"] (delegated in admin.js) or data-open via JSON.
--}}
@props([
    'id' => 'fc-modal',
    'title' => 'dialog',
    'maxWidth' => '560px',
])

<div class="fc-modal" id="{{ $id }}" data-open="0" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="fc-modal__backdrop" data-admin-modal-close></div>

    <div class="fc-modal__panel" style="max-width: {{ $maxWidth }};">
        <div class="fc-modal__head">
            <span>{{ $title }}</span>
            <button type="button" class="fc-drawer__close" data-admin-modal-close title="Close" aria-label="Close">&times;</button>
        </div>
        <div class="fc-modal__body">
            {{ $slot }}
        </div>
    </div>
</div>
