<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class MailQuota
{
    private const KEY = 'mail:quota-exhausted';

    public static function tripped(): bool
    {
        return (bool) Cache::get(self::KEY);
    }

    public static function trip(): void
    {
        Cache::put(self::KEY, 1, now()->endOfDay());
    }

    public static function causedBy(\Throwable $e): bool
    {
        return preg_match('/(allowance|quota)/i', $e->getMessage()) === 1;
    }
}
