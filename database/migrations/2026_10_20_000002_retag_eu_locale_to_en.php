<?php

use App\Support\CacheVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->move('eu', 'en');
    }

    public function down(): void
    {
        $this->move('en', 'eu', $this->ids());
    }

    private function move(string $from, string $to, ?array $ids = null): void
    {
        $query = DB::table('petition_translations')->where('locale', $from);

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        } else {
            $query->whereIn('id', $this->ids());

            if ($this->hasCollision()) {
                return;
            }
        }

        $query->update(['locale' => $to]);

        $this->bumpCaches();
    }

    private function ids(): array
    {
        $path = database_path('data/legacy_eu_translation_ids.txt');

        if (! is_file($path)) {
            return [];
        }

        return array_filter(array_map('intval', file($path, FILE_IGNORE_NEW_LINES)));
    }

    private function hasCollision(): bool
    {
        return DB::table('petition_translations')
            ->where('locale', 'eu')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('petition_translations as o')
                ->whereColumn('o.slug', 'petition_translations.slug')
                ->where('o.locale', 'en'))
            ->exists();
    }

    private function bumpCaches(): void
    {
        try {
            CacheVersion::bumpAll();
        } catch (Throwable) {
        }
    }
};
