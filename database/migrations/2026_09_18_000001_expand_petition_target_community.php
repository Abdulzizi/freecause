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
            $table->string('target', 255)->nullable()->change();
            $table->string('community', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        $lengthFn = DB::connection()->getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';

        $tooLong = DB::table('petitions')
            ->whereRaw("{$lengthFn}(target) > 190")
            ->orWhereRaw("{$lengthFn}(community) > 190")
            ->exists();

        if ($tooLong) {
            throw new RuntimeException('Cannot roll back: petitions.target or petitions.community contains values longer than 190 characters.');
        }

        Schema::table('petitions', function (Blueprint $table) {
            $table->string('target', 190)->nullable()->change();
            $table->string('community', 190)->nullable()->change();
        });
    }
};
