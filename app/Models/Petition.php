<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Petition extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_count',
        'user_id',
        'status',
        'goal_signatures',
        'category_id',
        'target',
        'tags',
        'city',
        'community',
        'community_url',
        'youtube_url',
        'image_url',
        'cover_image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function translations()
    {
        return $this->hasMany(PetitionTranslation::class);
    }

    public function coverUrl(): string
    {
        if ($this->cover_image) {
            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
                return $this->cover_image;
            }

            return Storage::disk('public')->url($this->cover_image);
        }

        if ($this->image_url) {
            return $this->image_url;
        }

        $n = ($this->id ? ($this->id % 14) : 0) + 1; // 1..14
        $path = public_path("legacy/images/petitions/covers/pic{$n}.jpg");

        if (file_exists($path)) {
            return asset("legacy/images/petitions/covers/pic{$n}.jpg");
        }

        return asset('legacy/images/demo-featured.jpg');
    }

    public function translation(?string $locale = null): ?PetitionTranslation
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    public function translationOrFallback(?string $locale = null): ?PetitionTranslation
    {
        $locale ??= app()->getLocale();

        $t = $this->translation($locale);
        if ($t) return $t;

        if ($this->relationLoaded('translations')) {
            return $this->translations->sortBy('id')->first();
        }

        return $this->translations()->orderBy('id')->first();
    }

    public function scopeVisible($q)
    {
        return $q->where('status', 'published')
            ->where('is_active', 1);
    }

    public function slugFor(?string $locale = null): ?string
    {
        return $this->translationOrFallback($locale)?->slug;
    }
    public function titleFor(?string $locale = null): ?string
    {
        return $this->translationOrFallback($locale)?->title;
    }
    public function descriptionFor(?string $locale = null): ?string
    {
        return $this->translationOrFallback($locale)?->description;
    }
}
