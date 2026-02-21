<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    protected $fillable = [
        'name',
        'visible_name',
        'is_system',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'level_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'level_id');
    }
}
