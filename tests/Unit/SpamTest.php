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
    }

    public function test_a_review_owned_word_is_ignored_as_a_whole_word(): void
    {
        $owned = Spam::reviewOwned();

        $this->assertContains('porn', $owned);
        $this->assertContains('escort', $owned);
        $this->assertContains('casino', $owned);

        $this->assertFalse(Spam::isSpam('porn', $owned));
        $this->assertFalse(Spam::isSpam('escort', $owned));
        $this->assertFalse(Spam::isSpam('casino', $owned));
    }

    public function test_a_derived_review_owned_wording_still_counts_as_spam(): void
    {
        $owned = Spam::reviewOwned();

        $this->assertTrue(Spam::isSpam('pornographic', $owned));
        $this->assertTrue(Spam::isSpam('escorted', $owned));
        $this->assertTrue(Spam::isSpam('porns', ['porn']));
        $this->assertTrue(Spam::isSpam('porn pornographic', $owned));
    }

    public function test_the_default_ignore_list_keeps_blocking_the_review_owned_words(): void
    {
        $this->assertTrue(Spam::isSpam('porn'));
        $this->assertTrue(Spam::isSpam('escort'));
        $this->assertTrue(Spam::isSpam('casino'));
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
