<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DeploymentSnapshotTest extends TestCase
{
    private function snapshot(string $scenario): array
    {
        $directory = sys_get_temp_dir().'/fc-deploy-test-'.bin2hex(random_bytes(6));
        mkdir($directory.'/recipe', 0700, true);
        file_put_contents($directory.'/recipe/laravel.php', '<?php');
        $stub = <<<'PHP'
namespace Deployer;
$GLOBALS['scenario'] = $argv[1];
$GLOBALS['events'] = [];
function set(...$args) {}
function add(...$args) {}
function desc(...$args) {}
function before(...$args) {}
function after(...$args) {}
function host(...$args) { return new class { public function __call($name, $args) { return $this; } }; }
function task($name, $callback) { $GLOBALS['tasks'][$name] = $callback; }
function test(...$args) { return true; }
function writeln(...$args) {}
function invoke($task) { $GLOBALS['events'][] = $task; }
function run($command, ...$options) {
    $scenario = $GLOBALS['scenario'];
    if (str_contains($command, 'migrate:status')) return $scenario === 'none' ? 'No pending migrations' : 'Pending';
    if (preg_match('/\^([A-Z_]+)=/', $command, $match)) return ['DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_DATABASE'=>'fixture','DB_USERNAME'=>'fixture','DB_PASSWORD'=>'fixture'][$match[1]];
    if (str_contains($command, 'df -Pk')) return '99999999';
    if (str_contains($command, 'information_schema.TABLES')) return $scenario === 'nontransactional' ? '1' : '0';
    if (str_contains($command, 'mysqldump --single-transaction')) {
        $GLOBALS['events'][] = 'snapshot';
        if ($scenario === 'failed') throw new \RuntimeException('Synthetic snapshot failure');
    }
    return '';
}
require $argv[2];
try { $GLOBALS['tasks']['db:snapshot'](); } catch (\RuntimeException $e) { $GLOBALS['events'][] = 'failed'; }
echo json_encode($GLOBALS['events']);
PHP;
        try {
            $process = new Process([PHP_BINARY, '-d', 'include_path='.$directory, '-r', $stub, $scenario, dirname(__DIR__, 2).'/deploy/deploy.php']);
            $process->mustRun();

            return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            unlink($directory.'/recipe/laravel.php');
            rmdir($directory.'/recipe');
            rmdir($directory);
        }
    }

    public function test_transactional_snapshot_finishes_before_maintenance_starts(): void
    {
        $this->assertSame(['snapshot', 'artisan:down'], $this->snapshot('transactional'));
    }

    public function test_nontransactional_database_keeps_the_original_quiet_snapshot(): void
    {
        $this->assertSame(['artisan:down', 'snapshot'], $this->snapshot('nontransactional'));
    }

    public function test_failed_online_snapshot_never_takes_the_site_down(): void
    {
        $this->assertSame(['snapshot', 'failed'], $this->snapshot('failed'));
    }

    public function test_no_pending_migration_needs_neither_snapshot_nor_maintenance(): void
    {
        $this->assertSame([], $this->snapshot('none'));
    }

    public function test_rollback_restarts_queue_and_scheduler(): void
    {
        $recipe = file_get_contents(dirname(__DIR__, 2).'/deploy/deploy.php');

        $this->assertStringContainsString("after('rollback', 'queue:restart');", $recipe);
        $this->assertStringContainsString("after('rollback', 'scheduler:restart');", $recipe);
    }
}
