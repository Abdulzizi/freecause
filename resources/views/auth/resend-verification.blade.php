@extends('layouts.legacy')

@section('title', __('auth.resend_verification_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-lg border-0" style="border-radius:12px;">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="text-center mb-4">{{ __('auth.resend_verification_heading') }}</h3>

                            @if (session('success'))
                                <div class="alert alert-success" role="status">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                            @endif

                            <form method="POST" action="{{ lroute('verification.resend') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">{{ __('auth.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        autocomplete="email" autofocus required>
                                </div>

                                <button class="btn btn-danger w-100">{{ __('auth.resend_email_btn') }}</button>

                                <div class="text-center mt-3">
                                    <a href="{{ lroute('login') }}" class="text-muted">{{ __('auth.back_login') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
