<div class="bg-surface rounded-md shadow-elev-1 p-4">
    @include('auth.partials._social_box', ['redirect' => $redirect, 'flow' => 'login'])

    <div class="mb-4">
        <div class="font-semibold">{{ __('auth.login') }}</div>
        <div class="h-0.5 bg-brand w-full mt-1.5"></div>
    </div>

    <form method="POST" action="{{ lroute('login.post') }}">
        @csrf
        <input type="hidden" name="auth_form" value="login">
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="mb-4">
            <x-ui.input
                type="email"
                name="email"
                :label="__('auth.email')"
                :value="old('email')"
                :required="true"
                autocomplete="email"
                class="max-md:min-h-12"
            />
        </div>

        <div class="mb-4">
            <x-ui.input
                type="password"
                name="password"
                :label="__('auth.password')"
                :required="true"
                autocomplete="current-password"
                class="max-md:min-h-12"
            />
        </div>

        <x-ui.button type="submit" variant="danger" class="px-6 max-md:min-h-12 w-full md:w-auto">
            {{ __('auth.login') }}
        </x-ui.button>

        <div class="mt-4">
            <a href="{{ lroute('register') }}" class="text-brand block font-bold">{{ __('auth.not_member') }} {{ __('auth.sign_up_now') }}</a>
            <a href="{{ lroute('password.request') }}" class="text-brand block mt-2">{{ __('auth.forgot_password') }}</a>
            <a href="{{ lroute('verification.resend.form') }}" class="text-brand block">{{ __('auth.resend_verification') }}</a>
        </div>
    </form>
</div>
