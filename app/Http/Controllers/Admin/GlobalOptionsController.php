<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalOptionsController extends Controller
{
    private array $defaults = [
        'base_url'                           => '',
        'short_base_url'                     => '',
        'email_to_staff'                     => '',
        'email_from'                         => '',
        'inject_head_html'                   => '',
        'inject_body_html'                   => '',
        'google_client_id'                   => '',
        'google_client_secret'               => '',
        'facebook_app_id'                    => '',
        'facebook_secret'                    => '',
        'smtp_enabled'                       => false,
        'smtp_host'                          => '',
        'smtp_user'                          => '',
        'smtp_pass'                          => '',
        'smtp_encryption'                    => 'tls',
        'max_featured_petitions_per_country' => 5,
        'special_debug_ip'                   => '',
        'logging_enabled'                    => false,
        'logging_cookie_name'                => '',
        'logging_cookie_value'               => '',
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

        foreach ($this->defaults as $key => $default) {
            try {
                if (is_bool($default)) {
                    $value = (bool) $request->input($key, 0);
                }
                elseif (is_int($default)) {
                    $value = (int) $request->input($key, $default);
                    if ($value <= 0) {
                        $value = $default;
                    }
                }
                else {
                    $value = $request->input($key, $default);
                }

                Settings::set($key, $value, 'global');
            } catch (\Throwable $e) {
                $errors[] = "Could not save [{$key}]: " . $e->getMessage();
            }
        }

        try {
            Cache::forget('default_language');
            Cache::forget('languages:codes');
        } catch (\Throwable) {
        }

        if (!empty($errors)) {
            return back()
                ->withInput()
                ->with('warning', 'Saved with some errors: ' . implode('; ', $errors));
        }

        return back()->with('success', 'Settings saved.');
    }
}
