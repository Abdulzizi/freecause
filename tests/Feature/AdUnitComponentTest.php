<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdUnitComponentTest extends TestCase
{
    public function test_renders_nothing_without_a_slot_id(): void
    {
        config([
            'services.adsense.enabled' => true,
            'services.adsense.client' => 'ca-pub-test',
            'services.adsense.slots.petition_sidebar' => null,
        ]);

        $html = Blade::render('<x-ad-unit placement="petition_sidebar" />');

        $this->assertStringNotContainsString('adsbygoogle', $html);
        $this->assertSame('', trim($html));
    }

    public function test_renders_nothing_when_disabled(): void
    {
        config([
            'services.adsense.enabled' => false,
            'services.adsense.client' => 'ca-pub-test',
            'services.adsense.slots.petition_sidebar' => '1234567890',
        ]);

        $html = Blade::render('<x-ad-unit placement="petition_sidebar" />');

        $this->assertStringNotContainsString('adsbygoogle', $html);
    }

    public function test_renders_ins_with_client_and_slot_when_configured(): void
    {
        config([
            'services.adsense.enabled' => true,
            'services.adsense.client' => 'ca-pub-5636773008040262',
            'services.adsense.slots.petition_sidebar' => '1234567890',
        ]);

        $html = Blade::render('<x-ad-unit placement="petition_sidebar" />');

        $this->assertStringContainsString('class="adsbygoogle', $html);
        $this->assertStringContainsString('data-ad-client="ca-pub-5636773008040262"', $html);
        $this->assertStringContainsString('data-ad-slot="1234567890"', $html);
        $this->assertStringContainsString('data-adsense', $html);
    }
}
