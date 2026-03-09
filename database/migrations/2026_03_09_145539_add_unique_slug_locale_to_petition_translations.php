<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dupes = \Illuminate\Support\Facades\DB::select("
            SELECT id, petition_id, locale, slug
            FROM petition_translations
            WHERE (locale, slug) IN (
                SELECT locale, slug
                FROM petition_translations
                GROUP BY locale, slug
                HAVING COUNT(*) > 1
            )
            ORDER BY locale, slug, id
        ");

        $seen = [];
        foreach ($dupes as $row) {
            $key = $row->locale . '|' . $row->slug;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
            } else {
                \Illuminate\Support\Facades\DB::table('petition_translations')
                    ->where('id', $row->id)
                    ->update(['slug' => $row->slug . '-' . $row->id]);
            }
        }

        Schema::table('petition_translations', function (Blueprint $table) {
            $table->unique(['locale', 'slug'], 'petition_translations_locale_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('petition_translations', function (Blueprint $table) {
            $table->dropUnique('petition_translations_locale_slug_unique');
        });
    }
};