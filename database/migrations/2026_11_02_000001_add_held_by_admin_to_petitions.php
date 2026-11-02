<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->boolean('held_by_admin')->default(false)->after('status');
        });

        // Old admin holds cannot be told apart from other drafts, so all stay held.
        DB::table('petitions')->where('status', 'draft')->update(['held_by_admin' => 1]);
    }

    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn('held_by_admin');
        });
    }
};
