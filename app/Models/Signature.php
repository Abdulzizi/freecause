<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'petition_id',
        'user_id',
        'name',
        'email',
        'locale',
        'text',
        'ip_address',
    ];

    public function petition()
    {
        return $this->belongsTo(Petition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
