<?php

namespace Tests\Feature;

use App\Mail\VerifyAccountMail;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestVerifyMailCapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_guest_verification_mail_stops_at_daily_cap(): void
    {
        Mail::fake();
        Settings::set('require_email_verification', true);
        Settings::set('smtp_enabled', true);
        Settings::set('verify_guest_daily_cap', 2);

        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Cap Petition',
            'slug' => 'cap-petition',
            'description' => 'Description',
        ]);

        foreach (['a', 'b', 'c'] as $n) {
            $this->post("/en/petition/cap-petition/{$petition->id}/sign", [
                'name' => 'Guest',
                'email' => "cap-{$n}@example.com",
                'agree1' => '1',
                'agree2' => '1',
                'agree3' => '1',
                'comment' => 'I support this petition',
            ])->assertRedirect("/en/petition/cap-petition/{$petition->id}/thanks");
        }

        Mail::assertQueuedCount(2);
        Mail::assertQueued(VerifyAccountMail::class, 2);
        $this->assertDatabaseCount('signatures', 3);
        $this->assertDatabaseMissing('signatures', ['petition_id' => $petition->id, 'confirmed' => true]);
    }
}
