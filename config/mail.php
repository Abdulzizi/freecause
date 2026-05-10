<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    | SMTP credentials are stored in the `settings` DB table and applied at
    | runtime by AppServiceProvider::bootMail(). The .env values serve as
    | fallback defaults. Only smtp, log, and array drivers are used.
    */

    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [

        'smtp' => [
            'transport'    => 'smtp',
            'host'         => env('MAIL_HOST', 'smtp-relay.brevo.com'),
            'port'         => env('MAIL_PORT', 587),
            'username'     => env('MAIL_USERNAME'),
            'password'     => env('MAIL_PASSWORD'),
            'encryption'   => env('MAIL_ENCRYPTION', 'tls'),
            'timeout'      => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@xpetition.com'),
        'name'    => env('MAIL_FROM_NAME', 'xPetition'),
    ],

];
