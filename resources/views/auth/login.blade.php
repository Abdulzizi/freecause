@extends('layouts.legacy')

@section('title', 'Login - FreeCause - Online Petition')

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
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success" role="status">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ lroute('login.post') }}">
                                @csrf

                                @if (!empty($redirect))
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label mb-1">{{ __('auth.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        autocomplete="email" autofocus
                                        placeholder="{{ __('auth.email_placeholder') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label mb-1">{{ __('auth.password') }}</label>
                                    <input type="password" name="password" class="form-control" autocomplete="current-password"
                                        placeholder="{{ __('auth.password_placeholder') }}" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 py-2 rounded-lg!">
                                    {{ __('auth.login') }}
                                </button>

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
