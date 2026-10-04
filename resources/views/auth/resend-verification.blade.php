@extends('layouts.legacy')

@section('title', __('auth.resend_verification_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-lg border-0 rounded-xl!">
                        <div class="card-body p-4 p-md-5">
                            <h1 class="text-center mb-4 text-[28px]! font-medium!">{{ __('auth.resend_verification_heading') }}</h1>

                            @if (session('success'))
                                <x-ui.alert variant="success" class="mb-3">{{ session('success') }}</x-ui.alert>
                            @endif

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-3">{{ $errors->first() }}</x-ui.alert>
                            @endif

                            <form method="POST" action="{{ lroute('verification.resend') }}">
                                @csrf

                                <div class="mb-3">
                                    <x-ui.input
                                        type="email"
                                        id="resend_email"
                                        name="email"
                                        :label="__('auth.email')"
                                        :value="old('email')"
                                        :required="true"
                                        autocomplete="email"
                                        autofocus
                                    />
                                </div>

                                <x-ui.button type="submit" variant="danger" class="w-100">{{ __('auth.resend_email_btn') }}</x-ui.button>

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
