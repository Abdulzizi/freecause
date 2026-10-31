<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionListingLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
        $this->withoutVite();
        config(['edge-cache.purge_enabled' => false]);
    }

    private function petition(string $locale, string $title, array $attributes = []): Petition
    {
        $petition = Petition::factory()->create(array_merge(['status' => 'published', 'is_active' => true], $attributes));
        PetitionTranslation::create([
            'petition_id' => $petition->id, 'locale' => $locale,
            'title' => $title, 'slug' => 'petition-'.$petition->id, 'description' => '<p>Petition content</p>',
        ]);

        return $petition;
    }

    public function test_every_language_filters_all_sorts_search_and_categories(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $expected = [];
        foreach (Language::where('is_active', true)->pluck('code') as $locale) {
            CategoryTranslation::create(['category_id' => $category->id, 'locale' => $locale, 'name' => 'Category', 'slug' => 'category']);
            $expected[$locale] = [$this->petition($locale, 'LocaleMarker '.$locale, ['category_id' => $category->id])->id];
        }
        // Imported content from languages without a site section is stored in the English bucket.
        $expected['en'][] = $this->petition('en', 'LocaleMarker Konten Indonesia', ['category_id' => $category->id])->id;
        $this->petition('de', 'LocaleMarker hidden', ['category_id' => $category->id, 'status' => 'draft']);

        foreach ($expected as $locale => $ids) {
            foreach (['newest', 'signed', 'trending'] as $sort) {
                $this->get("/{$locale}/petitions?sort={$sort}")->assertOk()
                    ->assertViewHas('petitions', fn ($p) => $p->total() === count($ids) && $p->pluck('id')->sort()->values()->all() === collect($ids)->sort()->values()->all());
            }
            foreach (['', '?q=LocaleMarker'] as $search) {
                $this->get("/{$locale}/petitions/category-category-{$category->id}{$search}")->assertOk()
                    ->assertViewHas('petitions', fn ($p) => $p->total() === count($ids) && $p->pluck('id')->sort()->values()->all() === collect($ids)->sort()->values()->all());
            }
            $this->get("/{$locale}/petitions?q=LocaleMarker")->assertOk()
                ->assertViewHas('petitions', fn ($p) => $p->total() === count($ids));
        }
    }

    public function test_search_uses_the_requested_translation_even_when_an_english_translation_exists(): void
    {
        $petition = $this->petition('de', 'Deutscher Titel');
        PetitionTranslation::create([
            'petition_id' => $petition->id, 'locale' => 'en', 'title' => 'EnglishNeedle',
            'slug' => 'english-title', 'description' => '<p>English body</p>',
        ]);
        $this->get('/de/petitions?q=EnglishNeedle')->assertOk()->assertViewHas('petitions', fn ($p) => $p->total() === 0);
        $this->get('/de/petitions?q=Deutscher')->assertOk()->assertViewHas('petitions', fn ($p) => $p->pluck('id')->all() === [$petition->id]);
        $this->get('/en/petitions?q=EnglishNeedle')->assertOk()->assertViewHas('petitions', fn ($p) => $p->pluck('id')->all() === [$petition->id]);
    }

    public function test_pagination_totals_and_cached_pages_are_language_specific(): void
    {
        for ($i = 0; $i < 16; $i++) {
            $this->petition('de', 'Deutsch '.$i);
            $this->petition('en', 'English '.$i);
        }
        $this->get('/de/petitions')->assertOk()->assertViewHas('petitions', fn ($p) => $p->total() === 16 && $p->count() === 15);
        $this->get('/en/petitions')->assertOk()->assertViewHas('petitions', fn ($p) => $p->total() === 16);
        $this->get('/de/petitions?page=2')->assertOk()->assertViewHas('petitions', fn ($p) => $p->total() === 16 && $p->count() === 1);
        $this->get('/de/petitions')->assertOk()->assertViewHas('petitions', fn ($p) => $p->total() === 16 && $p->count() === 15);
    }
}
