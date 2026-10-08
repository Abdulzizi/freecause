<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SanitizeAdminHtmlTest extends TestCase
{
    public function test_returns_empty_string_for_empty_input(): void
    {
        $this->assertSame('', sanitizeAdminHtml(null));
        $this->assertSame('', sanitizeAdminHtml(''));
        $this->assertSame('', sanitizeAdminHtml("  \n "));
    }

    public function test_keeps_benign_formatting(): void
    {
        $html = '<p>text <strong>bold</strong> <em>italic</em> <u>under</u></p>';
        $out = sanitizeAdminHtml($html);

        $this->assertSame($html, $out);
    }

    public function test_keeps_structure_tags_expressed_as_allowlist(): void
    {
        $html = '<h2>t</h2><ul><li>a</li></ul><ol><li>b</li></ol><blockquote>q</blockquote><br><hr>';
        $out = sanitizeAdminHtml($html);

        $this->assertSame('<h2>t</h2><ul><li>a</li></ul><ol><li>b</li></ol><blockquote>q</blockquote><br /><hr />', $out);
    }

    public function test_strips_style_attribute(): void
    {
        $out = sanitizeAdminHtml('<p style="position:fixed;top:0">x</p>');

        $this->assertStringNotContainsString('style', $out);
        $this->assertSame('<p>x</p>', $out);
    }

    public function test_strips_style_element_and_its_contents(): void
    {
        $out = sanitizeAdminHtml('<style>body{background:url(javascript:alert(1))}</style><p>ok</p>');

        $this->assertStringNotContainsString('<style', $out);
        $this->assertStringNotContainsString('background', $out);
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_strips_event_handlers(): void
    {
        $out = sanitizeAdminHtml('<img src="/a.png" onerror="alert(1)" alt="a"><p onclick="alert(1)">x</p>');

        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('<img src="/a.png" alt="a" /><p>x</p>', $out);
    }

    public function test_removes_script_element_and_its_contents(): void
    {
        $out = sanitizeAdminHtml('<p>a</p><script>alert(1)</script><p>b</p>');

        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('<p>a</p><p>b</p>', $out);
    }

    public function test_removes_javascript_href_variants(): void
    {
        $this->assertSame('<a rel="noopener">x</a>', sanitizeAdminHtml('<a href="javascript:alert(1)">x</a>'));
        $this->assertSame('<a rel="noopener">x</a>', sanitizeAdminHtml('<a href="jav&#x61;script:alert(1)">x</a>'));
        $this->assertSame('<a rel="noopener">x</a>', sanitizeAdminHtml("<a href=\"java\tscript:alert(1)\">x</a>"));
    }

    public function test_drops_unknown_tags_with_their_contents(): void
    {
        $this->assertSame('', sanitizeAdminHtml('<foo>bar</foo>'));
        $this->assertSame('<p>a </p>', sanitizeAdminHtml('<p>a <marquee>b</marquee></p>'));
    }

    public function test_external_links_get_rel_noopener(): void
    {
        $this->assertSame('<a href="https://x.test/y" rel="noopener">x</a>', sanitizeAdminHtml('<a href="https://x.test/y">x</a>'));
        $this->assertSame('<a href="http://x.test/y" rel="noopener">x</a>', sanitizeAdminHtml('<a href="http://x.test/y">x</a>'));
        $this->assertSame('<a href="/en/foo" rel="noopener">x</a>', sanitizeAdminHtml('<a href="/en/foo">x</a>'));
        $this->assertSame('<a href="mailto:a&#64;b.com" rel="noopener">m</a>', sanitizeAdminHtml('<a href="mailto:a@b.com">m</a>'));
    }

    public function test_img_src_must_be_https_or_relative(): void
    {
        $this->assertSame('<img src="https://x.test/a.png" alt="a" />', sanitizeAdminHtml('<img src="https://x.test/a.png" alt="a">'));
        $this->assertSame('<img alt="a" />', sanitizeAdminHtml('<img src="http://x.test/a.png" alt="a">'));
        $this->assertSame('<img alt="x" />', sanitizeAdminHtml('<img src="data:text/html;base64,PHNjcmlwdD4=" alt="x">'));
        $this->assertSame('<img alt="x" />', sanitizeAdminHtml('<img src="//evil.test/x.png" alt="x">'));
        $this->assertSame('<img src="/legacy/images/logo7footer.png" alt="Freecause" />', sanitizeAdminHtml('<img src="/legacy/images/logo7footer.png" alt="Freecause">'));
    }

    public function test_is_idempotent(): void
    {
        $dirty = '<p style="x">a<script>alert(1)</script><a href="javascript:x" onclick="y">l</a><foo>b</foo></p>';

        $once = sanitizeAdminHtml($dirty);

        $this->assertSame($once, sanitizeAdminHtml($once));
    }
}
