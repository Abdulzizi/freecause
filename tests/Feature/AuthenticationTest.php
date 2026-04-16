<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'UserLevelSeeder']);
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
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/en');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_name(): void
    {
        $user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'verified' => true,
        ]);

        $response = $this->post('/en/login', [
            'login' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/en');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/en/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
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
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation_match(): void
    {
        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
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
                'login' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->post('/en/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(429);
    }
}
