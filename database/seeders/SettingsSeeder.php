<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['group' => 'global', 'key' => 'base_url', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'short_base_url', 'type' => 'string', 'value' => ''],

            ['group' => 'global', 'key' => 'email_to_staff', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'email_from', 'type' => 'string', 'value' => ''],

            ['group' => 'global', 'key' => 'inject_head_html', 'type' => 'text', 'value' => ''],
            ['group' => 'global', 'key' => 'inject_body_html', 'type' => 'text', 'value' => ''],

            ['group' => 'global', 'key' => 'facebook_app_id', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'facebook_secret', 'type' => 'string', 'value' => ''],

            ['group' => 'global', 'key' => 'google_client_id', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'google_client_secret', 'type' => 'string', 'value' => ''],

            ['group' => 'global', 'key' => 'smtp_enabled', 'type' => 'bool', 'value' => '0'],
            ['group' => 'global', 'key' => 'smtp_host', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'smtp_port', 'type' => 'int', 'value' => '587'],
            ['group' => 'global', 'key' => 'smtp_user', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'smtp_pass', 'type' => 'string', 'value' => ''],
            ['group' => 'global', 'key' => 'smtp_encryption', 'type' => 'string', 'value' => 'tls'],

            ['group' => 'global', 'key' => 'max_featured_petitions_per_country', 'type' => 'int', 'value' => '10'],

            ['group' => 'global', 'key' => 'special_debug_ip', 'type' => 'string', 'value' => ''],

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
