@extends('layouts.legacy')

@section('title', __('messages.auth.forgot_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="fc-auth-card shadow-sm">
                        <h4 class="mb-4 text-center">{{ __('messages.auth.forgot_title') }}</h4>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <form method="POST" action="{{ lroute('password.email') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.auth.email') }}</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-danger w-100">{{ __('messages.auth.send_reset') }}</button>

                            <div class="text-center mt-3">
                                <a href="{{ lroute('login') }}" class="text-muted" style="font-size:14px;">{{ __('messages.auth.back_login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
