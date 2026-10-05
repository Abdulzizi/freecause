<?php

use App\Models\PageTranslation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const SLUGS = ['privacy-policy'];

    public function up(): void
    {
        PageTranslation::whereIn('slug', self::SLUGS)
            ->get()
            ->each(function (PageTranslation $translation) {
                $content = (string) $translation->content;

                if (! str_contains($content, 'petitions-list') || ! str_contains($content, 'card-body')) {
                    return;
                }

                $stripped = preg_replace(
                    '/^\s*<section[^>]*petitions-list[^>]*>\s*<div[^>]*container[^>]*>\s*<div[^>]*\brow\b[^>]*>\s*<div[^>]*col-md-12[^>]*>\s*<div[^>]*\bcard\b[^>]*>\s*<div[^>]*card-body[^>]*>\s*(?:<div[^>]*mb-4[^>]*>)?/s',
                    '',
                    $content,
                    1
                );

                $stripped = preg_replace(
                    '/(?:<\/div>\s*)?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/section>\s*$/s',
                    '',
                    $stripped,
                    1
                );

                if ($stripped !== null && $stripped !== $content) {
                    $translation->content = trim($stripped);
                    $translation->save();
                }
            });
    }

    public function down(): void
    {
        PageTranslation::whereIn('slug', self::SLUGS)
            ->get()
            ->each(function (PageTranslation $translation) {
                $content = (string) $translation->content;

                if (str_contains($content, 'petitions-list')) {
                    return;
                }

                $wrapped = '<section class="petitions-list py-5">'."\n"
                    .'    <div class="container">'."\n"
                    .'        <div class="row">'."\n"
                    .'            <div class="col-md-12">'."\n"
                    .'                <div class="card">'."\n"
                    .'                    <div class="card-body">'."\n"
                    .'                        <div class="mb-4">'."\n"
                    .$content."\n"
                    .'                        </div>'."\n"
                    .'                    </div>'."\n"
                    .'                </div>'."\n"
                    .'            </div>'."\n"
                    .'        </div>'."\n"
                    .'    </div>'."\n"
                    .'</section>';

                $translation->content = $wrapped;
                $translation->save();
            });
    }
};
