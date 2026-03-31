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

            @if ($base_url)
                <div class="fc-row">
                    <label></label>
                    <span style="font-size:11px; color:#b7791f;">
                        active -- all urls forced to <strong>{{ $base_url }}</strong>. clear to disable.
                    </span>
                </div>
            @endif

            <div class="fc-row">
                <label>short base url</label>
                <input class="fc-input" type="text" name="short_base_url" value="{{ $short_base_url }}">
            </div>

            @if ($short_base_url)
                <div class="fc-row">
                    <label></label>
                    <span style="font-size:11px; color:#666;">
                        used for share links — e.g. <strong>{{ $short_base_url }}/en/petition/...</strong>
                    </span>
                </div>
            @endif

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

        <div class="fc-tab">inject html <span style="font-size:0.75em; color:#c00;">(system admin only)</span></div>
        <div class="fc-box">
            <p style="font-size:12px; color:#666; margin-bottom:8px;">
                These fields are rendered verbatim on every page — for analytics scripts, tracking pixels, etc.
                Only super-admin accounts can save changes here.
            </p>
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

        </div>

        <div class="fc-tab">smtp</div>
        <div class="fc-box">
            <input type="hidden" name="smtp_enabled" value="0">
            <input type="hidden" name="require_email_verification" value="0">

            <div class="fc-row">
                <label></label>
                <label style="font-weight:normal">
                    <input type="checkbox" name="smtp_enabled" value="1" {{ $smtp_enabled ? 'checked' : '' }}>
                    enable smtp
                </label>
            </div>

            <div class="fc-row">
                <label></label>
                <label style="font-weight:normal">
                    <input type="checkbox" name="require_email_verification" value="1" {{ $require_email_verification ? 'checked' : '' }}>
                    require email verification for new accounts
                    <span style="font-size:11px; color:#666;">(if smtp disabled, account is pending until manually verified)</span>
                </label>
            </div>

            <div class="fc-row">
                <label>smtp host</label>
                <input class="fc-input" type="text" name="smtp_host" value="{{ $smtp_host }}">
            </div>
            <div class="fc-row">
                <label>smtp user</label>
                <input class="fc-input" type="text" name="smtp_user" value="{{ $smtp_user }}">
            </div>
            <div class="fc-row">
                <label>smtp password</label>
                <div style="position:relative;">
                    <input id="smtp_pass_input" class="fc-input" type="password" name="smtp_pass"
                        value="{{ $smtp_pass }}" style="padding-right:36px;">

                    <i id="toggle_smtp_pass" class="fa fa-eye"
                        style="
                            position:absolute;
                            right:10px;
                            top:50%;
                            transform:translateY(-50%);
                            cursor:pointer;
                            color:#777;
                        ">
                    </i>
                </div>
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
                    <input type="checkbox" name="logging_enabled" value="1"
                        {{ $logging_enabled ? 'checked' : '' }}>
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

        <div class="fc-tab">announcement banner</div>
        <div class="fc-box">
            <p style="font-size:12px; color:#666; margin-bottom:8px;">
                Show a red banner at the top of every page for all visitors. Plain text only — no HTML.
            </p>
            <input type="hidden" name="announcement_active" value="0">
            <div class="fc-row">
                <label>active</label>
                <input type="checkbox" name="announcement_active" value="1" {{ $announcement_active ? 'checked' : '' }}>
            </div>
            <div class="fc-row">
                <label>message</label>
                <input class="fc-input" type="text" name="announcement_text" value="{{ $announcement_text }}"
                    placeholder="e.g. Site under maintenance from 14:00–15:00 UTC">
            </div>
        </div>

        <div class="fc-tab">verify account email</div>
        <div class="fc-box">
            <p style="font-size:12px; color:#666; margin-bottom:8px;">
                Customize the verification email sent to new users. Leave blank to use the default text.
            </p>
            <div class="fc-row">
                <label>subject</label>
                <input class="fc-input" type="text" name="email_verify_subject" value="{{ $email_verify_subject }}"
                    placeholder="Verify your account">
            </div>
            <div class="fc-row">
                <label>greeting / heading</label>
                <input class="fc-input" type="text" name="email_verify_greeting" value="{{ $email_verify_greeting }}"
                    placeholder="Welcome to FreeCause">
            </div>
            <div class="fc-row">
                <label>button text</label>
                <input class="fc-input" type="text" name="email_verify_button_text" value="{{ $email_verify_button_text }}"
                    placeholder="Verify My Account">
            </div>
            <div class="fc-row">
                <label>footer line</label>
                <input class="fc-input" type="text" name="email_verify_footer" value="{{ $email_verify_footer }}"
                    placeholder="FreeCause – Online Petition Platform">
            </div>
        </div>

        <div style="margin-top:6px; text-align: end;">
            <button class="fc-btn" type="submit">save</button>
        </div>
    </form>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('smtp_pass_input');
        var toggle = document.getElementById('toggle_smtp_pass');

        toggle.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        });
    });
</script>
