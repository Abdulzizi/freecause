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
                                <x-ui.input
                                    type="email"
                                    id="reset_email"
                                    name="email"
                                    :label="__('auth.email')"
                                    :value="old('email')"
                                    :required="true"
                                    :error="$errors->first('email')"
                                />
                            </div>

                            <div class="mb-3">
                                <x-ui.input
                                    type="password"
                                    id="reset_password"
                                    name="password"
                                    :label="__('auth.new_password')"
                                    :required="true"
                                    :error="$errors->first('password')"
                                />
                            </div>

                            <div class="mb-3">
                                <x-ui.input
                                    type="password"
                                    id="reset_password_confirmation"
                                    name="password_confirmation"
                                    :label="__('auth.confirm_password')"
                                    :required="true"
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-100">{{ __('auth.reset_submit') }}</x-ui.button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
