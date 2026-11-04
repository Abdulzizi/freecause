<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\View\ViewException;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DbConnectionFailure503Test extends TestCase
{
    use RefreshDatabase; // global middleware queries the DB before the route throws

    private function failWith(string $message, bool $wrap = false): void
    {
        $this->seed(LanguageSeeder::class);
        Route::get('/{lang}/_boom/x/y', function () use ($message, $wrap) {
            $e = new QueryException('mysql', 'select 1', [], new PDOException($message));
            throw $wrap ? new ViewException('view failed', 0, 1, null, null, $e) : $e;
        })->where('lang', '[a-z]{2}');
    }

    #[DataProvider('connectionFailures')]
    public function test_connection_failures_return_static_503(string $message, bool $wrap): void
    {
        $this->failWith($message, $wrap);

        $r = $this->get('/de/_boom/x/y')->assertStatus(503)->assertHeader('Retry-After');
        $this->assertStringContainsString('<html lang="de">', $r->getContent());
        $this->getJson('/en/_boom/x/y')->assertStatus(503)->assertHeader('Retry-After');
    }

    public static function connectionFailures(): array
    {
        return [
            '2002' => ['SQLSTATE[HY000] [2002] Connection refused', false],
            '1040' => ['SQLSTATE[08004] [1040] Too many connections', false],
            '1040 in ViewException' => ['SQLSTATE[08004] [1040] Too many connections', true],
        ];
    }

    public function test_other_query_exception_stays_500(): void
    {
        $this->failWith('SQLSTATE[42S02]: Base table or view not found: 1146 no table');

        $this->get('/en/_boom/x/y')->assertStatus(500)->assertHeaderMissing('Retry-After');
    }
}
