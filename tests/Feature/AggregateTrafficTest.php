<?php

namespace Tests\Feature;

use App\Support\AggregateTraffic;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AggregateTrafficTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        config(['app.url' => 'https://www.freecause.com', 'aggregate-traffic.enabled' => true]);
    }

    private function record(array $data = [], array $headers = [])
    {
        return $this->postJson('https://www.freecause.com/site-metrics', array_replace([
            'area' => 'petition', 'locale' => 'it', 'device' => 'mobile', 'automated' => false,
        ], $data), array_replace([
            'Origin' => 'https://www.freecause.com', 'Sec-Fetch-Site' => 'same-origin',
            'User-Agent' => 'Mozilla/5.0 Chrome/156.0 Safari/537.36',
        ], $headers));
    }

    public function test_same_bucket_increments_without_session_or_identifiers(): void
    {
        $this->travelTo(now('UTC')->setTime(12, 15));
        $this->record()->assertNoContent()->assertHeader('Cache-Control', 'no-store, private');
        $response = $this->record();
        $response->assertNoContent();
        $this->assertSame([], $response->headers->getCookies());
        $this->assertDatabaseCount('aggregate_traffic', 1);
        $this->assertDatabaseHas('aggregate_traffic', ['page_loads' => 2, 'kind' => 'browser']);
        $this->assertSame(['hour', 'area', 'locale', 'device', 'kind', 'page_loads'], Schema::getColumnListing('aggregate_traffic'));
    }

    public function test_cross_origin_and_missing_origin_cannot_write(): void
    {
        $this->record([], ['Origin' => 'https://other.example'])->assertForbidden();
        $this->record([], ['Origin' => '', 'Sec-Fetch-Site' => 'cross-site'])->assertForbidden();
        $this->record([], ['Sec-Fetch-Site' => 'cross-site'])->assertForbidden();
        $this->assertDatabaseCount('aggregate_traffic', 0);
    }

    public function test_privacy_signals_and_disabled_service_do_not_record(): void
    {
        $this->record([], ['DNT' => '1'])->assertNoContent();
        $this->record([], ['Sec-GPC' => '1'])->assertNoContent();
        config(['aggregate-traffic.enabled' => false]);
        $this->record()->assertNoContent();
        $this->assertDatabaseCount('aggregate_traffic', 0);
    }

    public function test_arbitrary_values_extra_personal_fields_and_large_payload_are_discarded(): void
    {
        foreach ([['area' => 'private-page'], ['locale' => 'xx'], ['device' => 'fingerprint'], ['automated' => 'false'],
            ['url' => 'https://example.test/private'], ['email' => 'private@example.test'], ['referrer' => str_repeat('x', 300)]] as $data) {
            $this->record($data)->assertNoContent();
        }
        $this->assertDatabaseCount('aggregate_traffic', 0);
    }

    public function test_automation_is_separate_and_report_respects_locale_and_hour_range(): void
    {
        $this->travelTo(now('UTC')->setTime(12, 15));
        $this->record();
        $this->record(['automated' => true]);
        $this->record(['locale' => 'en'], ['User-Agent' => 'Googlebot/2.1']);
        $this->record(['locale' => 'en', 'area' => 'magazine']);
        DB::table('aggregate_traffic')->insert([
            'hour' => now('UTC')->subDays(2)->startOfHour(), 'area' => 'home',
            'locale' => 'it', 'device' => 'desktop', 'kind' => 'browser', 'page_loads' => 5,
        ]);
        $this->assertSame(2, AggregateTraffic::report('', '1d')['browser']);
        $this->assertSame(2, AggregateTraffic::report('', '1d')['automation']);
        $this->assertSame(1, AggregateTraffic::report('it', '1d')['browser']);
        $this->assertSame(6, AggregateTraffic::report('it', '7d')['browser']);
    }

    public function test_counter_and_localized_explanation_are_present_without_starting_optional_tags(): void
    {
        $this->get('https://www.freecause.com/it')->assertOk()->assertSee('aggregate-traffic.js');
        $this->assertSame(14, count(config('aggregate-traffic.notices')));
        foreach (AggregateTraffic::LOCALES as $locale) {
            $this->assertCount(2, config('aggregate-traffic.notices.'.$locale));
        }
    }
}
