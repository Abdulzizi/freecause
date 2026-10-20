@extends('layouts.legacy')

@section('title', __('auth.reset_title'))

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-5/12">
                    <div class="bg-surface shadow-elev-1 rounded-lg p-6 md:p-8">
                        <h1 class="mb-6 text-center text-2xl">{{ __('auth.reset_title') }}</h1>

                        <form method="POST" action="{{ lroute('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-4">
                                <x-ui.input
                                    type="email"
                                    id="reset_email"
                                    name="email"
                                    :label="__('auth.email')"
                                    :value="old('email')"
                                    :required="true"
                                    :error="$errors->first('email')"
                                    class="max-md:min-h-12"
                                />
                            </div>

                            <div class="mb-4">
                                <x-ui.input
                                    type="password"
                                    id="reset_password"
                                    name="password"
                                    :label="__('auth.new_password')"
                                    :required="true"
                                    :error="$errors->first('password')"
                                    data-strength
                                    data-strength-labels='["Very weak","Weak","Fair","Good","Strong"]'
                                    class="max-md:min-h-12"
                                />
                            </div>

                            <div class="mb-4">
                                <x-ui.input
                                    type="password"
                                    id="reset_password_confirmation"
                                    name="password_confirmation"
                                    :label="__('auth.confirm_password')"
                                    :required="true"
                                    class="max-md:min-h-12"
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-full max-md:min-h-12">{{ __('auth.reset_submit') }}</x-ui.button>
                        </form>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
