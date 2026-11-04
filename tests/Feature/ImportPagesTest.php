<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportPagesTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = base_path('crowdin-content/pages-zz.json');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    private function enPage(string $slug): void
    {
        $page = Page::create();
        PageTranslation::create([
            'page_id' => $page->id, 'locale' => 'en', 'title' => 'T', 'slug' => $slug,
            'content' => '<p>en</p>', 'published' => true,
        ]);
    }

    private function zzContent(string $slug): string
    {
        return PageTranslation::where('locale', 'zz')->where('slug', $slug)->value('content');
    }

    public function test_import_sanitizes_translated_content(): void
    {
        $this->enPage('bad');
        $this->enPage('safe');
        $this->enPage('plain');
        $this->enPage('chunked');

        $safe = '<p>text <strong>bold</strong> <a href="https://x" rel="noopener">link</a></p>';
        file_put_contents($this->path, json_encode(['pages' => [
            'bad' => ['title' => 'B', 'content' => '<p>a</p><script>alert(1)</script><form action="https://evil"><input name="p"></form><p>b</p>'],
            'safe' => ['title' => 'S', 'content' => $safe],
            'plain' => ['title' => 'P', 'content' => 'just text'],
            'chunked' => ['title' => 'C', 'content' => ['1' => '<script>x()</script>', '0' => '<p>one</p>']],
        ]]));

        $this->artisan('translations:import-pages', ['locale' => 'zz'])->assertSuccessful();

        $bad = $this->zzContent('bad');
        $this->assertStringNotContainsString('script', $bad);
        $this->assertStringNotContainsString('<form', $bad);
        $this->assertStringNotContainsString('<input', $bad);
        $this->assertStringContainsString('<p>a</p>', $bad);
        $this->assertStringContainsString('<p>b</p>', $bad);

        $this->assertSame($safe, $this->zzContent('safe'));
        $this->assertSame('just text', $this->zzContent('plain'));
        $this->assertSame('<p>one</p>', $this->zzContent('chunked'));
    }
}
