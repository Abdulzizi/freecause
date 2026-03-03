<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public static function get(string $key, mixed $default = null, string $group = 'global'): mixed
    {
        try {
            $cacheKey = "setting:{$group}:{$key}";

            $value = Cache::remember($cacheKey, 300, function () use ($key, $group) {
                return Setting::where('key', $key)
                    ->where('group', $group)
                    ->value('value');
            });

            if ($value === null) {
                return $default;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && !is_string($decoded)) {
                return $decoded;
            }

            return $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, string $group = 'global'): void
    {
        if (is_bool($value) || is_array($value)) {
            $value = json_encode($value);
        }

        Setting::updateOrCreate(
            ['key' => $key, 'group' => $group],
            ['value' => $value]
        );

        Cache::forget("setting:{$group}:{$key}");
    }
}
