<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyHeadRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_serves_external_analytics_without_inline_scripts(): void
    {
        $this->withoutMiddleware(SetLocale::class);

        $this->get('/en')
            ->assertOk()
            ->assertSee('js/analytics.js', false)
            ->assertSee('gtag/js?id=G-9CYCSY3PWD', false)
            ->assertDontSee('window.dataLayer', false);

        $csp = $this->get('/en')->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('www.googletagmanager.com', (string) $csp);
        $this->assertStringContainsString('google-analytics.com', (string) $csp);
    }
}
