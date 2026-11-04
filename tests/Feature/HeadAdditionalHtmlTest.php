<?php

namespace Tests\Feature;

use App\Models\PageContent;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadAdditionalHtmlTest extends TestCase
{
    use RefreshDatabase;

    // Production keeps Google site-verification tags here; dropping it unverifies Search Console.
    public function test_global_head_additional_html_is_rendered_in_the_layout(): void
    {
        $this->seed(LanguageSeeder::class);
        PageContent::updateOrCreate(
            ['page' => 'global', 'locale' => 'en', 'key' => 'head_additional_html'],
            ['value' => '<meta name="google-site-verification" content="test-token-123">']
        );

        $this->get('/en')
            ->assertOk()
            ->assertSee('<meta name="google-site-verification" content="test-token-123">', false);
    }
}
