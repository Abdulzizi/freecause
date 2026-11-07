<?php

use App\Support\CacheVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_translations')->where('slug', 'privacy-policy-12')->update(['published' => 0]);

        CacheVersion::bumpSitemap();
    }

    public function down(): void
    {
        DB::table('page_translations')->where('slug', 'privacy-policy-12')->update(['published' => 1]);

        CacheVersion::bumpSitemap();
    }
};
