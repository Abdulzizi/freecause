<?php

namespace Deployer;

require 'recipe/laravel.php';

set('application', 'freecause');
set('repository', 'https://github.com/Abdulzizi/frecause.git');
set('keep_releases', 5);
set('ssh_multiplexing', true);
set('git_tty', false);

set('bin/php', 'php8.3');
set('php_fpm_service', 'php8.3-fpm');
set('supervisor_group', 'freecause-release-queue:*');

set('shared_files', ['.env']);
set('shared_dirs', ['storage']);
add('shared_files', [
    'public/magazine/wp-config.php',
    'public/magazine/.htaccess',
]);
add('shared_dirs', [
    'public/magazine/wp-content/uploads',
    'public/magazine/wp-content/cache',
    'public/magazine/wp-content/upgrade',
    'public/magazine/wp-content/upgrade-temp-backup',
    'public/magazine/wp-content/updraft',
    'public/magazine/wp-content/updraft_backup',
]);

set('writable_dirs', ['bootstrap/cache', 'storage']);
set('manage_services', true);

host('production')
    ->set('hostname', getenv('DEPLOY_HOST') ?: '37.60.254.112')
    ->set('remote_user', getenv('DEPLOY_USER') ?: 'root')
    ->set('deploy_path', getenv('DEPLOY_PATH') ?: '/var/www/freecause')
    ->set('identity_file', getenv('DEPLOY_IDENTITY') ?: '~/.ssh/id_deploy')
    ->set('labels', ['stage' => 'production']);

host('rehearsal')
    ->set('hostname', getenv('DEPLOY_HOST') ?: '37.60.254.112')
    ->set('remote_user', getenv('DEPLOY_USER') ?: 'root')
    ->set('deploy_path', getenv('REHEARSAL_PATH') ?: '/var/www/freecause-rehearsal')
    ->set('identity_file', getenv('DEPLOY_IDENTITY') ?: '~/.ssh/id_deploy')
    ->set('manage_services', false)
    ->set('labels', ['stage' => 'rehearsal']);

desc('Upload the Vite build produced in CI');
task('deploy:assets', function () {
    upload('public/build', '{{release_path}}/public/build');
});
after('deploy:update_code', 'deploy:assets');

desc('Record the deployed version for /version');
task('deploy:version', function () {
    $commit = run('cd {{release_path}} && git rev-parse --short HEAD');
    run('{{bin/php}} {{bin/artisan}} app:stamp-version "{{target}}" --commit="'.$commit.'"');
});
after('deploy:vendors', 'deploy:version');

desc('Warm the application caches');
task('cache:warm', function () {
    run('{{bin/php}} {{bin/artisan}} cache:warm');
});

desc('Restart the queue workers');
task('queue:restart', function () {
    if (! get('manage_services')) {
        writeln('manage_services=false: skipping queue restart');

        return;
    }
    run('{{bin/php}} {{bin/artisan}} queue:restart');
});

desc('Restart the supervisor queue group');
task('supervisor:restart', function () {
    if (! get('manage_services')) {
        writeln('manage_services=false: skipping supervisor restart');

        return;
    }
    run('sudo supervisorctl restart {{supervisor_group}}');
});

desc('Reload PHP-FPM, resetting opcache');
task('php-fpm:reload', function () {
    if (! get('manage_services')) {
        writeln('manage_services=false: skipping PHP-FPM reload');

        return;
    }
    run('sudo systemctl reload {{php_fpm_service}}');
});

before('deploy:migrate', 'artisan:down');
after('deploy:success', 'artisan:up');
after('deploy:symlink', 'cache:warm');
after('deploy:symlink', 'queue:restart');
after('queue:restart', 'supervisor:restart');
after('deploy:symlink', 'php-fpm:reload');

after('deploy:failed', 'deploy:unlock');
after('deploy:failed', 'artisan:up');
