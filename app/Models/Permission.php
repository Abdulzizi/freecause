<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'level_id',
        'module',
        'action',
    ];

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'level_id');
    }
}
