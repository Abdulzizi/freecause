@extends('layouts.legacy')

@section('title', __('auth.title_login'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">

                    <div class="card shadow-lg border-0 rounded-xl!">
                        <div class="card-body p-4 p-md-5">

                            <h1 class="text-center mb-4 text-[28px]! font-medium!">{{ __('auth.google_heading') }}</h1>

                            <div class="text-center mb-4">
                                <a href="{{ lroute('oauth.google', ['flow' => 'login']) }}"
                                    class="btn fc-google-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                    {{ __('auth.continue_google') }}
                                </a>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="h-px! bg-[#ddd]! flex-1!"></div>
                                <div class="fw-semibold">{{ __('auth.or') }}</div>
                                <div class="h-px! bg-[#ddd]! flex-1!"></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold">{{ __('auth.login') }}</div>
                                <div class="h-0.5! bg-[#d61f26]! w-full! mt-1.5!"></div>
                            </div>

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-3">
                                    {{ $errors->first() }}
                                </x-ui.alert>
                            @endif

                            @if (session('success'))
                                <x-ui.alert variant="success" class="mb-3">
                                    {{ session('success') }}
                                </x-ui.alert>
                            @endif

                            @if (session('error'))
                                <x-ui.alert variant="error" class="mb-3">
                                    {{ session('error') }}
                                </x-ui.alert>
                            @endif

                            <form method="POST" action="{{ lroute('login.post') }}">
                                @csrf

                                @if (!empty($redirect))
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                @endif

                                <div class="mb-3">
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
                                    />
                                </div>

                                <div class="mb-3">
                                    <x-ui.input
                                        type="password"
                                        id="login_password"
                                        name="password"
                                        :label="__('auth.password')"
                                        :placeholder="__('auth.password_placeholder')"
                                        :required="true"
                                        autocomplete="current-password"
                                    />
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
                                </div>

                                <x-ui.button type="submit" variant="danger" class="w-100 py-2 rounded-lg!">
                                    {{ __('auth.login') }}
                                </x-ui.button>

                                <div class="text-center mt-3">
                                    <a href="{{ lroute('password.request') }}" class="text-brand!">{{ __('auth.forgot_password') }}</a>
                                    <span class="text-muted px-2">|</span>
                                    <a href="{{ lroute('verification.resend.form') }}" class="text-brand!">
                                        {{ __('auth.resend_verification') }}
                                    </a>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="text-muted">{{ __('auth.not_member') }}</span>
                                    <a href="{{ lroute('register') }}" class="text-brand!">{{ __('auth.sign_up_now') }}</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
