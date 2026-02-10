<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');

            $table->string('ip', 45)->nullable()->after('locale');

            $table->string('level', 30)->default('user')->after('ip');

            $table->boolean('verified')->default(false)->after('level');

            $table->string('locale', 10)->default('en_US')->change();

            $table->index('email');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('ip');
            $table->index('level');
            $table->index('locale');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['ip']);
            $table->dropIndex(['level']);
            $table->dropIndex(['locale']);
            $table->dropIndex(['created_at']);

            $table->dropColumn(['first_name', 'last_name', 'ip', 'level', 'verified']);

            $table->string('locale', 5)->default('en')->change();
        });
    }
};
