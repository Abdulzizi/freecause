<?php

use App\Support\PetitionCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CHUNK = 500;

    public function up(): void
    {
        $this->move('en', false);
        $this->bumpCaches();
    }

    public function down(): void
    {
        $this->move('en', true);
        $this->bumpCaches();
    }

    private function move(string $baseLocale, bool $reverse): void
    {
        $moves = [];

        foreach ($this->mappedLocales() as $petitionId => $locale) {
            $moves[] = $reverse
                ? [$petitionId, $locale, $baseLocale]
                : [$petitionId, $baseLocale, $locale];
        }

        foreach (array_chunk($moves, self::CHUNK) as $chunk) {
            $ids = array_column($chunk, 0);
            $fromLocales = array_values(array_unique(array_column($chunk, 1)));
            $toLocales = array_values(array_unique(array_column($chunk, 2)));

            $plans = [];

            foreach ($chunk as [$id, $fromLocale, $toLocale]) {
                $plans[$id.'|'.$fromLocale] = $toLocale;
            }

            $rows = DB::table('petition_translations')
                ->whereIn('petition_id', $ids)
                ->whereIn('locale', $fromLocales)
                ->get(['id', 'petition_id', 'locale', 'slug']);

            if ($rows->isEmpty()) {
                continue;
            }

            $claims = DB::table('petition_translations')
                ->whereIn('petition_id', $ids)
                ->get(['petition_id', 'locale'])
                ->mapWithKeys(fn ($row) => [$row->petition_id.'|'.$row->locale => true])
                ->all();

            $taken = DB::table('petition_translations')
                ->whereIn('locale', $toLocales)
                ->whereIn('slug', $rows->pluck('slug')->all())
                ->get(['locale', 'slug'])
                ->mapWithKeys(fn ($row) => [$row->locale.'|'.$row->slug => true])
                ->all();

            foreach ($rows as $row) {
                $target = $plans[$row->petition_id.'|'.$row->locale] ?? null;

                if ($target === null
                    || isset($claims[$row->petition_id.'|'.$target])
                    || isset($taken[$target.'|'.$row->slug])) {
                    continue;
                }

                DB::table('petition_translations')->where('id', $row->id)->update(['locale' => $target]);

                $claims[$row->petition_id.'|'.$target] = true;
                $taken[$target.'|'.$row->slug] = true;
            }
        }
    }

    private function mappedLocales(): array
    {
        $path = database_path('data/legacy_petition_locales.csv');

        if (! is_file($path)) {
            return [];
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $map = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2 || ! is_numeric($row[0])) {
                continue;
            }

            $map[(int) $row[0]] = (string) $row[1];
        }

        fclose($handle);

        return $map;
    }

    private function bumpCaches(): void
    {
        try {
            PetitionCache::bump();
        } catch (Throwable) {
        }
    }
};
