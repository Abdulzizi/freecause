<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI') ?: rtrim((string) env('APP_URL'), '/').'/en/oauth/google/callback',
    ],

    'adsense' => [
        'client' => env('ADSENSE_CLIENT', 'ca-pub-5636773008040262'),
        'enabled' => env('ADSENSE_ENABLED', true),
        'slots' => [
            'petition_sidebar' => env('ADSENSE_SLOT_PETITION_SIDEBAR'),
            'listing_inline' => env('ADSENSE_SLOT_LISTING_INLINE'),
            'article_footer' => env('ADSENSE_SLOT_ARTICLE_FOOTER'),
        ],
    ],

    'emergency_email' => env('ADMIN_EMERGENCY_EMAIL'),
    'emergency_password' => env('ADMIN_EMERGENCY_PASSWORD'),

    'admin_username' => env('ADMIN_USERNAME'),
    'admin_username_password' => env('ADMIN_USERNAME_PASSWORD'),
];
