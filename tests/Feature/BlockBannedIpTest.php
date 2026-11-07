<?php

namespace Tests\Feature;

use App\Models\BannedIp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlockBannedIpTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_ip_is_blocked_every_time_but_logged_once(): void
    {
        BannedIp::create(['ip' => '9.9.9.9']);

        $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])->get('/')->assertForbidden();
        $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])->get('/')->assertForbidden();

        $this->assertSame(1, DB::table('spam_logs')->where('type', 'blocked')->where('ip', '9.9.9.9')->count());
    }
}
