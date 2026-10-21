<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Spam
{
    public static function turnstileEnabled(): bool
    {
        return (bool) Settings::get('turnstile_enabled', false, 'global')
            && trim((string) Settings::get('turnstile_site_key', '', 'global')) !== ''
            && trim((string) Settings::get('turnstile_secret', '', 'global')) !== '';
    }

    public static function verifyTurnstile(?string $token): bool
    {
        if (! self::turnstileEnabled()) {
            return true;
        }

        if ($token === null || trim($token) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => (string) Settings::get('turnstile_secret', '', 'global'),
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

            return $response->successful() && ($response->json('success') === true);
        } catch (\Throwable $e) {
            return false;
        }
    }

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
        'free money',
        'paypal',
    ];

    public static function isSpam(string $text): bool
    {
        $text = strtr(strtolower($text), ['1' => 'i']);

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
        $limit = $type === 'petition.review' ? 60000 : 8000;

        DB::table('spam_logs')->insert([
            'type' => $type,
            'ip' => request()->ip(),
            'payload' => mb_strcut($payload, 0, $limit, 'UTF-8'),
            'created_at' => now(),
        ]);
    }

    public static function rateLimit(string $type, int $limit = 20): bool
    {
        // Track attempts under a dedicated type suffix so this counter is not
        // polluted by genuine spam-event rows that callers log under $type
        // (e.g. Spam::log('petition', ...)). Count the attempts recorded so
        // far in the window, then record this one.
        $attemptType = $type.':rl';

        $count = DB::table('spam_logs')
            ->where('ip', request()->ip())
            ->where('type', $attemptType)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        self::log($attemptType, 'Rate limit attempt');

        if ($count >= $limit) {
            self::banCurrentIp('Auto ban: too many '.$type.' attempts');

            return true;
        }

        return false;
    }

    public static function banCurrentIp(?string $reason = null): void
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
