<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PetitionSanitizerTest extends TestCase
{
    public function test_keeps_allowed_formatting_tags(): void
    {
        $this->assertSame(
            '<p>Hello <strong>world</strong> <em>it</em> <u>is</u></p>',
            sanitizePetitionHtml('<p>Hello <strong>world</strong> <em>it</em> <u>is</u></p>')
        );
        $this->assertSame(
            '<ul><li>a</li></ul><ol><li>b</li></ol>',
            sanitizePetitionHtml('<ul><li>a</li></ul><ol><li>b</li></ol>')
        );
        $this->assertSame('<br />one<br />two', sanitizePetitionHtml('<br>one<br>two'));
    }

    public function test_b_and_i_tags_are_unwrapped_keeping_text(): void
    {
        $this->assertSame('x', sanitizePetitionHtml('<b>x</b>'));
        $this->assertSame('y', sanitizePetitionHtml('<i>y</i>'));
        $this->assertSame('<p>axc</p>', sanitizePetitionHtml('<p>a<b>x</b>c</p>'));
    }

    public function test_strips_all_attributes_from_allowed_tags(): void
    {
        $this->assertSame('<p>t</p>', sanitizePetitionHtml('<p class="x" onclick="y()">t</p>'));
    }

    public function test_drops_unknown_tags_with_their_contents(): void
    {
        $this->assertSame('', sanitizePetitionHtml('<foo>bar</foo>'));
        $this->assertSame('', sanitizePetitionHtml('<foo bar="1">bar</foo>'));
    }

    public function test_nested_unknown_elements_do_not_leak_unsafe_content(): void
    {
        $out = sanitizePetitionHtml('<foo><img src="x" onerror="alert(1)"></foo>');

        $this->assertStringNotContainsString('<foo', $out);
        $this->assertStringNotContainsString('<img', $out);
        $this->assertStringNotContainsString('src=', $out);
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('', $out);
    }

    public function test_script_tags_and_their_contents_are_dropped(): void
    {
        $out = sanitizePetitionHtml('<div><script>alert(1)</script>safe</div>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('alert(1)', $out);
        $this->assertSame('safe', $out);
    }
}
