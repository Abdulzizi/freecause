<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['is_active', 'sort_order'];

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
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
            ?? $this->translations()->first();
    }

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }
}
