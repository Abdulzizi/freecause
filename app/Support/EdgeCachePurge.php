<?php

namespace App\Support;

use App\Jobs\PurgeEdgeListings;
use App\Models\CategoryTranslation;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdgeCachePurge
{
    public static function listings(): void
    {
        if (! config('edge-cache.purge_enabled')) {
            return;
        }

        dispatch(new PurgeEdgeListings);
    }

    public static function listingUrls(): array
    {
        $base = rtrim((string) config('edge-cache.base_url'), '/');
        $urls = [];

        foreach (Language::where('is_active', true)->pluck('code') as $locale) {
            $urls[] = $base.'/'.$locale.'/petitions';
            foreach (CategoryTranslation::where('locale', $locale)->get(['category_id', 'slug']) as $category) {
                $urls[] = $base.'/'.$locale.'/petitions/category-'.rawurlencode($category->slug).'-'.$category->category_id;
            }
        }

        return $urls;
    }

    public static function petitionUrls(array $ids): array
    {
        if (! config('edge-cache.purge_enabled')) {
            return [];
        }

        $base = rtrim((string) config('edge-cache.base_url'), '/');

        return DB::table('petition_translations')->whereIn('petition_id', $ids)
            ->get(['petition_id', 'locale', 'slug'])
            ->flatMap(fn ($tr) => [
                $base.'/'.rawurlencode($tr->locale).'/petition/'.rawurlencode($tr->slug).'/'.$tr->petition_id,
                $base.'/'.rawurlencode($tr->locale).'/petition/'.rawurlencode($tr->slug).'/'.$tr->petition_id.'/signatures',
            ])->all();
    }

    public static function pageUrls(array $ids): array
    {
        if (! config('edge-cache.purge_enabled')) {
            return [];
        }

        $base = rtrim((string) config('edge-cache.base_url'), '/');

        return DB::table('page_translations')->whereIn('page_id', $ids)
            ->get(['locale', 'slug'])
            ->map(fn ($tr) => $base.'/'.rawurlencode($tr->locale).'/'.rawurlencode($tr->slug))->all();
    }

    public static function dispatchListingPurge(): void
    {
        try {
            self::urls(self::listingUrls());
        } catch (\Throwable) {
            Log::warning('Edge cache purge could not be scheduled.');
        }
    }

    public static function urls(array $urls): void
    {
        if (! config('edge-cache.purge_enabled') || ! config('edge-cache.api_token') || ! config('edge-cache.zone_id')) {
            return;
        }

        $base = rtrim((string) config('edge-cache.base_url'), '/');
        if (parse_url($base, PHP_URL_SCHEME) !== 'https') {
            return;
        }
        $urls = array_values(array_unique(array_filter($urls, fn ($url) => is_string($url) && str_starts_with($url, $base.'/'))));

        foreach (array_chunk($urls, 30) as $batch) {
            try {
                $response = Http::withToken(config('edge-cache.api_token'))
                    ->connectTimeout(2)->timeout(5)
                    ->post('https://api.cloudflare.com/client/v4/zones/'.config('edge-cache.zone_id').'/purge_cache', ['files' => $batch]);
                if (! $response->successful() || $response->json('success') !== true) {
                    Log::warning('Edge cache purge failed; cached pages expire at the configured TTL.');
                }
            } catch (\Throwable) {
                Log::warning('Edge cache purge unavailable; cached pages expire at the configured TTL.');
            }
        }
    }
}
