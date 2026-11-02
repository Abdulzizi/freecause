<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Support\ReviewKeywords;
use App\Support\Spam;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanCryptoReviewTermsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->user = User::factory()->create(['verified' => true]);
        $this->category = Category::factory()->create(['is_active' => true]);
        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'Test Petition Title Here',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ], $over);
    }

    public function test_loan_and_crypto_are_no_longer_hard_blocked(): void
    {
        $this->assertFalse(Spam::isSpam('Sloan'));
        $this->assertFalse(Spam::isSpam('loana007'));
        $this->assertFalse(Spam::isSpam('cryptography'));
        $this->assertFalse(Spam::isSpam('student loan'));

        $this->assertTrue(Spam::isSpam('viagra'));
        $this->assertTrue(Spam::isSpam('paypal'));
    }

    public function test_loan_and_crypto_are_held_for_review(): void
    {
        $this->assertNotEmpty(ReviewKeywords::matches('student loans are unfair'));
        $this->assertNotEmpty(ReviewKeywords::matches('crypto scam'));
        $this->assertSame([], ReviewKeywords::matches('cryptography research'));
        $this->assertSame([], ReviewKeywords::matches('loaned books'));
    }

    public function test_petition_with_loans_is_held_and_cryptography_is_published(): void
    {
        $this->actingAs($this->user)->post('/en/create-petition', $this->payload([
            'title' => 'Student loans are unfair',
        ]))->assertRedirect();

        $this->assertDatabaseHas('petitions', ['status' => 'draft']);

        $this->actingAs($this->user)->post('/en/create-petition', $this->payload([
            'title' => 'Cryptography research funding',
        ]))->assertRedirect();

        $this->assertDatabaseHas('petitions', ['status' => 'published']);
    }

    public function test_guest_signature_with_loana_name_succeeds(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Loan Petition',
            'slug' => 'loan-petition',
            'description' => 'Description',
        ]);

        $this->post("/en/petition/loan-petition/{$petition->id}/sign/confirm", [
            'name' => 'loana007',
            'email' => 'loana007@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertOk();

        $this->post("/en/petition/loan-petition/{$petition->id}/sign", [
            'name' => 'loana007',
            'email' => 'loana007@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('signatures', ['petition_id' => $petition->id, 'email' => 'loana007@example.com']);
    }

    public function test_migration_appends_missing_terms_to_english_override(): void
    {
        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_en',
            'value' => json_encode([['term' => 'casino', 'concept' => 'gambling'], ['term' => 'Crypto', 'concept' => 'crypto']]),
            'type' => 'json',
        ]);

        Cache::put('settings:all', 'stale');
        Cache::put('review_keywords:rows', 'stale');

        $migration = require database_path('migrations/2026_11_02_000002_add_loan_crypto_to_review_keyword_override.php');
        $migration->up();

        $this->assertSame(
            [
                ['term' => 'casino', 'concept' => 'gambling'],
                ['term' => 'Crypto', 'concept' => 'crypto'],
                ['term' => 'loan', 'concept' => 'loan'],
            ],
            json_decode(DB::table('settings')->where('key', 'review_keywords_en')->value('value'), true)
        );
        $this->assertNull(Cache::get('settings:all'));
        $this->assertNull(Cache::get('review_keywords:rows'));

        $migration->up();

        $this->assertCount(3, json_decode(DB::table('settings')->where('key', 'review_keywords_en')->value('value'), true));
    }

    public function test_migration_leaves_invalid_json_and_missing_row_alone(): void
    {
        $invalid = '{not json';

        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_en',
            'value' => $invalid,
            'type' => 'json',
        ]);

        $migration = require database_path('migrations/2026_11_02_000002_add_loan_crypto_to_review_keyword_override.php');
        $migration->up();

        $this->assertSame($invalid, DB::table('settings')->where('key', 'review_keywords_en')->value('value'));

        DB::table('settings')->delete();

        $migration->up();

        $this->assertFalse(DB::table('settings')->where('key', 'review_keywords_en')->exists());
    }
}
