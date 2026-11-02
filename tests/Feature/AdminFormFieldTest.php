<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdminFormFieldTest extends TestCase
{
    private function field(string $inner, string $attrs = 'label="Name"'): string
    {
        return Blade::render('<x-admin.form-field '.$attrs.'>'.$inner.'</x-admin.form-field>');
    }

    public function test_injects_aria_label_on_first_control_only(): void
    {
        foreach (['<input name="a"><input name="b">', '<select name="a"></select><input name="b">', '<textarea name="a"></textarea><input name="b">'] as $inner) {
            $html = $this->field($inner);
            $this->assertSame(1, substr_count($html, 'aria-label="Name"'));
            $this->assertMatchesRegularExpression('/<(input|select|textarea) aria-label="Name" name="a">/', $html);
        }
    }

    public function test_no_injection_when_for_is_set_or_label_empty(): void
    {
        $this->assertStringNotContainsString('aria-label', $this->field('<input id="x">', 'label="Name" for="x"'));
        $this->assertStringContainsString('for="x"', $this->field('<input id="x">', 'label="Name" for="x"'));
        $this->assertStringNotContainsString('aria-label', $this->field('<input name="a">', 'label=""'));
    }

    public function test_skips_checkbox_radio_and_hidden_in_any_quoting(): void
    {
        foreach (['checkbox', 'radio', 'hidden'] as $type) {
            foreach (["type=\"$type\"", "type='$type'", "type=$type"] as $attr) {
                $this->assertStringNotContainsString('aria-label', $this->field("<input $attr name=\"a\">"), $attr);
            }
        }
    }

    public function test_hidden_does_not_block_a_later_control(): void
    {
        $html = $this->field('<input type="hidden" name="h"><input name="a">');
        $this->assertStringContainsString('<input type="hidden" name="h">', $html);
        $this->assertSame(1, substr_count($html, 'aria-label'));
    }

    public function test_data_type_hidden_does_not_skip_control(): void
    {
        $this->assertStringContainsString('aria-label="Name"', $this->field('<input data-type="hidden" name="a">'));
    }

    public function test_existing_aria_label_is_not_duplicated(): void
    {
        $html = $this->field('<input aria-label="Own" name="a">');
        $this->assertSame(1, substr_count($html, 'aria-label'));
        $this->assertStringContainsString('aria-label="Own"', $html);
    }

    public function test_data_aria_label_does_not_count_as_existing(): void
    {
        $html = $this->field('<input data-aria-label="x" name="a">');
        $this->assertStringContainsString('aria-label="Name" data-aria-label="x"', $html);
    }

    public function test_dollar_and_backslash_in_label_stay_literal(): void
    {
        $html = $this->field('<input name="a">', 'label="Cost $1 \\0 \\1"');
        $this->assertStringContainsString('aria-label="Cost $1 \\0 \\1"', $html);
    }

    public function test_label_is_html_escaped(): void
    {
        $html = Blade::render('<x-admin.form-field :label="$l"><input name="a"></x-admin.form-field>', ['l' => 'A "b" <i>c</i>']);
        $this->assertStringContainsString('aria-label="A &quot;b&quot; &lt;i&gt;c&lt;/i&gt;"', $html);
        $this->assertStringNotContainsString('<i>', $html);
    }

    public function test_hint_and_error_render(): void
    {
        $html = $this->field('<input name="a">', 'label="Name" hint="Some hint" error="Bad value"');
        $this->assertStringContainsString('Some hint', $html);
        $this->assertStringContainsString('role="alert">Bad value<', $html);
    }
}
