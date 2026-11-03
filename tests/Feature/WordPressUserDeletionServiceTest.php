<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WordPressUserDeletionService;
use RuntimeException;
use Tests\TestCase;

class WordPressUserDeletionServiceTest extends TestCase
{
    public function test_delete_throws_when_wordpress_cannot_reach_its_database(): void
    {
        // Port 1 on loopback is closed, so WordPress prints its DB error page and exits 0.
        $message = $this->deleteMessage(new WordPressUserDeletionService, '1');

        $this->assertStringContainsString('WordPress cleanup did not complete', $message);
        $this->assertStringNotContainsString('nobody@example.test', $message);
        $this->assertStringNotContainsString('Error establishing', $message);
    }

    public function test_delete_throws_a_plain_message_when_wordpress_times_out(): void
    {
        // A listener that never accepts leaves mysqli waiting for the server greeting.
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (string) parse_url('tcp://'.stream_socket_get_name($server, false), PHP_URL_PORT);

        try {
            $message = $this->deleteMessage(new WordPressUserDeletionService(1), $port);
        } finally {
            fclose($server);
        }

        $this->assertSame('WordPress cleanup did not complete (timed out after 1s).', $message);
    }

    private function deleteMessage(WordPressUserDeletionService $service, string $port): string
    {
        $vars = ['MAGAZINE_DB_HOST' => '127.0.0.1', 'MAGAZINE_DB_PORT' => $port];
        $saved = [];
        foreach ($vars as $key => $value) {
            $saved[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        $user = new User;
        $user->email = 'nobody@example.test';

        try {
            $service->delete($user);
            $this->fail('Expected the WordPress cleanup to throw.');
        } catch (RuntimeException $e) {
            return $e->getMessage();
        } finally {
            foreach ($saved as $key => [$env, $envArr, $server]) {
                putenv($env === false ? $key : "{$key}={$env}");
                if ($envArr === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = $envArr;
                }
                if ($server === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $server;
                }
            }
        }
    }
}
