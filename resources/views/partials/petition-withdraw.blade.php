<form method="POST" action="{{ lroute('petition.withdraw', ['slug' => $slug, 'id' => $id]) }}" data-petition-withdraw
    data-confirm-title="{{ __('myp.withdraw_title') }}"
    data-confirm-text="{{ __('myp.withdraw_text') }}"
    data-confirm-ok="{{ __('myp.withdraw_ok') }}"
    data-confirm-cancel="{{ __('myp.withdraw_cancel') }}">
    @csrf
    <x-ui.button variant="danger" size="sm">{{ __('myp.withdraw') }}</x-ui.button>
</form>
