<?php

namespace Tests\Feature;

use App\Services\ContentRenderer;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentRendererTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class]);
    }

    public function test_known_tokens_are_replaced_with_links(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('{CONTACTS_LINK} {ETHICAL_CODE_LINK} {TOS_LINK} {PRIVACY_POLICY_ROOT_LINK}');

        $this->assertStringContainsString('>Contacts</a>', $out);
        $this->assertStringContainsString('>Ethical Code</a>', $out);
        $this->assertStringContainsString('>Terms of Service</a>', $out);
        $this->assertStringContainsString('>Privacy Policy</a>', $out);
    }

    public function test_privacy_policy_link_alias_resolves(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('{PRIVACY_POLICY_LINK}');

        $this->assertStringContainsString('/privacy-policy', $out);
        $this->assertStringContainsString('>Privacy Policy</a>', $out);
        $this->assertStringNotContainsString('{PRIVACY_POLICY_LINK}', $out);
    }

    public function test_unknown_tokens_are_stripped_not_leaked(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('A {UNKNOWN_TOKEN} B {ANOTHER_MISSING_ONE} C');

        $this->assertStringNotContainsString('{UNKNOWN_TOKEN}', $out);
        $this->assertStringNotContainsString('{ANOTHER_MISSING_ONE}', $out);
        $this->assertStringContainsString('A ', $out);
        $this->assertStringContainsString('B ', $out);
        $this->assertStringContainsString('C', $out);
    }

    public function test_null_and_empty_input_render_empty(): void
    {
        $this->assertSame('', ContentRenderer::render(null));
        $this->assertSame('', ContentRenderer::render(''));
    }

    public function test_non_token_braces_are_preserved(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('<style>.x{color:red}</style>');

        $this->assertStringContainsString('{color:red}', $out);
    }
}
