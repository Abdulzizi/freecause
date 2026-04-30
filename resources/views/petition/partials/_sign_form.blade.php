@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'col-md-6' : 'col-12';
    $useLabels = $variant === 'split';
    $isAuthed = auth()->check();

    $tSignedAlready = trans_db('sign.already_signed');
    $tSignedHint = trans_db('sign.signed_hint');
    $tBtnSign = trans_db('sign.btn');
    $tBtnArrow = trans_db('sign.btn_arrow');

    $phName = trans_db('sign.ph_name');
    $phSurname = trans_db('sign.ph_surname');
    $phEmail = trans_db('sign.ph_email');
    $phPassword = trans_db('sign.ph_password');
    $phCity = trans_db('sign.ph_city');
    $phNickname = trans_db('sign.ph_nickname');
    $phComment = trans_db('sign.ph_comment');
    $defaultComment = trans_db('sign.default_comment');

    $lblName = trans_db('sign.lbl_name');
    $lblSurname = trans_db('sign.lbl_surname');
    $lblEmail = trans_db('sign.lbl_email');
    $lblPassword = trans_db('sign.lbl_password');
    $lblCity = trans_db('sign.lbl_city');
    $lblNickname = trans_db('sign.lbl_nickname');
    $lblComment = trans_db('sign.lbl_comment');

    $privacyHint = trans_db('sign.privacy_hint');
    $emailWarning = trans_db('sign.email_warning');

    $agree1Title = trans_db('sign.agree1_title', ['terms_url' => lroute('page.show', ['slug' => 'terms-of-service'])]);
    $agree2Title = trans_db('sign.agree2_title', ['privacy_url' => lroute('page.show', ['slug' => 'privacy-policy'])]);
    $agree3Title = trans_db('sign.agree3_title', ['privacy_url' => lroute('page.show', ['slug' => 'privacy-policy'])]);
    $agreeYes = trans_db('sign.agree_yes');
    $agreeNo = trans_db('sign.agree_no');

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
                                <li><a class="red" href="{{ session('login_url') }}">go to login</a></li>
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
