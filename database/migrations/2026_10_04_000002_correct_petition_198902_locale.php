<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PETITION_ID = 198902;

    private const TRANSLATION_ID = 174190;

    private const SLUG = 'peti-o-para-substitui-o-de-transformadores-com-sobrecarga-na-rede-el-trica';

    public function up(): void
    {
        $translation = DB::table('petition_translations')
            ->where('id', self::TRANSLATION_ID)
            ->where('petition_id', self::PETITION_ID)
            ->where('locale', 'en')
            ->where('slug', self::SLUG)
            ->first();

        if (! $translation || DB::table('petition_translations')
            ->where('petition_id', self::PETITION_ID)
            ->where('locale', 'pt')
            ->exists()) {
            return;
        }

        DB::table('petition_translations')
            ->where('id', self::TRANSLATION_ID)
            ->update(['locale' => 'pt']);
    }

    public function down(): void
    {
        $translation = DB::table('petition_translations')
            ->where('id', self::TRANSLATION_ID)
            ->where('petition_id', self::PETITION_ID)
            ->where('locale', 'pt')
            ->where('slug', self::SLUG)
            ->first();

        if (! $translation || DB::table('petition_translations')
            ->where('petition_id', self::PETITION_ID)
            ->where('locale', 'en')
            ->exists()) {
            return;
        }

        DB::table('petition_translations')
            ->where('id', self::TRANSLATION_ID)
            ->update(['locale' => 'en']);
    }
};
