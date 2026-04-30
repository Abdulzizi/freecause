<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = ['locale', 'group', 'key', 'value', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeForLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeForGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
