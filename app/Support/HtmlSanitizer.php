<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class HtmlSanitizer
{
    private const KILLED = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'form', 'base', 'link', 'meta',
        'svg', 'math', 'frame', 'frameset', 'noscript', 'template', 'head', 'title', 'xmp', 'plaintext',
        'input', 'button', 'select', 'option', 'textarea', 'keygen', 'source', 'track',
    ];

    private const UNWRAPPED = [
        'div', 'span', 'section', 'article', 'header', 'footer', 'main', 'nav', 'aside', 'figure',
        'figcaption', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'colgroup', 'col',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'kbd', 'samp', 'var', 'dl', 'dt',
        'dd', 'font', 'center', 'small', 'big', 'tt', 'strike', 's', 'del', 'ins', 'mark', 'abbr', 'cite',
        'q', 'time', 'address', 'details', 'summary', 'fieldset', 'legend', 'label', 'output', 'progress',
        'meter', 'ruby', 'rt', 'rp', 'bdi', 'bdo', 'wbr', 'area', 'map', 'picture', 'video', 'audio',
        'canvas', 'b', 'i', 'a', 'sub', 'sup', 'hr', 'img',
    ];

    private const ADMIN_ALLOWED = [
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

    private const PETITION_ALLOWED = ['br', 'p', 'strong', 'em', 'u', 'ul', 'ol', 'li'];

    private static ?SymfonyHtmlSanitizer $petition = null;

    private static ?SymfonyHtmlSanitizer $admin = null;

    private static ?SymfonyHtmlSanitizer $head = null;

    public static function petition(): SymfonyHtmlSanitizer
    {
        if (self::$petition) {
            return self::$petition;
        }

        $config = self::baseConfig();

        foreach (self::PETITION_ALLOWED as $element) {
            $config = $config->allowElement($element);
        }

        return self::$petition = new SymfonyHtmlSanitizer($config->withMaxInputLength(-1));
    }

    public static function admin(): SymfonyHtmlSanitizer
    {
        if (self::$admin) {
            return self::$admin;
        }

        $config = self::baseConfig();

        foreach (self::ADMIN_ALLOWED as $element => $attributes) {
            $config = $config->allowElement($element, $attributes);
        }

        return self::$admin = new SymfonyHtmlSanitizer(
            $config
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https'])
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener')
                ->withAttributeSanitizer(self::rejectProtocolRelativeUrls())
                ->withMaxInputLength(-1)
        );
    }

    public static function head(): SymfonyHtmlSanitizer
    {
        if (self::$head) {
            return self::$head;
        }

        $config = new HtmlSanitizerConfig;

        return self::$head = new SymfonyHtmlSanitizer(
            $config
                ->allowElement('meta', ['name', 'property', 'content'])
                ->withMaxInputLength(-1)
        );
    }

    private static function baseConfig(): HtmlSanitizerConfig
    {
        $config = new HtmlSanitizerConfig;

        foreach (self::UNWRAPPED as $element) {
            $config = $config->blockElement($element);
        }

        foreach (self::KILLED as $element) {
            $config = $config->dropElement($element);
        }

        return $config;
    }

    private static function rejectProtocolRelativeUrls(): AttributeSanitizerInterface
    {
        return new class implements AttributeSanitizerInterface
        {
            public function getSupportedElements(): ?array
            {
                return null;
            }

            public function getSupportedAttributes(): ?array
            {
                return ['href', 'src'];
            }

            public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
            {
                return str_starts_with($value, '//') ? null : $value;
            }
        };
    }
}
