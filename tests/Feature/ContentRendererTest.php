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

    public function test_url_encoded_tokens_are_replaced_with_links(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('<a href="%7BETHICAL_CODE_LINK%7D">Ethical Code</a> %7BCONTACTS_URL%7D %7BSITE_NAME%7D');

        $this->assertStringContainsString('>Ethical Code</a>', $out);
        $this->assertStringContainsString('/ethical-code', $out);
        $this->assertStringContainsString('/contacts', $out);
        $this->assertStringContainsString(config('app.name'), $out);
        $this->assertStringNotContainsString('%7B', $out);
    }

    public function test_non_token_braces_are_preserved(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::render('<style>.x{color:red}</style>');

        $this->assertStringContainsString('{color:red}', $out);
    }

    public function test_faq_accordion_builds_bootstrap_collapse_markup(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::renderFaqAccordion(
            '<h2>General Information</h2><h3>Are online petitions useful?</h3><p>Yes they are.</p><h3>Second question</h3><p>Answer two.</p>'
        );

        $this->assertStringContainsString('<h2 class="mb-4! text-xl! font-bold!">General Information</h2>', $out);
        $this->assertStringContainsString('class="accordion-button collapsed"', $out);
        $this->assertStringContainsString('data-bs-toggle="collapse" data-bs-target="#faqCollapse1"', $out);
        $this->assertStringContainsString('<p>Yes they are.</p>', $out);
        $this->assertStringContainsString('data-bs-parent="#faqAcc-general-information"', $out);
        $this->assertStringContainsString('id="faqCollapse2"', $out);
        $this->assertStringContainsString('Second question', $out);
    }

    public function test_faq_accordion_falls_back_to_plain_render_without_questions(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::renderFaqAccordion('<h2>Heading</h2><p>Just a paragraph.</p>');

        $this->assertStringNotContainsString('accordion', $out);
        $this->assertStringContainsString('<h2>Heading</h2>', $out);
    }

    public function test_faq_accordion_replaces_tokens_in_answers(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::renderFaqAccordion('<h2>Info</h2><h3>Q</h3><p>{CONTACTS_LINK}</p>');

        $this->assertStringContainsString('>Contacts</a>', $out);
        $this->assertStringNotContainsString('{CONTACTS_LINK}', $out);
    }

    public function test_faq_accordion_renders_placeholder_for_empty_answer(): void
    {
        app()->setLocale('en');

        $out = ContentRenderer::renderFaqAccordion('<h2>Info</h2><h3>Q</h3><p>   </p>');

        $this->assertStringContainsString('text-gray-500!">...</p>', $out);
    }

    public function test_faq_accordion_handles_null_and_empty_input(): void
    {
        $this->assertSame('', ContentRenderer::renderFaqAccordion(null));
        $this->assertSame('', ContentRenderer::renderFaqAccordion(''));
    }
}
