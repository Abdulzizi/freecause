@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'col-md-6' : 'col-12';
    $useLabels = $variant === 'split';
    $isAuthed = auth()->check();

    $content = $content ?? collect();

    $tSignedAlready = $content['signed_already'] ?? 'You signed this petition.';
    $tSignedHint =
        $content['signed_hint'] ?? 'Support and share your cause. Please click "like" button and sign the petition';
    $tBtnSign = $content['btn_sign'] ?? 'Sign';
    $tBtnArrow = $content['btn_sign_arrow'] ?? '»';

    $phName = $content['ph_name'] ?? 'Name (mandatory)';
    $phSurname = $content['ph_surname'] ?? 'Surname (mandatory)';
    $phEmail = $content['ph_email'] ?? 'Email (mandatory)';
    $phPassword = $content['ph_password'] ?? 'Choose a password (mandatory)';
    $phCity = $content['ph_city'] ?? 'City (optional)';
    $phNickname = $content['ph_nickname'] ?? 'Nickname (optional)';
    $phComment = $content['ph_comment'] ?? 'Comment';
    $defaultComment = $content['default_comment'] ?? 'I support this petition';

    $lblName = $content['lbl_name'] ?? $phName;
    $lblSurname = $content['lbl_surname'] ?? $phSurname;
    $lblEmail = $content['lbl_email'] ?? $phEmail;
    $lblPassword = $content['lbl_password'] ?? $phPassword;
    $lblCity = $content['lbl_city'] ?? $phCity;
    $lblNickname = $content['lbl_nickname'] ?? $phNickname;
    $lblComment = $content['lbl_comment'] ?? $phComment;

    $privacyHint = $content['privacy_hint'] ?? 'Privacy in the search engines? You can use a nickname:';
    $emailWarning =
        $content['email_warning'] ??
        'Attention, the email address you supply must be valid in order to validate the signature, otherwise it will be deleted.';

    $agree1Title =
        $content['agree1_title'] ??
        'I confirm registration and I agree to <a class="red" href="' .
            lroute('page.show', ['slug' => 'terms-of-service']) .
            '">Usage and Limitations of Services</a>';
    $agree2Title =
        $content['agree2_title'] ??
        'I confirm that I have read the <a class="red" href="' .
            lroute('page.show', ['slug' => 'privacy-policy']) .
            '">Privacy Policy</a>';
    $agree3Title =
        $content['agree3_title'] ??
        'I agree to the <a class="red" href="' .
            lroute('page.show', ['slug' => 'privacy-policy']) .
            '">Personal Data Processing</a>';
    $agreeYes = $content['agree_yes'] ?? 'I agree';
    $agreeNo = $content['agree_no'] ?? 'I do not agree';

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
