<?php

namespace App\Observers;

use App\Models\CategoryTranslation;
use App\Support\CacheVersion;

class CategoryTranslationObserver
{
    public function saved(CategoryTranslation $translation): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('sitemap');
    }

    public function deleted(CategoryTranslation $translation): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('sitemap');
    }
}
