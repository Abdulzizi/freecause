@extends('admin.layouts.app')

@section('title', 'Global Options')

@section('content')
    <h1>options</h1>

    @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('admin.options.global.update') }}">
        @csrf

        <div class="fc-tab">general</div>
        <div class="fc-box">
            <div class="fc-row">
                <label>base url</label>
                <input class="fc-input" type="text" name="base_url" value="{{ $base_url }}">
            </div>
            <div class="fc-row">
                <label>short base url</label>
                <input class="fc-input" type="text" name="short_base_url" value="{{ $short_base_url }}">
            </div>
        </div>

        <div class="fc-tab">email</div>
        <div class="fc-box">
            <div class="fc-row">
                <label>to (staff)</label>
                <input class="fc-input" type="email" name="email_to_staff" value="{{ $email_to_staff }}">
            </div>
            <div class="fc-row">
                <label>from</label>
                <input class="fc-input" type="email" name="email_from" value="{{ $email_from }}">
            </div>
        </div>

        <div class="fc-tab">inject html</div>
        <div class="fc-box">
            <div class="fc-row">
                <label>in &lt;head&gt; ... &lt;/head&gt;</label>
                <textarea class="fc-textarea" name="inject_head_html">{{ $inject_head_html }}</textarea>
            </div>
            <div class="fc-row">
                <label>in &lt;body&gt; ... &lt;/body&gt;</label>
                <textarea class="fc-textarea" name="inject_body_html">{{ $inject_body_html }}</textarea>
            </div>
        </div>

        <div class="fc-tab">oauth</div>
        <div class="fc-box">
            <div class="fc-row">
                <label>facebook app id</label>
                <input class="fc-input" type="text" name="facebook_app_id" value="{{ $facebook_app_id }}">
            </div>
            <div class="fc-row">
                <label>facebook secret</label>
                <input class="fc-input" type="text" name="facebook_secret" value="{{ $facebook_secret }}">
            </div>
            <div class="fc-row">
                <label>google client id</label>
                <input class="fc-input" type="text" name="google_client_id" value="{{ $google_client_id }}">
            </div>
            <div class="fc-row">
                <label>google client secret</label>
                <input class="fc-input" type="text" name="google_client_secret" value="{{ $google_client_secret }}">
            </div>
        </div>

        <div class="fc-tab">smtp</div>
        <div class="fc-box">
            <input type="hidden" name="smtp_enabled" value="0">

            <div class="fc-row">
                <label></label>
                <label style="font-weight:normal">
                    <input type="checkbox" name="smtp_enabled" value="1" {{ $smtp_enabled ? 'checked' : '' }}>
                    enable smtp
                </label>
            </div>

            <div class="fc-row">
                <label>smtp host</label>
                <input class="fc-input" type="text" name="smtp_host" value="{{ $smtp_host }}">
            </div>
            <div class="fc-row">
                <label>smtp port</label>
                <input class="fc-input" type="number" name="smtp_port" value="{{ $smtp_port }}">
            </div>
            <div class="fc-row">
                <label>smtp user</label>
                <input class="fc-input" type="text" name="smtp_user" value="{{ $smtp_user }}">
            </div>
            <div class="fc-row">
                <label>smtp password</label>
                <input class="fc-input" type="password" name="smtp_pass" value="{{ $smtp_pass }}">
            </div>

            <div class="fc-row">
                <label>smtp encryption</label>
                <select class="fc-select" name="smtp_encryption">
                    <option value="tls" {{ $smtp_encryption === 'tls' ? 'selected' : '' }}>tls</option>
                    <option value="ssl" {{ $smtp_encryption === 'ssl' ? 'selected' : '' }}>ssl</option>
                    <option value="" {{ $smtp_encryption === '' ? 'selected' : '' }}>none</option>
                </select>
            </div>
        </div>

        <div style="margin-top:6px;">
            <button class="fc-btn" type="submit">save</button>
        </div>
    </form>
@endsection
