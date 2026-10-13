<?php

namespace Tests\Feature;

use App\Services\ContentRenderer;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicContentLinksTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/en',
        '/en/faqs',
        '/en/contacts',
        '/en/petitions',
        '/en/login',
        '/en/create-petition',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class]);
    }

    public function test_public_pages_render_no_nested_or_placeholder_hrefs(): void
    {
        $patterns = [
            '/href="<a /' => 'a nested anchor',
            '/href="\{[A-Z_]+\}"/' => 'an unexpanded placeholder in an href',
            '/href="%7B[A-Z_]+%7D"/' => 'an encoded placeholder in an href',
        ];

        foreach (self::PAGES as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            foreach ($patterns as $pattern => $label) {
                $this->assertDoesNotMatchRegularExpression($pattern, $html, "{$url} renders {$label}");
            }
        }
    }

    public function test_url_link_tokens_are_expanded_as_urls_and_link_tokens_as_anchors(): void
    {
        $html = ContentRenderer::render('<p><a href="{ETHICAL_CODE_URL}">Ethical Code</a> and {ETHICAL_CODE_LINK}.</p>');

        $this->assertStringContainsString('href="'.lroute('page.show', ['slug' => 'ethical-code']).'"', $html);
        $this->assertStringNotContainsString('href="<a ', $html);
        $this->assertStringContainsString('<a href="'.lroute('page.show', ['slug' => 'ethical-code']).'">', $html);
    }

    public function test_migration_repairs_the_ethical_code_href_in_stored_content(): void
    {
        DB::table('page_translations')->insert([
            'page_id' => 1,
            'locale' => 'en',
            'title' => 'Broken',
            'slug' => 'broken-content',
            'content' => 'Read our <a href="%7BETHICAL_CODE_LINK%7D">Ethical Code</a> and <a href="{ETHICAL_CODE_LINK}">again</a>.',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_13_120000_fix_ethical_code_placeholder_in_hrefs.php');
        $migration->up();

        $content = DB::table('page_translations')->where('slug', 'broken-content')->value('content');

        $this->assertStringNotContainsString('href="%7BETHICAL_CODE_LINK%7D"', $content);
        $this->assertStringNotContainsString('href="{ETHICAL_CODE_LINK}"', $content);
        $this->assertSame(2, substr_count($content, 'href="{ETHICAL_CODE_URL}"'));
    }
}
