<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {

            if (!Schema::hasColumn('petitions', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('status');
            }

            if (!Schema::hasColumn('petitions', 'is_featured')) {
                $table->boolean('is_featured')
                    ->default(false)
                    ->after('is_active');
            }
        });

        DB::table('petitions')
            ->whereNull('is_active')
            ->update(['is_active' => 1]);

        DB::table('petitions')
            ->whereNull('is_featured')
            ->update(['is_featured' => 0]);
    }

    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {

            if (Schema::hasColumn('petitions', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('petitions', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
