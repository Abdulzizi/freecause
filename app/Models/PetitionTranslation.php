<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function petition()
    {
        return $this->belongsTo(Petition::class);
    }
}
