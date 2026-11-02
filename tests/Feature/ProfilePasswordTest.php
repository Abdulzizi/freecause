<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_google_only_user_cannot_set_a_password_from_the_profile_form(): void
    {
        $user = User::factory()->create([
            'google_id' => 'google-account-id',
            'password' => Hash::make('unknown-random-password'),
            'password_changed_at' => null,
            'verified' => true,
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
        ]);

        $this->assertFalse($user->hasLocalPassword());

        $this->actingAs($user)
            ->withSession(['password.confirmed_at' => now()->timestamp])
            ->post('/en/profile', [
                'first_name' => 'Grace',
                'last_name' => 'Hopper',
                'country' => 'US',
                'gender' => 'female',
                'locale' => 'en',
                'new_password' => 'brand-new-pass',
                'new_password_confirmation' => 'brand-new-pass',
            ])
            ->assertSessionHasErrors('profile');

        $this->assertFalse($user->fresh()->hasLocalPassword());
        $this->assertTrue(Hash::check('unknown-random-password', $user->fresh()->password));
    }

    public function test_google_only_profile_links_to_forgot_password(): void
    {
        $user = User::factory()->create([
            'google_id' => 'google-account-id',
            'password' => Hash::make('unknown-random-password'),
            'password_changed_at' => null,
            'verified' => true,
        ]);

        $this->actingAs($user)->get('/en/profile')
            ->assertOk()
            ->assertSee('/en/forgot-password', false)
            ->assertDontSee('name="new_password"', false);
    }

    public function test_user_with_a_password_still_needs_the_correct_current_password(): void
    {
        $user = User::factory()->create([
            'google_id' => null,
            'password' => Hash::make('correct-current'),
            'password_changed_at' => now(),
            'verified' => true,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $this->actingAs($user)
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'country' => 'US',
                'gender' => 'female',
                'locale' => 'en',
                'current_password' => 'wrong-current',
                'new_password' => 'another-new-pass',
                'new_password_confirmation' => 'another-new-pass',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('correct-current', $user->fresh()->password));
    }

    public function test_user_with_a_password_can_change_it_with_the_correct_current_password(): void
    {
        $user = User::factory()->create([
            'google_id' => null,
            'password' => Hash::make('correct-current'),
            'password_changed_at' => now(),
            'verified' => true,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $this->actingAs($user)
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'country' => 'US',
                'gender' => 'female',
                'locale' => 'en',
                'current_password' => 'correct-current',
                'new_password' => 'another-new-pass',
                'new_password_confirmation' => 'another-new-pass',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('another-new-pass', $user->fresh()->password));
    }
}
