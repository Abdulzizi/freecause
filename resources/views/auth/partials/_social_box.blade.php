<div class="mb-4">
    <div class="font-semibold">{{ __('auth.google_heading') }}</div>
    <div class="h-0.5 bg-brand w-full mt-1.5"></div>
</div>

<div class="flex justify-center gap-2 flex-wrap mb-4">

    <a href="{{ lroute('oauth.google', ['flow' => $flow ?? 'register', 'redirect' => $redirect]) }}"
        class="btn fc-google-btn fc-oauth-btn">
        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-4.5 h-4.5 block shrink-0" aria-hidden="true">
        {{ __('auth.continue_google') }}
    </a>
</div>

<div class="flex items-center gap-4 my-4">
    <div class="h-px bg-border flex-1"></div>
    <div class="font-semibold bg-surface px-2 rounded-full">{{ __('auth.or') }}</div>
    <div class="h-px bg-border flex-1"></div>
</div>
