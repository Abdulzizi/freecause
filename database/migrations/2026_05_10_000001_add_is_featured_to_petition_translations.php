<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petition_translations', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('slug');
            $table->index(['locale', 'is_featured'], 'pt_locale_featured_idx');
        });

        // Seed: any petition currently marked is_featured globally → mark all its
        // existing translations as featured too (preserves existing admin intent).
        DB::table('petition_translations')
            ->whereIn('petition_id', DB::table('petitions')->where('is_featured', 1)->select('id'))
            ->update(['is_featured' => 1]);
    }

    public function down(): void
    {
        Schema::table('petition_translations', function (Blueprint $table) {
            $table->dropIndex('pt_locale_featured_idx');
            $table->dropColumn('is_featured');
        });
    }
};
