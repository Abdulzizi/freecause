<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicValidationToastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_login_page_renders_validation_fallback_marker(): void
    {
        $this->post('/en/login', ['email' => 'not-an-email', 'password' => '']);

        $response = $this->get('/en/login');

        $response->assertOk();
        $response->assertSee('id="validation-errors"', false);
    }

    public function test_forgot_password_status_renders_success_alert_for_toast(): void
    {
        $response = $this->withSession(['status' => 'Reset link sent.'])->get('/en/forgot-password');

        $response->assertOk();
        $response->assertSee('data-alert-variant="success"', false);
        $response->assertSee('Reset link sent.', false);
    }

    public function test_alert_component_tags_error_variant(): void
    {
        $view = $this->blade(
            '<x-ui.alert variant="error" class="mb-4">Failed</x-ui.alert>'
        );

        $view->assertSee('data-alert-variant="error"', false);
    }

    public function test_warning_alert_stays_inline(): void
    {
        $view = $this->blade(
            '<x-ui.alert variant="warning" class="mb-4">Heads up</x-ui.alert>'
        );

        $view->assertSee('data-alert-variant="warning"', false);
    }
}
