<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReviewKeywords
{
    private const SETTINGS_GROUP = 'moderation';

    private const SETTINGS_PREFIX = 'review_keywords_';

    private const DISABLED_KEY = self::SETTINGS_PREFIX.'disabled';

    private const RESCAN_PREFIX = self::SETTINGS_PREFIX.'rescan';

    private const CACHE_KEY = 'review_keywords:rows';

    private const CACHE_TTL = 300;

    private const MEMO_TTL = 5;

    public const MAX_TERMS = 500;

    public const MAX_CONCEPT_LENGTH = 255;

    public const MAX_TERM_LENGTH = 255;

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
        'fortune tellers',
        'paid telephone consultation',
        'gambling bonus',
        'casino games',
        'betting odds',
        'loan',
        'crypto',
    ];

    private static array $parsed = [];

    private static array $memoRows = [];

    private static float $memoExpires = 0.0;

    private static mixed $memoApp = null;

    private static bool $cacheUnreliable = false;

    public static function translations(): array
    {
        static $translations;

        return $translations ??= require __DIR__.'/review_keyword_translations.php';
    }

    /**
     * @return array<string, array<int, array{term: string, concept: string}>>
     */
    public static function overrides(): array
    {
        return self::parsedState()['overrides'];
    }

    /**
     * @return array<int, string>
     */
    public static function disabledConcepts(): array
    {
        return self::parsedState()['disabled'];
    }

    /**
     * @return array<int, array{term: string, concept: string}>
     */
    public static function entriesFor(string $locale): array
    {
        $overrides = self::overrides();

        if (isset($overrides[$locale])) {
            return $overrides[$locale];
        }

        return self::bundledEntries($locale);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function catalogue(): array
    {
        $overrides = self::overrides();
        $disabled = self::disabledConcepts();

        $catalogue = [];

        foreach (self::locales() as $locale) {
            $entries = $overrides[$locale] ?? self::bundledEntries($locale);
            $terms = [];

            foreach ($entries as $entry) {
                if (in_array((string) $entry['concept'], $disabled, true)) {
                    continue;
                }

                $terms[] = $entry['term'];
            }

            $catalogue[$locale] = $terms;
        }

        return $catalogue;
    }

    /**
     * @return array<string, array<string, array<int, string>>>
     */
    public static function concepts(): array
    {
        $concepts = [];

        foreach (self::locales() as $locale) {
            foreach (self::entriesFor($locale) as $entry) {
                $concept = (string) $entry['concept'];

                $concepts[$concept][$locale][] = $entry['term'];
            }
        }

        return $concepts;
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
     * @return array<int, string>
     */
    public static function unblockedLegacyTerms(): array
    {
        $catalogue = [];

        foreach (self::all() as $term) {
            $catalogue[mb_strtolower($term)] = true;
        }

        $unblocked = [];

        foreach (Spam::reviewOwned() as $word) {
            $key = mb_strtolower(trim((string) $word));

            if ($key === '' || isset($catalogue[$key])) {
                continue;
            }

            $unblocked[] = $word;
        }

        return $unblocked;
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
     * @param  array<int, mixed>  $terms
     */
    public static function saveLocale(string $locale, array $terms): void
    {
        $entries = array_slice(self::normalizeEntries($terms), 0, self::MAX_TERMS);

        try {
            Settings::set(self::SETTINGS_PREFIX.$locale, $entries, self::SETTINGS_GROUP);
        } finally {
            self::flushCache();
        }
    }

    public static function resetLocale(string $locale): void
    {
        try {
            Settings::delete(self::SETTINGS_PREFIX.$locale, self::SETTINGS_GROUP);
        } finally {
            self::flushCache();
        }
    }

    public static function setConceptEnabled(string $concept, bool $enabled): bool
    {
        $concept = mb_strtolower(trim($concept));

        if ($concept === '') {
            return false;
        }

        $written = false;

        try {
            DB::transaction(function () use ($concept, $enabled, &$written): void {
                $row = self::lockDisabledRow();

                if ($row === null) {
                    if (self::insertDisabledRow()) {
                        $raw = '[]';
                    } else {
                        $row = self::lockDisabledRow();

                        if ($row === null) {
                            return;
                        }

                        $raw = $row->value;
                    }
                } else {
                    $raw = $row->value;
                }

                $disabled = self::decodeDisabled($raw);

                if ($disabled === null) {
                    return;
                }

                if ($enabled) {
                    $disabled = array_values(array_filter($disabled, fn (string $item) => $item !== $concept));
                } elseif (! in_array($concept, $disabled, true)) {
                    $disabled[] = $concept;
                }

                Settings::set(self::DISABLED_KEY, $disabled, self::SETTINGS_GROUP);

                $written = true;
            }, 3);
        } finally {
            self::flushCache();
        }

        return $written;
    }

    /**
     * @return array<int, array{term: string, concept: string}>
     */
    public static function parseTerms(string $text, ?int $limit = self::MAX_TERMS): array
    {
        $lines = preg_split('/\R/u', $text);

        if ($lines === false) {
            return [];
        }

        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = explode('::', $line, 2);

            $term = trim($parts[0]);
            $concept = isset($parts[1]) ? trim($parts[1]) : '';

            if ($term === '') {
                continue;
            }

            $items[] = ['term' => $term, 'concept' => $concept];
        }

        $entries = self::normalizeEntries($items);

        if ($limit === null) {
            return $entries;
        }

        return array_slice($entries, 0, $limit);
    }

    /**
     * @param  array<int, mixed>  $entries
     */
    public static function formatTerms(array $entries): string
    {
        $lines = [];

        foreach ($entries as $entry) {
            if (is_string($entry)) {
                $term = trim($entry);
                $concept = '';
            } elseif (is_array($entry)) {
                $term = is_string($entry['term'] ?? null) ? trim($entry['term']) : '';
                $concept = is_string($entry['concept'] ?? null) ? trim($entry['concept']) : '';
            } else {
                continue;
            }

            if ($term === '') {
                continue;
            }

            if ($concept !== '' && mb_strtolower($concept) !== mb_strtolower($term)) {
                $lines[] = $term.' :: '.$concept;

                continue;
            }

            $lines[] = $term;
        }

        return implode("\n", $lines);
    }

    public static function flushCache(): void
    {
        self::$parsed = [];
        self::$memoRows = [];
        self::$memoExpires = 0.0;

        self::$cacheUnreliable = ! self::purgeCache();
    }

    protected static function purgeCache(): bool
    {
        try {
            Cache::forget(self::CACHE_KEY);

            return true;
        } catch (\Throwable $e) {
            rescue(fn () => Log::warning('Review keyword cache flush failed: '.$e->getMessage()), report: false);

            return false;
        }
    }

    /**
     * @return array<string, array<int, array<int, string>>>
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

    /**
     * @return array{overrides: array<string, array<int, array{term: string, concept: string}>>, disabled: array<int, string>}
     */
    private static function parsedState(): array
    {
        $rows = self::rows();

        if (self::$parsed === []) {
            self::$parsed = [
                'overrides' => self::parseOverrides($rows),
                'disabled' => self::parseDisabled($rows),
            ];
        }

        return self::$parsed;
    }

    /**
     * @return array<string, string>
     */
    private static function rows(): array
    {
        $app = app();

        if (self::$memoApp !== $app) {
            self::$memoApp = $app;
            self::$memoRows = [];
            self::$memoExpires = 0.0;
            self::$parsed = [];
            self::$cacheUnreliable = false;
        }

        $now = microtime(true);

        if (self::$memoExpires > $now) {
            return self::$memoRows;
        }

        $rows = self::readRows();

        self::$memoRows = $rows;
        self::$memoExpires = $now + self::MEMO_TTL;
        self::$parsed = [];

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private static function readRows(): array
    {
        if (self::$cacheUnreliable) {
            $rows = self::directRows();

            if (self::purgeCache()) {
                self::$cacheUnreliable = false;
            }

            return $rows;
        }

        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return Setting::where('group', self::SETTINGS_GROUP)
                    ->where('key', 'like', self::SETTINGS_PREFIX.'%')
                    ->where('key', 'not like', self::RESCAN_PREFIX.'%')
                    ->pluck('value', 'key')
                    ->all();
            });
        } catch (\Throwable $e) {
            rescue(fn () => Log::warning('Review keyword overrides unreadable: '.$e->getMessage()), report: false);

            self::$cacheUnreliable = true;

            return self::directRows();
        }
    }

    /**
     * @return array<string, string>
     */
    private static function directRows(): array
    {
        try {
            return Setting::where('group', self::SETTINGS_GROUP)
                ->where('key', 'like', self::SETTINGS_PREFIX.'%')
                ->where('key', 'not like', self::RESCAN_PREFIX.'%')
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable $e) {
            rescue(fn () => Log::warning('Review keyword overrides unreadable: '.$e->getMessage()), report: false);

            return array_filter(
                Settings::group(self::SETTINGS_GROUP),
                fn ($key): bool => ! str_starts_with((string) $key, self::RESCAN_PREFIX),
                ARRAY_FILTER_USE_KEY
            );
        }
    }

    /**
     * @param  array<string, string>  $rows
     * @return array<string, array<int, array{term: string, concept: string}>>
     */
    private static function parseOverrides(array $rows): array
    {
        $overrides = [];

        foreach ($rows as $key => $value) {
            $key = (string) $key;

            if (! str_starts_with($key, self::SETTINGS_PREFIX) || $key === self::DISABLED_KEY) {
                continue;
            }

            $locale = substr($key, strlen(self::SETTINGS_PREFIX));

            if ($locale === '') {
                continue;
            }

            $entries = self::decodeEntries((string) $value);

            if ($entries === null) {
                continue;
            }

            $overrides[$locale] = $entries;
        }

        return $overrides;
    }

    /**
     * @param  array<string, string>  $rows
     * @return array<int, string>
     */
    private static function parseDisabled(array $rows): array
    {
        return self::decodeDisabled($rows[self::DISABLED_KEY] ?? null) ?? [];
    }

    /**
     * @return array<int, string>|null
     */
    private static function decodeDisabled(mixed $raw): ?array
    {
        if ($raw === null) {
            return [];
        }

        if (! is_string($raw)) {
            return null;
        }

        $raw = ltrim($raw);

        if ($raw === '') {
            return [];
        }

        if (! str_starts_with($raw, '[')) {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            return null;
        }

        $disabled = [];

        foreach ($decoded as $concept) {
            if (! is_string($concept)) {
                continue;
            }

            $concept = mb_strtolower(trim($concept));

            if ($concept === '' || in_array($concept, $disabled, true)) {
                continue;
            }

            $disabled[] = $concept;
        }

        return $disabled;
    }

    private static function lockDisabledRow(): ?object
    {
        return DB::table('settings')
            ->where('group', self::SETTINGS_GROUP)
            ->where('key', self::DISABLED_KEY)
            ->lockForUpdate()
            ->first();
    }

    private static function insertDisabledRow(): bool
    {
        try {
            DB::table('settings')->insert([
                'group' => self::SETTINGS_GROUP,
                'key' => self::DISABLED_KEY,
                'value' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }

    /**
     * @return array<int, array{term: string, concept: string}>|null
     */
    private static function decodeEntries(string $raw): ?array
    {
        $raw = ltrim($raw);

        if (! str_starts_with($raw, '[')) {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            return null;
        }

        return self::normalizeEntries($decoded);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{term: string, concept: string}>
     */
    private static function normalizeEntries(array $items): array
    {
        $entries = [];
        $seen = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $term = $item;
                $concept = '';
            } elseif (is_array($item)) {
                $term = is_string($item['term'] ?? null) ? $item['term'] : '';
                $concept = is_string($item['concept'] ?? null) ? $item['concept'] : '';
            } else {
                continue;
            }

            $term = trim($term);

            if ($term === '') {
                continue;
            }

            $term = mb_substr($term, 0, self::MAX_TERM_LENGTH);

            $key = mb_strtolower($term);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $concept = mb_strtolower(trim($concept));

            if ($concept === '') {
                $concept = $key;
            }

            $entries[] = ['term' => $term, 'concept' => mb_substr($concept, 0, self::MAX_CONCEPT_LENGTH)];
        }

        return $entries;
    }

    /**
     * @return array<int, array{term: string, concept: string}>
     */
    private static function bundledEntries(string $locale): array
    {
        $terms = $locale === 'en' ? self::TERMS : (self::translations()[$locale] ?? []);

        $entries = [];

        foreach ($terms as $term) {
            $term = trim((string) $term);

            if ($term === '') {
                continue;
            }

            $entries[] = ['term' => $term, 'concept' => mb_strtolower($term)];
        }

        return $entries;
    }

    /**
     * @return array<int, string>
     */
    private static function locales(): array
    {
        $locales = ['en'];

        foreach (array_keys(self::translations()) as $locale) {
            $locales[] = (string) $locale;
        }

        foreach (array_keys(self::overrides()) as $locale) {
            $locales[] = (string) $locale;
        }

        return array_values(array_unique($locales));
    }
}
