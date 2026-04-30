<div class="bg-white rounded-3 shadow-sm p-4" style="border:1px solid #eee;">
    <div class="mb-3">
        <div class="fw-semibold">{{ __('auth.login') }}</div>
        <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ lroute('login.post') }}">
        @csrf
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="mb-3">
            <label class="form-label mb-1">{{ __('auth.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">{{ __('auth.password') }}</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-danger px-4" style="border-radius:6px;">
            {{ __('auth.login') }}
        </button>

        <div class="mt-3">
            <a href="{{ lroute('register') }}" class="red d-block fw-bold">{{ __('auth.not_member') }} {{ __('auth.sign_up_now') }}</a>
            <a href="{{ lroute('password.request') }}" class="red d-block mt-2">{{ __('auth.forgot_password') }}</a>
            <a href="{{ lroute('verification.resend.form') }}" class="red d-block">{{ __('auth.resend_verification') }}</a>
        </div>
    </form>
</div>