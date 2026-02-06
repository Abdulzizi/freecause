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
            if (Schema::hasColumn('petitions', 'slug')) $table->dropUnique(['slug']);
            if (Schema::hasColumn('petitions', 'slug')) $table->dropColumn('slug');
            if (Schema::hasColumn('petitions', 'description')) $table->dropColumn('description');
            if (Schema::hasColumn('petitions', 'locale')) $table->dropIndex(['locale', 'status']);
            if (Schema::hasColumn('petitions', 'locale')) $table->dropColumn('locale');
        });
    }

    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('locale', 5)->default('en');

            $table->index(['locale', 'status']);
        });
    }
};
