<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_default',
        // 'flag',
    ];

    public function getFlagAttribute()
    {
        $map = config('language_flags');
        $key = $map[$this->code] ?? 'en_US';

        $path = 'legacy/images/country-flags/rounded1/' . $key . '.png';

        return file_exists(public_path($path))
            ? $path
            : 'legacy/images/country-flags/rounded1/en_US.png';
    }
}
