<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PETITION_ID = 17265;

    private const TRANSLATION_ID = 24713;

    private const TITLE_PREFIX = 'शिक्षा';

    public function up(): void
    {
        if (! $this->isTarget()) {
            return;
        }

        DB::table('petitions')
            ->where('id', self::PETITION_ID)
            ->where('is_featured', 1)
            ->update(['is_featured' => 0]);
    }

    public function down(): void
    {
        if (! $this->isTarget()) {
            return;
        }

        if (DB::table('petitions')->where('id', self::PETITION_ID)->where('is_featured', 1)->exists()) {
            return;
        }

        DB::table('petitions')
            ->where('id', self::PETITION_ID)
            ->update(['is_featured' => 1]);
    }

    private function isTarget(): bool
    {
        return DB::table('petition_translations')
            ->where('id', self::TRANSLATION_ID)
            ->where('petition_id', self::PETITION_ID)
            ->where('locale', 'en')
            ->where('title', 'like', self::TITLE_PREFIX.'%')
            ->exists();
    }
};
