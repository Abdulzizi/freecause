<?php

namespace Tests\Unit;

use App\Support\Spam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['request']->setTrustedProxies(['127.0.0.1'], Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST);
    }

    public function test_spam_detects_bad_keywords(): void
    {
        $this->assertTrue(Spam::isSpam('Buy v1agra online now'));
        $this->assertTrue(Spam::isSpam('cas1no online gambling'));
        $this->assertTrue(Spam::isSpam('Free money at paypal'));
        $this->assertTrue(Spam::isSpam('cheap loan approved'));
    }

    public function test_spam_allows_normal_content(): void
    {
        $this->assertFalse(Spam::isSpam('I support this petition to save the whales'));
        $this->assertFalse(Spam::isSpam('Please sign my petition for cleaner streets'));
        $this->assertFalse(Spam::isSpam('Join me in supporting local businesses'));
    }

    public function test_spam_detects_multiple_urls(): void
    {
        $this->assertTrue(Spam::isSpam('Check out http://spam.com and http://more-spam.com'));
    }

    public function test_spam_allows_single_url(): void
    {
        $this->assertFalse(Spam::isSpam('Sign my petition at http://petition.com'));
    }

    public function test_spam_rate_limit_allows_normal_requests(): void
    {
        Spam::rateLimit('petition', 5);

        $this->assertFalse(Spam::rateLimit('petition', 5));
    }

    public function test_spam_rate_limit_blocks_excessive_requests(): void
    {
        for ($i = 0; $i < 20; $i++) {
            Spam::rateLimit('petition', 20);
        }

        $this->assertTrue(Spam::rateLimit('petition', 20));
    }

    public function test_spam_ban_current_ip(): void
    {
        Spam::banCurrentIp('Test ban');

        $this->assertDatabaseHas('banned_ips', [
            'ip' => request()->ip(),
        ]);
    }

    public function test_spam_log_creates_record(): void
    {
        Spam::log('test_type', 'test payload');

        $this->assertDatabaseHas('spam_logs', [
            'type' => 'test_type',
            'ip' => request()->ip(),
        ]);
    }

    public function test_spam_log_stores_a_payload_longer_than_the_old_byte_cap(): void
    {
        $payload = str_repeat('a', 5000);

        Spam::log('test_type', $payload);

        $stored = DB::table('spam_logs')
            ->where('type', 'test_type')
            ->orderByDesc('id')
            ->value('payload');

        $this->assertSame($payload, $stored);
    }

    public function test_spam_log_cuts_a_multi_byte_payload_on_a_character_boundary(): void
    {
        $payload = str_repeat('€', 30000);

        Spam::log('test_type', $payload);

        $stored = (string) DB::table('spam_logs')
            ->where('type', 'test_type')
            ->orderByDesc('id')
            ->value('payload');

        $this->assertTrue(mb_check_encoding($stored, 'UTF-8'), 'The stored payload must stay valid UTF-8.');
        $this->assertNotSame($payload, $stored, 'The payload must be cut at the cap.');
        $this->assertSame(
            0,
            strlen($stored) % 3,
            'The stored payload must not end in the middle of a three byte character.'
        );
        $this->assertSame(
            mb_substr($payload, 0, mb_strlen($stored)),
            $stored,
            'The stored payload must be a whole character prefix of the original.'
        );
    }
}
