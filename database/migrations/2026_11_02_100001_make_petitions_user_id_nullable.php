<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deleting a user must keep their petitions (and everyone's signatures on them).
        $this->dropUserForeign();

        Schema::table('petitions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropUserForeign();

        // Column stays nullable: orphaned petitions (user_id NULL) would make NOT NULL fail.
        Schema::table('petitions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    // Prod FK name may not be Laravel's default, so look it up by column (SQLite has no FK names).
    private function dropUserForeign(): void
    {
        foreach (Schema::getForeignKeys('petitions') as $fk) {
            if ($fk['columns'] === ['user_id']) {
                Schema::table('petitions', fn (Blueprint $table) => $table->dropForeign($fk['name'] ?? $fk['columns']));
            }
        }
    }
};
