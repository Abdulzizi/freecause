<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalOptionsController extends Controller
{
    private array $defaults = [
        'site_name' => 'FreeCause',
        'site_name_short' => 'FreeCause',
        'site_logo' => '',
        'site_favicon' => '',
        'base_url' => '',
        'short_base_url' => '',
        'email_to_staff' => '',
        'email_from' => '',
        'inject_head_html' => '',
        'inject_body_html' => '',
        'google_client_id' => '',
        'google_client_secret' => '',
        'smtp_enabled' => false,
        'require_email_verification' => false,
        'smtp_host' => '',
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_encryption' => 'tls',
        'max_featured_petitions_per_country' => 5,
        'special_debug_ip' => '',
        'logging_enabled' => false,
        'logging_cookie_name' => '',
        'logging_cookie_value' => '',
        'announcement_active' => false,
        'announcement_text' => '',

        // Email Templates
        'email_verify_subject' => '',
        'email_verify_greeting' => '',
        'email_verify_button_text' => '',
        'email_verify_footer' => '',
        'email_contact_subject' => 'New contact form submission',
        'email_contact_enabled' => true,
    ];

    public function edit()
    {
        $data = [];

        foreach ($this->defaults as $key => $default) {
            try {
                $data[$key] = Settings::get($key, $default, 'global');
            } catch (\Throwable $e) {
                $data[$key] = $default;
            }
        }

        $data['google_client_secret'] = '';
        $data['smtp_pass'] = '';
        $data['google_client_secret_set'] = trim((string) Settings::get('google_client_secret', '', 'global')) !== '';
        $data['smtp_pass_set'] = trim((string) Settings::get('smtp_pass', '', 'global')) !== '';

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;
        $data['is_system_admin'] = $isSystemAdmin;
        if (! $isSystemAdmin) {
            $data['inject_head_html'] = '';
            $data['inject_body_html'] = '';
        }

        return view('admin.options.global', $data);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'base_url' => ['nullable', 'url', 'max:255'],
            'short_base_url' => ['nullable', 'url', 'max:255'],
            'site_logo' => ['nullable', 'url', 'max:255'],
            'site_favicon' => ['nullable', 'url', 'max:255'],
            'email_to_staff' => ['nullable', 'email', 'max:190'],
            'email_from' => ['nullable', 'email', 'max:190'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'max_featured_petitions_per_country' => ['nullable', 'integer', 'min:1', 'max:100'],
            'special_debug_ip' => ['nullable', 'ip'],
        ]);

        $errors = [];

        $secretKeys = ['google_client_secret', 'smtp_pass'];

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;

        foreach ($this->defaults as $key => $default) {
            if (in_array($key, ['inject_head_html', 'inject_body_html'], true) && ! $isSystemAdmin) {
                continue;
            }

            if (in_array($key, $secretKeys, true)) {
                $new = (string) $request->input($key, '');
                if (trim($new) === '') {
                    continue;
                }
            }

            try {
                if (is_bool($default)) {
                    $value = (bool) $request->input($key, 0);
                } elseif (is_int($default)) {
                    $value = (int) $request->input($key, $default);
                    if ($value <= 0) {
                        $value = $default;
                    }
                } elseif (array_key_exists($key, $validated)) {
                    $value = $validated[$key] ?? $default;
                } else {
                    $value = $request->input($key, $default);
                }

                Settings::set($key, $value, 'global');
            } catch (\Throwable $e) {
                $errors[] = "Could not save [{$key}]: ".$e->getMessage();
            }
        }

        try {
            Cache::forget('default_language');
            Cache::forget('languages:codes');
        } catch (\Throwable) {
        }

        if (! empty($errors)) {
            return back()
                ->withInput()
                ->with('warning', 'Saved with some errors: '.implode('; ', $errors));
        }

        return back()->with('success', 'Settings saved.');
    }

    public function clearCache(Request $request)
    {
        try {
            Cache::flush();

            return back()->with('success', 'All cache cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cache clear failed: '.$e->getMessage());
        }
    }
}
