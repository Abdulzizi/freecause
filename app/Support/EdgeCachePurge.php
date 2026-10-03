<?php

namespace App\Support;

use App\Models\CategoryTranslation;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdgeCachePurge
{
    /** Only canonical, query-free listing URLs are future cache candidates. */
    public static function listings(): void
    {
        if (! config('edge-cache.purge_enabled')) {
            return;
        }

        DB::afterCommit(function () {
            try {
                $base = rtrim((string) config('edge-cache.base_url'), '/');
                $urls = [];
                foreach (Language::where('is_active', true)->pluck('code') as $locale) {
                    $urls[] = $base.'/'.$locale.'/petitions';
                    foreach (CategoryTranslation::where('locale', $locale)->get(['category_id', 'slug']) as $category) {
                        $urls[] = $base.'/'.$locale.'/petitions/category-'.rawurlencode($category->slug).'-'.$category->category_id;
                    }
                }
                self::urls($urls);
            } catch (\Throwable) {
                Log::warning('Edge cache purge could not be scheduled.');
            }
        });
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
                // Do not expose request headers, credentials or remote response bodies.
                Log::warning('Edge cache purge unavailable; cached pages expire at the configured TTL.');
            }
        }
    }
}
