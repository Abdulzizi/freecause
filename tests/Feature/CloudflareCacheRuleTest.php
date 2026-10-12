<?php

namespace Tests\Feature;

use Tests\TestCase;

class CloudflareCacheRuleTest extends TestCase
{
    private function rule(): array
    {
        $path = base_path('deploy/cloudflare/cache-rules.json');

        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_rule_is_disabled_until_the_origin_ships(): void
    {
        $this->assertFalse($this->rule()['enabled']);
    }

    public function test_expression_uses_only_free_plan_operators(): void
    {
        $expression = $this->rule()['expression'];

        foreach (['matches', 'starts_with', 'ends_with', 'wildcard', 'lower('] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $expression, "Free plan rejects {$forbidden}");
        }

        $this->assertStringContainsString('http.request.uri.path contains', $expression);
    }

    public function test_expression_requires_an_anonymous_queryless_request(): void
    {
        $expression = $this->rule()['expression'];

        $this->assertStringContainsString('http.cookie eq ""', $expression);
        $this->assertStringContainsString('http.request.uri.query eq ""', $expression);
        $this->assertStringContainsString('http.request.headers["authorization"]', $expression);
        $this->assertStringContainsString('http.host eq "www.freecause.com"', $expression);
    }

    public function test_expression_covers_every_allowlisted_route_shape(): void
    {
        $expression = $this->rule()['expression'];
        $config = config('edge-cache.routes');

        $this->assertContains('petitions.index', $config);
        $this->assertContains('petitions.byCategory', $config);
        $this->assertContains('petition.show', $config);

        foreach (['/petitions', '/petition/', '/faqs', '/how-to-write-a-petition', '/donations', '/personal-data-processing', '/terms-of-service', '/privacy-policy', '/ethical-code'] as $shape) {
            $this->assertStringContainsString('contains "'.$shape.'"', $expression, $shape);
        }
    }

    public function test_expression_guards_personalized_and_form_paths(): void
    {
        $expression = $this->rule()['expression'];

        foreach (['/user/', '/admin', '/sign', '/create-petition', '/download', '/widget'] as $guard) {
            $this->assertStringContainsString('not (http.request.uri.path contains "'.$guard.'")', $expression, $guard);
        }
    }

    public function test_rule_respects_the_origin_cache_control(): void
    {
        $parameters = $this->rule()['action_parameters'];

        $this->assertTrue($parameters['cache']);
        $this->assertSame('bypass_by_default', $parameters['edge_ttl']['mode']);
        $this->assertSame('respect_origin', $parameters['browser_ttl']['mode']);
    }
}
