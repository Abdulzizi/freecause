<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersionEndpointTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = storage_path('app/version.json');
    }

    public function test_returns_the_stamped_version(): void
    {
        $original = $this->capture();

        file_put_contents($this->path, json_encode([
            'version' => 'v-test',
            'release' => 'v-test',
            'commit' => 'abc1234',
            'deployed_at' => '2026-01-01T00:00:00+00:00',
        ]));

        try {
            $this->get('/version')->assertOk()->assertJson([
                'commit' => 'abc1234',
                'release' => 'v-test',
                'deployed_at' => '2026-01-01T00:00:00+00:00',
            ]);
        } finally {
            $this->restore($original);
        }
    }

    public function test_returns_nulls_when_not_stamped(): void
    {
        $original = $this->capture();
        @unlink($this->path);

        try {
            $this->get('/version')->assertOk()->assertJson([
                'commit' => null,
                'release' => null,
                'deployed_at' => null,
            ]);
        } finally {
            $this->restore($original);
        }
    }

    private function capture(): ?string
    {
        return is_file($this->path) ? (string) file_get_contents($this->path) : null;
    }

    private function restore(?string $original): void
    {
        if ($original === null) {
            @unlink($this->path);

            return;
        }

        file_put_contents($this->path, $original);
    }
}
