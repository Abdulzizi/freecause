<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class WordPressUserDeletionService
{
    public function __construct(private int $timeout = 20) {}

    public function delete(User $user): void
    {
        // The e2e server has no WordPress database, so there is no WordPress user to clean up.
        if (app()->environment('e2e')) {
            return;
        }

        $wpLoad = base_path('public/magazine/wp-load.php');

        if (! is_file($wpLoad)) {
            throw new RuntimeException('WordPress bootstrap was not found.');
        }

        $script = <<<'PHP'
require $argv[1];
require_once ABSPATH.'wp-admin/includes/user.php';
global $wpdb;
$email = $argv[2];
$id = $wpdb->get_var($wpdb->prepare(
    'SELECT ID FROM '.$wpdb->users.' WHERE user_email = %s OR user_login = %s',
    $email,
    $email
));
if ($wpdb->last_error) {
    exit(3);
}
if ($id && ! wp_delete_user((int) $id)) {
    exit(2);
}
echo 'FREECAUSE_WP_DELETE_DONE';
PHP;

        $process = new Process([
            PHP_BINDIR.DIRECTORY_SEPARATOR.'php',
            '-r',
            $script,
            $wpLoad,
            $user->email,
        ]);
        // Plain messages only: the command line holds the email and WordPress output can hold DB details.
        try {
            $process->setTimeout($this->timeout)->run();
        } catch (ProcessTimedOutException) {
            throw new RuntimeException("WordPress cleanup did not complete (timed out after {$this->timeout}s).");
        }

        // WordPress exits 0 after printing its own error page when it cannot reach its database.
        if (! $process->isSuccessful() || ! str_contains($process->getOutput(), 'FREECAUSE_WP_DELETE_DONE')) {
            throw new RuntimeException('WordPress cleanup did not complete (exit code '.$process->getExitCode().').');
        }
    }
}
