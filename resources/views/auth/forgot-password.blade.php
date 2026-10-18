@extends('layouts.legacy')

@section('title', __('auth.forgot_title'))

@section('content')
<x-ads.slot name="leaderboard" />
<x-ads.slot name="mobile" />
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-5/12">
                    <div class="bg-surface shadow-elev-1 rounded-lg p-6 md:p-8">
                        <h1 class="mb-6 text-center text-2xl">{{ __('auth.forgot_title') }}</h1>

                        @if (session('status'))
                            <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                        @endif

                        <form method="POST" action="{{ lroute('password.email') }}">
                            @csrf

                            <div class="mb-4">
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
                                    class="max-md:min-h-12"
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-full max-md:min-h-12">{{ __('auth.send_reset') }}</x-ui.button>

                            <div class="text-center mt-4">
                                <a href="{{ lroute('login') }}" class="text-muted text-sm">{{ __('auth.back_login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
