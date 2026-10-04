@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'w-full! md:w-1/2! shrink-0! px-1!' : 'w-full! px-1!';
    $useLabels = $variant === 'split';
    $isAuthed = auth()->check();

    $tSignedAlready = __('sign.already_signed');
    $tSignedHint = __('sign.signed_hint');
    $tBtnSign = __('sign.btn');
    $tBtnArrow = __('sign.btn_arrow');

    $phName = __('sign.ph_name');
    $phEmail = __('sign.ph_email');
    $phComment = __('sign.ph_comment');
    $defaultComment = __('sign.default_comment');

    $lblName = __('sign.lbl_name');
    $lblEmail = __('sign.lbl_email');
    $lblComment = __('sign.lbl_comment');

    $privacyHint = __('sign.privacy_hint');
    $emailWarning = __('sign.email_warning');

    $agree1Title = __('sign.agree1_title', ['terms_url' => lroute('page.show', ['slug' => 'terms-of-service'])]);
    $agree2Title = __('sign.agree2_title', ['privacy_url' => lroute('page.show', ['slug' => 'privacy-policy'])]);
    $agree3Title = __('sign.agree3_title', ['privacy_url' => lroute('page.show', ['slug' => 'privacy-policy'])]);
    $agreeYes = __('sign.agree_yes');
    $agreeNo = __('sign.agree_no');

    $signPageUrl = lroute('petition.sign.page', ['slug' => $tr->slug ?? '', 'id' => $petition->id]);
@endphp

