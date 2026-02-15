<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalOptionsController extends Controller
{
    public function edit()
    {
        return view('admin.options.global', [
            'base_url' => Settings::get('base_url', ''),
            'short_base_url' => Settings::get('short_base_url', ''),
            'email_to_staff' => Settings::get('email_to_staff', ''),
            'email_from' => Settings::get('email_from', ''),
            'inject_head_html' => Settings::get('inject_head_html', ''),
            'inject_body_html' => Settings::get('inject_body_html', ''),
            'facebook_app_id' => Settings::get('facebook_app_id', ''),
            'facebook_secret' => Settings::get('facebook_secret', ''),
            'google_client_id' => Settings::get('google_client_id', ''),
            'google_client_secret' => Settings::get('google_client_secret', ''),
            'smtp_enabled' => Settings::get('smtp_enabled', false),
            'smtp_host' => Settings::get('smtp_host', ''),
            'smtp_port' => Settings::get('smtp_port', 587),
            'smtp_user' => Settings::get('smtp_user', ''),
            'smtp_pass' => Settings::get('smtp_pass', ''),
            'smtp_encryption' => Settings::get('smtp_encryption', 'tls'),
            'max_featured_petitions_per_country' => Settings::get('max_featured_petitions_per_country', 10),
            'special_debug_ip' => Settings::get('special_debug_ip', ''),
            'logging_enabled' => Settings::get('logging_enabled', false),
            'logging_cookie_name' => Settings::get('logging_cookie_name', 'dothelog'),
            'logging_cookie_value' => Settings::get('logging_cookie_value', '1'),
        ]);
    }

    public function update(Request $request)
    {
        Settings::set('base_url', $request->base_url ?? '', 'string');
        Settings::set('short_base_url', $request->short_base_url ?? '', 'string');

        Settings::set('email_to_staff', $request->email_to_staff ?? '', 'string');
        Settings::set('email_from', $request->email_from ?? '', 'string');

        Settings::set('inject_head_html', $request->inject_head_html ?? '', 'text');
        Settings::set('inject_body_html', $request->inject_body_html ?? '', 'text');

        Settings::set('facebook_app_id', $request->facebook_app_id ?? '', 'string');
        Settings::set('facebook_secret', $request->facebook_secret ?? '', 'string');

        Settings::set('google_client_id', $request->google_client_id ?? '', 'string');
        Settings::set('google_client_secret', $request->google_client_secret ?? '', 'string');

        Settings::set('smtp_enabled', $request->boolean('smtp_enabled') ? '1' : '0', 'bool');
        Settings::set('smtp_host', $request->smtp_host ?? '', 'string');
        Settings::set('smtp_port', (string)($request->smtp_port ?? 587), 'int');
        Settings::set('smtp_user', $request->smtp_user ?? '', 'string');
        Settings::set('smtp_pass', $request->smtp_pass ?? '', 'string');
        Settings::set('smtp_encryption', $request->smtp_encryption ?? 'tls', 'string');

        Settings::set('max_featured_petitions_per_country', (string)($request->max_featured_petitions_per_country ?? 10), 'int');

        Settings::set('special_debug_ip', $request->special_debug_ip ?? '', 'string');

        Settings::set('logging_enabled', $request->boolean('logging_enabled') ? '1' : '0', 'bool');
        Settings::set('logging_cookie_name', $request->logging_cookie_name ?? 'dothelog', 'string');
        Settings::set('logging_cookie_value', $request->logging_cookie_value ?? '1', 'string');

        Cache::flush();

        return back()->with('success', 'saved');
    }
}
