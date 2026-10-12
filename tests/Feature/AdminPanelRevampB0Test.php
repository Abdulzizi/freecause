<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Support\Locale;
use App\Support\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminPanelRevampB0Test extends TestCase
{
    use RefreshDatabase;

    public function test_locale_options_include_only_active_languages_with_default_first_then_name(): void
    {
        Language::query()->delete();

        Language::create(['code' => 'fr', 'name' => 'French', 'is_active' => true, 'is_default' => false]);
        Language::create(['code' => 'de', 'name' => 'German', 'is_active' => true, 'is_default' => false]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => true]);
        Language::create(['code' => 'es', 'name' => 'Spanish', 'is_active' => false, 'is_default' => false]);

        $this->assertSame([
            'en' => 'English',
            'fr' => 'French',
            'de' => 'German',
        ], Locale::options());
    }

    public function test_slug_dedupe_slugifies_values_and_appends_collision_suffixes(): void
    {
        $checked = [];
        $slug = Slug::dedupe('A Better, Fairer World!', function (string $candidate) use (&$checked): bool {
            $checked[] = $candidate;

            return in_array($candidate, ['a-better-fairer-world', 'a-better-fairer-world-1'], true);
        });

        $this->assertSame('a-better-fairer-world-2', $slug);
        $this->assertSame([
            'a-better-fairer-world',
            'a-better-fairer-world-1',
            'a-better-fairer-world-2',
        ], $checked);
    }

    public function test_slug_dedupe_uses_slugified_fallback_for_empty_input(): void
    {
        $this->assertSame(
            'petition-item',
            Slug::dedupe(' -- ', fn (string $candidate): bool => false, 'Petition Item')
        );
    }

    public function test_slug_dedupe_uses_item_when_input_and_fallback_are_empty(): void
    {
        $this->assertSame(
            'item-1',
            Slug::dedupe('!!!', fn (string $candidate): bool => $candidate === 'item', '---')
        );
    }

    public function test_admin_foundation_components_render_without_adopting_them_in_admin_pages(): void
    {
        $empty = new LengthAwarePaginator([], 0, 25);
        $filled = new LengthAwarePaginator([new \stdClass], 1, 25);

        $this->blade(<<<'BLADE'
            <x-admin.page-header title="Petitions" :count="3" context="All locales">
                <button type="button">Create petition</button>
            </x-admin.page-header>
            <x-admin.section-panel title="Filters">
                <p>Filter controls</p>
            </x-admin.section-panel>
            <x-admin.form-field label="Title" for="title" hint="Public petition title" error="Title is required">
                <input id="title" name="title" />
            </x-admin.form-field>
            <x-admin.empty-state title="Nothing here" message="No petitions found">
                <a href="/admin/petitions/create">Create one</a>
            </x-admin.empty-state>
            <x-admin.bulk-toolbar>
                <button type="button">Archive selected</button>
            </x-admin.bulk-toolbar>
            <x-admin.data-table :p="$filled">
                <x-slot:thead><tr><th>Petition</th></tr></x-slot:thead>
                <x-slot:tbody><tr><td>Example row</td></tr></x-slot:tbody>
                <x-slot:footer>Table footer</x-slot:footer>
            </x-admin.data-table>
            <x-admin.data-table :p="$empty" empty-text="No table rows" />
            <x-admin.data-table :p="$empty" emptyText="No rows via camel alias" />
            BLADE, ['empty' => $empty, 'filled' => $filled])
            ->assertSee('Petitions')
            ->assertSee('3')
            ->assertSee('All locales')
            ->assertSee('Create petition')
            ->assertSee('Filters')
            ->assertSee('Filter controls')
            ->assertSee('Public petition title')
            ->assertSee('Title is required')
            ->assertSee('Nothing here')
            ->assertSee('No petitions found')
            ->assertSee('Archive selected')
            ->assertSee('No table rows')
            ->assertSee('Petition')
            ->assertSee('Example row')
            ->assertSee('Table footer')
            ->assertSee('No rows via camel alias');
    }
}
