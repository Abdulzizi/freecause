<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use Symfony\Component\Process\Process;

class WordPressUserDeletionService
{
    public function __construct(private ?string $wpLoad = null) {}

    public function delete(User $user): void
    {
        $wpLoad = $this->wpLoad ?? base_path('public/magazine/wp-load.php');

        if (! is_file($wpLoad)) {
            throw new RuntimeException('WordPress bootstrap was not found.');
        }

        $script = <<<'PHP'
require $argv[1];
require_once ABSPATH.'wp-admin/includes/user.php';
$email = strtolower(trim($argv[2]));
$user = get_user_by('email', $email);
if (! $user) {
    exit(0);
}
// Same rule as public/magazine/sso.php: only SSO-provisioned subscribers, never editorial accounts.
$ssoLogin = sanitize_user(strstr($email, '@', true).'_'.substr(md5($email), 0, 6), true);
if (array_values($user->roles) !== ['subscriber']
    || (! get_user_meta($user->ID, 'fc_sso_account', true) && $user->user_login !== $ssoLogin)) {
    exit(0);
}
if (! wp_delete_user($user->ID)) {
    exit(2);
}
PHP;

        $process = new Process([
            PHP_BINDIR.DIRECTORY_SEPARATOR.'php',
            '-r',
            $script,
            $wpLoad,
            $user->email,
        ]);
        $process->setTimeout(20)->mustRun();
    }
}
