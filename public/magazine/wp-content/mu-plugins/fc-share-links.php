<?php
/**
 * Plugin Name: FC Share Links
 * Description: Adds plain Facebook, X and WhatsApp share links below single posts, with no JS, external requests or tracking.
 */

add_filter('the_content', function ($content) {
    if (! is_singular('post') || ! in_the_loop() || ! is_main_query()) {
        return $content;
    }

    $url = rawurlencode(get_permalink());
    $title = rawurlencode(html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'));
    $links = [
        'Facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
        'X' => "https://x.com/intent/tweet?url={$url}&text={$title}",
        'WhatsApp' => "https://wa.me/?text={$title}%20{$url}",
    ];

    $html = '<nav class="fc-share-links" aria-label="Share this post" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:24px 0">';
    $html .= '<span>' . esc_html('Share') . '</span>';
    foreach ($links as $name => $link) {
        $html .= '<a href="' . esc_url($link) . '" rel="noopener noreferrer" target="_blank" style="padding:4px 12px;border:1px solid currentColor;border-radius:4px;text-decoration:none">' . esc_html($name) . '</a>';
    }

    return $content . $html . '</nav>';
});
