<?php

/**
 * Plugin Name: FC Shared Cookie Preferences
 * Description: Shares the main site's first-party consent controls with the magazine.
 */
if (! defined('ABSPATH')) {
    exit;
}

function fc_cookie_consent_gate_html(string $html): string
{
    if (stripos($html, '<html') === false) {
        return $html;
    }

    $html = preg_replace_callback('#<script\b([^>]*)>(.*?)</script\s*>#is', function ($match) {
        $attributes = $match[1];
        $body = $match[2];
        if (str_contains($attributes, 'data-fc-future-adsense') || str_contains($attributes, 'data-category="analytics"')) {
            return $match[0];
        }
        if (preg_match('#(?<![\w-])src\s*=\s*(["\'])(.*?)\1#is', $attributes, $src)) {
            $url = html_entity_decode($src[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $host = strtolower((string) parse_url(str_starts_with($url, '//') ? 'https:'.$url : $url, PHP_URL_HOST));
            $path = (string) parse_url($url, PHP_URL_PATH);
            $analytics = $host === 'www.googletagmanager.com' && $path === '/gtag/js';
            $adsense = $host === 'pagead2.googlesyndication.com' && $path === '/pagead/js/adsbygoogle.js';
            if ($analytics || $adsense) {
                $attributes = preg_replace('#\s+type\s*=\s*(["\']).*?\1#is', '', $attributes);
                $attributes = preg_replace('#(?<![\w-])src\s*=#i', 'data-src=', $attributes, 1);
                $consent = $analytics ? ' data-category="analytics" data-service="googleAnalytics"' : ' data-fc-future-adsense';

                return '<script type="text/plain"'.$consent.$attributes.'>'.$body.'</script>';
            }
        } elseif (str_contains($body, 'adsbygoogle')) {
            $attributes = preg_replace('#\s+type\s*=\s*(["\']).*?\1#is', '', $attributes);

            return '<script type="text/plain" data-fc-future-adsense'.$attributes.'>'.$body.'</script>';
        }

        return $match[0];
    }, $html);

    return preg_replace_callback('#<link\b[^>]*>#is', function ($match) {
        if (preg_match('#\brel\s*=\s*(["\'])(?:preconnect|dns-prefetch|preload|prefetch)\1#i', $match[0])
            && preg_match('#(?:googletagmanager\.com|google-analytics\.com|googlesyndication\.com)#i', $match[0])) {
            return '';
        }

        return $match[0];
    }, $html);
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_robots() || wp_is_json_request()) {
        return;
    }
    ob_start('fc_cookie_consent_gate_html');
}, 0);

add_action('wp_head', function () {
    $locale = strtolower(substr(str_replace('_', '-', determine_locale()), 0, 2));
    echo '<link rel="stylesheet" href="/vendor/cookieconsent/cookieconsent.css?v=3.2.1">';
    echo '<link rel="stylesheet" href="/css/consent.css?v=1">';
    echo '<script src="/js/consent-bootstrap.js?v=1" data-cfasync="false"></script>';
    echo '<script defer src="/vendor/cookieconsent/cookieconsent.umd.js?v=3.2.1" data-cfasync="false"></script>';
    echo '<script defer src="/js/consent.js?v=2" data-fc-consent-locale="'.esc_attr($locale).'" data-cfasync="false"></script>';
    echo '<script defer src="/js/aggregate-traffic.js?v=1" data-cfasync="false"></script>';
}, -1000);

add_action('wp_footer', function () {
    echo '<div class="fc-magazine-cookie-settings"><button type="button" data-fc-cookie-settings data-cc="show-preferencesModal">Cookies</button></div>';
}, 100);
