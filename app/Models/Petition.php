<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Petition extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'goal_signatures',
        'category_id',
        'target',
        'tags',
        'city',
        'country',
        'community',
        'community_url',
        'youtube_url',
        'image_url',
        'cover_image',
    ];

    protected $casts = [
        'last_notified_at' => 'datetime',
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

    protected function translationTable(): string
    {
        return 'petition_translations';
    }

    protected function translationForeignKey(): string
    {
        return 'petition_id';
    }

    protected function translationModel(): string
    {
        return PetitionTranslation::class;
    }

    public function coverUrl(): string
    {
        if ($this->cover_image) {
            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
                return $this->cover_image;
            }

            $path = Storage::disk('public')->path($this->cover_image);
            if (file_exists($path)) {
                return Storage::disk('public')->url($this->cover_image);
            }
        }

        if ($this->image_url) {
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }

            $path = Storage::disk('public')->path("petitions/{$this->image_url}");
            if (file_exists($path)) {
                return Storage::disk('public')->url("petitions/{$this->image_url}");
            }
        }

        $n = ($this->id ? ($this->id % 7) : 0) + 1;
        $legacyPath = public_path("legacy/images/pic{$n}.jpg");

        if (file_exists($legacyPath)) {
            return asset("legacy/images/pic{$n}.jpg");
        }

        return asset('legacy/images/demo-featured.jpg');
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
