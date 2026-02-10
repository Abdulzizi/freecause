<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    public static function get(string $key, $default = null, string $group = 'global')
    {
        $row = Setting::where('group', $group)->where('key', $key)->first();
        return $row ? $row->castedValue() : $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'global'): void
    {
        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['type' => $type, 'value' => is_array($value) ? json_encode($value) : (string)$value]
        );
    }
}
