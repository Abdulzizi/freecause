<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdComponentsTest extends TestCase
{
    public function test_infeed_component_renders_without_a_name_prop(): void
    {
        config(['ads.enabled' => true]);

        foreach (array_keys(config('ads.slots', [])) as $slot) {
            config(["ads.slots.{$slot}.key" => '']);
        }

        $this->blade('<x-ads.infeed />')
            ->assertDontSee('fc-ad-infeed');
    }
}
