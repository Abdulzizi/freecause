@extends('layouts.legacy')

@section('title', 'Account - FreeCause')

@section('content')
    @php
        $u = auth()->user();
    @endphp

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

                        {{-- <div class="mt-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="fc-social fb">Facebook</div>
                                <div class="fc-toggle">
                                    <span class="knob"></span>
                                    <span class="label">OFF</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="fc-social gg">Google</div>
                                <div class="fc-toggle on">
                                    <span class="knob"></span>
                                    <span class="label">ON</span>
                                </div>
                            </div>

                            <div class="mt-3 fc-small text-muted">
                                {{ $u->name }} ( <strong>{{ $u->email }}</strong> )
                            </div>

                            <div class="fc-dots my-3"></div>
                        </div> --}}
                        <div class="fc-dots my-3"></div>

                        <h3 class="fc-h3 mb-2">Account Deletion</h3>

                        <form method="POST" action="{{ lroute('account.delete') }}">
                            @csrf

                            <label class="d-flex align-items-center gap-2 mb-3" style="font-size:14px;">
                                <input type="checkbox" name="confirm_delete" value="1">
                                <span>I agree with the account permanent deletion</span>
                            </label>

                            <button type="submit" class="btn fc-btn-danger px-4">
                                Delete <span class="ms-3">»</span>
                            </button>
                        </form>

                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="fc-card p-4">
                        <h1 class="fc-title text-center mb-4">Profile</h1>

                        <form method="POST" action="{{ lroute('account.profile.update') }}">
                            @csrf

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">Name</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="first_name"
                                        value="{{ old('first_name', $u->first_name ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">Surname</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="last_name"
                                        value="{{ old('last_name', $u->last_name ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">Soprannome</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="nickname"
                                        value="{{ old('nickname', $u->nickname ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label">In this website, identify me with :</label>
                                <div class="col-sm-9">
                                    @php
                                        $idMode = old('identify_mode', $u->identify_mode ?? 'full'); // full|name|nick
                                    @endphp

                                    <div class="d-flex flex-wrap gap-4" style="font-size:14px;">
                                        <label class="d-flex align-items-center gap-2">
                                            <input type="radio" name="identify_mode" value="full" {{ $idMode === 'full' ? 'checked' : '' }}>
                                            <span>name and surname</span>
                                        </label>

                                        <label class="d-flex align-items-center gap-2">
                                            <input type="radio" name="identify_mode" value="name" {{ $idMode === 'name' ? 'checked' : '' }}>
                                            <span>only the name</span>
                                        </label>

                                        <label class="d-flex align-items-center gap-2">
                                            <input type="radio" name="identify_mode" value="nick" {{ $idMode === 'nick' ? 'checked' : '' }}>
                                            <span>nickname</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">City</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="city"
                                        value="{{ old('city', $u->city ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="fc-section-note">
                                        If you would like to change the email address, fill the following fields :
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">New email</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="new_email" value="{{ old('new_email') }}">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">Verify email</label>
                                <div class="col-sm-9">
                                    <input class="form-control fc-input" name="new_email_confirmation"
                                        value="{{ old('new_email_confirmation') }}">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="fc-section-note">
                                        If you would like to change the password, fill the following fields :
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label">New password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control fc-input" name="new_password">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label">Verify password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control fc-input" name="new_password_confirmation">
                                </div>
                            </div>

                            <div class="text-center">
                                <button class="btn fc-btn-edit px-5">
                                    Edit <span class="ms-3">»</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
