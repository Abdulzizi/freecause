<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_levels')
            ->where('name', '!=', 'admin')
            ->where('is_system', 1)
            ->update(['is_system' => 0]);
    }

    public function down(): void
    {
    }
};
