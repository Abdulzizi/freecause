<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'locale',
        'ip',
        'level',
        'verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }
}
