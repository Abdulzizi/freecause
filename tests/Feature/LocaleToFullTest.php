<?php

namespace Tests\Feature;

use App\Support\Locale;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocaleToFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_full_does_not_query_db_on_warm_cache(): void
    {
        $this->seed(LanguageSeeder::class);
        Locale::toFull('en');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertSame(config('language_flags')['fr'] ?? 'en_US', Locale::toFull('fr'));
        Locale::toFull('xx');

        $this->assertSame([], DB::getQueryLog());
    }
}
