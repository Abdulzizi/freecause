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

        $card = 'rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-6';
        $input = 'block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none';
        $label = 'text-sm font-medium text-gray-700';
        $btnBrand = 'inline-flex items-center justify-center gap-2 rounded-md bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-light focus:ring-2 focus:ring-brand/40 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50';
        $btnOutline = 'inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:ring-2 focus:ring-brand/30 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60';
    @endphp

    <section class="py-6 sm:py-10">
        <div class="mx-auto max-w-6xl px-4">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                <aside class="lg:col-span-1">
                    <div class="{{ $card }}">

                        <h1 class="mb-4 text-xl font-semibold text-gray-900">{{ __('profile.my_data') }}</h1>

                        <dl class="space-y-3 text-sm">
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-3">
                                <dt class="text-gray-500 sm:w-32 sm:shrink-0">{{ __('auth.email') }}:</dt>
                                <dd class="break-all font-medium text-gray-900">{{ $u->email }}</dd>
                            </div>
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-3">
                                <dt class="text-gray-500 sm:w-32 sm:shrink-0">{{ __('profile.reg_date') }}</dt>
                                <dd class="font-medium text-gray-900">{{ optional($u->created_at)->format('d M y H:i') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4">
                            <a class="text-sm font-medium text-brand hover:underline" href="{{ lroute('account.petitions') }}">
                                {{ __('profile.my_petitions') }}
                            </a>
                        </div>

                        <hr class="my-5 border-gray-200">

                        @if ($u->google_id)
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="24" height="24" class="mt-0.5 h-6 w-6 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-900">
                                        {{ __('profile.google_connected') }} <strong class="break-all">{{ $u->email }}</strong>
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>

                            @if ($googleOnly)
                                <button type="button" class="{{ $btnOutline }} mt-3" disabled aria-disabled="true">
                                    {{ __('profile.google_disconnect') }}
                                </button>
                                <p class="mt-2 text-sm text-gray-500">{{ __('profile.google_disconnect_needs_password') }}</p>
                            @else
                                <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect class="mt-3">
                                    @csrf

                                    <button type="button" class="{{ $btnOutline }}" data-google-disconnect-trigger>
                                        {{ __('profile.google_disconnect') }}
                                    </button>

                                    <div class="mt-3 hidden rounded-md border border-gray-200 bg-gray-50 p-3" data-google-disconnect-panel>
                                        <p class="mb-3 text-sm text-gray-700">{{ __('profile.google_disconnect_confirm_q') }}</p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="submit" class="{{ $btnBrand }} hidden" data-google-disconnect-confirm>
                                                {{ __('profile.google_disconnect_confirm') }}
                                            </button>
                                            <button type="button" class="{{ $btnOutline }} hidden" data-google-disconnect-cancel>
                                                {{ __('common.cancel') }}
                                            </button>
                                        </div>
                                    </div>

                                    <noscript>
                                        <button type="submit" class="{{ $btnBrand }} mt-3">
                                            {{ __('profile.google_disconnect') }}
                                        </button>
                                    </noscript>
                                </form>
                            @endif
                        @else
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="24" height="24" class="mt-0.5 h-6 w-6 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-900">{{ __('profile.google_not_connected') }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>
                            <a href="{{ lroute('oauth.google') }}" class="{{ $btnOutline }} mt-3">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="18" height="18" class="h-[18px] w-[18px] shrink-0">
                                {{ __('profile.google_connect') }}
                            </a>
                        @endif

                        <hr class="my-5 border-gray-200">

                        <div class="rounded-md border border-red-300 bg-red-50/40 p-4">
                            <h2 class="text-lg font-semibold text-red-700">{{ __('profile.account_deletion') }}</h2>
                            <p class="mt-2 text-sm text-gray-700">{{ __('profile.delete_explain') }}</p>

                            @error('google_delete')
                                <div class="mt-3 rounded-md border border-red-300 bg-red-100 px-3 py-2 text-sm text-red-800" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror

                            @if ($googleOnly && ! $googleDeleteVerified)
                                <p class="mt-3 text-sm text-gray-700">{{ __('profile.verify_google_delete') }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ __('profile.delete_google_why') }}</p>
                                <a href="{{ lroute('account.delete.google') }}" class="{{ $btnOutline }} mt-3">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                        width="18" height="18" class="h-[18px] w-[18px] shrink-0">
                                    {{ __('profile.verify_with_google') }}
                                </a>
                            @else
                                <form method="POST" action="{{ lroute('account.delete') }}" data-account-delete-form class="mt-4 space-y-3">
                                    @csrf

                                    @unless ($googleOnly)
                                        <div>
                                            <label class="{{ $label }}" for="delete_password">{{ __('profile.enter_password') }}</label>
                                            <input type="password" name="password" id="delete_password" class="{{ $input }} mt-1" required>
                                        </div>
                                    @endunless

                                    <label class="flex items-start gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="confirm_delete" value="1" required data-account-delete-confirm
                                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand/40">
                                        <span>{{ __('profile.confirm_delete_lbl') }}</span>
                                    </label>

                                    <button type="submit" data-account-delete-submit
                                        class="inline-flex items-center justify-center gap-2 rounded-md bg-red-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 focus:ring-2 focus:ring-red-500/40 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                        {{ __('profile.delete') }} <span aria-hidden="true">&raquo;</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </aside>

                <div class="lg:col-span-2">
                    <div class="{{ $card }}">
                        <h3 class="mb-6 text-center text-2xl font-semibold text-gray-900">{{ __('profile.heading') }}</h3>

                        @if ($errors->any())
                            <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3">
                                <ul class="list-disc space-y-1 ps-5 text-sm text-red-800">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ lroute('account.profile.update') }}" class="space-y-5">
                            @csrf

                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="first_name">{{ __('profile.first_name') }}</label>
                                <div class="sm:col-span-3">
                                    <input class="{{ $input }}" id="first_name" name="first_name" value="{{ old('first_name', $u->first_name) }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="last_name">{{ __('profile.last_name') }}</label>
                                <div class="sm:col-span-3">
                                    <input class="{{ $input }}" id="last_name" name="last_name" value="{{ old('last_name', $u->last_name) }}">
                                </div>
                            </div>

                            @php $idMode = old('identify_mode', $u->identify_mode ?? 'full'); @endphp
                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:gap-4">
                                <span class="{{ $label }} sm:col-span-1 sm:pt-2">{{ __('profile.identify_as') }}</span>
                                <div class="flex flex-wrap gap-x-6 gap-y-2 sm:col-span-3 sm:pt-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="identify_mode" value="full" @checked($idMode === 'full')
                                            class="h-4 w-4 border-gray-300 text-brand focus:ring-brand/40"> {{ __('profile.id_full') }}
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="identify_mode" value="name" @checked($idMode === 'name')
                                            class="h-4 w-4 border-gray-300 text-brand focus:ring-brand/40"> {{ __('profile.id_name') }}
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="identify_mode" value="nick" @checked($idMode === 'nick')
                                            class="h-4 w-4 border-gray-300 text-brand focus:ring-brand/40"> {{ __('profile.id_nick') }}
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="nickname">{{ __('profile.id_nick') }}</label>
                                <div class="sm:col-span-3">
                                    <input class="{{ $input }}" id="nickname" name="nickname" value="{{ old('nickname', $u->nickname) }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="city">{{ __('form.city') }}</label>
                                <div class="sm:col-span-3">
                                    <input class="{{ $input }}" id="city" name="city" value="{{ old('city', $u->city) }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="country">{{ __('profile.country') }}</label>
                                <div class="sm:col-span-3">
                                    <select class="{{ $input }}" id="country" name="country">
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
                            <div class="grid grid-cols-1 gap-1 sm:grid-cols-4 sm:items-center sm:gap-4">
                                <label class="{{ $label }} sm:col-span-1" for="locale">{{ __('profile.preferred_language') }}</label>
                                <div class="sm:col-span-3">
                                    <select class="{{ $input }}" id="locale" name="locale" required>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->code }}" @selected($preferredLanguage === $language->code)>
                                                {{ $language->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-gray-900">{{ __('profile.change_email') }}</p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:gap-4">
                                    <div class="space-y-3 sm:col-span-3 sm:col-start-2">
                                        <input class="{{ $input }}" name="new_email" placeholder="{{ __('profile.new_email') }}"
                                            value="{{ old('new_email') }}">
                                        <input class="{{ $input }}" name="new_email_confirmation"
                                            placeholder="{{ __('profile.confirm_email') }}" value="{{ old('new_email_confirmation') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-gray-900">{{ __('profile.change_password') }}</p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:gap-4">
                                    <div class="space-y-3 sm:col-span-3 sm:col-start-2">
                                        <input type="password" class="{{ $input }}" name="current_password"
                                            placeholder="{{ __('profile.current_password') }}">
                                        <input type="password" class="{{ $input }}" name="new_password"
                                            placeholder="{{ __('profile.new_password') }}">
                                        <input type="password" class="{{ $input }}" name="new_password_confirmation"
                                            placeholder="{{ __('profile.confirm_new_password') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 text-center">
                                <button type="submit" data-busy-submit class="{{ $btnBrand }} min-w-40">
                                    {{ __('profile.save_changes') }} <span aria-hidden="true">&raquo;</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
