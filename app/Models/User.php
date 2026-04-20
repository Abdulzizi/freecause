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
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'verification_token_sent_at' => 'datetime',
        'verified' => 'boolean',
        'banned_until' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
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

    public function isTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_secret;
    }

    public function enableTwoFactor(string $secret): void
    {
        $this->two_factor_secret = $secret;
        $this->two_factor_enabled = true;
        $this->save();
    }

    public function disableTwoFactor(): void
    {
        $this->two_factor_secret = null;
        $this->two_factor_enabled = false;
        $this->two_factor_confirmed_at = null;
        $this->save();
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (! $this->two_factor_secret) {
            return false;
        }

        return $this->totp->verify($code, window: 1);
    }
}
