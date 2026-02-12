<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [];

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $this->hasOne(PageTranslation::class)
            ->where('locale', $locale);
    }
}
