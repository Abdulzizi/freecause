<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->whereIn('key', ['facebook_app_id', 'facebook_secret'])->delete();
    }

    public function down(): void
    {
        // Facebook OAuth was removed — no rollback needed
    }
};
