<?php

namespace Tests\Feature;

use App\Mail\PetitionNewSignaturesMail;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PetitionNewSignaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        Settings::set('smtp_enabled', true, 'global');
    }

    public function test_sends_batched_email_to_opted_in_owner(): void
    {
        $owner = User::factory()->create(['locale' => 'en', 'notify_new_signatures' => true]);
        $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Save the Park', 'slug' => 'save-the-park', 'description' => 'D']);
        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true, 'created_at' => now()->subMinutes(10)]);

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');

        Mail::assertQueued(PetitionNewSignaturesMail::class);
    }

    public function test_opt_out_owner_gets_nothing(): void
    {
        $owner = User::factory()->create(['locale' => 'en', 'notify_new_signatures' => false]);
        $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true, 'created_at' => now()->subMinutes(10)]);

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');

        Mail::assertNothingQueued();
    }

    public function test_does_not_resend_on_second_run_without_new_signatures(): void
    {
        $owner = User::factory()->create(['locale' => 'en', 'notify_new_signatures' => true]);
        $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Save the Park', 'slug' => 'save-the-park', 'description' => 'D']);
        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true, 'created_at' => now()->subMinutes(5)]);

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');
        Mail::assertQueued(PetitionNewSignaturesMail::class, 1);

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');
        Mail::assertNothingQueued();
    }

    public function test_sends_mail_for_more_than_200_petitions(): void
    {
        $owner = User::factory()->create(['locale' => 'en', 'notify_new_signatures' => true]);
        $at = now()->subMinutes(10);
        for ($i = 0; $i < 205; $i++) {
            $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
            Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true, 'is_spam' => false, 'created_at' => $at]);
        }

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');

        Mail::assertQueued(PetitionNewSignaturesMail::class, 205);
    }

    public function test_skips_sending_while_mail_is_paused(): void
    {
        $owner = User::factory()->create(['locale' => 'en', 'notify_new_signatures' => true]);
        $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Save the Park', 'slug' => 'save-the-park', 'description' => 'D']);
        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true, 'created_at' => now()->subMinutes(10)]);

        Settings::set('smtp_enabled', false);

        Mail::fake();
        Artisan::call('emails:petition-new-signatures');

        Mail::assertNothingQueued();
    }
}
