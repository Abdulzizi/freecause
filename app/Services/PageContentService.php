<?php

namespace App\Services;

use App\Models\PageContent;
use Illuminate\Support\Facades\Cache;

class PageContentService
{
    public function getPage(string $page, string $locale): array
    {
        return Cache::remember(
            "page_content_{$page}_{$locale}",
            now()->addMinutes(30),
            function () use ($page, $locale) {
                return PageContent::where('page', $page)
                    ->where('locale', $locale)
                    ->pluck('value', 'key')
                    ->toArray();
            }
        );
    }

    public function clear(string $page, string $locale): void
    {
        Cache::forget("page_content_{$page}_{$locale}");
    }
}
