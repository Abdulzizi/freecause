<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelRevampB2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->withoutVite();
        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($admin, 'admin');
    }

    /** @return array<string, string> */
    private function adminViews(): array
    {
        return [
            '/admin/dashboard' => 'Dashboard',
            '/admin/petitions' => 'petitions',
            '/admin/pages' => 'pages',
            '/admin/users' => 'users',
            '/admin/signatures' => 'signatures',
            '/admin/categories' => 'categories',
            '/admin/spam' => 'spam',
            '/admin/stats' => 'statistics',
            '/admin/logs' => 'logs',
            '/admin/languages' => 'languages',
            '/admin/options/global' => 'Site Settings',
            '/admin/options/language?locale=en' => 'localized content',
            '/admin/ads' => 'ads.txt',
            '/admin/system/user-info' => 'user info',
            '/admin/system/user-levels' => 'user levels',
            '/admin/system/permissions' => 'permissions',
        ];
    }

    public function test_admin_views_render_key_content_and_shared_page_header(): void
    {
        foreach ($this->adminViews() as $uri => $heading) {
            $response = $this->get($uri);

            $response->assertOk()->assertSee($heading);

            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            $componentHeaders = $xpath->query(
                '//h1[contains(@class, "m-0!") and contains(@class, "text-lg!")] | //*[@data-slot="page-header"]//h1'
            );

            $this->assertGreaterThan(
                0,
                $componentHeaders->length,
                "{$uri} should render the shared admin page-header structure."
            );
        }
    }

    public function test_admin_views_have_no_inline_event_attributes_or_native_dialog_calls(): void
    {
        foreach ($this->adminViews() as $uri => $_heading) {
            $html = $this->get($uri)->assertOk()->getContent();
            $this->assertDelegatedMarkup($html, $uri);
        }
    }

    public function test_global_options_password_toggle_and_selected_detail_views_render(): void
    {
        $this->get('/admin/options/global')
            ->assertOk()
            ->assertSee('id="smtp_pass_input"', false)
            ->assertSee('id="toggle_smtp_pass"', false)
            ->assertSee('name="smtp_pass"', false);

        $petition = Petition::factory()->create();
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Selected petition detail',
            'slug' => 'selected-petition-detail',
            'description' => '<p>Petition body</p>',
            'is_featured' => false,
        ]);

        $page = Page::create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Selected page detail',
            'slug' => 'selected-page-detail',
            'content' => '<p>Page body</p>',
            'published' => true,
        ]);

        $user = User::factory()->create([
            'email' => 'selected-detail@example.test',
            'level_id' => UserLevel::where('name', 'user')->value('id'),
        ]);

        $log = Log::create([
            'level' => 'info',
            'context' => 'admin.audit',
            'title' => 'Selected log detail',
            'content' => 'Selected log body',
        ]);

        $selectedViews = [
            "/admin/petitions?select={$petition->id}&locale=en" => 'Selected petition detail',
            "/admin/pages?select={$page->id}&locale=en" => 'Selected page detail',
            "/admin/users?select={$user->id}" => $user->email,
            "/admin/logs?select={$log->id}" => 'Selected log body',
        ];

        foreach ($selectedViews as $uri => $expected) {
            $html = $this->get($uri)->assertOk()->assertSee($expected)->getContent();
            $this->assertDelegatedMarkup($html, $uri);
        }
    }

    private function assertDelegatedMarkup(string $html, string $uri): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $eventAttributes = [
            'onclick', 'ondblclick', 'onchange', 'onsubmit', 'oninput', 'onmouseover', 'onmouseout',
            'onload', 'onerror', 'onfocus', 'onblur', 'onkeydown', 'onkeyup', 'onkeypress',
            'onmouseenter', 'onmouseleave', 'onpointerdown', 'onpointerup', 'onscroll',
        ];
        $inlineHandlers = $xpath->query('//*['.implode(' or ', array_map(fn ($attribute) => '@'.$attribute, $eventAttributes)).']');

        $this->assertSame(
            0,
            $inlineHandlers->length,
            "{$uri} should delegate browser events instead of using inline event attributes."
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\b(?:window\s*\.\s*)?(?:alert|confirm|prompt)\s*\(/i',
            $html,
            "{$uri} should not call native alert/confirm/prompt dialogs."
        );
    }
}
