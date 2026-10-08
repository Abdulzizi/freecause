<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasTranslations;

    protected $fillable = [];

    protected function translationTable(): string
    {
        return 'page_translations';
    }

    protected function translationForeignKey(): string
    {
        return 'page_id';
    }

    protected function translationModel(): string
    {
        return PageTranslation::class;
    }
}
