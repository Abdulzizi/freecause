<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReviewKeywords
{
    private const SETTINGS_GROUP = 'moderation';

    private const SETTINGS_PREFIX = 'review_keywords_';

    public const MAX_TERMS = 500;

    public const TERMS = [
        'betting',
        'gambling',
        'casino',
        'online casino',
        'sports betting',
        'bookmaker',
        'slot machine',
        'online slots',
        'online poker',
        'betting bonus',
        'casino bonus',
        'free spins',
        'porn',
        'pornography',
        'xxx',
        'adult content',
        'adult services',
        'escort',
        'erotic encounters',
        'paid sex',
        'sexual services',
        'erotic webcam',
        'sex cam',
        'camgirl',
        'phone sex',
        'fortune teller',
        'fortune telling',
        'palm reader',
        'palm reading',
        'clairvoyant',
        'clairvoyance',
        'psychic',
        'psychic reading',
        'tarot reading',
        'esoteric consultation',
        'paid rituals',
        'love spells',
        'telephone consultations',
        'paid telephone consultations',
        'paid consultations',
        'premium rate number',
        'promotional code',
        'discount code',
        'promo code',
        'referral code',
        'affiliate link',
        'welcome bonus',
        'sign up and earn',
        'guaranteed earnings',
        'easy money',
        'adulzi',
        'keyword',
        'fortune tellers',
        'paid telephone consultation',
        'gambling bonus',
        'casino games',
        'betting odds',
    ];

    public static function translations(): array
    {
        static $translations;

        return $translations ??= require __DIR__.'/review_keyword_translations.php';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function overrides(): array
    {
        $rows = [];

        try {
            $rows = Setting::where('group', self::SETTINGS_GROUP)
                ->where('key', 'like', self::SETTINGS_PREFIX.'%')
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable $e) {
            rescue(fn () => Log::warning('Review keyword overrides unreadable: '.$e->getMessage()), report: false);

            $rows = Settings::group(self::SETTINGS_GROUP);
        }

        $overrides = [];

        foreach ($rows as $key => $value) {
            $key = (string) $key;

            if (! str_starts_with($key, self::SETTINGS_PREFIX)) {
                continue;
            }

            $locale = substr($key, strlen(self::SETTINGS_PREFIX));

            if ($locale === '') {
                continue;
            }

            $raw = (string) $value;

            if (! str_starts_with(ltrim($raw), '[')) {
                continue;
            }

            $decoded = json_decode($raw, true);

            if (! is_array($decoded) || ! array_is_list($decoded)) {
                continue;
            }

            $overrides[$locale] = self::normalizeTerms($decoded);
        }

        return $overrides;
    }

    /**
     * @param  array<int, mixed>  $terms
     * @return array<int, string>
     */
    public static function normalizeTerms(array $terms): array
    {
        $clean = [];

        foreach ($terms as $term) {
            if (! is_string($term)) {
                continue;
            }

            $term = trim($term);

            if ($term === '') {
                continue;
            }

            $key = mb_strtolower($term);

            if (! isset($clean[$key])) {
                $clean[$key] = $term;
            }
        }

        return array_values($clean);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function catalogue(): array
    {
        $overrides = self::overrides();

        $catalogue = ['en' => $overrides['en'] ?? self::TERMS];

        foreach (self::translations() as $locale => $terms) {
            $catalogue[$locale] = $overrides[$locale] ?? array_values($terms);
        }

        foreach ($overrides as $locale => $terms) {
            if (! isset($catalogue[$locale])) {
                $catalogue[$locale] = $terms;
            }
        }

        return $catalogue;
    }

    /**
     * @param  array<int, string>  $terms
     */
    public static function saveLocale(string $locale, array $terms): void
    {
        $terms = array_slice(self::normalizeTerms($terms), 0, self::MAX_TERMS);

        Settings::set(self::SETTINGS_PREFIX.$locale, $terms, self::SETTINGS_GROUP);
    }

    public static function resetLocale(string $locale): void
    {
        Settings::delete(self::SETTINGS_PREFIX.$locale, self::SETTINGS_GROUP);
    }

    public static function all(): array
    {
        $terms = [];

        foreach (self::catalogue() as $localeTerms) {
            $terms = array_merge($terms, $localeTerms);
        }

        return array_values(array_unique($terms));
    }

    /**
     * @return array<int, string> matched keywords
     */
    public static function matches(string $text): array
    {
        $tokens = self::tokenize($text);

        if ($tokens === []) {
            return [];
        }

        $matches = [];

        foreach (self::tokenizedTerms() as $term => $termTokens) {
            if (self::containsSequence($tokens, $termTokens)) {
                $matches[] = (string) $term;
            }
        }

        return $matches;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected static function tokenizedTerms(): array
    {
        static $cached = [];

        $terms = self::all();
        $key = md5(implode("\n", $terms));

        if (! isset($cached[$key])) {
            $map = [];

            foreach ($terms as $term) {
                $termTokens = self::tokenize($term);

                if ($termTokens !== []) {
                    $map[$term] = $termTokens;
                }
            }

            $cached = [$key => $map] + array_slice($cached, 0, 1, true);
        }

        return $cached[$key];
    }

    /**
     * @return array<string, string>
     */
    public static function localeMap(): array
    {
        $map = [];

        foreach (self::catalogue() as $locale => $terms) {
            foreach ($terms as $term) {
                $map[mb_strtolower($term)] ??= $locale;
            }
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<int, string>  $termTokens
     */
    protected static function containsSequence(array $tokens, array $termTokens): bool
    {
        $count = count($termTokens);
        $last = count($tokens) - $count;

        for ($start = 0; $start <= $last; $start++) {
            $matched = true;

            foreach ($termTokens as $offset => $termToken) {
                $token = $tokens[$start + $offset];

                if ($token === $termToken) {
                    continue;
                }

                if ($offset === $count - 1 && $token === $termToken.'s') {
                    continue;
                }

                $matched = false;

                break;
            }

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected static function tokenize(string $text): array
    {
        $text = Str::lower($text);

        if (! preg_match('/[\p{Greek}\p{Cyrillic}]/u', $text)) {
            $text = Str::ascii($text);
        }

        $text = strtr($text, ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't']);

        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : array_values($parts);
    }
}
