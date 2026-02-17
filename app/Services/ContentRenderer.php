<?php

namespace App\Services;

class ContentRenderer
{
    public static function render(?string $html): string
    {
        if (!$html) {
            return '';
        }

        $locale = app()->getLocale();

        $tokens = [
            // '{ROOT_URL}' => url('/'),
            '{ROOT_URL}' => base_url(),
            '{SITE_NAME}' => config('app.name'),
            '{BRAND_NAME}' => config('app.name'),

            '{SITE_NAME_LINK}' => '<span class="red"><a href="' . url('/') . '">' . config('app.name') . '</a></span>',

            '{CONTACTS_LINK}' =>
            '<a href="' . lroute('contacts') . '">Contacts</a>',

            '{ETHICAL_CODE_LINK}' =>
            '<a href="' . lroute('page.show', ['slug' => 'ethical-code']) . '">Ethical Code</a>',

            '{TOS_LINK}' =>
            '<a href="' . lroute('page.show', ['slug' => 'terms-of-service']) . '">Terms of Service</a>',

            '{PRIVACY_POLICY_ROOT_LINK}' =>
            '<a href="' . lroute('page.show', ['slug' => 'privacy-policy']) . '">Privacy Policy</a>',

            '{CREATE_PETITION_URL}' => lroute('petition.create'),
            '{HOW_TO_CREATE_URL}' => lroute('petition.create'),
        ];

        return str_replace(
            array_keys($tokens),
            array_values($tokens),
            $html
        );
    }
}
