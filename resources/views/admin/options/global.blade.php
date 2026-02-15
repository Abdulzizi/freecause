@extends('admin.layouts.app')

@section('title', 'Global Options')

@section('content')
    <h1>options</h1>

    <form method="post" action="{{ route('admin.options.global.update') }}">
        @csrf

        <div class="fc-tab">general</div>
        <div class="fc-box">

            <div class="fc-row">
                <label>base url</label>
                <input class="fc-input" type="text" name="base_url" value="{{ $base_url }}">
            </div>

            @if($base_url)
                <div style="
                                                            background:#fff8e5;
                                                            border:1px solid #e6c26e;
                                                            padding:10px 12px;
                                                            margin:8px 0 12px 0;
                                                            font-size:12px;
                                                            line-height:1.5;
                                                        ">
                    <strong style="color:#b7791f;">⚠️ advanced configuration</strong><br>
                    changing the base url may break assets, routing, and internal links
                    unless the server is configured to serve the application under
                    this exact root path.<br><br>
                    this option is currently <strong>disabled at framework level</strong>.
                    review infrastructure before enabling.
                </div>
            @endif

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
                <a href="https://console.cloud.google.com/apis/credentials" target="_blank"
                    style="text-decoration:none; cursor:pointer;">
                    <label style="cursor:pointer; color:#1a0dab;">
                        google client id
                    </label>
                </a>
                <input class="fc-input" type="text" name="google_client_id" value="{{ $google_client_id }}">
            </div>

            <div class="fc-row">
                <label>google client secret</label>
                <input class="fc-input" type="text" name="google_client_secret" value="{{ $google_client_secret }}">
            </div>

            <div class="fc-row">
                <a href="https://developers.facebook.com/apps/" target="_blank"
                    style="text-decoration:none; cursor:pointer;">
                    <label style="cursor:pointer; color:#1a0dab;">
                        facebook app id
                    </label>
                </a>
                <input class="fc-input" type="text" name="facebook_app_id" value="{{ $facebook_app_id }}">
            </div>

            <div class="fc-row">
                <label>facebook secret</label>
                <input class="fc-input" type="text" name="facebook_secret" value="{{ $facebook_secret }}">
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
                    <option value="tls" {{ $smtp_encryption === 'tls' ? 'selected' : '' }}>
                        explicit tls (starttls – port 587)
                    </option>
                    <option value="ssl" {{ $smtp_encryption === 'ssl' ? 'selected' : '' }}>
                        implicit ssl (port 465)
                    </option>
                    <option value="" {{ $smtp_encryption === '' ? 'selected' : '' }}>
                        none (not recommended)
                    </option>
                </select>
            </div>
        </div>

        <div class="fc-tab">miscellaneous</div>
        <div class="fc-box">
            <div class="fc-row">
                <label>max featured petitions per country</label>
                <input class="fc-input" type="number" name="max_featured_petitions_per_country"
                    value="{{ $max_featured_petitions_per_country }}">
            </div>
        </div>

        <div class="fc-tab">maintenance</div>
        <div class="fc-box">
            <p style="font-size:12px; color:#666; margin-bottom:8px;">
                your ip: <strong style="color:#000;">{{ request()->ip() }}</strong>
            </p>
            <div class="fc-row">
                <label>special debug ip</label>
                <input class="fc-input" type="text" name="special_debug_ip" value="{{ $special_debug_ip }}">
            </div>
        </div>

        <div class="fc-tab">logging</div>
        <div class="fc-box">
            <input type="hidden" name="logging_enabled" value="0">

            <div class="fc-row">
                <label></label>
                <label style="font-weight:normal">
                    <input type="checkbox" name="logging_enabled" value="1" {{ $logging_enabled ? 'checked' : '' }}>
                    enable logging
                </label>
            </div>

            <div class="fc-row">
                <label>cookie name</label>
                <input class="fc-input" type="text" name="logging_cookie_name" value="{{ $logging_cookie_name }}">
            </div>

            <div class="fc-row">
                <label>cookie value</label>
                <input class="fc-input" type="text" name="logging_cookie_value" value="{{ $logging_cookie_value }}">
            </div>
        </div>

        <div style="margin-top:6px; text-align: end;">
            <button class="fc-btn" type="submit">save</button>
        </div>
    </form>
@endsection
