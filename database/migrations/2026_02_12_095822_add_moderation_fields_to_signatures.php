<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {

            $table->text('text')->nullable()->after('locale');

            $table->boolean('confirmed')
                ->default(true)
                ->after('text');

            $table->string('ip_address', 45)
                ->nullable()
                ->after('confirmed');

            $table->boolean('is_spam')
                ->default(false)
                ->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            foreach (['text','confirmed','ip_address','is_spam'] as $col) {
                if (Schema::hasColumn('signatures', $col)) $table->dropColumn($col);
            }
        });
    }
};
