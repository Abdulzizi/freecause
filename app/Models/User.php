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
        'verified',
        'verification_token',
        'verification_token_sent_at',
        'level_id',
        'identify_mode',
        'city',
        'nickname',
        'google_id',
        'facebook_id',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'verification_token_sent_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'level_id');
    }

    public function hasLevel(string $name): bool
    {
        return $this->level?->name === $name;
    }

    public function getDisplayNameAttribute(): string
    {
        return match ($this->identify_mode) {
            'name' => $this->first_name ?: $this->name,
            'nick' => $this->nickname ?: $this->name,
            default => $this->name,
        };
    }
}
