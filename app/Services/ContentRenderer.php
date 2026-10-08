<?php

namespace App\Services;

use App\Support\Settings;
use DOMDocument;
use Illuminate\Support\Str;

class ContentRenderer
{
    public static function render(?string $html): string
    {
        if (! $html) {
            return '';
        }

        return static::substituteTokens($html);
    }

    public static function renderFaqAccordion(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $doc->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return static::render($html);
        }

        $sections = [];
        $questionIndex = null;

        foreach ($doc->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($node->nodeName);

            if ($tag === 'h2') {
                $sections[] = ['title' => trim($node->textContent), 'items' => []];
                $questionIndex = null;

                continue;
            }

            if ($tag === 'h3') {
                if ($sections === []) {
                    $sections[] = ['title' => '', 'items' => []];
                }

                $section = count($sections) - 1;
                $sections[$section]['items'][] = ['q' => trim($node->textContent), 'answer' => []];
                $questionIndex = count($sections[$section]['items']) - 1;

                continue;
            }

            if ($questionIndex !== null && $sections !== []) {
                $section = count($sections) - 1;
                $sections[$section]['items'][$questionIndex]['answer'][] = $doc->saveHTML($node);
            }
        }

        $hasQuestions = false;

        foreach ($sections as $section) {
            if ($section['items'] !== []) {
                $hasQuestions = true;
                break;
            }
        }

        if (! $hasQuestions) {
            return static::render($html);
        }

        $output = '';
        $index = 0;

        foreach ($sections as $section) {
            $output .= '<h2 class="mb-4! text-xl! font-bold!">'.e($section['title']).'</h2>';
            $accordionId = 'faqAcc-'.Str::slug($section['title'] ?: 'faq');

            $output .= '<div class="accordion mb-6!" id="'.$accordionId.'">';

            foreach ($section['items'] as $item) {
                $index++;
                $collapseId = 'faqCollapse'.$index;
                $headingId = 'faqHeading'.$index;

                $answer = trim(implode('', $item['answer']));
                $body = $answer !== '' && trim(strip_tags($answer)) !== ''
                    ? $answer
                    : '<p class="mb-0! text-gray-500!">...</p>';

                $output .= '<div class="accordion-item border border-gray-200">'
                    .'<h2 class="accordion-header" id="'.$headingId.'">'
                    .'<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#'.$collapseId.'" aria-expanded="false" aria-controls="'.$collapseId.'">'
                    .e($item['q'])
                    .'</button>'
                    .'</h2>'
                    .'<div id="'.$collapseId.'" class="accordion-collapse collapse" data-bs-parent="#'.$accordionId.'" aria-labelledby="'.$headingId.'">'
                    .'<div class="accordion-body">'.$body.'</div>'
                    .'</div>'
                    .'</div>';
            }

            $output .= '</div>';
        }

        return static::substituteTokens($output);
    }

    private static function substituteTokens(string $html): string
    {
        $locale = app()->getLocale();

        $siteName = config('app.name');
        $privacyPolicyUrl = lroute('page.show', ['slug' => 'privacy-policy']);
        $privacyPolicyLink = '<a href="'.$privacyPolicyUrl.'">'.__('pages.privacy_policy', [], $locale).'</a>';
        $createPetitionUrl = lroute('petition.create');
        $contactsUrl = lroute('contacts');
        $contactsLink = '<a href="'.$contactsUrl.'">'.__('contacts.title', [], $locale).'</a>';
        $faqUrl = lroute('faqs');

        $tokens = [
            '{ROOT_URL}' => Settings::get('base_url', config('app.url')),
            '{SITE_NAME}' => $siteName,
            '{BRAND_NAME}' => $siteName,

            '{SITE_NAME_LINK}' => '<span class="red"><a href="'.url('/').'">'.$siteName.'</a></span>',

            '{CONTACTS_LINK}' => $contactsLink,

            '{ETHICAL_CODE_LINK}' => '<a href="'.lroute('page.show', ['slug' => 'ethical-code']).'">'.__('pages.ethical_code', [], $locale).'</a>',

            '{TOS_LINK}' => '<a href="'.lroute('page.show', ['slug' => 'terms-of-service']).'">'.__('pages.terms_of_service', [], $locale).'</a>',

            '{PRIVACY_POLICY_ROOT_LINK}' => $privacyPolicyLink,

            '{PRIVACY_POLICY_LINK}' => $privacyPolicyLink,

            '{PRIVACY_POLICY_URL}' => $privacyPolicyUrl,

            '{CREATE_PETITION_URL}' => $createPetitionUrl,
            '{HOW_TO_CREATE_URL}' => $createPetitionUrl,

            '{RECOVER_URL}' => lroute('password.request'),
            '{DONATIONS_LINK}' => '<a href="'.lroute('donations').'">'.__('pages.donations', [], $locale).'</a>',
            '{HOW_TO_CREATE_LINK}' => '<a href="'.lroute('how-to-write-a-petition').'">'.__('pages.how_to_write_a_petition', [], $locale).'</a>',

            '{FAQ_URL}' => $faqUrl,
            '{CONTACTS_URL}' => $contactsUrl,
            '{FAQ_LINK}' => '<a href="'.$faqUrl.'">'.__('pages.faqs', [], $locale).'</a>',
            '{CONTACTS_LINK_FULL}' => $contactsLink,
        ];

        foreach ($tokens as $key => $value) {
            if (str_starts_with($key, '{') && str_ends_with($key, '}')) {
                $tokens['%7B'.substr($key, 1, -1).'%7D'] = $value;
            }
        }

        $rendered = str_replace(
            array_keys($tokens),
            array_values($tokens),
            $html
        );

        return preg_replace('/\{[A-Z0-9_]+\}/', '', $rendered);
    }
}
