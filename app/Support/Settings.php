<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const CACHE_KEY = 'settings:all';

    /**
     * @return array<string, array<string, string|null>> group => key => raw value
     */
    private static function all(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, 300, function () {
                $grouped = [];

                foreach (Setting::query()->get(['group', 'key', 'value']) as $row) {
                    $grouped[$row->group][$row->key] = $row->value;
                }

                return $grouped;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function get(string $key, mixed $default = null, string $group = 'global'): mixed
    {
        try {
            $value = self::all()[$group][$key] ?? null;

            if ($value === null) {
                return $default;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && ! is_string($decoded)) {
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

        self::forget();
    }

    /**
     * Invalidate the whole settings snapshot so the next read rebuilds it from the DB.
     */
    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
