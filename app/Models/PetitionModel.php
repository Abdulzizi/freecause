<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetitionModel extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'goal_signatures',
        'signature_count',
        'status',
        'locale',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }
}
