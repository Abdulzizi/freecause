<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function petition(): array
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Reportable',
            'slug' => 'reportable',
            'description' => 'Description',
        ]);

        return [$petition, 'reportable'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_guest_can_report_a_petition_and_it_lands_in_the_admin_log(): void
    {
        [$petition] = $this->petition();

        $this->post('/en/report', [
            'reportable_type' => 'petition',
            'reportable_id' => $petition->id,
            'reason' => 'spam',
            'details' => 'Looks like advertising.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => Petition::class,
            'reportable_id' => $petition->id,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        $this->assertSame(1, DB::table('logs')->where('title', 'Content reported')->count());
    }

    public function test_reason_must_be_one_of_the_known_values(): void
    {
        [$petition] = $this->petition();

        $this->post('/en/report', [
            'reportable_type' => 'petition',
            'reportable_id' => $petition->id,
            'reason' => 'because-i-say-so',
        ])->assertSessionHasErrors('reason');

        $this->assertSame(0, DB::table('reports')->count());
    }

    public function test_forged_reporter_id_is_ignored(): void
    {
        [$petition] = $this->petition();
        $victim = User::factory()->create();

        $this->post('/en/report', [
            'reportable_type' => 'petition',
            'reportable_id' => $petition->id,
            'reason' => 'other',
            'reporter_id' => $victim->id,
        ])->assertRedirect();

        $this->assertNull(DB::table('reports')->value('reporter_id'));
    }

    public function test_comment_report_requires_a_real_signature(): void
    {
        [$petition] = $this->petition();

        $this->post('/en/report', [
            'reportable_type' => 'comment',
            'reportable_id' => 999999,
            'reason' => 'abuse',
        ])->assertRedirect();

        $this->assertSame(0, DB::table('reports')->count());

        $signature = Signature::create([
            'petition_id' => $petition->id,
            'email' => 'signer@example.com',
            'name' => 'Signer',
            'locale' => 'en',
        ]);

        $this->post('/en/report', [
            'reportable_type' => 'comment',
            'reportable_id' => $signature->id,
            'reason' => 'abuse',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => Signature::class,
            'reportable_id' => $signature->id,
        ]);
    }

    public function test_cached_petition_page_carries_no_report_token(): void
    {
        [$petition, $slug] = $this->petition();

        $response = $this->get("/en/petition/{$slug}/{$petition->id}")->assertOk();

        $this->assertStringNotContainsString('name="_token"', $response->getContent());
        $this->assertStringNotContainsString('reportSheet', $response->getContent());
    }
}
