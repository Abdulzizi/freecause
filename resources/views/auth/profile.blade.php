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

        $card = 'rounded-xl! border! border-gray-200! bg-white! p-6! shadow-sm!';
        $input = 'block! w-full! rounded-md! border! border-gray-300! bg-white! px-3! py-2! text-[14px]! leading-normal! text-gray-900! transition! focus:border-brand! focus:ring-2! focus:ring-brand/25! focus:outline-none!';
        $label = 'text-[13px]! font-medium! text-gray-700!';
        $btnBrand = 'inline-flex! items-center! justify-center! gap-2! rounded-md! bg-brand! px-5! py-2.5! text-[14px]! font-semibold! text-white! no-underline! transition! hover:bg-brand-light! focus:outline-none! disabled:cursor-not-allowed! disabled:opacity-50!';
        $btnOutline = 'inline-flex! items-center! justify-center! gap-2! rounded-md! border! border-gray-300! bg-white! px-4! py-2! text-[13px]! font-medium! text-gray-700! no-underline! transition! hover:bg-gray-50! focus:outline-none! disabled:cursor-not-allowed! disabled:opacity-60!';
    @endphp

    <section class="py-8! bg-[#f7f7f8]!">
        <div class="mx-auto! max-w-5xl! px-4!">
            <div class="grid! grid-cols-1! items-start! gap-6! lg:grid-cols-12!">

                <aside class="lg:col-span-5! xl:col-span-4!">
                    <div class="{{ $card }}">

                        <h1 class="mb-4! text-[18px]! font-semibold! text-gray-900!">{{ __('profile.my_data') }}</h1>

                        <div class="space-y-2.5! text-[13px]!">
                            <div class="flex! flex-wrap! gap-x-2!">
                                <span class="w-28! shrink-0! text-gray-500!">{{ __('auth.email') }}:</span>
                                <span class="min-w-0! flex-1! font-medium! break-all! text-gray-900!">{{ $u->email }}</span>
                            </div>
                            <div class="flex! flex-wrap! gap-x-2!">
                                <span class="w-28! shrink-0! text-gray-500!">{{ __('profile.reg_date') }}</span>
                                <span class="min-w-0! flex-1! font-medium! text-gray-900!">{{ optional($u->created_at)->format('d M y H:i') }}</span>
                            </div>
                        </div>

                        <div class="mt-4!">
                            <a class="text-[13px]! font-medium! text-brand! no-underline! hover:underline!" href="{{ lroute('account.petitions') }}">
                                {{ __('profile.my_petitions') }}
                            </a>
                        </div>

                        <hr class="my-5! border-gray-200!">

                        @error('google_link')
                            <div class="mb-3! rounded-md! border! border-red-300! bg-red-50! px-3! py-2! text-[12px]! text-red-800!" role="alert">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($u->google_id && $googleOnly)
                            <div class="flex! items-start! gap-3!">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5! h-[22px]! w-[22px]! shrink-0!">
                                <div class="min-w-0!">
                                    <p class="text-[13px]! text-gray-900!">{{ __('profile.google_connected') }}</p>
                                    <strong class="block! text-[13px]! font-semibold! break-all! text-gray-900!">{{ $u->email }}</strong>
                                    <p class="mt-1! text-[12px]! leading-relaxed! text-gray-500!">{{ __('profile.google_only_note') }}</p>
                                </div>
                            </div>

                            <button type="button" class="{{ $btnBrand }} mt-3!" data-scroll-to-password>
                                {{ __('profile.set_password_cta') }}
                            </button>

                            <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect data-google-disconnect-needs-password
                                data-need-title="{{ __('profile.need_password_title') }}"
                                data-need-text="{{ __('profile.need_password_text') }}"
                                data-need-ok="{{ __('profile.need_password_ok') }}" class="mt-3!">
                                @csrf

                                <button type="button" class="{{ $btnOutline }}" data-google-disconnect-trigger>
                                    {{ __('profile.google_disconnect') }}
                                </button>

                                <noscript>
                                    <p class="mt-2! text-[12px]! text-gray-500!">{{ __('profile.google_disconnect_needs_password') }}</p>
                                </noscript>
                            </form>
                        @elseif ($u->google_id && ! $googleOnly)
                            <div class="flex! items-start! gap-3!">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5! h-[22px]! w-[22px]! shrink-0!">
                                <div class="min-w-0!">
                                    <p class="text-[13px]! text-gray-900!">{{ __('profile.google_connected') }}</p>
                                    <strong class="block! text-[13px]! font-semibold! break-all! text-gray-900!">{{ $u->email }}</strong>
                                    <p class="mt-1! text-[12px]! leading-relaxed! text-gray-500!">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ lroute('account.unlink.google') }}" data-google-disconnect
                                data-confirm-title="{{ __('profile.confirm_disconnect_title') }}"
                                data-confirm-text="{{ __('profile.confirm_disconnect_text') }}"
                                data-confirm-ok="{{ __('profile.confirm_disconnect_ok') }}"
                                data-confirm-cancel="{{ __('profile.confirm_cancel') }}" class="mt-3!">
                                @csrf

                                <button type="button" class="{{ $btnOutline }}" data-google-disconnect-trigger>
                                    {{ __('profile.google_disconnect') }}
                                </button>

                                <noscript>
                                    <button type="submit" class="{{ $btnBrand }} mt-3!">
                                        {{ __('profile.google_disconnect') }}
                                    </button>
                                </noscript>
                            </form>
                        @else
                            <div class="flex! items-start! gap-3!">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="22" height="22" class="mt-0.5! h-[22px]! w-[22px]! shrink-0!">
                                <div class="min-w-0!">
                                    <p class="text-[13px]! text-gray-900!">{{ __('profile.google_not_connected') }}</p>
                                    <p class="mt-1! text-[12px]! leading-relaxed! text-gray-500!">{{ __('profile.google_help') }}</p>
                                </div>
                            </div>
                            <a href="{{ lroute('oauth.google', ['flow' => 'link']) }}" class="{{ $btnOutline }} mt-3!">
                                <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                    width="16" height="16" class="h-4! w-4! shrink-0!">
                                {{ __('profile.google_connect') }}
                            </a>
                        @endif

                        <hr class="my-5! border-gray-200!">

                        <div class="rounded-lg! border! border-red-200! bg-red-50/50! p-4!">
                            <h2 class="text-[15px]! font-semibold! text-red-700!">{{ __('profile.account_deletion') }}</h2>
                            <p class="mt-2! text-[12px]! leading-relaxed! text-gray-600!">{{ __('profile.delete_explain') }}</p>

                            @error('google_delete')
                                <div class="mt-3! rounded-md! border! border-red-300! bg-red-100! px-3! py-2! text-[12px]! text-red-800!" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror

                            @if ($googleOnly && ! $googleDeleteVerified)
                                <p class="mt-3! text-[12px]! text-gray-600!">{{ __('profile.verify_google_delete') }}</p>
                                <p class="mt-1! text-[12px]! text-gray-500!">{{ __('profile.delete_google_why') }}</p>
                                <a href="{{ lroute('account.delete.google') }}" class="{{ $btnOutline }} mt-3!">
                                    <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" aria-hidden="true"
                                        width="16" height="16" class="h-4! w-4! shrink-0!">
                                    {{ __('profile.verify_with_google') }}
                                </a>
                            @else
                                <form method="POST" action="{{ lroute('account.delete') }}" data-account-delete-form
                                    data-confirm-title="{{ __('profile.confirm_delete_title') }}"
                                    data-confirm-text="{{ __('profile.confirm_delete_text') }}"
                                    data-confirm-ok="{{ __('profile.confirm_delete_ok') }}"
                                    data-confirm-cancel="{{ __('profile.confirm_cancel') }}" class="mt-4! space-y-3!">
                                    @csrf

                                    @unless ($googleOnly)
                                        <div>
                                            <label class="{{ $label }}" for="delete_password">{{ __('profile.enter_password') }}</label>
                                            <input type="password" name="password" id="delete_password" class="{{ $input }} mt-1!" required>
                                        </div>
                                    @endunless

                                    <label class="flex! items-start! gap-2! text-[12px]! text-gray-700!">
                                        <input type="checkbox" name="confirm_delete" value="1" required data-account-delete-confirm
                                            class="mt-0.5! h-4! w-4! shrink-0! rounded! border-gray-300! text-brand! focus:ring-brand/40!">
                                        <span>{{ __('profile.confirm_delete_lbl') }}</span>
                                    </label>

                                    <button type="submit" data-account-delete-submit
                                        class="inline-flex! items-center! justify-center! gap-2! rounded-md! bg-red-700! px-5! py-2.5! text-[14px]! font-semibold! text-white! transition! hover:bg-red-800! focus:outline-none! disabled:cursor-not-allowed! disabled:opacity-50!">
                                        {{ __('profile.delete') }} <span aria-hidden="true">&raquo;</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </aside>

                <div class="lg:col-span-7! xl:col-span-8!">
                    <div class="{{ $card }}">
                        <h3 class="mb-6! text-center! text-[22px]! font-semibold! text-gray-900!">{{ __('profile.heading') }}</h3>

                        @if ($errors->any())
                            <div class="mb-4! rounded-md! border! border-red-300! bg-red-50! px-4! py-3!">
                                <ul class="m-0! list-disc! space-y-1! ps-5! text-[13px]! text-red-800!">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-4! rounded-md! border! border-green-300! bg-green-50! px-4! py-3! text-[13px]! text-green-800!">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ lroute('account.profile.update') }}" class="space-y-4!">
                            @csrf

                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="first_name">{{ __('profile.first_name') }}</label>
                                <input class="{{ $input }}" id="first_name" name="first_name" value="{{ old('first_name', $u->first_name) }}">
                            </div>

                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="last_name">{{ __('profile.last_name') }}</label>
                                <input class="{{ $input }}" id="last_name" name="last_name" value="{{ old('last_name', $u->last_name) }}">
                            </div>

                            @php $idMode = old('identify_mode', $u->identify_mode ?? 'full'); @endphp
                            <div class="grid! grid-cols-1! gap-2! md:grid-cols-[160px_1fr]! md:gap-4!">
                                <span class="{{ $label }} md:pt-2!">{{ __('profile.identify_as') }}</span>
                                <div class="flex! flex-wrap! gap-x-6! gap-y-2! md:pt-2!">
                                    <label class="inline-flex! items-center! gap-2! text-[13px]! text-gray-700!">
                                        <input type="radio" name="identify_mode" value="full" @checked($idMode === 'full')
                                            class="h-4! w-4! border-gray-300! text-brand! focus:ring-brand/40!"> {{ __('profile.id_full') }}
                                    </label>
                                    <label class="inline-flex! items-center! gap-2! text-[13px]! text-gray-700!">
                                        <input type="radio" name="identify_mode" value="name" @checked($idMode === 'name')
                                            class="h-4! w-4! border-gray-300! text-brand! focus:ring-brand/40!"> {{ __('profile.id_name') }}
                                    </label>
                                    <label class="inline-flex! items-center! gap-2! text-[13px]! text-gray-700!">
                                        <input type="radio" name="identify_mode" value="nick" @checked($idMode === 'nick')
                                            class="h-4! w-4! border-gray-300! text-brand! focus:ring-brand/40!"> {{ __('profile.id_nick') }}
                                    </label>
                                </div>
                            </div>

                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="nickname">{{ __('profile.id_nick') }}</label>
                                <input class="{{ $input }}" id="nickname" name="nickname" value="{{ old('nickname', $u->nickname) }}">
                            </div>

                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="city">{{ __('form.city') }}</label>
                                <input class="{{ $input }}" id="city" name="city" value="{{ old('city', $u->city) }}">
                            </div>

                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="country">{{ __('profile.country') }}</label>
                                <select class="{{ $input }}" id="country" name="country">
                                    <option value="">{{ __('profile.select_country') }}</option>
                                    @foreach ($countries as $countryCode => $countryName)
                                        <option value="{{ $countryCode }}" @selected(old('country', $u->country) === $countryCode)>
                                            {{ $countryName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @php $preferredLanguage = old('locale', \App\Support\Locale::toShort($u->locale)); @endphp
                            <div class="grid! grid-cols-1! gap-1! md:grid-cols-[160px_1fr]! md:items-center! md:gap-4!">
                                <label class="{{ $label }}" for="locale">{{ __('profile.preferred_language') }}</label>
                                <select class="{{ $input }}" id="locale" name="locale" required>
                                    @foreach ($languages as $language)
                                        <option value="{{ $language->code }}" @selected($preferredLanguage === $language->code)>
                                            {{ $language->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="border-gray-200!">

                            <p class="text-[14px]! font-semibold! text-gray-900!">{{ __('profile.change_email') }}</p>
                            <div class="grid! grid-cols-1! gap-3! md:grid-cols-[160px_1fr]! md:gap-4!">
                                <span class="hidden! md:block!"></span>
                                <div class="space-y-3!">
                                    <input class="{{ $input }}" name="new_email" placeholder="{{ __('profile.new_email') }}" value="{{ old('new_email') }}">
                                    <input class="{{ $input }}" name="new_email_confirmation" placeholder="{{ __('profile.confirm_email') }}" value="{{ old('new_email_confirmation') }}">
                                </div>
                            </div>

                            <hr class="border-gray-200!">

                            <div id="password-section">
                                @if ($u->hasLocalPassword())
                                    <p class="text-[14px]! font-semibold! text-gray-900!">{{ __('profile.change_password') }}</p>
                                    <div class="grid! grid-cols-1! gap-3! md:grid-cols-[160px_1fr]! md:gap-4!">
                                        <span class="hidden! md:block!"></span>
                                        <div class="space-y-3!">
                                            <input type="password" class="{{ $input }}" name="current_password" placeholder="{{ __('profile.current_password') }}">
                                            <input type="password" class="{{ $input }}" name="new_password" placeholder="{{ __('profile.new_password') }}">
                                            <input type="password" class="{{ $input }}" name="new_password_confirmation" placeholder="{{ __('profile.confirm_new_password') }}">
                                        </div>
                                    </div>
                                @else
                                    <p class="text-[14px]! font-semibold! text-gray-900!">{{ __('profile.set_password') }}</p>
                                    <div class="grid! grid-cols-1! gap-3! md:grid-cols-[160px_1fr]! md:gap-4!">
                                        <span class="hidden! md:block!"></span>
                                        <div class="space-y-3!">
                                            <p class="text-[12px]! leading-relaxed! text-gray-500!">{{ __('profile.set_password_help') }}</p>
                                            <input type="password" class="{{ $input }}" name="new_password" placeholder="{{ __('profile.new_password') }}">
                                            <input type="password" class="{{ $input }}" name="new_password_confirmation" placeholder="{{ __('profile.confirm_new_password') }}">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-2! text-center!">
                                <button type="submit" data-busy-submit class="{{ $btnBrand }} w-full! sm:w-auto! sm:min-w-40!">
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
