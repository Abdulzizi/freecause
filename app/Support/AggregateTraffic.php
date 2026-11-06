<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AggregateTraffic
{
    public const AREAS = ['home', 'petitions', 'petition', 'profile', 'magazine', 'other'];

    public const LOCALES = ['da', 'de', 'el', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'];

    public const DEVICES = ['desktop', 'mobile', 'tablet'];

    public static function knownAutomation(string $agent): bool
    {
        return (bool) preg_match('/bot\b|crawler|spider|slurp|headless|lighthouse|pagespeed|uptime|monitoring|curl\b|wget\b|python-requests|httpclient|scrapy|selenium|playwright/i', $agent);
    }

    public static function report(string $locale, string $range): array
    {
        $hours = match ($range) {
            '1d' => 24, '7d' => 168, default => 720
        };
        $from = now('UTC')->startOfHour()->subHours($hours - 1);
        $rows = DB::table('aggregate_traffic')->where('hour', '>=', $from)
            ->when(in_array($locale, self::LOCALES, true), fn ($q) => $q->where('locale', $locale))
            ->selectRaw('area, device, kind, SUM(page_loads) AS total')
            ->groupBy('area', 'device', 'kind')->get();

        return [
            'browser' => (int) $rows->where('kind', 'browser')->sum('total'),
            'automation' => (int) $rows->where('kind', 'automation')->sum('total'),
            'areas' => $rows->where('kind', 'browser')->groupBy('area')->map(fn ($items) => (int) $items->sum('total'))->all(),
            'devices' => $rows->where('kind', 'browser')->groupBy('device')->map(fn ($items) => (int) $items->sum('total'))->all(),
        ];
    }
}
