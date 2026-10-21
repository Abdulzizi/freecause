<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\ReviewKeywords;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminReviewKeywordSheetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->user = User::factory()->create(['verified' => true]);

        $this->category = Category::factory()->create(['is_active' => true]);

        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $level = UserLevel::create([
            'name' => 'editor_petitions',
            'visible_name' => 'Editor',
            'is_system' => false,
        ]);

        Permission::create(['level_id' => $level->id, 'module' => 'petitions', 'action' => 'view']);
        Permission::create(['level_id' => $level->id, 'module' => 'petitions', 'action' => 'edit']);

        $this->admin = User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    private function createPetition(string $locale, string $title): Petition
    {
        $this->actingAs($this->user)->post("/{$locale}/create-petition", [
            'title' => $title,
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ])->assertRedirect();

        return Petition::latest('id')->firstOrFail();
    }

    private function sheetResponse(int $petitionId, string $locale): TestResponse
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/petitions?select={$petitionId}&locale={$locale}");

        $response->assertOk();

        return $response;
    }

    private function sheetHtml(int $petitionId, string $locale): string
    {
        return (string) $this->sheetResponse($petitionId, $locale)->getContent();
    }

    private function reviewAlert(string $html, string $needle = 'banned keywords'): string
    {
        preg_match_all('/<div[^>]*data-slot="alert"[^>]*>(.*?)<\/div>/s', $html, $matches);

        foreach ($matches[1] as $chunk) {
            if (str_contains($chunk, 'Held for review') && str_contains($chunk, $needle)) {
                return $chunk;
            }
        }

        $this->fail('The sheet must render a "Held for review" alert.');
    }

    public function test_detail_sheet_shows_the_matched_keyword_with_its_locale(): void
    {
        $petition = $this->createPetition('en', 'Stop betting advertising');

        $this->assertDatabaseHas('spam_logs', ['type' => 'petition.review']);

        $html = $this->sheetHtml($petition->id, 'en');

        $this->assertStringContainsString('betting (en)', $html);
    }

    public function test_detail_sheet_lists_one_entry_per_spelling_of_the_same_keyword(): void
    {
        $petition = $this->createPetition('en', 'Stop betting advertising');

        DB::table('spam_logs')->insert([
            'type' => 'petition.review',
            'ip' => '127.0.0.1',
            'payload' => json_encode([
                'petition_id' => $petition->id,
                'title' => 'Stop betting advertising',
                'keywords' => ['Porno', 'porno'],
            ]),
            'created_at' => now(),
        ]);

        $alert = $this->reviewAlert($this->sheetHtml($petition->id, 'en'));

        $this->assertStringContainsString('betting (en)', $alert);
        $this->assertSame(
            1,
            preg_match_all('/porno\s*\(/i', $alert),
            'The two spellings of the same keyword must be listed once.'
        );
        $this->assertSame(
            1,
            substr_count(mb_strtolower($alert), 'porno'),
            'The keyword must not be rendered twice.'
        );
    }

    public function test_detail_sheet_attributes_the_keyword_to_the_petition_locale(): void
    {
        $petition = $this->createPetition('ro', 'Pariuri online pe scara');

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'status' => 'draft']);
        $this->assertSame('ro', $petition->translations()->orderBy('id')->value('locale'));

        $html = $this->sheetHtml($petition->id, 'ro');

        $this->assertStringContainsString('pariuri (ro)', $html);
        $this->assertStringNotContainsString('pariuri (en)', $html);
        $this->assertStringNotContainsString('pariuri (de)', $html);
    }

    public function test_detail_sheet_prefers_the_petition_locale_over_the_catalogue_locale(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);
        ReviewKeywords::saveLocale('de', ['cuvant de test']);

        $petition = $this->createPetition('ro', 'Cuvant de test in Romania');

        $this->assertSame('ro', $petition->translations()->orderBy('id')->value('locale'));

        $html = $this->sheetHtml($petition->id, 'ro');

        $this->assertStringContainsString('cuvant de test (ro)', $html);
        $this->assertStringNotContainsString('cuvant de test (de)', $html);
    }

    public function test_detail_sheet_still_banners_an_unparseable_review_payload(): void
    {
        $petition = $this->createPetition('en', 'Stop betting advertising');

        DB::table('spam_logs')->insert([
            'type' => 'petition.review',
            'ip' => '127.0.0.1',
            'payload' => '{"petition_id":'.$petition->id.',"keywords":["betting"',
            'created_at' => now(),
        ]);

        $alert = $this->reviewAlert($this->sheetHtml($petition->id, 'en'), 'unavailable');

        $this->assertStringContainsString('Held for review', $alert);
        $this->assertStringContainsString('unavailable', mb_strtolower($alert));
    }

    public function test_detail_sheet_does_not_banner_a_truncated_payload_of_another_petition(): void
    {
        $petitionA = Petition::forceCreate(['id' => 12, 'user_id' => $this->user->id, 'status' => 'draft']);
        $petitionB = Petition::forceCreate(['id' => 123, 'user_id' => $this->user->id, 'status' => 'draft']);

        foreach ([$petitionA, $petitionB] as $petition) {
            PetitionTranslation::create([
                'petition_id' => $petition->id,
                'locale' => 'en',
                'title' => 'Keep the local library open',
                'slug' => 'keep-the-local-library-open-'.$petition->id,
                'description' => '<p>A neutral petition description without any banned word.</p>',
            ]);
        }

        $truncatedAtIdBoundary = substr('{"petition_id":'.$petitionB->id, 0, -1);

        $this->assertSame(
            '{"petition_id":'.$petitionA->id,
            $truncatedAtIdBoundary,
            'The truncated payload must end exactly on the id boundary of the other petition.'
        );

        DB::table('spam_logs')->insert([
            [
                'type' => 'petition.review',
                'ip' => '127.0.0.1',
                'payload' => '{"petition_id":'.$petitionB->id.',"keywords":["betting',
                'created_at' => now(),
            ],
            [
                'type' => 'petition.review',
                'ip' => '127.0.0.1',
                'payload' => $truncatedAtIdBoundary,
                'created_at' => now(),
            ],
        ]);

        $this->assertStringNotContainsString(
            'keyword details unavailable',
            $this->sheetHtml($petitionA->id, 'en')
        );

        $this->assertStringContainsString(
            'keyword details unavailable',
            $this->sheetHtml($petitionB->id, 'en')
        );
    }

    public function test_detail_sheet_does_not_banner_a_published_petition(): void
    {
        $petition = Petition::forceCreate([
            'id' => 456,
            'user_id' => $this->user->id,
            'status' => 'published',
            'is_active' => 1,
            'is_featured' => 0,
            'goal_signatures' => 100,
            'signature_count' => 0,
            'created_at' => '2026-01-15 09:30:00',
            'updated_at' => '2026-01-15 09:30:00',
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Keep the local library open',
            'slug' => 'keep-the-local-library-open-'.$petition->id,
            'description' => '<p>A neutral petition description without any banned word.</p>',
        ]);

        DB::table('spam_logs')->insert([
            [
                'type' => 'petition.review',
                'ip' => '127.0.0.1',
                'payload' => '{"petition_id":'.$petition->id.',"title":"x","keywords":["betting"]}',
                'created_at' => '2026-01-15 09:31:00',
            ],
            [
                'type' => 'petition.review',
                'ip' => '127.0.0.1',
                'payload' => '{"petition_id":'.$petition->id.',"keywords":["betting"',
                'created_at' => '2026-01-15 09:32:00',
            ],
        ]);

        $this->assertDatabaseHas('petitions', [
            'id' => $petition->id,
            'status' => 'published',
        ]);

        $response = $this->sheetResponse($petition->id, 'en');

        $this->assertEmpty(
            $response->viewData('selectedReviewKeywords'),
            'A petition that is no longer held must not collect review keywords.'
        );
        $this->assertFalse(
            $response->viewData('selectedReviewPayloadBroken'),
            'A petition that is no longer held must not report a broken review payload.'
        );

        $html = (string) $response->getContent();

        $this->assertStringNotContainsString('Held for review', $html);
        $this->assertStringNotContainsString('keyword details unavailable', $html);

        DB::table('petitions')->where('id', $petition->id)->update(['status' => 'draft']);

        $heldHtml = $this->sheetHtml($petition->id, 'en');

        $this->assertStringContainsString(
            'Held for review',
            $heldHtml,
            'The same review logs must banner the petition once it is held again.'
        );
        $this->assertStringContainsString(
            'betting (en)',
            $heldHtml,
            'The same review logs must surface the keyword once the petition is held again.'
        );
        $this->assertStringContainsString(
            'keyword details unavailable',
            $heldHtml,
            'The same review logs must banner the broken payload once the petition is held again.'
        );
    }
}
