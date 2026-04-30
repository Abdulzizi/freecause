@extends('layouts.legacy')

@section('title', 'Registration - xPetition - Online Petition')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">

                    <div class="card shadow-lg border-0" style="border-radius:12px;">
                        <div class="card-body p-4 p-md-5">

                            <h3 class="text-center mb-4">{{ __('auth.create_account') }}</h3>

                            <div class="text-center mb-4">
                                <a href="{{ lroute('oauth.google', ['flow' => 'register']) }}" class="btn btn-light border px-3" style="border-radius:8px;">
                                    <img src="{{ asset('legacy/images-v2/google.png') }}" alt="" style="width:18px;margin-right:8px;">
                                    {{ __('auth.continue_google') }}
                                </a>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                                <div class="fw-semibold">{{ __('auth.or') }}</div>
                                <div style="height:1px;background:#ddd;flex:1;"></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold">{{ __('auth.register') }}</div>
                                <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ url("/{$locale}/register") }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('auth.name') }}</label>
                                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('auth.surname') }}</label>
                                        <input type="text" name="surname" value="{{ old('surname') }}" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('auth.email') }}</label>
                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('auth.password') }}</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('sign.nickname') }}</label>
                                        <input type="text" name="nickname" value="{{ old('nickname') }}" class="form-control">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label mb-1">{{ __('sign.city') }}</label>
                                        <input type="text" name="city" value="{{ old('city') }}" class="form-control">
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms"
                                        {{ old('agree_terms') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="agree_terms">
                                        <span class="red">{{ __('auth.accept_terms') }}</span>
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-danger px-4 py-2" style="border-radius:8px; min-width:160px;">
                                        {{ __('auth.submit') }}
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="text-muted">{{ __('auth.already_member') }}</span>
                                    <a href="{{ lroute('login') }}" class="red">{{ __('auth.sign_in_now') }}</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
