<?php

namespace Tests\Feature;

use App\Support\Locale;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_optional_tags_are_inert_in_server_rendered_pages(): void
    {
        $response = $this->get('/en');
        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('js/consent-bootstrap.js', $html);
        $this->assertStringContainsString('data-fc-cookie-settings', $html);
        $document = new DOMDocument;
        @$document->loadHTML($html);
        $xpath = new DOMXPath($document);
        $this->assertSame(0, $xpath->query('//script[contains(@src,"googletagmanager.com") or contains(@src,"adsbygoogle.js")]')->length);
        $this->assertSame(0, $xpath->query('//iframe[contains(@src,"/ads/frame/")]')->length);
        $this->assertGreaterThan(0, $xpath->query('//script[@type="text/plain" and @data-category="analytics"]')->length);
    }

    public function test_banner_has_the_same_fields_in_all_supported_languages(): void
    {
        $source = file_get_contents(public_path('js/consent.js'));
        preg_match('/const translations = (\{.*?\n\});/s', $source, $match);
        $translations = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
        foreach (Locale::supported() as $locale) {
            $this->assertArrayHasKey($locale, $translations);
            $this->assertCount(15, $translations[$locale]);
            foreach ($translations[$locale] as $text) {
                $this->assertNotSame('', trim($text));
            }
        }
    }

    public function test_magazine_gates_analytics_and_holds_legacy_adsense_without_changing_other_scripts(): void
    {
        $html = '<html><head><link rel="preconnect" href="https://www.googletagmanager.com">'
            .'<script async src="//www.googletagmanager.com/gtag/js?id=G-TEST&amp;x=1"></script>'
            .'<script src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-test"></script>'
            .'<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>'
            .'<script src="/magazine/menu.js"></script></head><body>Editorial content</body></html>';
        $gated = $this->magazineHtml($html);
        $this->assertStringContainsString('data-category="analytics" data-service="googleAnalytics"', $gated);
        $this->assertStringContainsString('data-src="//www.googletagmanager.com/gtag/js?id=G-TEST&amp;x=1"', $gated);
        $this->assertSame(2, substr_count($gated, 'data-fc-future-adsense'));
        $this->assertStringNotContainsString('rel="preconnect"', $gated);
        $this->assertStringContainsString('<script src="/magazine/menu.js"></script>', $gated);
        $this->assertStringContainsString('Editorial content', $gated);
        $this->assertSame($gated, $this->magazineHtml($gated));
    }

    public function test_magazine_filter_leaves_json_untouched(): void
    {
        $json = '{"message":"No optional tags"}';
        $this->assertSame($json, $this->magazineHtml($json));
    }

    private function magazineHtml(string $html): string
    {
        $script = 'function add_action(...$args) {} define("ABSPATH", "/test/"); require $argv[1]; echo fc_cookie_consent_gate_html(base64_decode($argv[2]));';
        $process = new Process([PHP_BINARY, '-r', $script, public_path('magazine/wp-content/mu-plugins/fc-cookie-consent.php'), base64_encode($html)]);
        $process->mustRun();

        return $process->getOutput();
    }
}
