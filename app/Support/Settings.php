<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    public static function get(string $key, $default = null, string $group = 'global')
    {
        $row = Setting::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $row ? $row->castedValue($default) : $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'global'): void
    {
        if (in_array($type, ['json', 'array'], true)) {
            $value = json_encode($value);
        }

        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}
