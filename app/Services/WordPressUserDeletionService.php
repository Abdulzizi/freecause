<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class WordPressUserDeletionService
{
    public function __construct(private ?string $wpLoad = null, private int $timeout = 20) {}

    public function delete(User $user): void
    {
        // The e2e server has no WordPress database, so there is no WordPress user to clean up.
        if (app()->environment('e2e')) {
            return;
        }

        $wpLoad = $this->wpLoad ?? base_path('public/magazine/wp-load.php');

        if (! is_file($wpLoad)) {
            throw new RuntimeException('WordPress bootstrap was not found.');
        }

        $script = <<<'PHP'
require $argv[1];
require_once ABSPATH.'wp-admin/includes/user.php';
global $wpdb;
$email = strtolower(trim($argv[2]));
$user = get_user_by('email', $email);
if (! empty($wpdb->last_error)) {
    exit(3);
}
if (! $user) {
    echo 'FREECAUSE_WP_DELETE_DONE';
    exit(0);
}
// Same rule as public/magazine/sso.php: only SSO-provisioned subscribers, never editorial accounts.
$ssoLogin = sanitize_user(strstr($email, '@', true).'_'.substr(md5($email), 0, 6), true);
if (array_values($user->roles) !== ['subscriber']
    || (! get_user_meta($user->ID, 'fc_sso_account', true) && $user->user_login !== $ssoLogin)) {
    echo 'FREECAUSE_WP_DELETE_DONE';
    exit(0);
}
if (! wp_delete_user($user->ID)) {
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
            throw new RuntimeException('WordPress cleanup did not complete (timed out after '.$this->timeout.'s).');
        }

        // WordPress exits 0 after printing its own error page when it cannot reach its database.
        if (! $process->isSuccessful() || ! str_contains($process->getOutput(), 'FREECAUSE_WP_DELETE_DONE')) {
            throw new RuntimeException('WordPress cleanup did not complete (exit code '.$process->getExitCode().').');
        }
    }
}
