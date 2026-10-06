<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::created(fn (self $user) => Cache::forget('level:user_counts'));
        static::updated(function (self $user) {
            if ($user->wasChanged('level_id')) {
                Cache::forget('level:user_counts');
            }
        });
        static::deleted(function (self $user) {
            if ($user->level_id) {
                Cache::forget('level:user_counts');
            }
        });
    }

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
        'country',
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

    public function canAccessAdmin(): bool
    {
        $this->loadMissing('level');

        if (! $this->level_id || ! $this->level) {
            return false;
        }

        if ($this->level->is_system) {
            return true;
        }

        return Permission::where('level_id', $this->level_id)->exists();
    }

    public function getDisplayNameAttribute(): string
    {
        return match ($this->identify_mode) {
            'name' => $this->first_name ?: $this->name,
            'nick' => $this->nickname ?: $this->name,
            default => $this->name,
        };
    }

    public function hasLocalPassword(): bool
    {
        return $this->password_changed_at !== null;
    }

    public function isGoogleOnly(): bool
    {
        return filled($this->google_id) && ! $this->hasLocalPassword();
    }
}
