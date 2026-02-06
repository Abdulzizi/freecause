<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (Schema::hasColumn('petitions', 'title')) $table->dropColumn('title');

            if (Schema::hasColumn('petitions', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('petitions', 'description')) $table->dropColumn('description');

            if (Schema::hasColumn('petitions', 'locale')) {
                $table->dropColumn('locale');
            }
        });
    }


    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (!Schema::hasColumn('petitions', 'title')) {
                $table->string('title');
            }

            if (!Schema::hasColumn('petitions', 'slug')) {
                $table->string('slug')->unique();
            }

            if (!Schema::hasColumn('petitions', 'description')) {
                $table->text('description');
            }

            if (!Schema::hasColumn('petitions', 'locale')) {
                $table->string('locale', 5)->default('en');
            }

            if (!Schema::hasIndex('petitions', 'petitions_locale_status_index')) {
                $table->index(['locale', 'status']);
            }
        });
    }
};
