<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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
            if (Schema::hasTable('languages')) {
                $cached = Cache::remember('languages:codes', 60, function () {
                    return Language::where('is_active', 1)->pluck('code')->toArray() ?: ['en'];
                });

                if (is_array($cached) && ! empty($cached)) {
                    return $cached;
                }
            }
        } catch (\Throwable) {
        }

        return ['en'];
    }
}
