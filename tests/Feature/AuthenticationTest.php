<?php

namespace Tests\Feature;

use App\Mail\VerifyAccountMail;
use App\Models\User;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/en/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_cannot_reopen_auth_pages(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $this->actingAs($user)->get('/en/login')->assertRedirect('/en');
        $this->actingAs($user)->get('/en/register')->assertRedirect('/en');
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'verified' => true,
        ]);

        $response = $this->post('/en/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/en');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_normalizes_email_case_and_spaces(): void
    {
        $user = User::factory()->create([
            'email' => 'normalized@example.com',
            'password' => bcrypt('password123'),
            'verified' => true,
        ]);

        $this->post('/en/login', [
            'email' => '  NORMALIZED@EXAMPLE.COM ',
            'password' => 'password123',
        ])->assertRedirect('/en');

        $this->assertAuthenticatedAs($user);
    }

    public function test_migrated_legacy_user_can_login_and_password_is_rehashed(): void
    {
        $salt = 'legacy-salt';
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'legacy@example.com',
            'password' => md5(md5($password).$salt).':'.$salt,
            'verified' => true,
        ]);

        $this->post('/en/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertRedirect('/en');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(password_verify($password, $user->fresh()->password));
    }

    public function test_login_requires_an_email_address(): void
    {
        $response = $this->post('/en/login', [
            'email' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/en/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_for_unverified_user(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'verified' => false,
        ]);

        $response = $this->post('/en/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'surname' => 'Example',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'agree_terms' => 'on',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'surname' => 'Example',
            'email' => 'invalid-email',
            'password' => 'password123',
            'agree_terms' => 'on',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_rejects_existing_email_after_normalization(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/en/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => ' EXISTING@EXAMPLE.COM ',
            'password' => 'password123',
            'agree_terms' => 'on',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'existing@example.com')->count());
    }

    public function test_registration_validates_legacy_nickname_rules(): void
    {
        $this->post('/en/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'nickname@example.com',
            'password' => 'password123',
            'nickname' => 'invalid nickname',
            'agree_terms' => 'on',
        ])->assertSessionHasErrors('nickname');
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'surname' => 'Example',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('agree_terms');
    }

    public function test_registration_starts_verification_token_expiry_clock(): void
    {
        Settings::set('require_email_verification', true);

        $this->post('/en/register', [
            'name' => 'Pending',
            'surname' => 'User',
            'email' => 'pending@example.com',
            'password' => 'password123',
            'agree_terms' => 'on',
        ])->assertRedirect('/en/login');

        $user = User::where('email', 'pending@example.com')->firstOrFail();

        $this->assertFalse($user->verified);
        $this->assertNotNull($user->verification_token);
        $this->assertNotNull($user->verification_token_sent_at);
    }

    public function test_resend_verification_does_not_reveal_account_status(): void
    {
        Settings::set('smtp_enabled', true);
        $verified = User::factory()->create(['verified' => true]);
        $message = 'If the account needs activation, a new email has been sent.';

        $this->from('/en/resend-verification')->post('/en/resend-verification', [
            'email' => 'missing@example.com',
        ])->assertRedirect('/en/resend-verification')->assertSessionHas('success', $message);

        $this->from('/en/resend-verification')->post('/en/resend-verification', [
            'email' => $verified->email,
        ])->assertRedirect('/en/resend-verification')->assertSessionHas('success', $message);
    }

    public function test_resend_verification_rotates_token_and_queues_email(): void
    {
        Mail::fake();
        Settings::set('smtp_enabled', true);
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'verified' => false,
            'verification_token' => 'old-token',
            'verification_token_sent_at' => now()->subDay(),
        ]);

        $this->post('/en/resend-verification', [
            'email' => ' PENDING@EXAMPLE.COM ',
        ])->assertSessionHas('success', 'If the account needs activation, a new email has been sent.');

        $user->refresh();
        $this->assertNotSame('old-token', $user->verification_token);
        $this->assertTrue($user->verification_token_sent_at->isToday());
        Mail::assertQueued(VerifyAccountMail::class);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/en/logout');

        $response->assertRedirect('/en');
        $this->assertGuest();
    }

    public function test_login_rate_limited(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        for ($i = 0; $i < 6; $i++) {
            $this->post('/en/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->post('/en/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}
