
<div
    x-data="{ message: '', title: 'Please confirm', resolver: null }"
    x-init="window.__fcConfirmReady = true"
    @fc-confirm.window="message = $event.detail.message; title = $event.detail.title; resolver = $event.detail.resolve; $dispatch('open-alert-dialog-fc-confirm')"
    @keydown.escape.window="if (resolver) { resolver(false); resolver = null }"
    class="contents"
>
    <x-blatui.alert-dialog id="fc-confirm">
        <x-blatui.alert-dialog-content>
            <x-blatui.alert-dialog-header>
                <x-blatui.alert-dialog-title x-text="title"></x-blatui.alert-dialog-title>
                <x-blatui.alert-dialog-description x-text="message"></x-blatui.alert-dialog-description>
            </x-blatui.alert-dialog-header>
            <x-blatui.alert-dialog-footer>
                <x-blatui.alert-dialog-cancel @click="resolver?.(false); resolver = null">Cancel</x-blatui.alert-dialog-cancel>
                <x-blatui.alert-dialog-action @click="resolver?.(true); resolver = null">Continue</x-blatui.alert-dialog-action>
            </x-blatui.alert-dialog-footer>
        </x-blatui.alert-dialog-content>
    </x-blatui.alert-dialog>
</div>
