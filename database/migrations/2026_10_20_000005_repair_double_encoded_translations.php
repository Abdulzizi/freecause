<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MARKERS = ['Ã§', 'Ã£', 'Ã©', 'Ã³', 'Ã¡', 'Ã­', 'Ãº', 'Ã¢', 'Ãµ', 'Ã´'];

    public function up(): void
    {
        $this->repair('title');
        $this->repair('description');
    }

    public function down(): void {}

    private function repair(string $column): void
    {
        DB::table('petition_translations as t')
            ->join('petitions as p', 'p.id', '=', 't.petition_id')
            ->where('t.locale', 'en')
            ->whereNot(function ($q) {
                $q->where('p.is_active', 0)->where('p.status', 'draft');
            })
            ->select('t.id', 't.'.$column.' as '.$column)
            ->orderBy('t.id')
            ->chunk(500, function ($rows) use ($column) {
                foreach ($rows as $row) {
                    $value = $row->{$column};

                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    if (! $this->hasMarker($value)) {
                        continue;
                    }

                    $fixed = $this->decode($value);

                    if ($fixed === null || $this->hasMarker($fixed)) {
                        continue;
                    }

                    DB::table('petition_translations')->where('id', $row->id)->update([$column => $fixed]);
                }
            });
    }

    private function hasMarker(string $value): bool
    {
        foreach (self::MARKERS as $marker) {
            if (str_contains($value, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function decode(string $value): ?string
    {
        $bytes = @mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');

        if ($bytes === false || $bytes === '') {
            return null;
        }

        $decoded = @mb_convert_encoding($bytes, 'UTF-8', 'ISO-8859-1');

        if ($decoded === false) {
            return null;
        }

        return $decoded;
    }
};
