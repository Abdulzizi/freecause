<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function petitions()
    {
        return $this->hasMany(PetitionModel::class);
    }

    public function signatures()
    {
        return $this->hasMany(SignatureModel::class);
    }
}
