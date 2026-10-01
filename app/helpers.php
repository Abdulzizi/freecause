<?php

use App\Models\Language;
use App\Models\Permission;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

if (! function_exists('active_locales')) {
    function active_locales(): array
    {
        return cache()->remember(
            'active_languages',
            60,
            fn () => Language::where('is_active', 1)->pluck('code')->toArray()
        );
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return cache()->remember(
            'default_language',
            60,
            fn () => Language::where('is_default', 1)->value('code') ?? 'en'
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

            static $translationCache = [];

            $cacheKey = "{$id}:{$newLocale}";
            if (! array_key_exists($cacheKey, $translationCache)) {
                // Load all translations for this petition in one query and cache every locale
                $allForPetition = PetitionTranslation::query()
                    ->where('petition_id', $id)
                    ->get(['locale', 'slug'])
                    ->keyBy('locale');

                foreach ($allForPetition as $loc => $t) {
                    $translationCache["{$id}:{$loc}"] = $t;
                }

                if (! array_key_exists($cacheKey, $translationCache)) {
                    $translationCache[$cacheKey] = $allForPetition->first() ?? null;
                }
            }

            $tr = $translationCache[$cacheKey];

            if ($tr) {
                $params['slug'] = $tr->slug;
                $params['locale'] = $tr->locale;
            }
        }

        return route($routeName, $params).
            (request()->getQueryString()
                ? '?'.request()->getQueryString()
                : '');
    }
}

function base_url()
{
    return Settings::get('base_url', config('app.url'));
}

if (! function_exists('canonical_url')) {
    function canonical_url(?string $path = null): string
    {
        $base = rtrim((string) config('app.canonical_url', 'https://www.freecause.com'), '/');

        $path = trim((string) ($path ?? request()->path()), '/');
        if ($path !== '') {
            $base .= '/'.$path;
        }

        return $base;
    }
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

    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $container = $doc->getElementsByTagName('div')->item(0);

    domSanitizeNode($container, $allowedTags, $doc);

    $out = '';
    foreach ($container->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }

    $out = str_ireplace(['<b>', '</b>', '<i>', '</i>'], ['<strong>', '</strong>', '<em>', '</em>'], $out);

    return trim($out);
}

