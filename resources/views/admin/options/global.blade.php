@extends('admin.layouts.app')

@section('title', 'Site Settings')

@section('content')
    <x-admin.page-header title="Site Settings" />

    <form method="post" action="{{ route('admin.options.global.update') }}">
        @csrf

        @if ($is_system_admin)
        <x-admin.section-panel title="general">

            <x-admin.form-field label="base url">
                <x-ui.input name="base_url" :value="$base_url" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            @if ($base_url)
                <x-admin.form-field label=""><span class="text-[11px]! text-[#b7791f]!">
                        Active: all urls forced to <strong>{{ $base_url }}</strong>. Clear to disable.
                    </span></x-admin.form-field>
            @endif

            <x-admin.form-field label="short base url">
                <x-ui.input name="short_base_url" :value="$short_base_url" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            @if ($short_base_url)
                <x-admin.form-field label=""><span class="text-[11px]! text-muted!">
                        Used for share links, e.g. <strong>{{ $short_base_url }}/en/petition/...</strong>
                    </span></x-admin.form-field>
            @endif

        </x-admin.section-panel>
        @endif

        <x-admin.section-panel title="email">
            <x-admin.form-field label="to (staff)">
                <x-ui.input name="email_to_staff" type="email" :value="$email_to_staff" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="from">
                <x-ui.input name="email_from" type="email" :value="$email_from" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        @if ($is_system_admin)
        <x-admin.section-panel title="inject html (system admin only)">
            <p class="text-xs! text-muted! mb-2!">
                These fields are rendered verbatim on every page, for analytics scripts, tracking pixels, etc.
                Only super-admin accounts can save changes here.
            </p>
            <x-admin.form-field label="in <head> ... </head>">
                <textarea class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]!" name="inject_head_html">{{ $inject_head_html }}</textarea>
            </x-admin.form-field>

            <x-admin.form-field label="in <body> ... </body>">
                <textarea class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]!" name="inject_body_html">{{ $inject_body_html }}</textarea>
            </x-admin.form-field>
        </x-admin.section-panel>
        @endif

        @if ($is_system_admin)
        <x-admin.section-panel title="oauth">

            <x-admin.form-field label="google client id">
                <input class="fc-filter-field" style="width: 100%;" type="text" name="google_client_id" value="{{ $google_client_id }}" aria-label="google client id">
            </x-admin.form-field>
            <p class="text-[11px]! text-muted! mt-1! mb-0!">get it at <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener" class="no-underline! text-brand!">console.cloud.google.com — credentials</a></p>

            <x-admin.form-field label="google client secret">
                <x-ui.input name="google_client_secret" type="password" value="" :placeholder="$google_client_secret_set ? '•••••••• (leave blank to keep)' : 'not set'" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" autocomplete="new-password" />
            </x-admin.form-field>

        </x-admin.section-panel>
        @endif

        @if ($is_system_admin)
        <x-admin.section-panel title="smtp">
            <input type="hidden" name="smtp_enabled" value="0">
            <input type="hidden" name="require_email_verification" value="0">

            <x-admin.form-field label=""><label class="font-normal! flex! items-center! gap-2!">
                    <span class="fc-switch">
                        <input type="checkbox" name="smtp_enabled" value="1" {{ $smtp_enabled ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                    enable smtp
                </label></x-admin.form-field>

            <x-admin.form-field label=""><label class="font-normal! flex! items-center! gap-2!">
                    <span class="fc-switch">
                        <input type="checkbox" name="require_email_verification" value="1" {{ $require_email_verification ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                    require email verification for new accounts
                    <span class="text-[11px]! text-muted!">(if smtp disabled, account is pending until manually verified)</span>
                </label></x-admin.form-field>

            <x-admin.form-field label="smtp host">
                <x-ui.input name="smtp_host" :value="$smtp_host" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="smtp user">
                <x-ui.input name="smtp_user" :value="$smtp_user" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="smtp password">
                <div class="relative!">
                    <x-ui.input name="smtp_pass" type="password" value="" id="smtp_pass_input" :placeholder="$smtp_pass_set ? '•••••••• (leave blank to keep)' : 'not set'" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white! pr-9!" autocomplete="new-password" />

                    <svg id="toggle_smtp_pass" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        class="absolute! right-2.5! top-1/2! -translate-y-1/2! cursor-pointer! text-muted!">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
            </x-admin.form-field>
            <x-admin.form-field label="smtp encryption">
                <select class="fc-filter-field" name="smtp_encryption">
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
            </x-admin.form-field>
        </x-admin.section-panel>
        @endif

        <x-admin.section-panel title="miscellaneous">
            <x-admin.form-field label="max featured petitions per country">
                <x-ui.input name="max_featured_petitions_per_country" type="number" :value="$max_featured_petitions_per_country" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        @if ($is_system_admin)
        <x-admin.section-panel title="maintenance">
            <p class="text-xs! text-muted! mb-2!">
                your ip: <strong class="text-black!">{{ request()->ip() }}</strong>
            </p>
            <x-admin.form-field label="special debug ip">
                <x-ui.input name="special_debug_ip" :value="$special_debug_ip" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>
        @endif

        @if ($is_system_admin)
        <x-admin.section-panel title="logging">
            <input type="hidden" name="logging_enabled" value="0">

            <x-admin.form-field label=""><label class="font-normal! flex! items-center! gap-2!">
                    <span class="fc-switch">
                        <input type="checkbox" name="logging_enabled" value="1"
                            {{ $logging_enabled ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                    enable logging
                </label></x-admin.form-field>

            <x-admin.form-field label="cookie name">
                <x-ui.input name="logging_cookie_name" :value="$logging_cookie_name" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="cookie value">
                <x-ui.input name="logging_cookie_value" :value="$logging_cookie_value" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>
        @endif

        <x-admin.section-panel title="announcement banner">
            <p class="text-xs! text-muted! mb-2!">
                Show a red banner at the top of every page for all visitors. Plain text only, no HTML.
            </p>
            <input type="hidden" name="announcement_active" value="0">
            <x-admin.form-field label="active" for="announcement-active">
                <span class="fc-switch">
                    <input id="announcement-active" type="checkbox" name="announcement_active" value="1" {{ $announcement_active ? 'checked' : '' }}>
                    <span class="fc-switch__track" aria-hidden="true"></span>
                </span>
            </x-admin.form-field>
            <x-admin.form-field label="message">
                <x-ui.input name="announcement_text" :value="$announcement_text" placeholder="e.g. Site under maintenance from 14:00–15:00 UTC" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        <x-admin.section-panel title="verify account email">
            <p class="text-xs! text-muted! mb-2!">
                Customize the verification email sent to new users. Leave blank to use the default text.
            </p>
            <x-admin.form-field label="subject">
                <x-ui.input name="email_verify_subject" :value="$email_verify_subject" placeholder="Verify your account" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="greeting / heading">
                <x-ui.input name="email_verify_greeting" :value="$email_verify_greeting" placeholder="Welcome to FreeCause" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="button text">
                <x-ui.input name="email_verify_button_text" :value="$email_verify_button_text" placeholder="Verify My Account" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
            <x-admin.form-field label="footer line">
                <x-ui.input name="email_verify_footer" :value="$email_verify_footer" placeholder="FreeCause – Online Petition Platform" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        <x-admin.section-panel title="contact email">
            <p class="text-xs! text-muted! mb-2!">
                Customize the contact form notification email sent to staff.
            </p>
            <input type="hidden" name="email_contact_enabled" value="0">
            <x-admin.form-field label=""><label class="font-normal! flex! items-center! gap-2!">
                    <span class="fc-switch">
                        <input type="checkbox" name="email_contact_enabled" value="1" {{ $email_contact_enabled ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                    enable contact form notifications
                </label></x-admin.form-field>
            <x-admin.form-field label="subject">
                <x-ui.input name="email_contact_subject" :value="$email_contact_subject" placeholder="New contact form submission" class="w-full! box-border! border! border-solid! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        <div class="mt-1.5! text-end!">
            <button type="submit" class="fc-abtn">save</button>
        </div>
    </form>
@endsection
