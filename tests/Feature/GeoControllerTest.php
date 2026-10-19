<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_city_suggestions_are_filtered_by_country_and_cached(): void
    {
        Cache::flush();
        Http::fake([
            'photon.komoot.io/api/*' => Http::response([
                'features' => [
                    ['properties' => ['city' => 'São Paulo', 'countrycode' => 'BR']],
                    ['properties' => ['city' => 'São Paulo', 'countrycode' => 'BR']],
                    ['properties' => ['name' => 'Campinas', 'countrycode' => 'BR']],
                    ['properties' => ['city' => 'Paris', 'countrycode' => 'FR']],
                ],
            ]),
        ]);

        $url = '/en/geo/cities?q=sao&country=BR';
        $this->getJson($url)->assertOk()->assertExactJson(['São Paulo', 'Campinas']);
        $this->getJson($url)->assertOk()->assertExactJson(['São Paulo', 'Campinas']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'countrycode=BR'));
        Http::assertSentCount(1);
    }

    public function test_city_suggestions_reject_invalid_country(): void
    {
        $this->getJson('/en/geo/cities?q=Paris&country=ZZ')->assertUnprocessable();
    }

    public function test_petition_form_uses_translated_location_labels_and_all_countries(): void
    {
        $user = User::factory()->create(['country' => 'US', 'gender' => 'none']);
        $this->actingAs($user)->get('/en/create-petition')->assertOk()
            ->assertSee('Country')
            ->assertSee('Select a country')
            ->assertSee('United States')
            ->assertSee('city-suggestions');
    }
}
