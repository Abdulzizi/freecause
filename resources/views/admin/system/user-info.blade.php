@extends('admin.layouts.app')

@section('title', 'User Info')

@section('content')

    <h1>user info</h1>

    <form method="post" action="{{ route('admin.system.user_info.update') }}" onsubmit="return confirm('Save changes?')">
        @csrf

        <div class="fc-tab">access data</div>
        <div class="fc-box" style="margin-bottom:20px;">
            <div class="fc-row">
                <label>username</label>
                <div>{{ $admin->name }}</div>
            </div>

            <div class="fc-row">
                <label>current password</label>
                <input type="password" name="current_password" class="fc-input">
                {{-- @error('current_password')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>

            <div class="fc-row">
                <label>new password</label>
                <input type="password" name="new_password" class="fc-input">
                {{-- @error('new_password')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>

            <div class="fc-row">
                <label>repeat new password</label>
                <input type="password" name="new_password_confirmation" class="fc-input">
            </div>
        </div>


        <div class="fc-tab">misc</div>
        <div class="fc-box">

            <div class="fc-row">
                <label>email</label>
                <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="fc-input">
                {{-- @error('email')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>
        </div>

        <div style="margin-top:20px;">
            <button class="fc-btn">
                save
            </button>
        </div>

    </form>

@endsection
