<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [

            // Base
            ['group' => 'global', 'key' => 'base_url', 'type' => 'string', 'value' => config('app.url')],
            ['group' => 'global', 'key' => 'short_base_url', 'type' => 'string', 'value' => ''],

            // Emails
            ['group' => 'global', 'key' => 'email_to_staff', 'type' => 'string', 'value' => env('MAIL_USERNAME')],
            ['group' => 'global', 'key' => 'email_from', 'type' => 'string', 'value' => 'noreply@freecause.com'],

            // HTML injections
            ['group' => 'global', 'key' => 'inject_head_html', 'type' => 'text', 'value' => ''],
            ['group' => 'global', 'key' => 'inject_body_html', 'type' => 'text', 'value' => ''],

            // OAuth (auto from .env)
            ['group' => 'global', 'key' => 'facebook_app_id', 'type' => 'string', 'value' => env('FACEBOOK_APP_ID')],
            ['group' => 'global', 'key' => 'facebook_secret', 'type' => 'string', 'value' => env('FACEBOOK_SECRET')],
            ['group' => 'global', 'key' => 'google_client_id', 'type' => 'string', 'value' => env('GOOGLE_CLIENT_ID')],
            ['group' => 'global', 'key' => 'google_client_secret', 'type' => 'string', 'value' => env('GOOGLE_CLIENT_SECRET')],

            // SMTP (auto from .env)
            ['group' => 'global', 'key' => 'smtp_enabled', 'type' => 'bool', 'value' => '1'],
            ['group' => 'global', 'key' => 'smtp_host', 'type' => 'string', 'value' => env('MAIL_HOST')],
            ['group' => 'global', 'key' => 'smtp_port', 'type' => 'int', 'value' => env('MAIL_PORT')],
            ['group' => 'global', 'key' => 'smtp_user', 'type' => 'string', 'value' => env('MAIL_USERNAME')],
            ['group' => 'global', 'key' => 'smtp_pass', 'type' => 'string', 'value' => env('MAIL_PASSWORD')],
            ['group' => 'global', 'key' => 'smtp_encryption', 'type' => 'string', 'value' => env('MAIL_ENCRYPTION', 'tls')],

            // System
            ['group' => 'global', 'key' => 'max_featured_petitions_per_country', 'type' => 'int', 'value' => '10'],
            ['group' => 'global', 'key' => 'special_debug_ip', 'type' => 'string', 'value' => ''],

            // Logging
            ['group' => 'global', 'key' => 'logging_enabled', 'type' => 'bool', 'value' => '0'],
            ['group' => 'global', 'key' => 'logging_cookie_name', 'type' => 'string', 'value' => 'dothelog'],
            ['group' => 'global', 'key' => 'logging_cookie_value', 'type' => 'string', 'value' => '1'],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(
                ['group' => $row['group'], 'key' => $row['key']],
                ['type' => $row['type'], 'value' => $row['value']]
            );
        }
    }
}
