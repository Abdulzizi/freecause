<?php

namespace App\Support;

use Illuminate\Support\Str;

class ReviewKeywords
{
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
        return require __DIR__.'/review_keyword_translations.php';
    }

    public static function all(): array
    {
        $terms = self::TERMS;

        foreach (self::translations() as $translated) {
            $terms = array_merge($terms, $translated);
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

        foreach (self::all() as $term) {
            $termTokens = self::tokenize($term);

            if ($termTokens === []) {
                continue;
            }

            if (self::containsSequence($tokens, $termTokens)) {
                $matches[] = $term;
            }
        }

        return $matches;
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
