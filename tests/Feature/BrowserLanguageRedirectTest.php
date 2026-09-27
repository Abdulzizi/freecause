<?php

namespace Tests\Feature;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowserLanguageRedirectTest extends TestCase
{
    use RefreshDatabase;

    private const COOKIE = 'locale_redirected_to';

    private function seedLocales(): void
    {
        foreach (['en' => 1, 'es' => 0, 'de' => 0] as $code => $isDefault) {
            Language::create(['code' => $code, 'name' => $code, 'is_active' => 1, 'is_default' => $isDefault]);
        }
    }

    private function visit(string $acceptLanguage, array $cookies = [], string $userAgent = 'Mozilla/5.0 (Macintosh) AppleWebKit/537.36 Chrome/145.0')
    {
        foreach ($cookies as $name => $value) {
            $this->withUnencryptedCookie($name, $value);
        }

        return $this->withHeader('Accept-Language', $acceptLanguage)
            ->withHeader('User-Agent', $userAgent)
            ->get('/en/');
    }

    public function test_non_english_browser_is_redirected_from_the_default_root(): void
    {
        $this->seedLocales();

        $this->visit('es-ES,es;q=0.9,en;q=0.8')
            ->assertStatus(302)
            ->assertHeader('Location', 'http://localhost/es')
            ->assertHeader('Vary', 'Accept-Language')
            ->assertCookie(self::COOKIE, 'es');
    }

    public function test_english_browser_stays_on_the_default_root(): void
    {
        $this->seedLocales();

        $this->visit('en-GB,en;q=0.9')->assertOk();
    }

    public function test_unsupported_browser_language_stays_on_the_default_root(): void
    {
        $this->seedLocales();

        $this->visit('ja-JP,ja;q=0.9')->assertOk();
    }

    public function test_explicit_locale_page_is_never_hijacked(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->get('/de/petitions')
            ->assertOk();
    }

    public function test_bots_are_left_on_the_default_root(): void
    {
        $this->seedLocales();

        $this->visit('es-ES,es;q=0.9', [], 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->assertOk();
    }

    public function test_cookie_stops_further_auto_redirects(): void
    {
        $this->seedLocales();

        $this->visit('es-ES,es;q=0.9', [self::COOKIE => 'en'])->assertOk();
    }

    public function test_no_redirect_loop_when_already_in_the_browser_language(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->get('/es/')
            ->assertOk();
    }

    public function test_chrome_prefetch_does_not_consume_the_one_shot_redirect(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->withHeader('X-Purpose', 'prefetch')
            ->get('/en/')
            ->assertOk();
    }

    public function test_root_redirects_to_the_browser_language_in_a_single_hop(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9,en;q=0.8')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->get('/')
            ->assertStatus(302)
            ->assertHeader('Location', 'http://localhost/es')
            ->assertHeader('Vary', 'Accept-Language')
            ->assertCookie(self::COOKIE, 'es');
    }

    public function test_root_still_falls_back_to_the_default_locale(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->get('/')
            ->assertRedirect('/'.default_locale());

        $this->get('/')->assertRedirect('/'.default_locale());
    }

    public function test_root_leaves_bots_on_the_default_locale(): void
    {
        $this->seedLocales();

        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9')
            ->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->get('/')
            ->assertRedirect('/en/');
    }

    public function test_root_redirect_is_a_single_hop_end_to_end(): void
    {
        $this->seedLocales();

        $response = $this->withHeader('Accept-Language', 'de-DE,de;q=0.9,en;q=0.8')
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/145.0')
            ->get('/');

        $target = parse_url($response->headers->get('Location'), PHP_URL_PATH);

        $this->withUnencryptedCookie(self::COOKIE, 'de')
            ->get($target)
            ->assertOk();
    }
}
