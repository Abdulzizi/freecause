{{-- expects: $locale, $redirect --}}
<div class="bg-white rounded-3 shadow-sm p-4" style="border:1px solid #eee;">
    @include('auth.partials._social_box', ['redirect' => $redirect])

    <div class="mb-3">
        <div class="fw-semibold" style="font-size:22px;">Register</div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ lroute('register.post') }}">
        @csrf
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="mb-3">
            <label class="form-label mb-1">Name (mandatory)</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">Surname (mandatory)</label>
            <input type="text" name="surname" value="{{ old('surname') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">Email (mandatory)</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">Choose a password (mandatory)</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3" style="font-size:14px;">
            Privacy in the search engines? You can use a nickname:
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">Nickname (optional)</label>
            <input type="text" name="nickname" value="{{ old('nickname') }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label mb-1">City (optional)</label>
            <input type="text" name="city" value="{{ old('city') }}" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" {{ old('agree_terms') ? 'checked' : '' }}>
            <label class="form-check-label" for="agree_terms">
                <span class="red">I accept the Terms and Conditions of Use</span>
            </label>
        </div>

        <button class="btn btn-danger px-4" style="border-radius:6px;">Submit</button>
    </form>
</div>
