<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ErrorPageData
{
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
