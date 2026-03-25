@extends('layouts.legacy')

@section('title', 'Account - FreeCause')

@section('content')
    @php $u = auth()->user(); @endphp

    <section class="py-4">
        <div class="container">
            <div class="row g-4">

                <div class="col-lg-4">
                    <div class="fc-card p-4">

                        <h2 class="fc-h2 mb-3">My Data</h2>

                        <div class="fc-kv">
                            <div class="fc-k">Email:</div>
                            <div class="fc-v">{{ $u->email }}</div>
                        </div>

                        <div class="fc-kv">
                            <div class="fc-k">Registration date:</div>
                            <div class="fc-v">
                                {{ optional($u->created_at)->format('d M y H:i') }}
                            </div>
                        </div>

                        <div class="mt-3">
                            <a class="fc-link-red" href="{{ lroute('account.petitions') }}">» My Petitions</a>
                        </div>

                        <div class="fc-dots my-3"></div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="fc-social fb">Google</div>

                                @if ($u->google_id)
                                    <form method="POST" action="{{ lroute('account.unlink.google') }}">
                                        @csrf
                                        <button type="submit" class="fc-toggle on">
                                            <span class="knob"></span>
                                            <span class="label">ON</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ lroute('oauth.google') }}" class="fc-toggle">
                                        <span class="knob"></span>
                                        <span class="label">OFF</span>
                                    </a>
                                @endif
                            </div>

                            <div class="mt-3 fc-small text-muted">
                                {{ $u->display_name }} ( <strong>{{ $u->email }}</strong> )
                            </div>

                            <div class="fc-dots my-3"></div>
                        </div>

                        <h3 class="fc-h3 mb-2">Account Deletion</h3>

                        <form method="POST" action="{{ lroute('account.delete') }}">
                            @csrf

                            <label class="d-flex align-items-center gap-2 mb-3" style="font-size:14px;">
                                <input type="checkbox" name="confirm_delete" value="1" required>
                                <span>I agree with the account permanent deletion</span>
                            </label>

                            <button type="submit" class="btn fc-btn-danger px-4"
                                onclick="this.disabled=true;this.form.submit();">
                                Delete <span class="ms-3">»</span>
                            </button>
                        </form>

                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="fc-card p-4">
                        <h1 class="fc-title text-center mb-4">Profile</h1>

                        @if ($errors->any())
                            <div class="fc-error mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="fc-success mb-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ lroute('account.profile.update') }}">
                            @csrf

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">First Name</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="first_name"
                                        value="{{ old('first_name', $u->first_name) }}">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">Last Name</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="last_name"
                                        value="{{ old('last_name', $u->last_name) }}">
                                </div>
                            </div>

                            @php $idMode = old('identify_mode', $u->identify_mode ?? 'full'); @endphp

                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label">Identify me as</label>
                                <div class="col-sm-9 d-flex gap-4">
                                    <label><input type="radio" name="identify_mode" value="full"
                                            {{ $idMode === 'full' ? 'checked' : '' }}> Full Name</label>
                                    <label><input type="radio" name="identify_mode" value="name"
                                            {{ $idMode === 'name' ? 'checked' : '' }}> First Name</label>
                                    <label><input type="radio" name="identify_mode" value="nick"
                                            {{ $idMode === 'nick' ? 'checked' : '' }}> Nickname</label>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">Nickname</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="nickname"
                                        value="{{ old('nickname', $u->nickname) }}">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">City</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="city"
                                        value="{{ old('city', $u->city) }}">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-2"><strong>Change Email</strong></div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input class="form-control fc-input" name="new_email" placeholder="New email"
                                        value="{{ old('new_email') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-9 offset-sm-3">
                                    <input class="form-control fc-input" name="new_email_confirmation"
                                        placeholder="Confirm new email" value="{{ old('new_email_confirmation') }}">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-2"><strong>Change Password</strong></div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control fc-input" name="current_password"
                                        placeholder="Current password">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control fc-input" name="new_password"
                                        placeholder="New password">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-9 offset-sm-3">
                                    <input type="password" class="form-control fc-input" name="new_password_confirmation"
                                        placeholder="Confirm new password">
                                </div>
                            </div>

                            <div class="text-center">
                                <button class="btn fc-btn-edit px-5" onclick="this.disabled=true;this.form.submit();">
                                    Save Changes <span class="ms-3">»</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
