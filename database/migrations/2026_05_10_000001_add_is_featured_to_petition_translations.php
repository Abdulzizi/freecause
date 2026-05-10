<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        DB::statement("
            UPDATE petition_translations pt
            JOIN petitions p ON p.id = pt.petition_id
            SET pt.is_featured = 1
            WHERE p.is_featured = 1
        ");
    }

    public function down(): void
    {
        Schema::table('petition_translations', function (Blueprint $table) {
            $table->dropIndex('pt_locale_featured_idx');
            $table->dropColumn('is_featured');
        });
    }
};
