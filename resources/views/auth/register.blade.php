@extends('layouts.legacy')

@section('title', __('auth.title_register'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">

                    <div class="card shadow-lg border-0 rounded-xl!">
                        <div class="card-body p-4 p-md-5">

                            <h1 class="text-center mb-4 text-[28px]! font-medium!">{{ __('auth.create_account') }}</h1>

                            <div class="text-center mb-4">
                                <a href="{{ lroute('oauth.google', ['flow' => 'register']) }}"
                                    class="btn fc-google-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                    {{ __('auth.continue_google') }}
                                </a>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="h-px! bg-[#ddd]! flex-1!"></div>
                                <div class="fw-semibold">{{ __('auth.or') }}</div>
                                <div class="h-px! bg-[#ddd]! flex-1!"></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold">{{ __('auth.register') }}</div>
                                <div class="h-0.5! bg-[#d61f26]! w-full! mt-1.5!"></div>
                            </div>

                            @if ($errors->any())
                                <x-ui.alert variant="error" class="mb-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </ul>
                                </x-ui.alert>
                            @endif

                            <form method="POST" action="{{ url("/{$locale}/register") }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="text"
                                            id="register_name"
                                            name="name"
                                            :label="__('auth.name')"
                                            :value="old('name')"
                                            :required="true"
                                            :error="$errors->first('name')"
                                            autocomplete="given-name"
                                        />
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="text"
                                            id="register_surname"
                                            name="surname"
                                            :label="__('auth.surname')"
                                            :value="old('surname')"
                                            :required="true"
                                            :error="$errors->first('surname')"
                                            autocomplete="family-name"
                                        />
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="email"
                                            id="register_email"
                                            name="email"
                                            :label="__('auth.email')"
                                            :value="old('email')"
                                            :required="true"
                                            :error="$errors->first('email')"
                                            autocomplete="email"
                                        />
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="password"
                                            id="register_password"
                                            name="password"
                                            :label="__('auth.password')"
                                            :required="true"
                                            :error="$errors->first('password')"
                                            autocomplete="new-password"
                                        />
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="text"
                                            id="register_nickname"
                                            name="nickname"
                                            :label="__('sign.nickname')"
                                            :value="old('nickname')"
                                            :error="$errors->first('nickname')"
                                        />
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <x-ui.input
                                            type="text"
                                            id="register_city"
                                            name="city"
                                            :label="__('sign.city')"
                                            :value="old('city')"
                                            :error="$errors->first('city')"
                                        />
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms"
                                        aria-labelledby="agree_terms_label" {{ old('agree_terms') ? 'checked' : '' }}>
                                    <a id="agree_terms_label" class="form-check-label text-brand!" href="{{ lroute('page.show', ['slug' => 'terms-of-service']) }}"
                                        target="_blank" rel="noopener">{{ __('auth.accept_terms') }}</a>
                                </div>

                                <div class="text-center">
                                    <x-ui.button type="submit" variant="danger" class="px-4 py-2 rounded-lg! min-w-[160px]!">
                                        {{ __('auth.submit') }}
                                    </x-ui.button>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="text-muted">{{ __('auth.already_member') }}</span>
                                    <a href="{{ lroute('login') }}" class="text-brand!">{{ __('auth.sign_in_now') }}</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