@if ($isAuthed)
    @if (!empty($hasSigned) && $hasSigned)

        <p class="mb-0! text-[14px]!">
            {{ $tSignedAlready }}
        </p>
    @else
        <form action="{{ $signPageUrl }}" method="POST" data-sign-busy class="fc-sign-form fc-sign-form--{{ $variant }}">
            @csrf
            <x-ui.alert variant="error" class="mt-2!" data-sign-network-error hidden>{{ __('sign.connection_error') }}</x-ui.alert>

            <div class="flex! flex-wrap! -mx-1!">

                @if ($errors->any())
                    <div class="w-full!">
                        <x-ui.alert variant="error">
                            <ul class="mb-0!">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    </div>
                @endif

                <div class="w-full! mb-[14px]!">
                    @if ($useLabels)
                        <label class="block! mb-2! text-[12px]! mb-[4px]! text-[#444]!" for="sign_comment_auth_{{ $variant }}">{{ $lblComment }}</label>
                        <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment" id="sign_comment_auth_{{ $variant }}"
                            value="{{ old('comment', $defaultComment) }}" required>
                    @else
                        <label for="sign_comment_auth_{{ $variant }}" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">{{ $lblComment }}</label>
                        <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment" id="sign_comment_auth_{{ $variant }}"
                            value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}" required>
                    @endif
                </div>

                {{-- @include('petition.partials._agreements') --}}

                <div class="mb-[14px]!">
                    <div class="font-bold!">
                        {!! $agree1Title !!}
                    </div>
                    <div class="mt-2!">
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree1" value="agree"
                                {{ old('agree1', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree1" value="no"
                                {{ old('agree1') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-[14px]!">
                    <div class="font-bold!">
                        {!! $agree2Title !!}
                    </div>
                    <div class="mt-2!">
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree2" value="agree"
                                {{ old('agree2', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree2" value="no"
                                {{ old('agree2') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-[14px]!">
                    <div class="font-bold!">
                        {!! $agree3Title !!}
                    </div>
                    <div class="mt-2!">
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree3" value="agree"
                                {{ old('agree3', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="block! text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree3" value="no"
                                {{ old('agree3') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="w-full!">
                    <button class="btn btn-danger bg-[#cc1f1f]! border-[#cc1f1f]! rounded-[6px]! px-[20px]! py-[8px]! font-semibold! text-[14px]!" type="submit">{{ $tBtnSign }}</button>
                </div>

            </div>
        </form>

    @endif
@else
    <form action="{{ $signPageUrl }}" method="POST" data-sign-busy class="fc-sign-form fc-sign-form--{{ $variant }}">
        @csrf
        <x-ui.alert variant="error" class="mt-2!" data-sign-network-error hidden>{{ __('sign.connection_error') }}</x-ui.alert>

        <div class="flex! flex-wrap! -mx-1!">

            @if ($errors->any())
                <div class="w-full!">
                    <x-ui.alert variant="error">
                        <ul class="mb-0!">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach

                            @if (session('login_url'))
                                <li><a class="text-brand!" href="{{ session('login_url') }}">{{ __('auth.go_to_login') }}</a></li>
                            @endif
                        </ul>
                    </x-ui.alert>
                </div>
            @endif

            <div class="{{ $col }} mb-[14px]!">
                @if ($useLabels)
                    <label class="block! mb-2! text-[12px]! mb-[4px]! text-[#444]!" for="sign_name_{{ $variant }}">{{ $lblName }}</label>
                    <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="name" id="sign_name_{{ $variant }}" value="{{ old('name') }}" required>
                @else
                    <label for="sign_name_{{ $variant }}" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">{{ $lblName }}</label>
                    <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="name" id="sign_name_{{ $variant }}" value="{{ old('name') }}"
                        placeholder="{{ $phName }}" required>
                @endif
            </div>

            <div class="{{ $col }} mb-[14px]!">
                @if ($useLabels)
                    <label class="block! mb-2! text-[12px]! mb-[4px]! text-[#444]!" for="sign_email_{{ $variant }}">{{ $lblEmail }}</label>
                    <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="email" name="email" id="sign_email_{{ $variant }}" value="{{ old('email') }}" required aria-required="true">
                @else
                    <label for="sign_email_{{ $variant }}" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">{{ $lblEmail }}</label>
                    <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="email" name="email" id="sign_email_{{ $variant }}" value="{{ old('email') }}"
                        placeholder="{{ $phEmail }}" required aria-required="true">
                @endif
            </div>

            @if ($variant === 'split')
                <div class="w-full! text-[14px]!">
                    {{ $privacyHint }}
                </div>
            @endif

            <div class="fc-sign-advanced hidden!">
                <div class="w-full! mb-[14px]!">
                    @if ($useLabels)
                        <label class="block! mb-2! text-[12px]! mb-[4px]! text-[#444]!" for="sign_comment_guest_{{ $variant }}">{{ $lblComment }}</label>
                        <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment" id="sign_comment_guest_{{ $variant }}"
                            value="{{ old('comment', $defaultComment) }}" required>
                    @else
                        <label for="sign_comment_guest_{{ $variant }}" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">{{ $lblComment }}</label>
                        <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment" id="sign_comment_guest_{{ $variant }}"
                            value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}" required>
                    @endif
                </div>
            </div>

            <div class="w-full!">
                @if ($variant === 'split')
                    <p class="mt-2! text-[13px]! leading-[1.5]! mb-[14px]!">
                        {{ $emailWarning }}
                    </p>
                @endif

                {{-- agreements --}}
                <div class="p-4! rounded-[.25rem]! bg-[#f8f9fa]! border! border-[#dee2e6]! mb-[14px]!">
                    <div class="flex! items-start! gap-2! mb-2!">
                        <input class="form-check-input shrink-0! float-none! ml-0! mt-0.5!" type="checkbox" id="agree1_{{ $variant }}" name="agree1" value="1" {{ old('agree1') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]! leading-snug!" for="agree1_{{ $variant }}">{!! $agree1Title !!}</label>
                    </div>
                    <div class="flex! items-start! gap-2! mb-2!">
                        <input class="form-check-input shrink-0! float-none! ml-0! mt-0.5!" type="checkbox" id="agree2_{{ $variant }}" name="agree2" value="1" {{ old('agree2') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]! leading-snug!" for="agree2_{{ $variant }}">{!! $agree2Title !!}</label>
                    </div>
                    <div class="flex! items-start! gap-2!">
                        <input class="form-check-input shrink-0! float-none! ml-0! mt-0.5!" type="checkbox" id="agree3_{{ $variant }}" name="agree3" value="1" {{ old('agree3') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]! leading-snug!" for="agree3_{{ $variant }}">{!! $agree3Title !!}</label>
                    </div>
                </div>

                <button class="btn btn-danger bg-[#cc1f1f]! border-[#cc1f1f]! rounded-[6px]! px-[20px]! py-[8px]! font-semibold! text-[14px]!" type="submit">{{ $tBtnSign }}</button>
            </div>
        </div>
    </form>
@endif
