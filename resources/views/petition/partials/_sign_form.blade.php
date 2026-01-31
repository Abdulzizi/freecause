@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'col-md-6' : 'col-12';
    $useLabels = $variant === 'split';
    $isAuthed = auth()->check();
@endphp

@if($isAuthed)
    @if(!empty($hasSigned) && $hasSigned)
        <p class="mb-0" style="font-size:14px;">
            You signed this petition.
        </p>
    @else
        <p class="mb-3" style="font-size:14px;">
            Support and share your cause. Please click "like" button and sign the petition
        </p>

        <a class="btn btn-danger fc-sign-btn"
            href="{{ route('petition.sign.page', ['locale' => $locale, 'slug' => $petition->slug, 'id' => $petition->id]) }}">
            Sign <span style="margin-left:10px;">»</span>
        </a>

    @endif

@else
    <form action="{{ route('petition.sign', ['locale' => $locale, 'slug' => $petition->slug, 'id' => $petition->id]) }}"
        method="POST" class="fc-sign-form fc-sign-form--{{ $variant }}">
        @csrf

        <div class="row g-2">

            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach

                            @if (session('login_url'))
                                <li><a class="red" href="{{ session('login_url') }}">go to login</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Name --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Name (mandatory)</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name') }}">
                @else
                    <input class="form-control" type="text" name="name" value="{{ old('name') }}"
                        placeholder="Name (mandatory)">
                @endif
            </div>

            {{-- Surname --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Surname (mandatory)</label>
                    <input class="form-control" type="text" name="surname" value="{{ old('surname') }}">
                @else
                    <input class="form-control" type="text" name="surname" value="{{ old('surname') }}"
                        placeholder="Surname (mandatory)">
                @endif
            </div>

            {{-- Email --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Email (mandatory)</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                @else
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                        placeholder="Email (mandatory)">
                @endif
            </div>

            {{-- Password --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Choose a password (mandatory)</label>
                    <input class="form-control" type="password" name="password">
                @else
                    <input class="form-control" type="password" name="password" placeholder="Choose a password (mandatory)">
                @endif
            </div>

            {{-- Privacy hint ONLY for split --}}
            @if($variant === 'split')
                <div class="col-12" style="font-size:14px;">
                    Privacy in the search engines? You can use a nickname:
                </div>
            @endif

            {{-- City --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">City (optional)</label>
                    <input class="form-control" type="text" name="city" value="{{ old('city') }}">
                @else
                    <input class="form-control" type="text" name="city" value="{{ old('city') }}" placeholder="City (optional)">
                @endif
            </div>

            {{-- Nickname --}}
            <div class="{{ $col }} mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Nickname (optional)</label>
                    <input class="form-control" type="text" name="nickname" value="{{ old('nickname') }}">
                @else
                    <input class="form-control" type="text" name="nickname" value="{{ old('nickname') }}"
                        placeholder="Nickname (optional)">
                @endif
            </div>

            {{-- Comment --}}
            <div class="col-12 mb-3">
                @if($useLabels)
                    <label class="form-label fc-mini-label">Comment</label>
                    <input class="form-control" type="text" name="comment"
                        value="{{ old('comment', 'I support this petition') }}">
                @else
                    <input class="form-control" type="text" name="comment"
                        value="{{ old('comment', 'I support this petition') }}" placeholder="Comment">
                @endif
            </div>

            <div class="col-12">
                @if ($variant === 'split')
                    <p class="mt-2 mb-3" style="font-size:13px;">
                        Attention, the email address you supply must be valid in order to validate the signature, otherwise it
                        will be deleted.
                    </p>
                @endif

                {{-- agreements --}}
                <div class="mb-3">
                    <div style="font-weight:700;">
                        I confirm registration and I agree to <a class="red" href="#">Usage and Limitations of Services</a>
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="agree" {{ old('agree1', 'agree') === 'agree' ? 'checked' : '' }}>
                            I agree
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree1" value="no" {{ old('agree1') === 'no' ? 'checked' : '' }}>
                            I do not agree
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        I confirm that I have read the <a class="red" href="#">Privacy Policy</a>
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="agree" {{ old('agree2', 'agree') === 'agree' ? 'checked' : '' }}>
                            I agree
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree2" value="no" {{ old('agree2') === 'no' ? 'checked' : '' }}>
                            I do not agree
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-weight:700;">
                        I agree to the <a class="red" href="#">Personal Data Processing</a>
                    </div>
                    <div class="mt-2">
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="agree" {{ old('agree3', 'agree') === 'agree' ? 'checked' : '' }}>
                            I agree
                        </label>
                        <label class="d-block" style="font-size:14px;">
                            <input type="radio" name="agree3" value="no" {{ old('agree3') === 'no' ? 'checked' : '' }}>
                            I do not agree
                        </label>
                    </div>
                </div>

                <button class="btn btn-danger fc-sign-btn" type="submit">Sign</button>
            </div>

        </div>
    </form>
@endif
