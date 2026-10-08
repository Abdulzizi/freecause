<?php

namespace App\Observers;

use App\Support\CacheVersion;

class FlushesListingCaches
{
    public function saved($model): void
    {
        CacheVersion::bumpListings();
    }

    public function deleted($model): void
    {
        CacheVersion::bumpListings();
    }
}
