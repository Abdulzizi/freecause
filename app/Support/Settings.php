<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Settings
{
    private const CACHE_KEY = 'settings:all';

    private static ?array $memo = null;

    // DB fallback while cache is down; kept apart from $memo so it is never treated as a cache hit.
    private static ?array $fallbackMemo = null;

    private static function all(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        try {
            return self::$memo = Cache::remember(self::CACHE_KEY, 300, fn () => self::load());
        } catch (\Throwable $e) {
            // Cache down: read DB once per request; the memo flush lets the next request/job retry the cache.
            try {
                return self::$fallbackMemo ??= self::load();
            } catch (\Throwable $e) {
                return [];
            }
        }
    }

    private static function load(): array
    {
        $grouped = [];

        foreach (Setting::query()->get(['group', 'key', 'value']) as $row) {
            $grouped[$row->group][$row->key] = $row->value;
        }

        return $grouped;
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

    public static function flushMemo(): void
    {
        self::$memo = null;
        self::$fallbackMemo = null;
    }

    public static function forget(): void
    {
        self::$memo = null;
        self::$fallbackMemo = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            rescue(fn () => Log::warning('Settings cache forget failed: '.$e->getMessage()), report: false);
        }
    }

    /**
     * @return array<string, string>
     */
    public static function group(string $group): array
    {
        return self::all()[$group] ?? [];
    }

    public static function delete(string $key, string $group = 'global'): void
    {
        Setting::where('group', $group)->where('key', $key)->delete();

        self::forget();
    }
}