function domSanitizeNode(DOMNode $node, array $allowedTags, DOMDocument $doc): void
{
    if (! $node->hasChildNodes()) {
        return;
    }

    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
        $child = $node->childNodes->item($i);

        if ($child->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($child->nodeName);

            if ($child->hasAttributes()) {
                while ($child->attributes->length) {
                    $child->removeAttributeNode($child->attributes->item(0));
                }
            }

            if (! in_array($tag, $allowedTags, true)) {
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
if (! function_exists('admin_user')) {
    function admin_user(): ?User
    {
        return Auth::guard('admin')->user();
    }
}

if (! function_exists('admin_can')) {
    function admin_can(string $module, string $action = 'view'): bool
    {
        $user = admin_user();
        if (! $user) {
            return false;
        }

        $user->loadMissing('level');
        if (! $user->level_id) {
            return false;
        }
        if ($user->level && $user->level->is_system) {
            return true;
        }

        return Cache::remember(
            "permissions:{$user->level_id}:{$module}:{$action}",
            300,
            fn () => Permission::where('level_id', $user->level_id)
                ->where('module', $module)
                ->where('action', $action)
                ->exists()
        );
    }
}

/**
 * Sanitize HTML for admin-managed rich content (pages, footer) using a strict
 * allowlist (adminHtmlAllowedTags()). Unknown containers are unwrapped so their
 * text survives; scripting/embedding elements are deleted with their contents.
 * Only href/src/alt/title/class survive — every style attribute and every on*
 * handler is dropped, and URLs are limited to http, https, mailto, tel and
 * relative paths. Unlike sanitizePetitionHtml(), formatting tags are permitted.
 */
if (! function_exists('sanitizeAdminHtml')) {
    function sanitizeAdminHtml(?string $html): string
    {
        if (! $html || trim($html) === '') {
            return '';
        }

        $escaped = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $internal = libxml_use_internal_errors(true);

        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $loaded = $doc->loadHTML(
                '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
                    .'<body><div data-fc-sanitize-root="1">'.$html.'</div></body></html>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
            );

            $body = $doc->getElementsByTagName('body')->item(0);
            $root = $body?->firstChild;

            if (! $loaded || ! $root instanceof DOMElement || $root->getAttribute('data-fc-sanitize-root') === '') {
                return $escaped;
            }

            $nodes = [$root];

            // Malformed input (e.g. a stray </div>) can spill the rest of the
            // fragment outside the wrapper; sanitize and keep those siblings too.
            for ($sibling = $root->nextSibling; $sibling; $sibling = $sibling->nextSibling) {
                $nodes[] = $sibling;
            }

            foreach ($nodes as $node) {
                adminHtmlSanitizeNode($node);
            }

            $out = '';
            foreach ($root->childNodes as $child) {
                $out .= $doc->saveHTML($child);
            }
            for ($sibling = $root->nextSibling; $sibling; $sibling = $sibling->nextSibling) {
                $out .= $doc->saveHTML($sibling);
            }
        } catch (Throwable) {
            return $escaped;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($internal);
        }

        return adminHtmlStripUnsafeUrls(trim($out));
    }
}

if (! function_exists('adminHtmlAllowedTags')) {
    /**
     * tag => permitted attributes. Kept in step with the CKEditor toolbar used by
     * the admin page/footer editors (format, bold/italic/underline, lists, link,
     * image, table) plus the div/section wrappers present in stored content.
     */
    function adminHtmlAllowedTags(): array
    {
        return [
            'p' => ['class'],
            'div' => ['class'],
            'section' => ['class'],
            'br' => [],
            'span' => ['class'],
            'strong' => ['class'],
            'b' => ['class'],
            'em' => ['class'],
            'i' => ['class'],
            'u' => ['class'],
            's' => ['class'],
            'a' => ['href', 'class'],
            'ul' => ['class'],
            'ol' => ['class'],
            'li' => ['class'],
            'h2' => ['class'],
            'h3' => ['class'],
            'h4' => ['class'],
            'h5' => ['class'],
            'h6' => ['class'],
            'blockquote' => ['class'],
            'pre' => ['class'],
            'code' => ['class'],
            'sub' => [],
            'sup' => [],
            'hr' => [],
            'img' => ['src', 'alt', 'title', 'class'],
            'table' => ['class'],
            'thead' => [],
            'tbody' => [],
            'tr' => [],
            'th' => ['colspan', 'rowspan'],
            'td' => ['colspan', 'rowspan'],
        ];
    }
}

if (! function_exists('adminHtmlKilledTags')) {
    /**
     * Elements dropped with their contents: they execute code, load remote
     * documents or re-open parser contexts. Everything else unknown is unwrapped.
     */
    function adminHtmlKilledTags(): array
    {
        return [
            'script', 'style', 'iframe', 'object', 'embed', 'applet', 'form', 'base', 'link', 'meta',
            'svg', 'math', 'frame', 'frameset', 'noscript', 'template', 'head', 'title', 'xmp', 'plaintext',
            'input', 'button', 'select', 'option', 'textarea', 'keygen', 'source', 'track',
        ];
    }
}

if (! function_exists('adminHtmlSafeUrl')) {
    function adminHtmlSafeUrl(?string $url, array $schemes = ['http', 'https', 'mailto', 'tel']): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        // Decode one entity layer and drop the whitespace/control characters
        // browsers ignore, so "jav&#x61;script:" and "java\tscript:" are seen.
        $probe = preg_replace('/[\s\x00-\x20\x7f]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';

        if ($probe === '' || str_starts_with($probe, '//')) {
            return false;
        }

        if (! preg_match('/^([a-z][a-z0-9+.\-]*):/i', $probe, $matches)) {
            return true; // relative path, fragment or query
        }

        return in_array(strtolower($matches[1]), $schemes, true);
    }
}

if (! function_exists('adminHtmlSanitizeNode')) {
    function adminHtmlSanitizeNode(DOMNode $node): void
    {
        $allowed = adminHtmlAllowedTags();
        $killed = adminHtmlKilledTags();

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);

                if (in_array($tag, $killed, true)) {
                    $node->removeChild($child);

                    continue;
                }

                adminHtmlSanitizeNode($child);

                if (! array_key_exists($tag, $allowed)) {
                    // Unknown container: drop the tag, keep the text.
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                $permitted = $allowed[$tag];
                $drop = [];
                $rewrite = [];

                for ($i = $child->attributes->length - 1; $i >= 0; $i--) {
                    $attr = $child->attributes->item($i);
                    $name = strtolower($attr->nodeName);

                    if (! in_array($name, $permitted, true)) {
                        $drop[] = $name;

                        continue;
                    }

                    $value = (string) $attr->nodeValue;

                    if ($name === 'class') {
                        $rewrite[$name] = trim(preg_replace('/[^a-zA-Z0-9 _-]/', '', $value) ?? '');

                        continue;
                    }

                    if ($name === 'href' && ! adminHtmlSafeUrl($value)) {
                        $drop[] = $name;
                    }

                    // Images only ever load over https or a site-relative path;
                    // plain http and any other scheme are refused outright.
                    if ($name === 'src' && ! adminHtmlSafeUrl($value, $tag === 'img' ? ['https'] : ['http', 'https', 'mailto', 'tel'])) {
                        $drop[] = $name;
                    }
                }

                foreach ($rewrite as $name => $value) {
                    $child->setAttribute($name, $value);
                }

                foreach ($drop as $name) {
                    $child->removeAttribute($name);
                }

                // External links must never hand the opening window to the
                // destination page.
                if ($tag === 'a'
                    && $child->hasAttribute('href')
                    && preg_match('/^https?:/i', (string) $child->getAttribute('href'))
                    && $child->getAttribute('rel') === ''
                ) {
                    $child->setAttribute('rel', 'noopener');
                }

                continue;
            }

            if (in_array($child->nodeType, [XML_COMMENT_NODE, XML_PI_NODE, XML_CDATA_SECTION_NODE], true)) {
                $node->removeChild($child);
            }
        }
    }
}

if (! function_exists('adminHtmlStripUnsafeUrls')) {
    /**
     * Second pass over the serialized output: re-check every href/src (decoding
     * entities once more) and strip any attribute that slipped through.
     */
    function adminHtmlStripUnsafeUrls(string $html): string
    {
        $cleaned = preg_replace_callback('/<([a-z][a-z0-9]*)((?:\s[^>]*)?)\/?>/i', function (array $tag): string {
            $attributes = preg_replace_callback('/([a-zA-Z_:.\-]+)\s*=\s*"([^"]*)"/', function (array $attr): string {
                $name = strtolower($attr[1]);

                if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcset', 'srcdoc', 'formaction', 'xlink:href'], true)) {
                    return '';
                }

                if (in_array($name, ['href', 'src'], true) && ! adminHtmlSafeUrl($attr[2], $name === 'src' ? ['https'] : ['http', 'https', 'mailto', 'tel'])) {
                    return '';
                }

                return $attr[0];
            }, $tag[2]);

            return '<'.strtolower($tag[1]).$attributes.'>';
        }, $html);

        return $cleaned ?? $html;
    }
}
