<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Spam
{
    protected static array $badKeywords = [
        'viagra',
        'casino',
        'loan',
        'bitcoin',
        'call girl',
        'seo service',
    ];

    public static function isSpam(string $text): bool
    {
        $text = strtolower($text);

        foreach (self::$badKeywords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }

    public static function log(string $type, string $payload): void
    {
        DB::table('spam_logs')->insert([
            'type' => $type,
            'ip' => request()->ip(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public static function rateLimit(string $type, int $limit = 5): bool
    {
        $count = DB::table('spam_logs')
            ->where('ip', request()->ip())
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        return $count >= $limit;
    }

    public static function banCurrentIp(string $reason = null): void
    {
        DB::table('banned_ips')->insert([
            'ip' => request()->ip(),
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
