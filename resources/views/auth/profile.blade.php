@extends('layouts.legacy')

@section('title', 'Account - FreeCause')

@section('content')
    @php
        $u = auth()->user();
        $googleOnly = $u->isGoogleOnly();
        $googleDeleteProof = session('account_delete_google_verified');
        $googleDeleteVerified = $googleOnly
            && is_array($googleDeleteProof)
            && (int) ($googleDeleteProof['user_id'] ?? 0) === (int) $u->id
            && (int) ($googleDeleteProof['verified_at'] ?? 0) >= now()->subMinutes(5)->timestamp;
    @endphp

    <section class="py-4">
        <div class="container">
            <div class="row g-4">

                <div class="col-lg-4">
                    <div class="bg-white! border! border-[#e9e9e9]! rounded-[3px]! shadow-[0_2px_8px_rgba(0,0,0,0.06)]! p-4">

                        <h1 class="text-[22px]! font-medium! mb-3">{{ __('profile.my_data') }}</h1>

                        <div class="flex! gap-2.5! my-2.5! text-sm!">
                            <div class="w-[140px]! text-[#333]!">{{ __('auth.email') }}:</div>
                            <div class="text-[#222]!">{{ $u->email }}</div>
                        </div>

                        <div class="flex! gap-2.5! my-2.5! text-sm!">
                            <div class="w-[140px]! text-[#333]!">{{ __('profile.reg_date') }}</div>
                            <div class="text-[#222]!">
                                {{ optional($u->created_at)->format('d M y H:i') }}
                            </div>
                        </div>

                        <div class="mt-3">
                            <a class="text-[#b10000]! no-underline! hover:underline!" href="{{ lroute('account.petitions') }}">{{ __('profile.my_petitions') }}</a>
                        </div>

                        <div class="border-b! border-dotted! border-[#bbb]! my-3"></div>

                        <div class="mt-4">
                            @if ($u->google_id)
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true" width="24" height="24" class="w-[24px]! h-[24px]! block! shrink-0! mt-[2px]!">
                                    <div>
                                        <div class="text-sm! text-[#222]!">
                                            {{ __('profile.google_connected') }} <strong>{{ $u->email }}</strong>
                                        </div>
                                        <div class="mt-1 text-sm! text-muted">{{ __('profile.google_help') }}</div>
                                    </div>
                                </div>

                                @if ($googleOnly)
                                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" disabled aria-disabled="true">
                                        {{ __('profile.google_disconnect') }}
                                    </button>
                                    <div class="mt-2 text-sm! text-muted">{{ __('profile.google_disconnect_needs_password') }}</div>
                                @else
                                    <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect class="mt-2">
                                        @csrf

                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-google-disconnect-trigger>
                                            {{ __('profile.google_disconnect') }}
                                        </button>

                                        <div class="mt-2 d-none" data-google-disconnect-panel>
                                            <p class="text-sm! mb-2">{{ __('profile.google_disconnect_confirm_q') }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="submit" class="btn btn-danger btn-sm d-none" data-google-disconnect-confirm>
                                                    {{ __('profile.google_disconnect_confirm') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm d-none" data-google-disconnect-cancel>
                                                    {{ __('common.cancel') }}
                                                </button>
                                            </div>
                                        </div>

                                        <noscript>
                                            <button type="submit" class="btn btn-danger btn-sm mt-2">
                                                {{ __('profile.google_disconnect') }}
                                            </button>
                                        </noscript>
                                    </form>
                                @endif
                            @else
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true" width="24" height="24" class="w-[24px]! h-[24px]! block! shrink-0! mt-[2px]!">
                                    <div>
                                        <div class="text-sm! text-[#222]!">{{ __('profile.google_not_connected') }}</div>
                                        <div class="mt-1 text-sm! text-muted">{{ __('profile.google_help') }}</div>
                                    </div>
                                </div>
                                <a href="{{ lroute('oauth.google') }}" class="btn fc-google-btn fc-oauth-btn btn-sm mt-2">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                    {{ __('profile.google_connect') }}
                                </a>
                            @endif

                            <div class="border-b! border-dotted! border-[#bbb]! my-3"></div>
                        </div>

                        <div class="border border-danger rounded-sm! p-3 mt-2">
                        <h2 class="text-[22px]! font-medium! text-danger mt-0! mb-2">{{ __('profile.account_deletion') }}</h2>

                        <p class="text-sm! text-[#333]!">{{ __('profile.delete_explain') }}</p>

                        @error('google_delete')
                            <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
                        @enderror

                        @if ($googleOnly && ! $googleDeleteVerified)
                            <p class="text-sm!">{{ __('profile.verify_google_delete') }}</p>
                            <p class="text-sm! text-muted">{{ __('profile.delete_google_why') }}</p>
                            <a href="{{ lroute('account.delete.google') }}" class="btn fc-google-btn fc-oauth-btn mb-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                {{ __('profile.verify_with_google') }}
                            </a>
                        @else

                        <form method="POST" action="{{ lroute('account.delete') }}" data-account-delete-form>
                            @csrf

                            @unless ($googleOnly)
                            <div class="mb-3">
                                <label class="form-label">{{ __('profile.enter_password') }}</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            @endunless

                            <label class="d-flex align-items-center gap-2 mb-3 text-sm!">
                                <input type="checkbox" name="confirm_delete" value="1" required data-account-delete-confirm>
                                <span>{{ __('profile.confirm_delete_lbl') }}</span>
                            </label>

                            <button type="submit" class="btn bg-gradient-to-b! from-[#b30000]! to-[#7d0000]! text-white! border-0! rounded-sm! font-semibold! px-4"
                                data-account-delete-submit>
                                {{ __('profile.delete') }} <span class="ms-3">»</span>
                            </button>
                        </form>
                        @endif
                        </div>

                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="bg-white! border! border-[#e9e9e9]! rounded-[3px]! shadow-[0_2px_8px_rgba(0,0,0,0.06)]! p-4">
                        <h3 class="text-[28px]! font-medium! text-center mb-4">{{ __('profile.heading') }}</h3>

                        @if ($errors->any())
                            <div class="mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ lroute('account.profile.update') }}">
                            @csrf

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">{{ __('profile.first_name') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-control rounded-sm! h-[38px]!" name="first_name"
                                        value="{{ old('first_name', $u->first_name) }}">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">{{ __('profile.last_name') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-control rounded-sm! h-[38px]!" name="last_name"
                                        value="{{ old('last_name', $u->last_name) }}">
                                </div>
                            </div>

                            @php $idMode = old('identify_mode', $u->identify_mode ?? 'full'); @endphp

                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label">{{ __('profile.identify_as') }}</label>
                                <div class="col-sm-9 d-flex gap-4">
                                    <label><input type="radio" name="identify_mode" value="full"
                                            {{ $idMode === 'full' ? 'checked' : '' }}> {{ __('profile.id_full') }}</label>
                                    <label><input type="radio" name="identify_mode" value="name"
                                            {{ $idMode === 'name' ? 'checked' : '' }}> {{ __('profile.id_name') }}</label>
                                    <label><input type="radio" name="identify_mode" value="nick"
                                            {{ $idMode === 'nick' ? 'checked' : '' }}> {{ __('profile.id_nick') }}</label>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">{{ __('profile.id_nick') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-control rounded-sm! h-[38px]!" name="nickname"
                                        value="{{ old('nickname', $u->nickname) }}">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">{{ __('form.city') }}</label>
                                <div class="col-sm-9">
                                    <input class="form-control rounded-sm! h-[38px]!" name="city"
                                        value="{{ old('city', $u->city) }}">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label" for="country">{{ __('profile.country') }}</label>
                                <div class="col-sm-9">
                                    <select class="form-select rounded-sm! h-[38px]!" id="country" name="country">
                                        <option value="">{{ __('profile.select_country') }}</option>
                                        @foreach ($countries as $countryCode => $countryName)
                                            <option value="{{ $countryCode }}" @selected(old('country', $u->country) === $countryCode)>
                                                {{ $countryName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @php $preferredLanguage = old('locale', \App\Support\Locale::toShort($u->locale)); @endphp

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label" for="locale">{{ __('profile.preferred_language') }}</label>
                                <div class="col-sm-9">
                                    <select class="form-select rounded-sm! h-[38px]!" id="locale" name="locale" required>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->code }}" @selected($preferredLanguage === $language->code)>
                                                {{ $language->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-2"><strong>{{ __('profile.change_email') }}</strong></div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input class="form-control rounded-sm! h-[38px]!" name="new_email" placeholder="{{ __('profile.new_email') }}"
                                        value="{{ old('new_email') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-9 offset-sm-3">
                                    <input class="form-control rounded-sm! h-[38px]!" name="new_email_confirmation"
                                        placeholder="{{ __('profile.confirm_email') }}" value="{{ old('new_email_confirmation') }}">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-2"><strong>{{ __('profile.change_password') }}</strong></div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control rounded-sm! h-[38px]!" name="current_password"
                                        placeholder="{{ __('profile.current_password') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control rounded-sm! h-[38px]!" name="new_password"
                                        placeholder="{{ __('profile.new_password') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control rounded-sm! h-[38px]!" name="new_password_confirmation"
                                        placeholder="{{ __('profile.confirm_new_password') }}">
                                </div>
                            </div>

                            <div class="text-center">
                                <button class="btn bg-gradient-to-b! from-[#b30000]! to-[#7d0000]! text-white! border-0! rounded-sm! font-semibold! min-w-[160px]! px-5" type="submit" data-busy-submit>
                                    {{ __('profile.save_changes') }} <span class="ms-3">»</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
