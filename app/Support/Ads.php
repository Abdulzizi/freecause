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

    // True the first time a slot name is rendered for the current request, false
    // after. The layout emits a default ad stack on every page; this keeps pages
    // that already place a slot inline from emitting the same zone twice, which
    // Adsterra treats as an error (duplicate code corrupts stats and CPM).
    public static function renderOnce(string $name): bool
    {
        $request = request();

        if ($request === null) {
            return true;
        }

        $key = 'ads_slot_rendered_'.$name;

        if ($request->attributes->has($key)) {
            return false;
        }

        $request->attributes->set($key, true);

        return true;
    }

    public static function pick(string $placement): ?string
    {
        $pool = (array) config("ads.placements.$placement", []);
        $pool = array_values(array_filter($pool, fn ($n) => ($c = self::slot($n)) && ! empty($c['key']) && ! request()->attributes->has('ads_slot_rendered_'.$n)));
        if ($pool === []) {
            return null;
        }
        $key = 'ads_pick_'.$placement;
        $i = (int) (request()->attributes->get($key, 0));
        request()->attributes->set($key, $i + 1);

        return $pool[$i % count($pool)];
    }
}
