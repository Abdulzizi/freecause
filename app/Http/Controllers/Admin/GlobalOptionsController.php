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

        return view('admin.options.global', $data);
    }

    public function update(Request $request)
    {
        $errors = [];

        // inject_head_html and inject_body_html can execute arbitrary JavaScript on
        // every page view. Restrict saving these to system-level admins only.
        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;

        foreach ($this->defaults as $key => $default) {
            if (in_array($key, ['inject_head_html', 'inject_body_html'], true) && ! $isSystemAdmin) {
                continue;
            }

            try {
                if (is_bool($default)) {
                    $value = (bool) $request->input($key, 0);
                } elseif (is_int($default)) {
                    $value = (int) $request->input($key, $default);
                    if ($value <= 0) {
                        $value = $default;
                    }
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
