<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToLanguageLines extends Command
{
    protected $signature = 'translations:migrate-to-language-lines';
    protected $description = 'Migrate translations from custom table to Spatie language_lines';

    public function handle(): int
    {
        $this->info('Migrating translations to language_lines...');

        $translations = DB::table('translations')
            ->where('is_active', true)
            ->get();

        $grouped = [];
        foreach ($translations as $t) {
            $key = "{$t->group}.{$t->key}";
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'group' => $t->group,
                    'key' => $t->key,
                    'text' => [],
                ];
            }
            $grouped[$key]['text'][$t->locale] = $t->value;
        }

        $count = 0;
        foreach ($grouped as $data) {
            DB::table('language_lines')->insert([
                'group' => $data['group'],
                'key' => $data['key'],
                'text' => json_encode($data['text']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("Migrated {$count} translation keys to language_lines.");
        return 0;
    }
}
