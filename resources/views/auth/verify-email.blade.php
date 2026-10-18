@extends('layouts.legacy')

@section('title', __('auth.verify_notice_title'))

@section('content')
<x-ads.slot name="leaderboard" />
<x-ads.slot name="mobile" />
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-2/3 lg:w-1/2">
                    <div class="relative flex flex-col min-w-0 bg-surface bg-clip-border rounded-lg shadow-elev-1">
                        <div class="grow shrink basis-auto p-6 md:p-12">
                            <h1 class="text-center mb-6 text-2xl font-medium">{{ __('auth.verify_notice_heading') }}</h1>

                            @if (session('success'))
                                <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
                            @endif

                            @if (session('error'))
                                <x-ui.alert variant="error" class="mb-4">{{ session('error') }}</x-ui.alert>
                            @endif

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
                            @endif

                            <p class="text-center text-muted mb-2">
                                @if ($maskedEmail)
                                    {{ __('auth.verify_notice_body', ['email' => $maskedEmail]) }}
                                @else
                                    {{ __('auth.verify_notice_body_unknown') }}
                                @endif
                            </p>

                            @if ($smtpEnabled && $email)
                                <p class="text-center text-muted mb-6">{{ __('auth.verify_notice_spam') }}</p>

                                <form method="POST" action="{{ lroute('verification.resend') }}">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $email }}">

                                    <x-ui.button type="submit" variant="danger" class="w-full max-md:min-h-12">{{ __('auth.resend_email_btn') }}</x-ui.button>
                                </form>
                            @else
                                <p class="text-center text-muted mb-6">{{ __('auth.verify_notice_pending') }}</p>
                            @endif

                            <div class="text-center mt-6 flex flex-wrap justify-center gap-x-3 gap-y-2">
                                <a href="{{ lroute('contacts') }}" class="text-brand">{{ __('auth.verify_notice_help') }}</a>
                                <span class="text-muted" aria-hidden="true">|</span>
                                <a href="{{ lroute('register') }}" class="text-brand">{{ __('auth.verify_notice_edit') }}</a>
                                <span class="text-muted" aria-hidden="true">|</span>
                                <a href="{{ lroute('login') }}" class="text-muted">{{ __('auth.back_login') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
