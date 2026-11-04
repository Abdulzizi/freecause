<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['is_active', 'sort_order'];

    public static function cachedActive(string $locale)
    {
        return cache()->remember(CacheKeys::homeCategories($locale), 43200, function () use ($locale) {
            return self::select(['categories.id', 'categories.sort_order'])
                ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'tr_name', 'slug' => 'tr_slug'])
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });
    }

    protected function translationTable(): string
    {
        return 'category_translations';
    }

    protected function translationForeignKey(): string
    {
        return 'category_id';
    }

    protected function translationModel(): string
    {
        return CategoryTranslation::class;
    }

    public function translation(string $locale)
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?? $this->translations->firstWhere('locale', default_locale())
                ?? $this->translations->first();
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?? $this->translations()->where('locale', default_locale())->first()
            ?? $this->translations()->orderBy('id')->first();
    }

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }
}
