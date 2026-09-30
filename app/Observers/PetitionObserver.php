<?php

namespace App\Observers;

use App\Models\Petition;
use App\Support\CacheVersion;

class PetitionObserver
{
    public function saved(Petition $petition): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
        CacheVersion::bump('sitemap');
    }

    public function deleted(Petition $petition): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
        CacheVersion::bump('sitemap');
    }
}
