<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PetitionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'petition_id',
        'locale',
        'title',
        'slug',
        'description',
        'is_featured',
    ];

    public static function booleanTerms(string $text): string
    {
        return Str::squish(preg_replace('/[+\-><()~*"@]/', ' ', $text));
    }

    public function petition()
    {
        return $this->belongsTo(Petition::class);
    }
}
