<?php

namespace App\Observers;

use App\Support\CacheVersion;

class FlushesListingCaches
{
    public function saved($model): void
    {
        CacheVersion::bumpAll();
    }

    public function deleted($model): void
    {
        CacheVersion::bumpAll();
    }
}
