<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\CacheVersion;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
        CacheVersion::bump('sitemap');
    }

    public function deleted(Category $category): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
        CacheVersion::bump('sitemap');
    }
}
