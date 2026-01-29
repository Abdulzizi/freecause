<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureModel extends Model
{
    protected $fillable = [
        'petition_id',
        'user_id',
        'name',
        'email',
    ];

    public function petition()
    {
        return $this->belongsTo(PetitionModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
