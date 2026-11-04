@extends('layouts.legacy')

@section('title', __('auth.title_login'))

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-2/3 lg:w-1/2">

                    <div class="relative flex flex-col min-w-0 bg-surface bg-clip-border rounded-lg shadow-elev-1">
                        <div class="grow shrink basis-auto p-6 md:p-12">

                            <h1 class="text-center mb-6 text-2xl font-medium">{{ __('auth.google_heading') }}</h1>

                            <div class="text-center mb-6">
                                <a href="{{ lroute('oauth.google', ['flow' => 'login']) }}"
                                    class="btn fc-google-btn fc-oauth-btn max-md:min-h-12">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-4.5 h-4.5 block shrink-0" aria-hidden="true">
                                    {{ __('auth.continue_google') }}
                                </a>
                            </div>

                            <div class="flex items-center gap-4 mb-6">
                                <div class="h-px bg-border flex-1"></div>
                                <div class="font-semibold">{{ __('auth.or') }}</div>
                                <div class="h-px bg-border flex-1"></div>
                            </div>

                            <div class="mb-4">
                                <div class="font-semibold">{{ __('auth.login') }}</div>
                                <div class="h-0.5 bg-brand w-full mt-1.5"></div>
                            </div>

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-4">
                                    {{ $errors->first() }}
                                </x-ui.alert>
                            @endif

                            @if (session('success'))
                                <x-ui.alert variant="success" class="mb-4">
                                    {{ session('success') }}
                                </x-ui.alert>
                            @endif

                            @if (session('error'))
                                <x-ui.alert variant="error" class="mb-4">
                                    {{ session('error') }}
                                </x-ui.alert>
                            @endif

                            <form method="POST" action="{{ lroute('login.post') }}">
                                @csrf

                                @if (!empty($redirect))
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                @endif

                                <div class="mb-4">
                                    <x-ui.input
                                        type="email"
                                        id="login_email"
                                        name="email"
                                        :label="__('auth.email')"
                                        :value="old('email')"
                                        :placeholder="__('auth.email_placeholder')"
                                        :required="true"
                                        autocomplete="email"
                                        autofocus
                                        class="max-md:min-h-12"
                                    />
                                </div>

                                <div class="mb-4">
                                    <x-ui.input
                                        type="password"
                                        id="login_password"
                                        name="password"
                                        :label="__('auth.password')"
                                        :placeholder="__('auth.password_placeholder')"
                                        :required="true"
                                        autocomplete="current-password"
                                        class="max-md:min-h-12"
                                    />
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
                                </div>

                                <x-ui.button type="submit" variant="danger" class="w-full max-md:min-h-12 py-2">
                                    {{ __('auth.login') }}
                                </x-ui.button>

                                <div class="text-center mt-4">
                                    <a href="{{ lroute('password.request') }}" class="text-brand">{{ __('auth.forgot_password') }}</a>
                                    <span class="text-muted px-2">|</span>
                                    <a href="{{ lroute('verification.resend.form') }}" class="text-brand">
                                        {{ __('auth.resend_verification') }}
                                    </a>
                                </div>

                                <div class="text-center mt-4">
                                    <span class="text-muted">{{ __('auth.not_member') }}</span>
                                    <a href="{{ lroute('register') }}" class="text-brand">{{ __('auth.sign_up_now') }}</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </x-ui.container>

        <x-ads.rotate placement="inline" class="mt-6 text-center" />
        <x-ads.rotate placement="page_mid" class="mt-6" />
    </section>
@endsection
