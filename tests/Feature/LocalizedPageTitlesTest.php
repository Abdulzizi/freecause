<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedPageTitlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_petitions_and_account_titles_are_localized(): void
    {
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
        $user = User::factory()->create(['verified' => true, 'country' => 'DE', 'gender' => 'm']);

        $this->actingAs($user)->get('/de/my-petitions')->assertOk()
            ->assertSee('<title>', false)
            ->assertSeeText(__('nav.my_petitions', [], 'de').' - FreeCause')
            ->assertDontSee('My petitions - FreeCause', false);

        $this->actingAs($user)->get('/de/profile')->assertOk()
            ->assertSeeText('Konto - FreeCause');
    }
}
