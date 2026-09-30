@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'col-md-6' : 'col-12';
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
    $signPostUrl = lroute('petition.sign', ['slug' => $tr->slug ?? '', 'id' => $petition->id]);
@endphp

@if ($isAuthed)
    @if (!empty($hasSigned) && $hasSigned)

        <p class="mb-0 text-[14px]!">
            {{ $tSignedAlready }}
        </p>
    @else
        <form action="{{ $signPageUrl }}" method="POST" class="fc-sign-form fc-sign-form--{{ $variant }}">
            @csrf

            <div class="row g-2">

                <div class="col-12 mb-[14px]!">
                    @if ($useLabels)
                        <label class="form-label text-[12px]! mb-[4px]! text-[#444]!">{{ $lblComment }}</label>
                        <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}">
                    @else
                        <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}">
                    @endif
                </div>

                {{-- @include('petition.partials._agreements') --}}

                <div class="mb-[14px]!">
                    <div class="font-bold!">
                        {!! $agree1Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree1" value="agree"
                                {{ old('agree1', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block text-[14px]!">
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
                    <div class="mt-2">
                        <label class="d-block text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree2" value="agree"
                                {{ old('agree2', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block text-[14px]!">
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
                    <div class="mt-2">
                        <label class="d-block text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree3" value="agree"
                                {{ old('agree3', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block text-[14px]!">
                            <input class="scale-90! mr-[6px]!" type="radio" name="agree3" value="no"
                                {{ old('agree3') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-danger bg-[#cc1f1f]! border-[#cc1f1f]! rounded-[6px]! px-[20px]! py-[8px]! font-semibold! text-[14px]!" type="submit">{{ $tBtnSign }}</button>
                </div>

            </div>
        </form>

    @endif
@else
    <form action="{{ $signPageUrl }}" method="POST" class="fc-sign-form fc-sign-form--{{ $variant }}">
        @csrf

        <div class="row g-2">

            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach

                            @if (session('login_url'))
                                <li><a class="text-brand!" href="{{ session('login_url') }}">{{ __('auth.go_to_login') }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif

            <div class="{{ $col }} mb-[14px]!">
                @if ($useLabels)
                    <label class="form-label text-[12px]! mb-[4px]! text-[#444]!">{{ $lblName }}</label>
                    <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="name" value="{{ old('name') }}">
                @else
                    <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="name" value="{{ old('name') }}"
                        placeholder="{{ $phName }}">
                @endif
            </div>

            <div class="{{ $col }} mb-[14px]!">
                @if ($useLabels)
                    <label class="form-label text-[12px]! mb-[4px]! text-[#444]!">{{ $lblEmail }}</label>
                    <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="email" name="email" value="{{ old('email') }}">
                @else
                    <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="email" name="email" value="{{ old('email') }}"
                        placeholder="{{ $phEmail }}">
                @endif
            </div>

            @if ($variant === 'split')
                <div class="col-12 text-[14px]!">
                    {{ $privacyHint }}
                </div>
            @endif

            <div class="fc-sign-advanced d-none">
                <div class="col-12 mb-[14px]!">
                    @if ($useLabels)
                        <label class="form-label text-[12px]! mb-[4px]! text-[#444]!">{{ $lblComment }}</label>
                        <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}">
                    @else
                        <input class="form-control h-[32px]! px-[10px]! py-[6px]! text-[13px]! rounded-[6px]! placeholder:italic! placeholder:text-[#999]!" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}">
                    @endif
                </div>
            </div>

            <div class="col-12">
                @if ($variant === 'split')
                    <p class="mt-2 text-[13px]! leading-[1.5]! mb-[14px]!">
                        {{ $emailWarning }}
                    </p>
                @endif

                {{-- agreements --}}
                <div class="p-3 rounded bg-light border mb-[14px]!">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="agree1_{{ $variant }}" name="agree1" value="1" {{ old('agree1') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]!" for="agree1_{{ $variant }}">{!! $agree1Title !!}</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="agree2_{{ $variant }}" name="agree2" value="1" {{ old('agree2') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]!" for="agree2_{{ $variant }}">{!! $agree2Title !!}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree3_{{ $variant }}" name="agree3" value="1" {{ old('agree3') ? 'checked' : '' }}>
                        <label class="form-check-label text-[14px]!" for="agree3_{{ $variant }}">{!! $agree3Title !!}</label>
                    </div>
                </div>

                <button class="btn btn-danger bg-[#cc1f1f]! border-[#cc1f1f]! rounded-[6px]! px-[20px]! py-[8px]! font-semibold! text-[14px]!" type="submit">{{ $tBtnSign }}</button>
            </div>
        </div>
    </form>
@endif
