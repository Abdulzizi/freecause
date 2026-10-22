<?php

namespace Tests\Feature;

use App\Models\BannedIp;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DbOutageResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_survives_a_failing_banned_ip_lookup(): void
    {
        $this->seed(LanguageSeeder::class);
        Schema::drop('banned_ips');
        Cache::flush();

        $this->get('/en')->assertOk();
    }

    public function test_banned_ip_still_gets_403(): void
    {
        $this->seed(LanguageSeeder::class);
        BannedIp::create(['ip' => '127.0.0.1', 'reason' => 'test']);
        Cache::flush();

        $this->get('/en/petitions')->assertForbidden();
    }
}
