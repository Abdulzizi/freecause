<div class="mb-4!">
    <div class="font-semibold!">{{ __('auth.google_heading') }}</div>
    <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
</div>

<div class="flex! justify-center! gap-2! flex-wrap! mb-4!">

    <a href="{{ lroute('oauth.google', ['flow' => $flow ?? 'register', 'redirect' => $redirect]) }}"
        class="btn fc-google-btn fc-oauth-btn">
        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
        {{ __('auth.continue_google') }}
    </a>
</div>

<div class="flex! items-center! gap-4! my-4!">
    <div style="height:1px;background:var(--color-border);flex:1;"></div>
    <div class="font-semibold!" style="background:#fff;padding:0 10px;border-radius:999px;">{{ __('auth.or') }}</div>
    <div style="height:1px;background:var(--color-border);flex:1;"></div>
</div>
