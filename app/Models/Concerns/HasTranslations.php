<?php

namespace App\Models\Concerns;

use App\Support\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasTranslations
{
    public function translations()
    {
        return $this->hasMany($this->translationModel(), $this->translationForeignKey());
    }

    public function translation(?string $locale = null): ?Model
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    public function translationOrFallback(?string $locale = null, ?string $fallbackLocale = null): ?Model
    {
        $locale ??= app()->getLocale();

        $translation = $this->translation($locale);

        if ($translation) {
            return $translation;
        }

        if ($fallbackLocale !== null && $fallbackLocale !== $locale) {
            $translation = $this->translation($fallbackLocale);

            if ($translation) {
                return $translation;
            }
        }

        if ($this->relationLoaded('translations')) {
            return $this->translations->sortBy('id')->first();
        }

        return $this->translations()->orderBy('id')->first();
    }

    public function scopeTranslated($query, string $locale, string $localeAlias, string $defaultAlias, array $columns, ?string $defaultLocale = null)
    {
        return $query->withTranslation([
            'base' => $this->getTable(),
            'base_key' => $this->getKeyName(),
            'translation_table' => $this->translationTable(),
            'translation_key' => $this->translationForeignKey(),
            'locale_alias' => $localeAlias,
            'default_alias' => $defaultAlias,
            'locale' => $locale,
            'default_locale' => $defaultLocale ?? default_locale(),
            'columns' => $columns,
        ]);
    }

    public function uniqueTranslationSlug(string $value, string $locale, ?int $ignoreId = null, ?int $maxLength = null): string
    {
        $model = $this->translationModel();

        return Slug::dedupe(
            $value,
            fn (string $slug) => $model::query()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists(),
            Str::snake(class_basename($this)),
            $maxLength
        );
    }

    abstract protected function translationTable(): string;

    abstract protected function translationForeignKey(): string;

    abstract protected function translationModel(): string;
}
