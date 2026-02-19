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
        'crypto',
        'porn',
        'escort',
        'seo service',
        'cheap pills',
    ];

    public static function isSpam(string $text): bool
    {
        $text = strtolower($text);

        foreach (self::$badKeywords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        if (substr_count($text, 'http') >= 2) {
            return true;
        }

        return false;
    }

    public static function log(string $type, string $payload): void
    {
        DB::table('spam_logs')->insert([
            'type' => $type,
            'ip' => request()->ip(),
            'payload' => substr($payload, 0, 2000),
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

        if ($count >= $limit) {
            self::banCurrentIp('Auto ban: too many ' . $type . ' attempts');
            return true;
        }

        return false;
    }

    public static function banCurrentIp(string $reason = null): void
    {
        DB::table('banned_ips')->updateOrInsert(
            ['ip' => request()->ip()],
            [
                'reason' => $reason,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
