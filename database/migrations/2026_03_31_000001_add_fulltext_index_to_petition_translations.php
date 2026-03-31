<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE petition_translations ADD FULLTEXT INDEX ft_petition_title (title)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE petition_translations DROP INDEX ft_petition_title');
    }
};
