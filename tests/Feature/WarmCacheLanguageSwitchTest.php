<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WarmCacheLanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_warmed_active_languages_cache_renders_language_switch(): void
    {
        $this->seed(LanguageSeeder::class);

        Artisan::call('cache:warm');

        $this->assertInstanceOf(Collection::class, Cache::get('active_languages_full'));

        $this->get('/en')->assertOk();
    }
}
