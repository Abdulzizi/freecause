<div class="mb-3">
    <div class="fw-semibold">{{ __('auth.google_heading') }}</div>
    <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
</div>

<div class="d-flex justify-content-center gap-2 flex-wrap mb-3">

    <a href="{{ lroute('oauth.google', ['redirect' => $redirect]) }}" class="btn btn-light border px-3"
        style="border-radius:6px;">
        <img src="{{ asset('legacy/images-v2/google.png') }}" alt="" style="width:18px;margin-right:8px;">
        {{ __('auth.continue_google') }}
    </a>
</div>

<div class="d-flex align-items-center gap-3 my-3">
    <div style="height:1px;background:#ddd;flex:1;"></div>
    <div class="fw-semibold" style="background:#fff;padding:0 10px;border-radius:999px;">{{ __('auth.or') }}</div>
    <div style="height:1px;background:#ddd;flex:1;"></div>
</div>
