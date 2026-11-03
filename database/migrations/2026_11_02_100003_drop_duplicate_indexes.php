<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Each index is covered by a unique or composite index with the same leading column(s).
    private const INDEXES = [
        'users' => ['users_email_index' => ['email']],
        'signatures' => [
            'signatures_email_index' => ['email'],
            'signatures_confirmed_index' => ['confirmed'],
        ],
        'petition_translations' => ['petition_translations_petition_id_locale_index' => ['petition_id', 'locale']],
        'petitions' => ['petitions_status_index' => ['status']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
                }
            }
        }
    }
};
