<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected $cachePrefix = 'translations:';
    protected $cacheTtl = 86400; // 24 hours

    public function get(string $locale, string $group, string $key, array $params = []): string
    {
        $cacheKey = $this->cachePrefix . $locale . ':' . $group;
        
        $translations = Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale, $group) {
            return Translation::forLocale($locale)
                ->forGroup($group)
                ->active()
                ->pluck('value', 'key')
                ->toArray();
        });

        $value = $translations[$key] ?? null;

        if ($value === null) {
            // Fallback to default locale (en)
            $defaultLocale = config('app.locale', 'en');
            if ($locale !== $defaultLocale) {
                $value = $this->get($defaultLocale, $group, $key);
            }
            
            // If still null, return key as fallback
            if ($value === null) {
                $value = $group . '.' . $key;
            }
        }

        // Replace parameters
        if (!empty($params)) {
            foreach ($params as $param => $replacement) {
                $value = str_replace(':' . $param, $replacement, $value);
            }
        }

        return $value;
    }

    public function getGroup(string $locale, string $group): array
    {
        $cacheKey = $this->cachePrefix . $locale . ':' . $group;
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale, $group) {
            return Translation::forLocale($locale)
                ->forGroup($group)
                ->active()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function set(string $locale, string $group, string $key, ?string $value): void
    {
        Translation::updateOrCreate(
            ['locale' => $locale, 'group' => $group, 'key' => $key],
            ['value' => $value, 'is_active' => true]
        );

        // Clear cache for this locale and group
        $this->clearCache($locale, $group);
    }

    public function bulkSet(string $locale, string $group, array $translations): void
    {
        foreach ($translations as $key => $value) {
            $this->set($locale, $group, $key, $value);
        }
    }

    public function clearCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale && $group) {
            Cache::forget($this->cachePrefix . $locale . ':' . $group);
        } elseif ($locale) {
            // Clear all groups for locale (we'd need to track groups or use tags)
            // For now, clear specific known groups or use a broader approach
            $groups = ['auth', 'petition', 'home', 'nav', 'footer', 'errors', 'emails', 'common', 'profile', 'sign', 'show', 'thanks', 'myp', 'sig', 'contacts', 'create', 'form', 'pages', 'layout'];
            foreach ($groups as $g) {
                Cache::forget($this->cachePrefix . $locale . ':' . $g);
            }
        } else {
            // Clear all translation caches
            Cache::flush(); // Or use tags if available
        }
    }

    public function exportToJson(string $locale): array
    {
        $translations = Translation::forLocale($locale)->active()->get();
        
        $result = ['locale' => $locale, 'groups' => []];
        
        foreach ($translations as $t) {
            if (!isset($result['groups'][$t->group])) {
                $result['groups'][$t->group] = [];
            }
            $result['groups'][$t->group][$t->key] = $t->value;
        }
        
        return $result;
    }

    public function importFromJson(string $locale, array $data): int
    {
        $count = 0;
        
        if (isset($data['groups']) && is_array($data['groups'])) {
            foreach ($data['groups'] as $group => $translations) {
                if (is_array($translations)) {
                    foreach ($translations as $key => $value) {
                        $this->set($locale, $group, $key, $value);
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }

    public function getAllGroups(): array
    {
        return Translation::distinct()->pluck('group')->toArray();
    }

    public function getMissingKeys(string $locale, string $group): array
    {
        $defaultLocale = config('app.locale', 'en');
        
        $sourceKeys = Translation::forLocale($defaultLocale)
            ->forGroup($group)
            ->active()
            ->pluck('key')
            ->toArray();
            
        $targetKeys = Translation::forLocale($locale)
            ->forGroup($group)
            ->active()
            ->pluck('key')
            ->toArray();
            
        return array_diff($sourceKeys, $targetKeys);
    }

    public function copyFromSource(string $targetLocale, string $group, ?string $key = null): int
    {
        $defaultLocale = config('app.locale', 'en');
        $count = 0;
        
        $query = Translation::forLocale($defaultLocale)->forGroup($group)->active();
        
        if ($key) {
            $query->where('key', $key);
        }
        
        $sourceTranslations = $query->get();
        
        foreach ($sourceTranslations as $source) {
            $exists = Translation::where('locale', $targetLocale)
                ->where('group', $group)
                ->where('key', $source->key)
                ->exists();
                
            if (!$exists) {
                Translation::create([
                    'locale' => $targetLocale,
                    'group' => $group,
                    'key' => $source->key,
                    'value' => $source->value,
                    'is_active' => true,
                ]);
                $count++;
            }
        }
        
        $this->clearCache($targetLocale, $group);
        
        return $count;
    }
}
