<?php

namespace Tests\Feature;

use Tests\TestCase;

class SanitizeAdminHtmlTest extends TestCase
{
    public function test_returns_empty_string_for_empty_input(): void
    {
        $this->assertSame('', sanitizeAdminHtml(null));
        $this->assertSame('', sanitizeAdminHtml(''));
        $this->assertSame('', sanitizeAdminHtml("  \n "));
    }

    public function test_keeps_benign_formatting_byte_for_byte(): void
    {
        $html = '<p>text <strong>bold</strong> <a href="https://x" rel="noopener">link</a></p>';

        $this->assertSame($html, sanitizeAdminHtml($html));
    }

    public function test_keeps_relative_links_and_anchors(): void
    {
        $this->assertSame('<a href="/en/foo">x</a>', sanitizeAdminHtml('<a href="/en/foo">x</a>'));
        $this->assertSame('<a href="foo/bar">x</a>', sanitizeAdminHtml('<a href="foo/bar">x</a>'));
        $this->assertSame('<a href="#top">x</a>', sanitizeAdminHtml('<a href="#top">x</a>'));
    }

    public function test_keeps_mailto_and_tel_links(): void
    {
        $this->assertSame('<a href="mailto:a@b.com">m</a>', sanitizeAdminHtml('<a href="mailto:a@b.com">m</a>'));
        $this->assertSame('<a href="tel:+123">t</a>', sanitizeAdminHtml('<a href="tel:+123">t</a>'));
    }

    public function test_removes_script_element_and_its_contents(): void
    {
        $out = sanitizeAdminHtml('<p>a</p><script>alert(1)</script><p>b</p>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('<p>a</p><p>b</p>', $out);
    }

    public function test_removes_style_element_and_its_contents(): void
    {
        $out = sanitizeAdminHtml('<style>body{background:url(javascript:alert(1))}</style><p>ok</p>');

        $this->assertStringNotContainsString('<style', $out);
        $this->assertStringNotContainsString('background', $out);
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_removes_img_onerror_handler(): void
    {
        $out = sanitizeAdminHtml('<img src="/a.png" onerror="alert(1)" alt="a">');

        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('<img src="/a.png" alt="a">', $out);
    }

    public function test_removes_style_attribute(): void
    {
        $out = sanitizeAdminHtml('<p style="position:fixed;top:0;width:100%">x</p>');

        $this->assertStringNotContainsString('style', $out);
        $this->assertSame('<p>x</p>', $out);
    }

    public function test_removes_javascript_href(): void
    {
        $out = sanitizeAdminHtml('<a href="javascript:alert(1)">x</a>');

        $this->assertStringNotContainsString('javascript', $out);
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_removes_encoded_javascript_href(): void
    {
        $out = sanitizeAdminHtml('<a href="jav&#x61;script:alert(1)">x</a>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_removes_whitespace_split_javascript_href(): void
    {
        $out = sanitizeAdminHtml("<a href=\"java\tscript:alert(1)\">x</a>");

        $this->assertStringNotContainsString('script', $out);
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_removes_data_uri_src(): void
    {
        $out = sanitizeAdminHtml('<img src="data:text/html;base64,PHNjcmlwdD4=" alt="x">');

        $this->assertStringNotContainsString('data:', $out);
        $this->assertSame('<img alt="x">', $out);
    }

    public function test_removes_protocol_relative_urls(): void
    {
        $this->assertSame('<a>x</a>', sanitizeAdminHtml('<a href="//evil.test/x">x</a>'));
        $this->assertSame('<img alt="x">', sanitizeAdminHtml('<img src="//evil.test/x.png" alt="x">'));
    }

    public function test_removes_srcset(): void
    {
        $out = sanitizeAdminHtml('<img src="/a.png" srcset="data:text/html,<script>alert(1)</script>">');

        $this->assertStringNotContainsString('srcset', $out);
        $this->assertSame('<img src="/a.png">', $out);
    }

    public function test_removes_svg_with_onload(): void
    {
        $out = sanitizeAdminHtml('<svg onload="alert(1)"><circle r="5"></circle></svg><p>ok</p>');

        $this->assertStringNotContainsString('svg', $out);
        $this->assertStringNotContainsString('onload', $out);
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_removes_math_element(): void
    {
        $out = sanitizeAdminHtml('<math><mtext><mglyph xlink:href="javascript:alert(1)">x</mglyph></mtext></math><p>ok</p>');

        $this->assertStringNotContainsString('math', $out);
        $this->assertStringNotContainsString('javascript', $out);
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_removes_iframe_form_and_input(): void
    {
        $out = sanitizeAdminHtml('<form action="/x"><input name="a" value="b"><button>go</button></form><iframe src="https://evil.test"></iframe><p>ok</p>');

        $this->assertStringNotContainsString('<form', $out);
        $this->assertStringNotContainsString('<input', $out);
        $this->assertStringNotContainsString('<iframe', $out);
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_unwraps_unknown_tag_keeping_text(): void
    {
        $this->assertSame('bar', sanitizeAdminHtml('<foo>bar</foo>'));
        $this->assertSame('<p>a b</p>', sanitizeAdminHtml('<p>a <marquee>b</marquee></p>'));
    }

    public function test_keeps_layout_containers_with_class_only(): void
    {
        $this->assertSame(
            '<div class="footer-logo"><img src="/legacy/images/logo7footer.png" alt="Freecause"></div>',
            sanitizeAdminHtml('<div class="footer-logo" style="display:none"><img src="/legacy/images/logo7footer.png" alt="Freecause" onload="x()"></div>')
        );
        $this->assertSame('<section class="a">t</section>', sanitizeAdminHtml('<section class="a">t</section>'));
    }

    public function test_keeps_editor_produced_markup(): void
    {
        $html = '<h3>Title</h3><ul><li><strong>a</strong> <em>b</em></li></ul>'
            .'<table border="1"><tbody><tr><td colspan="2">c</td></tr></tbody></table><hr>';

        $out = sanitizeAdminHtml($html);

        $this->assertStringContainsString('<h3>Title</h3>', $out);
        $this->assertStringContainsString('<li><strong>a</strong> <em>b</em></li>', $out);
        $this->assertStringContainsString('<td colspan="2">c</td>', $out);
        $this->assertStringContainsString('<hr>', $out);
        $this->assertStringNotContainsString('border', $out);
    }

    public function test_neutralizes_class_attribute(): void
    {
        $this->assertSame('<p class="a b">x</p>', sanitizeAdminHtml('<p class="a b" onclick="alert(1)">x</p>'));
        $this->assertSame('<p class="ab">x</p>', sanitizeAdminHtml('<p class="a&quot;b">x</p>'));
        $this->assertSame('<p class="abc">x</p>', sanitizeAdminHtml('<p class="a<b>c">x</p>'));
    }

    public function test_removes_comments_and_keeps_surrounding_text(): void
    {
        $this->assertSame('<p>ab</p>', sanitizeAdminHtml('<p>a<!-- [if IE]><script>alert(1)</script><![endif] -->b</p>'));
    }

    public function test_is_idempotent(): void
    {
        $dirty = '<p style="x">a<script>alert(1)</script><a href="javascript:x" onclick="y">l</a><foo>b</foo></p>';

        $once = sanitizeAdminHtml($dirty);

        $this->assertSame($once, sanitizeAdminHtml($once));
    }

    public function test_malformed_input_does_not_throw_and_stays_escaped(): void
    {
        $out = sanitizeAdminHtml('<p>unclosed <strong>bold <a href="javascript:alert(1)">x');

        $this->assertStringNotContainsString('javascript', $out);
        $this->assertIsString($out);
    }
}
