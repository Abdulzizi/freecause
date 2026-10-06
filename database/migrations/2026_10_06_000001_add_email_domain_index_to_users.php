<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'email_domain')) {
            return;
        }

        $expression = match (DB::getDriverName()) {
            'mysql', 'mariadb' => "LOWER(SUBSTRING_INDEX(email, '@', -1))",
            'sqlite' => "LOWER(SUBSTR(email, INSTR(email, '@') + 1))",
            default => throw new RuntimeException('Unsupported database driver for users.email_domain.'),
        };

        Schema::table('users', function (Blueprint $table) use ($expression) {
            $table->string('email_domain', 190)
                ->virtualAs($expression);
            $table->index('email_domain', 'users_email_domain_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'email_domain')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_domain_index');
            $table->dropColumn('email_domain');
        });
    }
};
