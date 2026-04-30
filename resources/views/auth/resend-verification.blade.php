@extends('layouts.legacy')

@section('title', __('auth.resend_verification_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <h3>{{ __('auth.resend_verification_heading') }}</h3>

            <form method="POST" action="{{ lroute('verification.resend') }}">
                @csrf

                <div class="mb-3">
                    <label>{{ __('auth.email') }}</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button class="btn btn-danger">{{ __('auth.resend_email_btn') }}</button>
            </form>
        </div>
    </section>
@endsection
