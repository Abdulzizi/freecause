<?php

namespace App\Support;

class PetitionCache
{
    public static function bump(): void
    {
        CacheVersion::bumpAll();
    }
}
