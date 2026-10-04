<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Data helpers for error pages.
 *
 * Error pages (notably 404) are frequently served while the app is degraded,
 * so any data lookup here must never throw. All queries are wrapped and
 * degrade gracefully to an empty collection, and results are cached briefly
 * to avoid hammering a struggling database.
 */
class ErrorPageData
{
    /**
     * Most-read published, active petitions for the current locale.
     *
     * Returns at most 6 rows (p.id, pt.title, pt.slug, p.signature_count)
     * ordered by signature_count descending. Never throws: on any failure
     * (DB down, missing tables, etc.) an empty collection is returned so the
     * error page can still render.
     */
    public static function mostReadPetitions(): Collection
    {
        $locale = app()->getLocale();

        return rescue(function () use ($locale) {
            return Cache::remember('errors:most_read:'.$locale, 300, function () use ($locale) {
                return DB::table('petitions as p')
                    ->join('petition_translations as pt', function ($j) use ($locale) {
                        $j->on('pt.petition_id', '=', 'p.id')->where('pt.locale', '=', $locale);
                    })
                    ->where('p.status', 'published')
                    ->where('p.is_active', 1)
                    ->select(['p.id', 'pt.title', 'pt.slug', 'p.signature_count'])
                    ->orderByDesc('p.signature_count')
                    ->limit(6)
                    ->get();
            });
        }, collect(), false);
    }
}
