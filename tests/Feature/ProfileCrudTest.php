<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function passwordUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'google_id' => null,
            'password' => Hash::make('correct-current'),
            'password_changed_at' => now(),
            'verified' => true,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'locale' => 'en_US',
        ], $overrides));
    }

    public function test_profile_page_renders_for_a_password_user(): void
    {
        $this->actingAs($this->passwordUser())
            ->get('/en/profile')
            ->assertOk();
    }

    public function test_profile_page_renders_for_a_google_only_user(): void
    {
        $user = $this->passwordUser([
            'google_id' => 'google-account-id',
            'password' => Hash::make('unknown'),
            'password_changed_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/en/profile')
            ->assertOk();
    }

    public function test_profile_page_renders_for_a_user_not_connected_to_google(): void
    {
        $this->actingAs($this->passwordUser(['google_id' => null]))
            ->get('/en/profile')
            ->assertOk();
    }

    public function test_user_can_update_core_profile_fields(): void
    {
        $user = $this->passwordUser();

        $this->actingAs($user)
            ->post('/en/profile', [
                'first_name' => 'Grace',
                'last_name' => 'Hopper',
                'nickname' => 'amazing-grace',
                'city' => 'New York',
                'country' => 'US',
                'locale' => 'en',
                'identify_mode' => 'nick',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Grace', $user->first_name);
        $this->assertSame('Hopper', $user->last_name);
        $this->assertSame('amazing-grace', $user->nickname);
        $this->assertSame('New York', $user->city);
        $this->assertSame('US', $user->country);
        $this->assertSame('nick', $user->identify_mode);
    }

    public function test_profile_update_requires_first_and_last_name(): void
    {
        $this->actingAs($this->passwordUser())
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => '',
                'last_name' => '',
                'locale' => 'en',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors(['first_name', 'last_name']);
    }

    public function test_profile_update_rejects_an_invalid_country(): void
    {
        $this->actingAs($this->passwordUser())
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'country' => 'ZZ',
                'locale' => 'en',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors('country');
    }

    public function test_profile_update_rejects_an_inactive_or_unknown_locale(): void
    {
        $this->actingAs($this->passwordUser())
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'zz',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors('locale');
    }

    public function test_email_change_is_rejected_when_smtp_is_disabled(): void
    {
        $user = $this->passwordUser();

        $this->actingAs($user)
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'en',
                'new_email' => 'new-address@example.com',
                'new_email_confirmation' => 'new-address@example.com',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors('new_email');

        $this->assertNotSame('new-address@example.com', $user->fresh()->email);
    }

    public function test_email_change_rejects_an_address_already_taken(): void
    {
        $taken = $this->passwordUser(['first_name' => 'Taken', 'last_name' => 'User']);
        $user = $this->passwordUser();

        $this->actingAs($user)
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'en',
                'new_email' => $taken->email,
                'new_email_confirmation' => $taken->email,
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors('new_email');
    }

    public function test_email_change_succeeds_and_queues_verification_when_smtp_enabled(): void
    {
        Mail::fake();
        Settings::set('smtp_enabled', true);

        $user = $this->passwordUser();

        $this->actingAs($user)
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'en',
                'new_email' => 'fresh-address@example.com',
                'new_email_confirmation' => 'fresh-address@example.com',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('fresh-address@example.com', $user->email);
        $this->assertFalse((bool) $user->verified);
    }

    public function test_user_with_a_password_can_disconnect_google(): void
    {
        $user = $this->passwordUser(['google_id' => 'google-account-id']);

        $this->actingAs($user)
            ->post('/en/account/unlink/google')
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->google_id);
    }

    public function test_resubmitting_the_same_email_does_not_trigger_a_change(): void
    {
        $user = $this->passwordUser(['email' => 'stable@example.com']);

        $this->actingAs($user)
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'en',
                'new_email' => 'stable@example.com',
                'new_email_confirmation' => 'stable@example.com',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();

        $this->assertTrue((bool) $user->fresh()->verified);
    }
}
