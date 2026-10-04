@extends('layouts.legacy')

@section('title', __('auth.reset_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="shadow-sm">
                        <h1 class="mb-4 text-center text-2xl!">{{ __('auth.reset_title') }}</h1>

                        <form method="POST" action="{{ lroute('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="reset_email" class="form-label">{{ __('auth.email') }}</label>
                                <input type="email" id="reset_email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="reset_password" class="form-label">{{ __('auth.new_password') }}</label>
                                <input type="password" id="reset_password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="reset_password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
                                <input type="password" id="reset_password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>

                            <button class="btn btn-danger w-100">{{ __('auth.reset_submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
