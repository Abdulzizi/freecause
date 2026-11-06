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

        $card = 'rounded-lg bg-surface p-6 shadow-elev-1';
        $inputBase = 'block w-full max-md:min-h-12 rounded-md border bg-surface px-3 py-2 text-base md:text-sm leading-normal text-ink transition focus:ring-2 focus:outline-none';
        $input = $inputBase.' border-border focus:border-brand focus:ring-brand/25';
        $inputInvalid = $inputBase.' border-danger focus:border-danger focus:ring-danger/25';
        $label = 'text-sm font-medium text-ink';
        $btnBrand = 'fcui-btn fcui-btn--danger fcui-btn--md max-md:min-h-12';
        $btnOutline = 'fcui-btn fcui-btn--secondary fcui-btn--md max-md:min-h-12';

        $mustComplete = ! $u->hasCompleteProfile();
        $missingCountry = ! old('country', $u->country);
        $missingGender = ! old('gender', $u->gender);
        $preferredLanguage = old('locale', ! $u->preferred_language_confirmed_at ? '' : ($u->hasSupportedPreferredLanguage() ? \App\Support\Locale::toShort($u->locale) : ''));
        $missingLanguage = ! $preferredLanguage;
        $reasonKey = match (request('reason')) {
            'sign' => 'complete_required_sign',
            'create' => 'complete_required_create',
            'edit' => 'complete_required_edit',
            'report' => 'complete_required_report',
            'preferences' => 'complete_required_preferences',
            default => 'complete_required',
        };
    @endphp

    <section class="fc-section bg-band">
        <div class="mx-auto max-w-5xl px-4">
            @if ($mustComplete)
                <div class="mb-6">
                    <x-ui.alert variant="warning">
                        <p class="mb-2">{{ __('profile.'.$reasonKey) }}</p>
                        <a class="inline-flex max-md:min-h-12 items-center text-sm font-medium text-brand no-underline hover:underline" href="#{{ $missingCountry ? 'country-field' : ($missingGender ? 'gender-field' : 'language-field') }}">{{ __('profile.jump_to_fields') }}</a>
                    </x-ui.alert>
                </div>
            @endif

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

                <aside class="lg:col-span-5 xl:col-span-4">
                    <div class="{{ $card }}">

                        <h1 class="mb-4 text-lg font-semibold text-ink">{{ __('profile.my_data') }}</h1>
                        <x-ui.button :href="lroute('preferences.show')" variant="secondary" size="sm" class="mb-4">{{ __('preferences.manage_preferences') }}</x-ui.button>

                        <div class="space-y-2.5 text-sm">
                            <div class="flex flex-wrap gap-x-2">
                                <span class="w-28 shrink-0 text-muted">{{ __('auth.email') }}:</span>
                                <span class="min-w-0 flex-1 font-medium break-all text-ink">{{ $u->email }}</span>
                            </div>
                            <div class="flex flex-wrap gap-x-2">
                                <span class="w-28 shrink-0 text-muted">{{ __('profile.reg_date') }}</span>
                                <span class="min-w-0 flex-1 font-medium text-ink">{{ optional($u->created_at)->format('d M y H:i') }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a class="inline-flex max-md:min-h-12 items-center text-sm font-medium text-brand no-underline hover:underline" href="{{ lroute('account.petitions') }}">
                                {{ __('profile.my_petitions') }}
                            </a>
                        </div>

                        <hr class="my-5 border-border">

                        @error('google_link')
                            <x-ui.alert variant="error" class="mb-3">
                                {{ $message }}
                            </x-ui.alert>
                        @enderror

                        @if ($u->google_id && $googleOnly)
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5 h-5.5 w-5.5 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-ink">{{ __('profile.google_connected') }}</p>
                                    <strong class="block text-sm font-semibold break-all text-ink">{{ $u->email }}</strong>
                                    <p class="mt-1 text-xs leading-relaxed text-muted">{{ __('profile.google_only_note') }}</p>
                                </div>
                            </div>

                            <a href="{{ lroute('password.request') }}" class="{{ $btnBrand }} mt-3">
                                {{ __('profile.set_password_cta') }}
                            </a>

                            <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect data-google-disconnect-needs-password
                                data-need-title="{{ __('profile.need_password_title') }}"
                                data-need-text="{{ __('profile.need_password_text') }}"
                                data-need-ok="{{ __('profile.need_password_ok') }}" class="mt-3">
                                @csrf

                                <button type="button" class="{{ $btnOutline }}" data-google-disconnect-trigger>
                                    {{ __('profile.google_disconnect') }}
                                </button>

                                <noscript>
                                    <p class="mt-2 text-xs text-muted">{{ __('profile.google_disconnect_needs_password') }}</p>
                                </noscript>
                            </form>
                        @elseif ($u->google_id && ! $googleOnly)
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5 h-5.5 w-5.5 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-ink">{{ __('profile.google_connected') }}</p>
                                    <strong class="block text-sm font-semibold break-all text-ink">{{ $u->email }}</strong>
                                    <p class="mt-1 text-xs leading-relaxed text-muted">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect
                                data-confirm-title="{{ __('profile.confirm_disconnect_title') }}"
                                data-confirm-text="{{ __('profile.confirm_disconnect_text') }}"
                                data-confirm-ok="{{ __('profile.confirm_disconnect_ok') }}"
                                data-confirm-cancel="{{ __('profile.confirm_cancel') }}" class="mt-3">
                                @csrf

                                <button type="button" class="{{ $btnOutline }}" data-google-disconnect-trigger>
                                    {{ __('profile.google_disconnect') }}
                                </button>

                                <noscript>
                                    <button type="submit" class="{{ $btnBrand }} mt-3" data-loading data-loading-text="{{ __('ui.saving') }}">
                                        {{ __('profile.google_disconnect') }}
                                    </button>
                                </noscript>
                            </form>
                        @else
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5 h-5.5 w-5.5 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-ink">{{ __('profile.google_not_connected') }}</p>
                                    <p class="mt-1 text-xs leading-relaxed text-muted">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>
                            <a href="{{ lroute('oauth.google', ['flow' => 'link']) }}" class="{{ $btnOutline }} mt-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="16" height="16" class="h-4 w-4 shrink-0">
                                {{ __('profile.google_connect') }}
                            </a>
                        @endif

                        <hr class="my-5 border-border">

                        <div class="rounded-lg border border-brand-border bg-danger-soft p-4">
                            <h2 class="text-sm font-semibold text-danger">{{ __('profile.account_deletion') }}</h2>
                            <p class="mt-2 text-xs leading-relaxed text-muted">{{ __('profile.delete_explain') }}</p>

                            @error('google_delete')
                                <x-ui.alert variant="error" class="mt-3">
                                    {{ $message }}
                                </x-ui.alert>
                            @enderror

                            @if ($googleOnly && ! $googleDeleteVerified)
                                <p class="mt-3 text-xs text-muted">{{ __('profile.verify_google_delete') }}</p>
                                <p class="mt-1 text-xs text-muted">{{ __('profile.delete_google_why') }}</p>
                                <a href="{{ lroute('account.delete.google') }}" class="{{ $btnOutline }} mt-3">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                        width="16" height="16" class="h-4 w-4 shrink-0">
                                    {{ __('profile.verify_with_google') }}
                                </a>
                            @else
                                <form method="POST" action="{{ lroute('account.delete') }}" data-account-delete-form
                                    data-confirm-title="{{ __('profile.confirm_delete_title') }}"
                                    data-confirm-text="{{ __('profile.confirm_delete_text') }}"
                                    data-confirm-ok="{{ __('profile.confirm_delete_ok') }}"
                                    data-confirm-cancel="{{ __('profile.confirm_cancel') }}" class="mt-4 space-y-3">
                                    @csrf

                                    @unless ($googleOnly)
                                        <div>
                                            <label class="{{ $label }}" for="delete_password">{{ __('profile.enter_password') }}</label>
                                            <input type="password" name="password" id="delete_password" class="{{ $input }} mt-1" required>
                                        </div>
                                    @endunless

                                    <label class="flex max-md:min-h-12 items-start gap-2 py-2 text-xs text-ink">
                                        <input type="checkbox" name="confirm_delete" value="1" required data-account-delete-confirm
                                            class="mt-0.5 h-4 w-4 shrink-0 rounded-sm border-border text-brand focus:ring-brand/40">
                                        <span>{{ __('profile.confirm_delete_lbl') }}</span>
                                    </label>

                                    <x-ui.button type="submit" variant="danger" data-account-delete-submit class="max-md:min-h-12 disabled:cursor-not-allowed disabled:opacity-50">
                                        {{ __('profile.delete') }} <span aria-hidden="true">&raquo;</span>
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>

                    </div>
                </aside>

                <div class="lg:col-span-7 xl:col-span-8">
                    <div class="{{ $card }}">
                        <h3 class="mb-6 text-center text-xl font-semibold text-ink">{{ __('profile.heading') }}</h3>

                        @if ($errors->any())
                            <x-ui.alert variant="error" class="mb-4">
                                <ul class="m-0 list-disc space-y-1 ps-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-ui.alert>
                        @endif

                        @if (session('success'))
                            <x-ui.alert variant="success" class="mb-4">
                                {{ session('success') }}
                            </x-ui.alert>
                        @endif

                        <form method="POST" action="{{ lroute('account.profile.update') }}" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="first_name">{{ __('profile.first_name') }}</label>
                                <input class="{{ $input }}" id="first_name" name="first_name" value="{{ old('first_name', $u->first_name) }}">
                            </div>

                            <div class="grid grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="last_name">{{ __('profile.last_name') }}</label>
                                <input class="{{ $input }}" id="last_name" name="last_name" value="{{ old('last_name', $u->last_name) }}">
                            </div>

                            @php $idMode = old('identify_mode', $u->identify_mode ?? 'full'); @endphp
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-[160px_1fr] md:gap-4">
                                <span class="{{ $label }} md:pt-2">{{ __('profile.identify_as') }}</span>
                                <div class="flex flex-wrap gap-x-6 gap-y-2 md:pt-2">
                                    <label class="inline-flex max-md:min-h-12 items-center gap-2 text-sm text-ink">
                                        <input type="radio" name="identify_mode" value="full" @checked($idMode === 'full')
                                            class="h-4 w-4 border-border text-brand focus:ring-brand/40"> {{ __('profile.id_full') }}
                                    </label>
                                    <label class="inline-flex max-md:min-h-12 items-center gap-2 text-sm text-ink">
                                        <input type="radio" name="identify_mode" value="name" @checked($idMode === 'name')
                                            class="h-4 w-4 border-border text-brand focus:ring-brand/40"> {{ __('profile.id_name') }}
                                    </label>
                                    <label class="inline-flex max-md:min-h-12 items-center gap-2 text-sm text-ink">
                                        <input type="radio" name="identify_mode" value="nick" @checked($idMode === 'nick')
                                            class="h-4 w-4 border-border text-brand focus:ring-brand/40"> {{ __('profile.id_nick') }}
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="nickname">{{ __('profile.id_nick') }}</label>
                                <input class="{{ $input }}" id="nickname" name="nickname" value="{{ old('nickname', $u->nickname) }}">
                            </div>

                            <div class="grid grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="city">{{ __('form.city') }}</label>
                                <input class="{{ $input }}" id="city" name="city" value="{{ old('city', $u->city) }}">
                            </div>

                            <div id="country-field" class="grid scroll-mt-16 grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="country">{{ __('profile.country') }}</label>
                                <div>
                                    <select class="{{ $mustComplete && $missingCountry ? $inputInvalid : $input }}" id="country" name="country" required
                                        @if ($mustComplete && $missingCountry) aria-invalid="true" aria-describedby="country-required" @endif>
                                        <option value="">{{ __('profile.select_country') }}</option>
                                        @foreach ($countries as $countryCode => $countryName)
                                            <option value="{{ $countryCode }}" @selected(old('country', $u->country) === $countryCode)>
                                                {{ $countryName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($mustComplete && $missingCountry)
                                        <span id="country-required" class="mt-1 block text-sm text-danger">{{ __('common.required') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div id="gender-field" class="grid scroll-mt-16 grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="gender">{{ __('profile.gender') }}</label>
                                <div>
                                    <select class="{{ $mustComplete && $missingGender ? $inputInvalid : $input }}" id="gender" name="gender" required
                                        @if ($mustComplete && $missingGender) aria-invalid="true" aria-describedby="gender-required" @endif>
                                        <option value="">{{ __('profile.select_gender') }}</option>
                                        @foreach (\App\Models\User::GENDERS as $genderValue)
                                            <option value="{{ $genderValue }}" @selected(old('gender', $u->gender) === $genderValue)>
                                                {{ __('profile.gender_'.$genderValue) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($mustComplete && $missingGender)
                                        <span id="gender-required" class="mt-1 block text-sm text-danger">{{ __('common.required') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div id="language-field" class="grid scroll-mt-16 grid-cols-1 gap-1 md:grid-cols-[160px_1fr] md:items-center md:gap-4">
                                <label class="{{ $label }}" for="locale">{{ __('profile.preferred_language') }}</label>
                                <div>
                                <select class="{{ $mustComplete && $missingLanguage ? $inputInvalid : $input }}" id="locale" name="locale" required
                                    @if ($mustComplete && $missingLanguage) aria-invalid="true" aria-describedby="language-required" @endif>
                                    <option value="">{{ __('profile.select_language') }}</option>
                                    @foreach ($languages as $language)
                                        <option value="{{ $language->code }}" @selected($preferredLanguage === $language->code)>
                                            {{ $language->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-muted">{{ __('profile.language_help') }}</p>
                                @if ($mustComplete && $missingLanguage)
                                    <span id="language-required" class="mt-1 block text-sm text-danger">{{ __('common.required') }}</span>
                                @endif
                                </div>
                            </div>

                            <hr class="border-border">

                            <p class="text-sm font-semibold text-ink">{{ __('profile.change_email') }}</p>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-[160px_1fr] md:gap-4">
                                <span class="hidden md:block"></span>
                                <div class="space-y-3">
                                    <input class="{{ $input }}" name="new_email" placeholder="{{ __('profile.new_email') }}" value="{{ old('new_email') }}">
                                    <input class="{{ $input }}" name="new_email_confirmation" placeholder="{{ __('profile.confirm_email') }}" value="{{ old('new_email_confirmation') }}">
                                </div>
                            </div>

                            <hr class="border-border">

                            <div id="password-section">
                                @if ($u->hasLocalPassword())
                                    <p class="text-sm font-semibold text-ink">{{ __('profile.change_password') }}</p>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[160px_1fr] md:gap-4">
                                        <span class="hidden md:block"></span>
                                        <div class="space-y-3">
                                            <input type="password" class="{{ $input }}" name="current_password" placeholder="{{ __('profile.current_password') }}">
                                            <input type="password" class="{{ $input }}" name="new_password" placeholder="{{ __('profile.new_password') }}">
                                            <input type="password" class="{{ $input }}" name="new_password_confirmation" placeholder="{{ __('profile.confirm_new_password') }}">
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm font-semibold text-ink">{{ __('profile.set_password') }}</p>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[160px_1fr] md:gap-4">
                                        <span class="hidden md:block"></span>
                                        <div class="space-y-3">
                                            <p class="text-xs leading-relaxed text-muted">{{ __('profile.set_password_help') }}</p>
                                            <a href="{{ lroute('password.request') }}" class="text-sm font-semibold text-brand">{{ __('profile.set_password_cta') }}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="hidden pt-2 text-center md:block">
                                <button type="submit" data-busy-submit data-loading data-loading-text="{{ __('ui.saving') }}" class="{{ $btnBrand }} w-full sm:w-auto sm:min-w-40">
                                    {{ __('profile.save_changes') }} <span aria-hidden="true">&raquo;</span>
                                </button>
                            </div>

                            <x-ui.sticky-action :label="__('profile.save_changes')" />
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

        <x-ads.rotate placement="page_mid" class="mt-6" />
@endsection
