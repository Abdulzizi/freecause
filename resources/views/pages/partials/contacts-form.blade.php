@if (session('contact_sent'))
    <p>{{ __('contacts.sent') }}</p>
    <p><a class="fcui-link" href="{{ route('contacts', app()->getLocale()) }}">{{ __('contacts.send_another') }}</a></p>
@else
@if ($errors->any())
    <x-ui.alert variant="error" class="mb-4">
        {{ $errors->first() }}
    </x-ui.alert>
@endif

<form method="post" action="{{ route('contacts.submit', app()->getLocale()) }}"
    id="contactForm"
    data-sending="{{ __('contacts.sending') }}"
    data-sent="{{ __('contacts.sent') }}"
    data-failed="{{ __('contacts.failed') }}">
    @csrf

    <fieldset>
        <input type="hidden" name="__contacts" value="1" />

        <div class="mb-4">
            <x-ui.input
                type="text"
                id="contact_name"
                name="name"
                :label="__('contacts.name_label')"
                :value="old('name')"
                :placeholder="__('contacts.name_placeholder')"
                :required="true"
                :error="$errors->first('name')"
                autocomplete="name"
            />
        </div>

        <div class="mb-4">
            <x-ui.input
                type="email"
                id="contact_email"
                name="email"
                :label="__('contacts.email_label')"
                :value="old('email')"
                :placeholder="__('contacts.email_placeholder')"
                :required="true"
                :error="$errors->first('email')"
                autocomplete="email"
            />
        </div>

        <div class="fcui-field mb-4">
            <label for="contact_text" class="fcui-field__label">
                {{ __('contacts.text_label') }}
                <span class="fcui-field__required" aria-hidden="true">*</span>
            </label>
            <textarea
                id="contact_text"
                name="text"
                rows="5"
                placeholder="{{ __('contacts.text_placeholder') }}"
                required
                @class(['fcui-input', 'fcui-input--error' => $errors->first('text')])
            >{{ old('text') }}</textarea>
            @if ($errors->first('text'))
                <p class="fcui-field__error">{{ $errors->first('text') }}</p>
            @endif
        </div>

        <div>
            @if (\App\Support\Spam::turnstileEnabled())
                <div class="mb-3 cf-turnstile" data-sitekey="{{ \App\Support\Settings::get('turnstile_site_key', '', 'global') }}"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif
            <x-ui.button type="submit" variant="danger" class="w-full py-2">
                {{ __('contacts.submit') }}
            </x-ui.button>
        </div>
    </fieldset>
</form>


<div id="contactSuccess" hidden tabindex="-1">
    <p class="mb-4">{{ __('contacts.sent') }}</p>
    <button type="button" class="fcui-link" data-contact-reset>{{ __('contacts.send_another') }}</button>
</div>
@endif
