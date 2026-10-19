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

    public function test_google_only_user_can_set_a_first_password_without_a_current_password(): void
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

        // Setting a first password now requires a recent password confirmation.
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
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->hasLocalPassword());
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check('brand-new-pass', $user->password));
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
