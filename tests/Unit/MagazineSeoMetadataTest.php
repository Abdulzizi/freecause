<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class MagazineSeoMetadataTest extends TestCase
{
    public function test_magazine_filters_fix_placeholders_without_rewriting_article_metadata(): void
    {
        $script = <<<'CODE'
        define('ABSPATH', __DIR__);
        $GLOBALS['home'] = true;
        $GLOBALS['hooks'] = [];
        function add_filter($hook, $callback, $priority = 10) { $GLOBALS['hooks'][$hook] = $callback; }
        function is_front_page() { return $GLOBALS['home']; }
        function is_home() { return false; }
        function get_query_var($key) { return 2; }
        function is_singular($type) { return !$GLOBALS['home']; }
        function get_post() { return (object) ['post_content' => '<p>An actual article about protecting public parks.</p>']; }
        function wp_strip_all_tags($value) { return strip_tags($value); }
        function strip_shortcodes($value) { return $value; }
        function home_url($path) { return 'https://www.freecause.com/magazine'.$path; }
        require $argv[1];
        $out = ['title' => fc_magazine_seo_title('Home'), 'home_description' => fc_magazine_seo_description('Read more')];
        $GLOBALS['home'] = false;
        $out['article_title'] = fc_magazine_seo_title('Original editorial title');
        $out['article_description'] = fc_magazine_seo_description('Author-written description');
        $out['fallback_description'] = fc_magazine_seo_description('Read more');
        $out['schema'] = fc_magazine_seo_schema([
            'publisher' => ['@id' => home_url('/').'#person', '@type' => ['Person', 'Organization'], 'name' => 'Media Republic', 'logo' => ['url' => 'http://www.freecause.com/magazine/old-logo.png', 'width' => 1848]],
            'author' => ['@id' => 'https://www.freecause.com/magazine/author/alex', '@type' => 'Person', 'name' => 'Alex'],
        ]);
        $out['hooks'] = array_keys($GLOBALS['hooks']);
        echo json_encode($out);
        CODE;
        $path = dirname(__DIR__, 2).'/public/magazine/wp-content/mu-plugins/fc-seo-metadata.php';
        $process = new Process([PHP_BINARY, '-r', $script, $path]);
        $process->mustRun();
        $data = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('FreeCause Magazine', $data['title']);
        $this->assertStringEndsWith(' | 2', $data['title']);
        $this->assertStringContainsString('civic action', $data['home_description']);
        $this->assertSame('Original editorial title', $data['article_title']);
        $this->assertSame('Author-written description', $data['article_description']);
        $this->assertSame('An actual article about protecting public parks.', $data['fallback_description']);
        $this->assertSame('Organization', $data['schema']['publisher']['@type']);
        $this->assertSame('Media Republic', $data['schema']['publisher']['name']);
        $this->assertSame('Person', $data['schema']['author']['@type']);
        $this->assertSame('https://www.freecause.com/legacy/images-v2/freecause_logo_icon_clear.png', $data['schema']['publisher']['logo']['url']);
        $this->assertArrayNotHasKey('width', $data['schema']['publisher']['logo']);
        $this->assertContains('rank_math/frontend/description', $data['hooks']);
    }
}
