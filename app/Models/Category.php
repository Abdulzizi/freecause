<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // protected $with = ['translations'];
    protected $fillable = ['is_active', 'sort_order'];

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(string $locale)
    {
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en')
            ?? $this->translations->first();
    }

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }
}
