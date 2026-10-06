<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyHeadRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_serves_restored_google_tags(): void
    {
        $this->withoutMiddleware(SetLocale::class);

        $this->get('/en')
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-9CYCSY3PWD', false)
            ->assertSee("gtag('config', 'G-9CYCSY3PWD')", false)
            ->assertSee('ns.html?id=GTM-9CYCSY3PWD', false);

        $csp = $this->get('/en')->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('www.googletagmanager.com', (string) $csp);
        $this->assertStringContainsString('sha256-a9fiG5Dd', (string) $csp);
        $this->assertStringContainsString('google-analytics.com', (string) $csp);
    }
}
