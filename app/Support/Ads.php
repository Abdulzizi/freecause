<?php

namespace App\Support;

// Resolves Adsterra ad config, preferring runtime admin Settings (group "ads")
// over the config/ads.php baseline. Admin edits the enable flag, host and the
// per-slot zone keys without a deploy; structural bits (dimensions, format,
// network path) stay in config since they are not client-tunable.
class Ads
{
    // settings group. Reuses "global" so the existing admin Global Options
    // save loop (which writes group "global") persists these without changes.
    private const GROUP = 'global';

    // master switch: Settings override, else config default.
    public static function enabled(): bool
    {
        $v = Settings::get('ads_enabled', null, self::GROUP);

        if ($v === null) {
            return (bool) config('ads.enabled', false);
        }

        return (bool) $v;
    }

    // network script host, trailing slash trimmed.
    public static function host(): string
    {
        $v = Settings::get('ads_host', null, self::GROUP);
        $host = $v !== null && trim((string) $v) !== ''
            ? (string) $v
            : (string) config('ads.host', '');

        return rtrim($host, '/');
    }

    // resolved slot config, or null for an unknown slot. The zone key is
    // overridden by Settings (ads_slot_{name}_key); dims/format/path from config.
    public static function slot(string $name): ?array
    {
        $cfg = config("ads.slots.$name");

        if (! is_array($cfg)) {
            return null;
        }

        $override = Settings::get("ads_slot_{$name}_key", null, self::GROUP);
        if ($override !== null && trim((string) $override) !== '') {
            $cfg['key'] = (string) $override;
        }

        return $cfg;
    }

    // slot names in a fixed order (for the admin form + iteration).
    public static function slotNames(): array
    {
        return array_keys((array) config('ads.slots', []));
    }
}
