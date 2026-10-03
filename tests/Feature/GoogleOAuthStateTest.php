<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleOAuthStateTest extends TestCase
{
    use RefreshDatabase;

    private MockHandler $googleResponses;

    private array $googleRequests = [];

    private string $lastGoogleRedirect = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        // Exercise the real Google provider while preventing all network calls.
        $this->googleResponses = new MockHandler;
        $handler = HandlerStack::create($this->googleResponses);
        $handler->push(Middleware::history($this->googleRequests));

        config()->set('services.google', [
            'client_id' => 'synthetic-client-id',
            'client_secret' => 'synthetic-client-secret',
            'redirect' => 'https://www.freecause.test/en/oauth/google/callback',
            'guzzle' => ['handler' => $handler],
        ]);
        config()->set('app.sso_secret', null);
    }

    public function test_google_redirect_state_matches_the_state_socialite_validates(): void
    {
        $state = $this->startGoogleLogin('/en/oauth/google?flow=login');

        $this->assertTrue(hash_equals($state, (string) session('state')));
        $this->assertNotEmpty($state);
        $this->assertCount(0, $this->googleRequests);
    }

    public function test_returned_google_state_completes_login_and_preserves_embedded_destination(): void
    {
        $state = $this->startGoogleLogin('/en/oauth/google?flow=login&redirect=%2Fen%2Fcreate-petition');
        $this->queueGoogleUser();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/en/create-petition')
            ->assertSessionMissing('state')
            ->assertSessionMissing('oauth_ctx');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-state@example.test',
            'google_id' => 'synthetic-google-user',
            'verified' => true,
        ]);
        $this->assertCount(2, $this->googleRequests);
        $this->assertSame('POST', $this->googleRequests[0]['request']->getMethod());
        $this->assertSame('/oauth2/v4/token', $this->googleRequests[0]['request']->getUri()->getPath());
        $this->assertSame('/oauth2/v3/userinfo', $this->googleRequests[1]['request']->getUri()->getPath());
    }

    public function test_google_login_preserves_petition_and_locale_across_english_callback(): void
    {
        $state = $this->startGoogleLogin('/fr/oauth/google?flow=petition&petition_id=42&slug=save-the-forest');
        $this->queueGoogleUser();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/fr/petition/save-the-forest/42/sign/confirm?oauth_logged_in=1');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-state@example.test',
            'locale' => 'fr_FR',
        ]);
    }

    public function test_existing_google_user_keeps_and_uses_saved_preferred_language(): void
    {
        $user = User::factory()->create([
            'email' => 'google-state@example.test',
            'google_id' => 'synthetic-google-user',
            'locale' => 'fr_FR',
            'verified' => true,
        ]);

        $state = $this->startGoogleLogin('/de/oauth/google?flow=login');
        $this->queueGoogleUser();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/fr');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'fr_FR',
        ]);
    }

    public function test_google_callback_rejects_mismatched_state_before_contacting_google(): void
    {
        $this->startGoogleLogin('/en/oauth/google?flow=login');

        $this->assertRejectedCallback(['state' => 'synthetic-wrong-state', 'code' => 'synthetic-code']);
    }

    public function test_google_callback_rejects_missing_returned_state_before_contacting_google(): void
    {
        $this->startGoogleLogin('/en/oauth/google?flow=login');

        $this->assertRejectedCallback(['code' => 'synthetic-code']);
    }

    public function test_google_callback_rejects_missing_session_state_before_contacting_google(): void
    {
        $state = $this->startGoogleLogin('/en/oauth/google?flow=login');
        session()->forget('state');

        $this->assertRejectedCallback(['state' => $state, 'code' => 'synthetic-code']);
    }

    public function test_google_callback_rejects_replay_of_a_successful_login(): void
    {
        $state = $this->startGoogleLogin('/en/oauth/google?flow=login');
        $this->queueGoogleUser();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])->assertRedirect('/en');
        $this->assertAuthenticated();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/en/login')
            ->assertSessionHasErrors('oauth')
            ->assertSessionMissing('state');

        $this->assertCount(2, $this->googleRequests);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_only_user_can_reauthenticate_for_account_deletion(): void
    {
        $user = User::factory()->create([
            'email' => 'google-state@example.test',
            'google_id' => 'synthetic-google-user',
            'password_changed_at' => null,
            'verified' => true,
        ]);

        $this->actingAs($user);
        $state = $this->startGoogleLogin('/en/account/delete/google');

        parse_str(parse_url($this->lastGoogleRedirect, PHP_URL_QUERY), $query);
        $this->assertSame('login', $query['prompt'] ?? null);
        $this->assertSame([
            'flow' => 'delete_account',
            'locale' => 'en',
            'user_id' => $user->id,
        ], session('oauth_ctx'));

        $this->queueGoogleUser();
        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/en/profile')
            ->assertSessionHas('account_delete_google_verified', function (array $proof) use ($user) {
                return $proof['user_id'] === $user->id && is_int($proof['verified_at']);
            });

        $this->assertAuthenticatedAs($user);
    }

    public function test_account_deletion_reauthentication_rejects_a_different_google_account(): void
    {
        $user = User::factory()->create([
            'email' => 'google-state@example.test',
            'google_id' => 'expected-google-user',
            'password_changed_at' => null,
            'verified' => true,
        ]);

        $this->actingAs($user);
        $state = $this->startGoogleLogin('/en/account/delete/google');
        $this->queueGoogleUser();

        $this->googleCallback(['state' => $state, 'code' => 'synthetic-code'])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors('google_delete')
            ->assertSessionMissing('account_delete_google_verified');

        $this->assertAuthenticatedAs($user);
    }

    private function startGoogleLogin(string $path): string
    {
        Socialite::forgetDrivers();
        $response = $this->get($path)->assertRedirect();

        $location = $response->headers->get('Location');
        $this->lastGoogleRedirect = $location;
        $this->assertSame('accounts.google.com', parse_url($location, PHP_URL_HOST));
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        return $query['state'];
    }

    private function googleCallback(array $query): TestResponse
    {
        // HTTP requests resolve a fresh provider in production. Forgetting the
        // test application's cached driver also prevents cached-user bypasses.
        Socialite::forgetDrivers();

        return $this->get('/en/oauth/google/callback?'.http_build_query($query));
    }

    private function assertRejectedCallback(array $query): void
    {
        $this->googleCallback($query)
            ->assertRedirect('/en/login')
            ->assertSessionHasErrors([
                'oauth' => 'Your session expired or the login was interrupted. Please try again.',
            ])
            ->assertSessionMissing('state')
            ->assertSessionMissing('oauth_ctx');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertCount(0, $this->googleRequests);
    }

    private function queueGoogleUser(): void
    {
        $this->googleResponses->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'synthetic-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'sub' => 'synthetic-google-user',
                'name' => 'Google State User',
                'email' => 'google-state@example.test',
                'email_verified' => true,
            ])),
        );
    }
}
