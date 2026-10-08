<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    protected function addTranslation(Petition $petition, string $locale, string $title, string $slug): PetitionTranslation
    {
        return PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => $locale,
            'title' => $title,
            'slug' => $slug,
            'description' => '<p>Body</p>',
        ]);
    }

    protected function scopeFixture(): array
    {
        $withFrench = Petition::factory()->create();
        $this->addTranslation($withFrench, 'en', 'English One', 'english-one');
        $this->addTranslation($withFrench, 'fr', 'French One', 'french-one');

        $englishOnly = Petition::factory()->create();
        $this->addTranslation($englishOnly, 'en', 'English Two', 'english-two');

        return [$withFrench, $englishOnly];
    }

    public function test_translation_returns_matching_locale_row_and_null_when_missing(): void
    {
        $petition = Petition::factory()->create();
        $this->addTranslation($petition, 'en', 'English Title', 'english-title');

        $this->assertSame('English Title', $petition->translation('en')?->title);
        $this->assertNull($petition->translation('de'));
    }

    public function test_translation_or_fallback_returns_fallback_locale_when_locale_missing(): void
    {
        $petition = Petition::factory()->create();
        $this->addTranslation($petition, 'en', 'English Title', 'english-title');

        $fallback = $petition->translationOrFallback('fr', 'en');

        $this->assertSame('en', $fallback?->locale);
        $this->assertSame('English Title', $fallback?->title);
    }

    public function test_translation_or_fallback_returns_locale_row_when_present(): void
    {
        $petition = Petition::factory()->create();
        $this->addTranslation($petition, 'en', 'English Title', 'english-title');
        $this->addTranslation($petition, 'fr', 'French Title', 'french-title');

        $this->assertSame('French Title', $petition->translationOrFallback('fr', 'en')?->title);
    }

    public function test_translation_or_fallback_returns_first_row_by_id_when_no_fallback(): void
    {
        $petition = Petition::factory()->create();
        $english = $this->addTranslation($petition, 'en', 'English Title', 'english-title');
        $this->addTranslation($petition, 'de', 'German Title', 'german-title');

        $first = $petition->translationOrFallback('fr');

        $this->assertNotNull($first);
        $this->assertSame($english->id, $first->id);
        $this->assertSame('en', $first->locale);
    }

    public function test_translation_methods_work_with_eager_loaded_translations(): void
    {
        $petition = Petition::factory()->create();
        $this->addTranslation($petition, 'en', 'English Title', 'english-title');
        $this->addTranslation($petition, 'fr', 'French Title', 'french-title');

        $loaded = Petition::with('translations')->find($petition->id);

        $this->assertTrue($loaded->relationLoaded('translations'));
        $this->assertSame('English Title', $loaded->translation('en')?->title);
        $this->assertSame('French Title', $loaded->translationOrFallback('fr', 'en')?->title);
        $this->assertSame('English Title', $loaded->translationOrFallback('de', 'en')?->title);
    }

    public function test_scope_translated_returns_locale_title_and_default_locale_fallback(): void
    {
        [$withFrench, $englishOnly] = $this->scopeFixture();

        $rows = Petition::query()
            ->select(['petitions.id'])
            ->translated('fr', 'pt', 'pt_default', ['title' => 'translated_title', 'slug' => 'translated_slug'], 'en')
            ->orderBy('petitions.id')
            ->get()
            ->keyBy('id');

        $this->assertSame('French One', $rows[$withFrench->id]->translated_title);
        $this->assertSame('french-one', $rows[$withFrench->id]->translated_slug);
        $this->assertSame('English Two', $rows[$englishOnly->id]->translated_title);
        $this->assertSame('english-two', $rows[$englishOnly->id]->translated_slug);
    }

    public function test_scope_translated_aliases_usable_in_where_and_order_by(): void
    {
        [, $englishOnly] = $this->scopeFixture();

        $ordered = Petition::query()
            ->select(['petitions.id'])
            ->translated('fr', 'pt', 'pt_default', ['title' => 'translated_title'], 'en')
            ->orderBy('translated_title')
            ->get();

        $this->assertSame(
            ['English Two', 'French One'],
            $ordered->pluck('translated_title')->all()
        );

        $filtered = Petition::query()
            ->select(['petitions.id'])
            ->translated('fr', 'pt', 'pt_default', ['title' => 'translated_title'], 'en')
            ->where('translated_title', 'English Two')
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertSame($englishOnly->id, $filtered->first()?->id);
    }

    public function test_unique_translation_slug_returns_base_slug_without_collision(): void
    {
        $petition = Petition::factory()->create();

        $this->assertSame('fresh-slug', $petition->uniqueTranslationSlug('fresh-slug', 'en'));
    }

    public function test_unique_translation_slug_appends_suffix_on_locale_collision(): void
    {
        $taken = Petition::factory()->create();
        $this->addTranslation($taken, 'en', 'Taken', 'taken-slug');

        $petition = Petition::factory()->create();

        $this->assertSame('taken-slug-1', $petition->uniqueTranslationSlug('taken-slug', 'en'));
    }

    public function test_unique_translation_slug_allows_same_slug_in_other_locale(): void
    {
        $existing = Petition::factory()->create();
        $this->addTranslation($existing, 'fr', 'Shared', 'shared-slug');

        $petition = Petition::factory()->create();

        $this->assertSame('shared-slug', $petition->uniqueTranslationSlug('shared-slug', 'en'));
        $this->assertSame('shared-slug-1', $petition->uniqueTranslationSlug('shared-slug', 'fr'));
    }

    public function test_unique_translation_slug_honours_ignore_id(): void
    {
        $petition = Petition::factory()->create();
        $own = $this->addTranslation($petition, 'en', 'Own', 'own-slug');

        $this->assertSame('own-slug-1', $petition->uniqueTranslationSlug('own-slug', 'en'));
        $this->assertSame('own-slug', $petition->uniqueTranslationSlug('own-slug', 'en', $own->id));
    }

    public function test_unique_translation_slug_respects_max_length(): void
    {
        $petition = Petition::factory()->create();

        $slug = $petition->uniqueTranslationSlug('a-very-long-petition-slug', 'en', null, 10);

        $this->assertSame('a-very-lon', $slug);
        $this->assertLessThanOrEqual(10, strlen($slug));
    }

    public function test_category_model_uses_translations_trait(): void
    {
        $category = Category::factory()->create();
        CategoryTranslation::create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'English Category',
            'slug' => 'english-category',
        ]);

        $this->assertSame('English Category', $category->translation('en')?->name);
        $this->assertSame('English Category', $category->translation('de')?->name);
        $this->assertSame('English Category', $category->translationOrFallback('fr', 'en')?->name);
        $this->assertSame('news', $category->uniqueTranslationSlug('news', 'en'));
    }
}
