@extends('layouts.legacy')

@section('title', __('auth.forgot_title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="shadow-sm">
                        <h1 class="mb-4 text-center text-2xl!">{{ __('auth.forgot_title') }}</h1>

                        @if (session('status'))
                            <x-ui.alert variant="success" class="mb-3">{{ session('status') }}</x-ui.alert>
                        @endif

                        <form method="POST" action="{{ lroute('password.email') }}">
                            @csrf

                            <div class="mb-3">
                                <x-ui.input
                                    type="email"
                                    id="forgot_email"
                                    name="email"
                                    :label="__('auth.email')"
                                    :value="old('email')"
                                    :required="true"
                                    :error="$errors->first('email')"
                                    autocomplete="email"
                                    autofocus
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-100">{{ __('auth.send_reset') }}</x-ui.button>

                            <div class="text-center mt-3">
                                <a href="{{ lroute('login') }}" class="text-muted text-sm!">{{ __('auth.back_login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
