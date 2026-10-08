<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminJsWiringTest extends TestCase
{
    private const HOOKS = [
        'data-live-validate',
        'data-slug-source',
        'data-slug-target',
        'data-detail-row',
        'fc-row-open',
        'data-live-search',
        'Undo',
    ];

    public function test_admin_source_defines_client_hooks(): void
    {
        $source = (string) file_get_contents(resource_path('js/admin.js'));

        foreach (glob(resource_path('js/admin/*.js')) ?: [] as $module) {
            $source .= (string) file_get_contents($module);
        }

        foreach (self::HOOKS as $hook) {
            $this->assertStringContainsString($hook, $source, "admin.js should wire {$hook}.");
        }

        $this->assertStringNotContainsString('prefetchTarget', $source);
        $this->assertStringNotContainsString('prefetchUrl', $source);
    }

    public function test_built_admin_bundle_contains_client_hooks(): void
    {
        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            $this->markTestSkipped('Vite build manifest is not present.');
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/js/admin.js', $manifest);

        $builtPath = public_path('build/'.$manifest['resources/js/admin.js']['file']);

        $this->assertFileExists($builtPath);

        $bundle = (string) file_get_contents($builtPath);

        foreach (self::HOOKS as $hook) {
            $this->assertStringContainsString($hook, $bundle, "Built admin bundle should contain {$hook}.");
        }
    }
}
