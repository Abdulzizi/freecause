<?php

/**
 * Plugin Name: FC Magazine SEO Metadata
 * Description: Corrects magazine metadata through Rank Math without changing editorial content.
 */
if (! defined('ABSPATH')) {
    exit;
}

function fc_magazine_seo_title($title)
{
    if (is_front_page() || is_home()) {
        $title = 'FreeCause Magazine | Petitions, Civic Action and Human Rights';
        $page = (int) get_query_var('paged');
        if ($page > 1) {
            $title .= ' | '.$page;
        }
    }

    return $title;
}

function fc_magazine_seo_description($description)
{
    if (is_front_page() || is_home()) {
        return 'Stories, practical guides and news on petitions, civic action, human rights and the environment from FreeCause Magazine.';
    }
    if (is_singular('post') && in_array(strtolower(trim(wp_strip_all_tags((string) $description))), ['', 'read more'], true)) {
        $post = get_post();
        if ($post) {
            $text = preg_replace('/\s+/u', ' ', wp_strip_all_tags(strip_shortcodes($post->post_content)));

            return mb_strimwidth(trim($text), 0, 160, '…', 'UTF-8');
        }
    }

    return $description;
}

function fc_magazine_seo_schema_urls($value)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = fc_magazine_seo_schema_urls($item);
        }
    } elseif (is_string($value) && str_starts_with($value, 'http://www.freecause.com/')) {
        $value = 'https://www.freecause.com/'.substr($value, strlen('http://www.freecause.com/'));
    }

    return $value;
}

function fc_magazine_seo_schema($data)
{
    $data = fc_magazine_seo_schema_urls($data);
    foreach ($data as &$entity) {
        if (! is_array($entity) || ($entity['@id'] ?? '') !== home_url('/').'#person') {
            continue;
        }
        // The existing publisher is an organization, not simultaneously a person.
        if (in_array('Organization', (array) ($entity['@type'] ?? []), true)) {
            $entity['@type'] = 'Organization';
            if (isset($entity['logo']) && is_array($entity['logo'])) {
                $entity['logo']['url'] = 'https://www.freecause.com/legacy/images-v2/freecause_logo_icon_clear.png';
                $entity['logo']['contentUrl'] = $entity['logo']['url'];
                unset($entity['logo']['width'], $entity['logo']['height']);
            }
            if (isset($entity['image']) && is_array($entity['image']) && isset($entity['image']['url'])) {
                $entity['image']['url'] = 'https://www.freecause.com/legacy/images-v2/freecause_logo_icon_clear.png';
                $entity['image']['contentUrl'] = $entity['image']['url'];
                unset($entity['image']['width'], $entity['image']['height']);
            }
        }
    }
    unset($entity);

    return $data;
}

add_filter('rank_math/frontend/title', 'fc_magazine_seo_title', 99);
add_filter('rank_math/frontend/description', 'fc_magazine_seo_description', 99);
add_filter('rank_math/opengraph/facebook/description', 'fc_magazine_seo_description', 99);
add_filter('rank_math/opengraph/twitter/description', 'fc_magazine_seo_description', 99);
add_filter('rank_math/json_ld', 'fc_magazine_seo_schema', 99);
