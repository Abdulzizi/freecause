@extends('layouts.legacy')

@section('title', 'Login - xPetition - Online Petition')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">

                    <div class="card shadow-lg border-0" style="border-radius:12px;">
                        <div class="card-body p-4 p-md-5">

                            <h3 class="text-center mb-4">{{ trans_db('auth.google_heading') }}</h3>

                            <div class="text-center mb-4">
                                <a href="{{ lroute('oauth.google', ['flow' => 'login']) }}" class="btn btn-light border px-3"
                                    style="border-radius:8px;">
                                    <img src="{{ asset('legacy/images-v2/google.png') }}" alt=""
                                        style="width:18px;margin-right:8px;">
                                    {{ trans_db('auth.continue_google') }}
                                </a>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                                <div class="fw-semibold">{{ trans_db('auth.or') }}</div>
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold">{{ trans_db('auth.login') }}</div>
                                <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ lroute('login.post') }}">
                                @csrf

                                @if (!empty($redirect))
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label mb-1">{{ trans_db('auth.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        placeholder="{{ trans_db('auth.email_placeholder') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label mb-1">{{ trans_db('auth.password') }}</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="{{ trans_db('auth.password_placeholder') }}" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">{{ trans_db('auth.remember_me') }}</label>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 py-2" style="border-radius:8px;">
                                    {{ trans_db('auth.login') }}
                                </button>

                                <div class="text-center mt-3">
                                    <a href="{{ lroute('password.request') }}" class="red">{{ trans_db('auth.forgot_password') }}</a>
                                    <span class="text-muted px-2">|</span>
                                    <a href="{{ lroute('verification.resend.form') }}" class="red">
                                        {{ trans_db('auth.resend_verification') }}
                                    </a>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="text-muted">{{ trans_db('auth.not_member') }}</span>
                                    <a href="{{ lroute('register') }}" class="red">{{ trans_db('auth.sign_up_now') }}</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
