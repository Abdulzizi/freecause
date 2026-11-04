<?php

namespace App\Observers;

use App\Models\User;
use App\Support\Locale;
use Illuminate\Support\Facades\Cache;

class UserProfileCacheFlusher
{
    public function saved(User $user): void
    {
        $locales = array_unique(array_merge(['en', default_locale()], $this->activeLocales()));

        foreach ($locales as $locale) {
            Cache::forget("user:profile:data:{$user->id}:{$locale}");
        }
    }

    private function activeLocales(): array
    {
        try {
            return Locale::supported();
        } catch (\Throwable) {
            return ['en'];
        }
    }
}
