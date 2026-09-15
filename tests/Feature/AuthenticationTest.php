<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/en/logout');

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
