<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const FROM = 'ETHICAL_CODE_LINK';

    private const REPLACEMENTS = [
        'href="%7BETHICAL_CODE_LINK%7D"' => 'href="{ETHICAL_CODE_URL}"',
        'href="{ETHICAL_CODE_LINK}"' => 'href="{ETHICAL_CODE_URL}"',
    ];

    public function up(): void
    {
        $this->replace(self::REPLACEMENTS);
    }

    public function down(): void
    {
        $this->replace([
            'href="{ETHICAL_CODE_URL}"' => 'href="{ETHICAL_CODE_LINK}"',
        ]);
    }

    private function replace(array $map): void
    {
        foreach (['page_translations' => 'content', 'page_contents' => 'value'] as $table => $column) {
            DB::table($table)
                ->where($column, 'like', '%'.self::FROM.'%')
                ->select(['id', $column])
                ->orderBy('id')
                ->chunk(200, function ($rows) use ($table, $column, $map) {
                    foreach ($rows as $row) {
                        $value = str_replace(array_keys($map), array_values($map), (string) $row->{$column});

                        if ($value !== (string) $row->{$column}) {
                            DB::table($table)->where('id', $row->id)->update([$column => $value]);
                        }
                    }
                });
        }
    }
};
