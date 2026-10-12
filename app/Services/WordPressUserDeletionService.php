<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use Symfony\Component\Process\Process;

class WordPressUserDeletionService
{
    public function delete(User $user): void
    {
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
if ($id && ! wp_delete_user((int) $id)) {
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
