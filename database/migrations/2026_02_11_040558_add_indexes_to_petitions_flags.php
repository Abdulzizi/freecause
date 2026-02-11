<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['status']);
        });
    }
};
