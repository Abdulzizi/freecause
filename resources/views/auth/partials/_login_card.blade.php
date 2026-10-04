<div class="bg-white! rounded-3! shadow-sm! p-4! border! border-[#eee]!">
    @include('auth.partials._social_box', ['redirect' => $redirect, 'flow' => 'login'])

    <div class="mb-3">
        <div class="fw-semibold">{{ __('auth.login') }}</div>
        <div class="h-[2px]! bg-[#d61f26]! w-full! mt-[6px]!"></div>
    </div>

    @if (old('auth_form') === 'login' && $errors->any())
        <x-ui.alert variant="error" class="mb-3">
            {{ $errors->first() }}
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ lroute('login.post') }}">
        @csrf
        <input type="hidden" name="auth_form" value="login">
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="mb-3">
            <x-ui.input
                type="email"
                name="email"
                :label="__('auth.email')"
                :value="old('email')"
                :required="true"
            />
        </div>

        <div class="mb-3">
            <x-ui.input
                type="password"
                name="password"
                :label="__('auth.password')"
                :required="true"
            />
        </div>

        <x-ui.button type="submit" variant="danger" class="px-4 rounded-[6px]!">
            {{ __('auth.login') }}
        </x-ui.button>

        <div class="mt-3">
            <a href="{{ lroute('register') }}" class="text-brand! d-block fw-bold">{{ __('auth.not_member') }} {{ __('auth.sign_up_now') }}</a>
            <a href="{{ lroute('password.request') }}" class="text-brand! d-block mt-2">{{ __('auth.forgot_password') }}</a>
            <a href="{{ lroute('verification.resend.form') }}" class="text-brand! d-block">{{ __('auth.resend_verification') }}</a>
        </div>
    </form>
</div>
