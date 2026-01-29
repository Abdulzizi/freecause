@php
    $variant = $variant ?? 'stack'; // stack | split
    $col = $variant === 'split' ? 'col-md-6' : 'col-12';
    $useLabels = $variant === 'split';
@endphp

<form action="#" method="post" class="fc-sign-form fc-sign-form--{{ $variant }}">
    <div class="row g-2">

        {{-- Name --}}
        <div class="{{ $col }} mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Name (mandatory)</label>
                <input class="form-control" type="text">
            @else
                <input class="form-control" type="text" placeholder="Name (mandatory)">
            @endif
        </div>

        {{-- Surname --}}
        <div class="{{ $col }} mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Surname (mandatory)</label>
                <input class="form-control" type="text">
            @else
                <input class="form-control" type="text" placeholder="Surname (mandatory)">
            @endif
        </div>

        {{-- Email --}}
        <div class="{{ $col }} mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Email (mandatory)</label>
                <input class="form-control" type="email">
            @else
                <input class="form-control" type="email" placeholder="Email (mandatory)">
            @endif
        </div>

        {{-- Password --}}
        <div class="{{ $col }} mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Choose a password (mandatory)</label>
                <input class="form-control" type="password">
            @else
                <input class="form-control" type="password" placeholder="Choose a password (mandatory)">
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
                <input class="form-control" type="text">
            @else
                <input class="form-control" type="text" placeholder="City (optional)">
            @endif
        </div>

        {{-- Nickname --}}
        <div class="{{ $col }} mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Nickname (optional)</label>
                <input class="form-control" type="text">
            @else
                <input class="form-control" type="text" placeholder="Nickname (optional)">
            @endif
        </div>

        {{-- Comment --}}
        <div class="col-12 mb-3">
            @if($useLabels)
                <label class="form-label fc-mini-label">Comment</label>
                <input class="form-control" type="text" value="I support this petition">
            @else
                <input class="form-control" type="text" placeholder="Comment" value="I support this petition">
            @endif
        </div>

        <div class="col-12">
            {{-- keep this message only for split --}}
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
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree1" checked> I
                        agree</label>
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree1"> I do not
                        agree</label>
                </div>
            </div>

            <div class="mb-3">
                <div style="font-weight:700;">
                    I confirm that I have read the <a class="red" href="#">Privacy Policy</a>
                </div>
                <div class="mt-2">
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree2" checked> I
                        agree</label>
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree2"> I do not
                        agree</label>
                </div>
            </div>

            <div class="mb-3">
                <div style="font-weight:700;">
                    I agree to the <a class="red" href="#">Personal Data Processing</a>
                </div>
                <div class="mt-2">
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree3" checked> I
                        agree</label>
                    <label class="d-block" style="font-size:14px;"><input type="radio" name="agree3"> I do not
                        agree</label>
                </div>
            </div>

            <button class="btn btn-danger fc-sign-btn" type="button">Sign</button>
        </div>

    </div>
</form>
