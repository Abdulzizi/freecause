@extends('admin.layouts.app')

@section('title', 'Site Settings')

@section('content')
    <h1>Site Settings</h1>

    <form method="post" action="{{ route('admin.options.global.update') }}">
        @csrf

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">general</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>base url</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="base_url" value="{{ $base_url }}">
            </div>

            @if ($base_url)
                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label></label>
                    <span class="text-[11px]! text-[#b7791f]!">
                        Active: all urls forced to <strong>{{ $base_url }}</strong>. Clear to disable.
                    </span>
                </div>
            @endif

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>short base url</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="short_base_url" value="{{ $short_base_url }}">
            </div>

            @if ($short_base_url)
                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label></label>
                    <span class="text-[11px]! text-[#666]!">
                        Used for share links, e.g. <strong>{{ $short_base_url }}/en/petition/...</strong>
                    </span>
                </div>
            @endif

        </div>
        @endif

        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">email</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>to (staff)</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="email" name="email_to_staff" value="{{ $email_to_staff }}">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>from</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="email" name="email_from" value="{{ $email_from }}">
            </div>
        </div>

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">inject html <span class="text-[0.75em]! text-[#c00]!">(system admin only)</span></div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <p class="text-xs! text-[#666]! mb-2!">
                These fields are rendered verbatim on every page, for analytics scripts, tracking pixels, etc.
                Only super-admin accounts can save changes here.
            </p>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>in &lt;head&gt; ... &lt;/head&gt;</label>
                <textarea class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]!" name="inject_head_html">{{ $inject_head_html }}</textarea>
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>in &lt;body&gt; ... &lt;/body&gt;</label>
                <textarea class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]!" name="inject_body_html">{{ $inject_body_html }}</textarea>
            </div>
        </div>
        @endif

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">oauth</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <a href="https://console.cloud.google.com/apis/credentials" target="_blank"
                    class="no-underline! cursor-pointer!">
                    <label class="cursor-pointer! text-[#1a0dab]!">
                        google client id
                    </label>
                </a>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="google_client_id" value="{{ $google_client_id }}">
            </div>

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>google client secret</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="password" name="google_client_secret" value=""
                    autocomplete="new-password"
                    placeholder="{{ $google_client_secret_set ? '•••••••• (leave blank to keep)' : 'not set' }}">
            </div>

        </div>
        @endif

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">smtp</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <input type="hidden" name="smtp_enabled" value="0">
            <input type="hidden" name="require_email_verification" value="0">

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label></label>
                <label class="font-normal!">
                    <input type="checkbox" name="smtp_enabled" value="1" {{ $smtp_enabled ? 'checked' : '' }}>
                    enable smtp
                </label>
            </div>

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label></label>
                <label class="font-normal!">
                    <input type="checkbox" name="require_email_verification" value="1" {{ $require_email_verification ? 'checked' : '' }}>
                    require email verification for new accounts
                    <span class="text-[11px]! text-[#666]!">(if smtp disabled, account is pending until manually verified)</span>
                </label>
            </div>

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>smtp host</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="smtp_host" value="{{ $smtp_host }}">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>smtp user</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="smtp_user" value="{{ $smtp_user }}">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>smtp password</label>
                <div class="relative!">
                    <input id="smtp_pass_input" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! pr-9!" type="password" name="smtp_pass"
                        value="" autocomplete="new-password"
                        placeholder="{{ $smtp_pass_set ? '•••••••• (leave blank to keep)' : 'not set' }}">

                    <svg id="toggle_smtp_pass" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        class="absolute! right-2.5! top-1/2! -translate-y-1/2! cursor-pointer! text-[#777]!">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>smtp encryption</label>
                <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="smtp_encryption">
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
        @endif

        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">miscellaneous</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>max featured petitions per country</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="number" name="max_featured_petitions_per_country"
                    value="{{ $max_featured_petitions_per_country }}">
            </div>
        </div>

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">maintenance</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <p class="text-xs! text-[#666]! mb-2!">
                your ip: <strong class="text-black!">{{ request()->ip() }}</strong>
            </p>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>special debug ip</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="special_debug_ip" value="{{ $special_debug_ip }}">
            </div>
        </div>
        @endif

        @if ($is_system_admin)
        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">logging</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <input type="hidden" name="logging_enabled" value="0">

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label></label>
                <label class="font-normal!">
                    <input type="checkbox" name="logging_enabled" value="1"
                        {{ $logging_enabled ? 'checked' : '' }}>
                    enable logging
                </label>
            </div>

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>cookie name</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="logging_cookie_name" value="{{ $logging_cookie_name }}">
            </div>

            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>cookie value</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="logging_cookie_value" value="{{ $logging_cookie_value }}">
            </div>
        </div>
        @endif

        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">announcement banner</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <p class="text-xs! text-[#666]! mb-2!">
                Show a red banner at the top of every page for all visitors. Plain text only, no HTML.
            </p>
            <input type="hidden" name="announcement_active" value="0">
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>active</label>
                <input type="checkbox" name="announcement_active" value="1" {{ $announcement_active ? 'checked' : '' }}>
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>message</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="announcement_text" value="{{ $announcement_text }}"
                    placeholder="e.g. Site under maintenance from 14:00–15:00 UTC">
            </div>
        </div>

        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">verify account email</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <p class="text-xs! text-[#666]! mb-2!">
                Customize the verification email sent to new users. Leave blank to use the default text.
            </p>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>subject</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email_verify_subject" value="{{ $email_verify_subject }}"
                    placeholder="Verify your account">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>greeting / heading</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email_verify_greeting" value="{{ $email_verify_greeting }}"
                    placeholder="Welcome to FreeCause">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>button text</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email_verify_button_text" value="{{ $email_verify_button_text }}"
                    placeholder="Verify My Account">
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>footer line</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email_verify_footer" value="{{ $email_verify_footer }}"
                    placeholder="FreeCause – Online Petition Platform">
            </div>
        </div>

        <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">contact email</div>
        <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
            <p class="text-xs! text-[#666]! mb-2!">
                Customize the contact form notification email sent to staff.
            </p>
            <input type="hidden" name="email_contact_enabled" value="0">
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label></label>
                <label class="font-normal!">
                    <input type="checkbox" name="email_contact_enabled" value="1" {{ $email_contact_enabled ? 'checked' : '' }}>
                    enable contact form notifications
                </label>
            </div>
            <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                <label>subject</label>
                <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email_contact_subject" value="{{ $email_contact_subject }}"
                    placeholder="New contact form submission">
            </div>
        </div>

        <div class="mt-1.5! text-end!">
            <button class="border! border-solid! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!" type="submit">save</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('smtp_pass_input');
        var toggle = document.getElementById('toggle_smtp_pass');

        if (! input || ! toggle) {
            return;
        }

        toggle.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                toggle.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" fill="none"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>';
            } else {
                input.type = 'password';
                toggle.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>';
            }
        });
    });
</script>
@endpush
