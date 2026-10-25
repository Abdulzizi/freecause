<?php

namespace App\Support;

/**
 * Boilerplate signature texts that are not real comments.
 *
 * The audit found that the "8.99M custom comments" figure was inflated by
 * localized variants of the default string: the stored text differs from the
 * current translation by wording (Polish "Podpisuje się pod tą petycją" vs the
 * configured "Popierám tę petycję") or by a single letter (Greek "αυτήν" vs
 * "αυτή"), so the exact-match filter let them through.
 *
 * Keep this list curatable: every entry here stops a whole family of
 * boilerplate from being rendered and counted as a comment.
 */
final class DefaultComments
{
    private const VARIANTS = [
        'pl' => [
            'Podpisuje się pod tą petycją',
            'Podpisuję się pod tą petycją',
        ],
        'el' => [
            'Υποστηρίζω αυτήν την αίτηση',
        ],
    ];

    /** @param  array<int, string>  $locales */
    public static function forLocales(array $locales): array
    {
        $out = [];

        foreach ($locales as $locale) {
            $text = __('sign.default_comment', [], $locale);
            if (! is_string($text) || trim($text) === '') {
                continue;
            }
            $text = trim($text);
            $out[] = $text;
            $out[] = $text.'.';
            $out[] = $text.'!';

            foreach (self::VARIANTS[$locale] ?? [] as $variant) {
                $out[] = $variant;
            }
        }

        return array_values(array_unique($out));
    }
}
