@extends('layouts.legacy')

@section('title', 'Login - FreeCause - Online Petition')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">

                    <div class="card shadow-lg border-0" style="border-radius:12px;">
                        <div class="card-body p-4 p-md-5">

                            <h3 class="text-center mb-4">Sign In Using Your Account With</h3>

                            <div class="text-center mb-4">
                                <a href="{{ lroute('oauth.google', ['flow' => 'login']) }}" class="btn btn-light border px-3"
                                    style="border-radius:8px;">
                                    <img src="{{ asset('legacy/images-v2/google.png') }}" alt=""
                                        style="width:18px;margin-right:8px;">
                                    Continue with Google
                                </a>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                                <div class="fw-semibold">OR</div>
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold">Login</div>
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
                                    <label class="form-label mb-1">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        placeholder="Enter your email" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label mb-1">Password</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Enter your password" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 py-2" style="border-radius:8px;">
                                    Sign In
                                </button>

                                <div class="text-center mt-3">
                                    <a href="{{ lroute('password.request') }}" class="red">Forgot password ?</a>
                                    <span class="text-muted px-2">|</span>
                                    <a href="{{ lroute('verification.resend.form') }}" class="red">
                                        Resend activation email
                                    </a>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="text-muted">Not a member yet?</span>
                                    <a href="{{ lroute('register') }}" class="red">Sign up now</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
