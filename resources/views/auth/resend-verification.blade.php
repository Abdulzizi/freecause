@extends('layouts.legacy')

@section('title', __('auth.resend_verification_title'))

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-2/3 lg:w-1/2">
                    <div class="relative flex flex-col min-w-0 bg-surface bg-clip-border rounded-lg shadow-elev-1">
                        <div class="grow shrink basis-auto p-6 md:p-12">
                            <h1 class="text-center mb-6 text-2xl font-medium">{{ __('auth.resend_verification_heading') }}</h1>

                            @if (session('success'))
                                <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
                            @endif

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
                            @endif

                            <form method="POST" action="{{ lroute('verification.resend') }}">
                                @csrf

                                <div class="mb-4">
                                    <x-ui.input
                                        type="email"
                                        id="resend_email"
                                        name="email"
                                        :label="__('auth.email')"
                                        :value="old('email')"
                                        :required="true"
                                        autocomplete="email"
                                        autofocus
                                        class="min-h-12"
                                    />
                                </div>

                                <x-ui.button type="submit" variant="danger" class="w-full min-h-12">{{ __('auth.resend_email_btn') }}</x-ui.button>

                                <div class="text-center mt-4">
                                    <a href="{{ lroute('login') }}" class="text-muted">{{ __('auth.back_login') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
