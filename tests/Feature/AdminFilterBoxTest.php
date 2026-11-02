<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdminFilterBoxTest extends TestCase
{
    private function box(string $inner): string
    {
        return Blade::render('<x-admin.filter-box>'.$inner.'</x-admin.filter-box>');
    }

    public function test_injects_label_from_name(): void
    {
        $this->assertStringContainsString('<input aria-label="owner id" name="owner_id">', $this->box('<input name="owner_id">'));
        $this->assertStringContainsString('<select aria-label="status" name="status">', $this->box('<select name="status"></select>'));
    }

    public function test_prefers_placeholder(): void
    {
        $html = $this->box('<input name="owner" placeholder="Owner ID">');
        $this->assertStringContainsString('aria-label="Owner ID"', $html);
        $this->assertStringNotContainsString('aria-label="owner"', $html);
    }

    public function test_keeps_existing_aria_label(): void
    {
        $html = $this->box('<input aria-label="Own" name="a">');
        $this->assertSame(1, substr_count($html, 'aria-label'));
    }

    public function test_skips_checkbox_radio_and_hidden(): void
    {
        foreach (['checkbox', 'radio', 'hidden'] as $type) {
            $this->assertStringNotContainsString('aria-label', $this->box("<input type=\"$type\" name=\"a\">"), $type);
        }
    }

    public function test_no_name_gets_nothing(): void
    {
        $this->assertStringNotContainsString('aria-label', $this->box('<input placeholder="x">'));
    }

    public function test_data_name_is_not_a_name(): void
    {
        $this->assertStringNotContainsString('aria-label', $this->box('<input data-name="a">'));
        $this->assertStringNotContainsString('aria-label', $this->box('<input x-bind:name="a">'));
    }
}
