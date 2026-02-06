<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetitionTranslation extends Model
{
    protected $fillable = [
        'petition_id',
        'locale',
        'title',
        'slug',
        'description',
    ];

    public function petition()
    {
        return $this->belongsTo(Petition::class);
    }
}
