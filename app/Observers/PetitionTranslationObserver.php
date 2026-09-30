<?php

namespace App\Observers;

use App\Models\PetitionTranslation;
use App\Support\CacheVersion;

class PetitionTranslationObserver
{
    public function saved(PetitionTranslation $translation): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
    }

    public function deleted(PetitionTranslation $translation): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
    }
}
