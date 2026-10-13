<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    private function incompleteUser(): User
    {
        return User::factory()->create([
            'country' => null,
            'gender' => null,
            'verified' => true,
        ]);
    }

    private function petition(): Petition
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Gate test petition',
            'slug' => 'gate-test-petition',
            'description' => 'Description',
        ]);

        return $petition;
    }

    public function test_user_without_country_and_gender_is_redirected_to_profile_before_signing(): void
    {
        $petition = $this->petition();

        $this->actingAs($this->incompleteUser())
            ->post("/en/petition/gate-test-petition/{$petition->id}/sign", [
                'agree1' => 'agree',
                'agree2' => 'agree',
                'agree3' => 'agree',
            ])
            ->assertRedirectContains('/en/profile')
            ->assertSessionHas('profile_required');

        $this->assertSame(0, $petition->signatures()->count());
    }

    public function test_user_with_incomplete_profile_is_redirected_before_opening_the_create_form(): void
    {
        $this->actingAs($this->incompleteUser())
            ->get('/en/create-petition')
            ->assertRedirectContains('/en/profile?return=')
            ->assertSessionHas('profile_required');
    }

    public function test_user_with_incomplete_profile_cannot_submit_the_create_form(): void
    {
        $before = Petition::count();

        $this->actingAs($this->incompleteUser())
            ->post('/en/create-petition', [
                'title' => 'Should never be created',
                'description' => str_repeat('x', 200),
            ])
            ->assertRedirectContains('/en/profile');

        $this->assertSame($before, Petition::count());
    }

    public function test_petition_page_stays_readable_for_users_with_an_incomplete_profile(): void
    {
        $petition = $this->petition();

        $this->actingAs($this->incompleteUser())
            ->get("/en/petition/gate-test-petition/{$petition->id}")
            ->assertOk();
    }

    public function test_guest_signing_is_not_gated(): void
    {
        $petition = $this->petition();

        $this->post("/en/petition/gate-test-petition/{$petition->id}/sign", [
            'name' => 'Guest Signer',
            'email' => 'guest.signer@example.com',
            'agree1' => 'on',
            'agree2' => 'on',
            'agree3' => 'on',
        ]);

        $this->assertSame(1, $petition->signatures()->count());
    }

    public function test_profile_update_requires_country_and_gender(): void
    {
        $this->actingAs($this->incompleteUser())
            ->from('/en/profile')
            ->post('/en/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'locale' => 'en',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasErrors(['country', 'gender']);
    }

    public function test_completing_the_profile_returns_the_user_to_where_they_were(): void
    {
        $user = $this->incompleteUser();

        $this->actingAs($user)
            ->get('/en/create-petition')
            ->assertRedirectContains('/en/profile?return=');

        $this->actingAs($user)
            ->post('/en/profile?return=/en/create-petition', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'country' => 'US',
                'gender' => 'female',
                'locale' => 'en',
            ])
            ->assertRedirect('/en/create-petition')
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('US', $user->country);
        $this->assertSame('female', $user->gender);

        $this->actingAs($user)
            ->get('/en/create-petition')
            ->assertOk();
    }

    public function test_profile_update_ignores_a_remote_return_url(): void
    {
        $this->actingAs($this->incompleteUser())
            ->post('/en/profile?return=https://evil.example.com/steal', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'country' => 'US',
                'gender' => 'male',
                'locale' => 'en',
            ])
            ->assertRedirect('/en/profile')
            ->assertSessionHasNoErrors();
    }

    public function test_account_and_participation_actions_are_gated(): void
    {
        $petition = $this->petition();
        $user = $this->incompleteUser();

        $gated = [
            ['get', '/en/my-petitions'],
            ['get', "/en/petition/gate-test-petition/{$petition->id}/edit"],
            ['get', '/en/preferences'],
            ['post', '/en/preferences'],
            ['post', '/en/report'],
            ['post', "/en/petition/gate-test-petition/{$petition->id}/claim"],
        ];

        foreach ($gated as [$method, $url]) {
            $this->actingAs($user)
                ->{$method}($url)
                ->assertRedirectContains('/en/profile');
        }
    }

    public function test_profile_page_never_redirects_to_itself(): void
    {
        $this->actingAs($this->incompleteUser())
            ->get('/en/profile')
            ->assertOk()
            ->assertSee('id="gender"', false);
    }

    public function test_logout_and_support_contact_stay_reachable(): void
    {
        $user = $this->incompleteUser();

        $logout = $this->actingAs($user)->post('/en/logout');
        $this->assertStringNotContainsString('/en/profile', (string) $logout->headers->get('Location'));

        $contact = $this->actingAs($user)->post('/en/contacts', [
            'name' => 'Ada Lovelace',
            'email' => $user->email,
            'message' => 'Help me, I cannot complete my profile.',
        ]);
        $this->assertStringNotContainsString('/en/profile', (string) $contact->headers->get('Location'));
    }
}
