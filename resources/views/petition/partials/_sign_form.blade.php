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
    $phSurname = __('sign.ph_surname');
    $phEmail = __('sign.ph_email');
    $phPassword = __('sign.ph_password');
    $phCity = __('sign.ph_city');
    $phNickname = __('sign.ph_nickname');
    $phComment = __('sign.ph_comment');
    $defaultComment = __('sign.default_comment');

    $lblName = __('sign.lbl_name');
    $lblSurname = __('sign.lbl_surname');
    $lblEmail = __('sign.lbl_email');
    $lblPassword = __('sign.lbl_password');
    $lblCity = __('sign.lbl_city');
    $lblNickname = __('sign.lbl_nickname');
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

        <p class="mb-0" style="font-size:14px;">
            {{ $tSignedAlready }}
        </p>
    @else
        <form action="{{ $signPageUrl }}" method="GET" class="fc-sign-form fc-sign-form--{{ $variant }}">

            <div class="row g-2">

                <div class="col-12 mb-3">
                    @if ($useLabels)
                        <label class="form-label fc-mini-label">{{ $lblComment }}</label>
                        <input class="form-control" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}">
                    @else
                        <input class="form-control" type="text" name="comment"
                            value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}">
                    @endif
                </div>

                {{-- @include('petition.partials._agreements') --}}

                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree1Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="agree"
                                {{ old('agree1', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="no"
                                {{ old('agree1') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree2Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="agree"
                                {{ old('agree2', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="no"
                                {{ old('agree2') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree3Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="agree"
                                {{ old('agree3', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="no"
                                {{ old('agree3') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-danger fc-sign-btn" type="submit">{{ $tBtnSign }}</button>
                </div>

            </div>
        </form>

    @endif
@else
    <form action="{{ $signPostUrl }}" method="POST" class="fc-sign-form fc-sign-form--{{ $variant }}">
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
                                <li><a class="red" href="{{ session('login_url') }}">{{ __('auth.go_to_login') }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblName }}</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name') }}">
                @else
                    <input class="form-control" type="text" name="name" value="{{ old('name') }}"
                        placeholder="{{ $phName }}">
                @endif
            </div>

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblSurname }}</label>
                    <input class="form-control" type="text" name="surname" value="{{ old('surname') }}">
                @else
                    <input class="form-control" type="text" name="surname" value="{{ old('surname') }}"
                        placeholder="{{ $phSurname }}">
                @endif
            </div>

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblEmail }}</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                @else
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                        placeholder="{{ $phEmail }}">
                @endif
            </div>

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblPassword }}</label>
                    <input class="form-control" type="password" name="password">
                @else
                    <input class="form-control" type="password" name="password" placeholder="{{ $phPassword }}">
                @endif
            </div>

            @if ($variant === 'split')
                <div class="col-12" style="font-size:14px;">
                    {{ $privacyHint }}
                </div>
            @endif

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblCity }}</label>
                    <input class="form-control" type="text" name="city" value="{{ old('city') }}">
                @else
                    <input class="form-control" type="text" name="city" value="{{ old('city') }}"
                        placeholder="{{ $phCity }}">
                @endif
            </div>

            <div class="{{ $col }} mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblNickname }}</label>
                    <input class="form-control" type="text" name="nickname" value="{{ old('nickname') }}">
                @else
                    <input class="form-control" type="text" name="nickname" value="{{ old('nickname') }}"
                        placeholder="{{ $phNickname }}">
                @endif
            </div>

            <div class="col-12 mb-3">
                @if ($useLabels)
                    <label class="form-label fc-mini-label">{{ $lblComment }}</label>
                    <input class="form-control" type="text" name="comment"
                        value="{{ old('comment', $defaultComment) }}">
                @else
                    <input class="form-control" type="text" name="comment"
                        value="{{ old('comment', $defaultComment) }}" placeholder="{{ $phComment }}">
                @endif
            </div>

            <div class="col-12">
                @if ($variant === 'split')
                    <p class="mt-2 mb-3" style="font-size:13px;">
                        {{ $emailWarning }}
                    </p>
                @endif

                {{-- agreements --}}
                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree1Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="agree"
                                {{ old('agree1', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="no"
                                {{ old('agree1') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree2Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="agree"
                                {{ old('agree2', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="no"
                                {{ old('agree2') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        {!! $agree3Title !!}
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="agree"
                                {{ old('agree3', 'agree') === 'agree' ? 'checked' : '' }}>
                            {{ $agreeYes }}
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="no"
                                {{ old('agree3') === 'no' ? 'checked' : '' }}>
                            {{ $agreeNo }}
                        </label>
                    </div>
                </div>

                <button class="btn btn-danger fc-sign-btn" type="submit">{{ $tBtnSign }}</button>
            </div>
        </div>
    </form>
@endif
