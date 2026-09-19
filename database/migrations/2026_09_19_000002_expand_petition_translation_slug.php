<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petition_translations', function (Blueprint $table) {
            $table->string('slug', 255)->change();
        });
    }

    public function down(): void
    {
        $lengthFn = DB::connection()->getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';

        $tooLong = DB::table('petition_translations')
            ->whereRaw("{$lengthFn}(slug) > 220")
            ->exists();

        if ($tooLong) {
            throw new RuntimeException('Cannot roll back: petition_translations.slug contains values longer than 220 characters.');
        }

        Schema::table('petition_translations', function (Blueprint $table) {
            $table->string('slug', 220)->change();
        });
    }
};
