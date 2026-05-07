<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $backupDir = storage_path('app/translation-backup');
        File::ensureDirectoryExists($backupDir);

        if (Schema::hasTable('language_lines')) {
            $rows = DB::table('language_lines')->get()->toArray();
            File::put(
                "{$backupDir}/language_lines-backup-" . date('Y-m-d') . ".json",
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            Schema::dropIfExists('language_lines');
        }

        if (Schema::hasTable('translations')) {
            $rows = DB::table('translations')->get()->toArray();
            File::put(
                "{$backupDir}/translations-backup-" . date('Y-m-d') . ".json",
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            Schema::dropIfExists('translations');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('language_lines')) {
            Schema::create('language_lines', function ($table) {
                $table->id();
                $table->string('group');
                $table->string('key');
                $table->json('text');
                $table->timestamps();
                $table->unique(['group', 'key']);
            });
        }

        if (! Schema::hasTable('translations')) {
            Schema::create('translations', function ($table) {
                $table->id();
                $table->string('locale', 10)->index();
                $table->string('group', 100)->index();
                $table->string('key', 255);
                $table->text('value')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['locale', 'group', 'key']);
            });
        }
    }
};
