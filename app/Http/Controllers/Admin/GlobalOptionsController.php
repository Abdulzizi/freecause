<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GlobalOptionsUpdateRequest;
use App\Models\Language;
use App\Models\Permission;
use App\Models\UserLevel;
use App\Support\Ads;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalOptionsController extends Controller
{
    private array $defaults = [
        'base_url' => '',
        'short_base_url' => '',
        'email_to_staff' => '',
        'email_from' => '',
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
        'logging_enabled' => false,
        'logging_cookie_name' => '',
        'logging_cookie_value' => '',
        'announcement_active' => false,
        'announcement_text' => '',

        'email_verify_subject' => '',
        'email_verify_greeting' => '',
        'email_verify_button_text' => '',
        'email_verify_footer' => '',
        'email_contact_subject' => 'New contact form submission',
        'email_contact_enabled' => true,

        'turnstile_enabled' => false,
        'turnstile_site_key' => '',
        'turnstile_secret' => '',

        // Adsterra banner config (admin-overridable; config/ads.php is the fallback)
        'ads_enabled' => false,
        'ads_host' => '',
        'ads_slot_leaderboard_key' => '',
        'ads_slot_rectangle_key' => '',
        'ads_slot_skyscraper_key' => '',
        'ads_slot_banner468_key' => '',
        'ads_slot_native_key' => '',
        'ads_slot_mobile_key' => '',
    ];

    private array $systemOnlyKeys = [
        'base_url',
        'short_base_url',
        'inject_body_html',
        'google_client_id',
        'google_client_secret',
        'smtp_enabled',
        'require_email_verification',
        'smtp_host',
        'smtp_user',
        'smtp_pass',
        'smtp_encryption',
        'logging_enabled',
        'logging_cookie_name',
        'logging_cookie_value',
        'turnstile_enabled',
        'turnstile_site_key',
        'turnstile_secret',

        'ads_enabled',
        'ads_host',
        'ads_slot_leaderboard_key',
        'ads_slot_rectangle_key',
        'ads_slot_skyscraper_key',
        'ads_slot_banner468_key',
        'ads_slot_native_key',
        'ads_slot_mobile_key',
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
        $data['turnstile_secret'] = '';

        // ads_enabled toggle must reflect the RESOLVED state (config default when
        // no Setting row exists), else an unsaved page would show it off while
        // ads are live from config.
        $data['ads_enabled'] = Ads::enabled();
        $data['google_client_secret_set'] = trim((string) Settings::get('google_client_secret', '', 'global')) !== '';
        $data['smtp_pass_set'] = trim((string) Settings::get('smtp_pass', '', 'global')) !== '';
        $data['turnstile_secret_set'] = trim((string) Settings::get('turnstile_secret', '', 'global')) !== '';

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;
        $data['is_system_admin'] = $isSystemAdmin;
        if (! $isSystemAdmin) {
            foreach ($this->systemOnlyKeys as $key) {
                if (array_key_exists($key, $data)) {
                    $data[$key] = is_bool($this->defaults[$key]) ? false : '';
                }
            }
        }

        return view('admin.options.global', $data);
    }

    public function update(GlobalOptionsUpdateRequest $request)
    {
        $validated = $request->validated();

        $errors = [];

        $secretKeys = ['google_client_secret', 'smtp_pass', 'turnstile_secret'];

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;

        foreach ($this->defaults as $key => $default) {
            if (in_array($key, $this->systemOnlyKeys, true) && ! $isSystemAdmin) {
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
        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;
        if (! $isSystemAdmin) {
            abort(403);
        }

        try {

            $flatKeys = [
                'settings:all',
                'default_language',
                'languages:codes',
                'active_languages',
                'active_languages_full',

                'admin:dashboard:stats:v2',
                'admin:dashboard:quick',
                'admin:dashboard:recent',
                'admin:dashboard:top_petitions',
                'admin:dashboard:health',
                'admin:stats:totals',
                'admin:users:verified_count',
                'admin:petitions:status_counts',
                'level:user_counts',

            ];

            foreach ($flatKeys as $key) {
                Cache::forget($key);
            }

            foreach (['users', 'petitions', 'signatures', 'categories', 'pages', 'logs', 'banned_ips'] as $table) {
                Cache::forget("approx_rows:{$table}");
            }

            foreach (['home', 'list', 'sitemap'] as $group) {
                Cache::forget("ver:{$group}");
            }

            $modules = Permission::matrix();
            foreach (UserLevel::pluck('id') as $levelId) {
                foreach ($modules as $module => $actions) {
                    foreach ($actions as $action) {
                        Cache::forget("permissions:{$levelId}:{$module}:{$action}");
                    }
                }
            }

            foreach (Language::where('is_active', true)->pluck('code') as $loc) {
                Cache::forget("categories:list:{$loc}");
            }

            return back()->with('success', 'Cache rebuilt: targeted keys flushed; snapshots reload lazily.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cache rebuild failed: '.$e->getMessage());
        }
    }
}
