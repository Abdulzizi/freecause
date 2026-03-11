<?php

use App\Models\Language;
use App\Models\PetitionTranslation;
use Illuminate\Support\Facades\Route;

if (! function_exists('active_locales')) {
    function active_locales(): array
    {
        return cache()->remember(
            'active_languages',
            60,
            fn() => Language::where('is_active', 1)->pluck('code')->toArray()
        );
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return cache()->remember(
            'default_language',
            60,
            fn() => Language::where('is_default', 1)->value('code') ?? 'en'
        );
    }
}

if (! function_exists('is_valid_locale')) {
    function is_valid_locale(string $locale): bool
    {
        return in_array($locale, active_locales(), true);
    }
}

if (! function_exists('normalize_locale')) {
    function normalize_locale(?string $locale): string
    {
        $locale = $locale ?? default_locale();

        return is_valid_locale($locale)
            ? $locale
            : default_locale();
    }
}

if (! function_exists('lroute')) {
    function lroute(string $name, array $params = [], bool $absolute = true): string
    {
        $locale = $params['locale']
            ?? session('locale')
            ?? request()->segment(1)
            ?? app()->getLocale();

        $locale = normalize_locale($locale);

        $params = array_merge(['locale' => $locale], $params);

        return route($name, $params, $absolute);
    }
}

if (! function_exists('locale_url')) {
    function locale_url(string $newLocale): string
    {
        $newLocale = normalize_locale($newLocale);

        $route = Route::current();
        $routeName = $route?->getName();

        if (! $routeName) {
            return url("/{$newLocale}");
        }

        $params = $route->parameters();
        $params['locale'] = $newLocale;

        if (isset($params['id']) && (isset($params['slug']) || str_contains($routeName, 'petition.'))) {
            $id = (int) $params['id'];

            $tr = PetitionTranslation::query()
                ->where('petition_id', $id)
                ->where('locale', $newLocale)
                ->first()
                ?? PetitionTranslation::query()
                ->where('petition_id', $id)
                ->where('locale', default_locale())
                ->first();

            if ($tr) {
                $params['slug'] = $tr->slug;
                $params['locale'] = $tr->locale;
            }
        }

        return route($routeName, $params) .
            (request()->getQueryString()
                ? '?' . request()->getQueryString()
                : '');
    }
}

function base_url()
{
    return \App\Support\Settings::get('base_url', config('app.url'));
}

function toast($message, $type = 'info')
{
    session()->flash('toast', [
        'type' => $type,
        'message' => $message,
    ]);
}

function sanitizePetitionHtml(string $html): string
{
    $allowedTags = ['br', 'p', 'strong', 'em', 'u', 'ul', 'ol', 'li'];

    libxml_use_internal_errors(true);

    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $container = $doc->getElementsByTagName('div')->item(0);

    domSanitizeNode($container, $allowedTags, $doc);

    $out = '';
    foreach ($container->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }

    $out = str_ireplace(['<b>', '</b>', '<i>', '</i>'], ['<strong>', '</strong>', '<em>', '</em>'], $out);

    return trim($out);
}

function domSanitizeNode(\DOMNode $node, array $allowedTags, \DOMDocument $doc): void
{
    if (!$node->hasChildNodes()) return;

    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
        $child = $node->childNodes->item($i);

        if ($child->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($child->nodeName);

            if ($child->hasAttributes()) {
                while ($child->attributes->length) {
                    $child->removeAttributeNode($child->attributes->item(0));
                }
            }

            if (!in_array($tag, $allowedTags, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            domSanitizeNode($child, $allowedTags, $doc);
        } elseif ($child->nodeType === XML_COMMENT_NODE) {
            $node->removeChild($child);
        }
    }
}
if (!function_exists('admin_user')) {
    function admin_user(): ?\App\Models\User
    {
        $id = session('admin_user_id');
        if (!$id) return null;
        return \App\Models\User::find($id);
    }
}
