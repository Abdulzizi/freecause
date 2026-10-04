<?php

use App\Models\PageTranslation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const SLUGS = ['terms-of-service', 'privacy-policy', 'ethical-code'];

    private const SEARCH = 'xPetition';

    private const REPLACE = 'FreeCause';

    public function up(): void
    {
        $this->rewrite(self::SEARCH, self::REPLACE);
    }

    public function down(): void
    {
        $this->rewrite(self::REPLACE, self::SEARCH);
    }

    private function rewrite(string $from, string $to): void
    {
        PageTranslation::whereIn('slug', self::SLUGS)
            ->get()
            ->each(function (PageTranslation $translation) use ($from, $to) {
                $content = (string) $translation->content;

                if (! str_contains($content, $from)) {
                    return;
                }

                $translation->content = str_replace($from, $to, $content);
                $translation->save();
            });
    }
};
