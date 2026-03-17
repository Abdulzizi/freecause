<?php
/**
 * Includes theme defaults and starter functions
 * 
 * @package Pubnews
 * @since 1.0.0
 */
 namespace Pubnews\CustomizerDefault;

 if( !function_exists( 'pubnews_get_customizer_option' ) ) :
    /**
     * Gets customizer "theme_mods" value
     * 
     * @package Pubnews
     * @since 1.0.0
     * 
     */
    function pubnews_get_customizer_option( $key ) {
        return get_theme_mod( $key, pubnews_get_customizer_default( $key ) );
    }
 endif;

 if( !function_exists( 'pubnews_get_multiselect_tab_option' ) ) :
    /**
     * Gets customizer "multiselect combine tab" value
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_get_multiselect_tab_option( $key ) {
        $value = pubnews_get_customizer_option( $key );
        if( !$value["desktop"] && !$value["tablet"] && !$value["mobile"] ) return apply_filters( "pubnews_get_multiselect_tab_option", false );
        return apply_filters( "pubnews_get_multiselect_tab_option", true );
    }
 endif;

 if( !function_exists( 'pubnews_get_customizer_default' ) ) :
    /**
     * Gets customizer "theme_mods" value
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_get_customizer_default($key) {
        $array_defaults = apply_filters( 'pubnews_get_customizer_defaults', array(
            'solid_presets' =>  [ '#64748b', '#27272a', '#ef4444', '#eab308', '#84cc16', '#22c55e', '#06b6d4', '#0284c7', '#6366f1', '#84cc16', '#a855f7', '#f43f5e' ],
            'gradient_presets' =>  [ 'linear-gradient( 135deg, #485563 10%, #29323c 100%)', 'linear-gradient( 135deg, #FF512F 10%, #F09819 100%)', 'linear-gradient( 135deg, #00416A 10%, #E4E5E6 100%)', 'linear-gradient( 135deg, #CE9FFC 10%, #7367F0 100%)', 'linear-gradient( 135deg, #90F7EC 10%, #32CCBC 100%)', 'linear-gradient( 135deg, #81FBB8 10%, #28C76F 100%)', 'linear-gradient( 135deg, #EB3349 10%, #F45C43 100%)', 'linear-gradient( 135deg, #FFF720 10%, #3CD500 100%)', 'linear-gradient( 135deg, #FF96F9 10%, #C32BAC 100%)', 'linear-gradient( 135deg, #69FF97 10%, #00E4FF 100%)', 'linear-gradient( 135deg, #3C8CE7 10%, #00EAFF 100%)', 'linear-gradient( 135deg, #FF7AF5 10%, #513162 100%)' ],

            'theme_color'   => '#02DE9C',
            'site_background_color'  => json_encode(array(
                'type'  => 'solid',
                'solid' => '#ffffff',
                'gradient'  => null
            )),
            'site_background_animation' =>  'none',
            'animation_object_color' => '--pubnews-global-preset-theme-color',
            'global_button_label'   =>  esc_html__( 'Read More', 'pubnews' ),
            'global_button_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '500', 'label' => 'Medium 500' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 14,
                    'smartphone' => 14
                ),
                'line_height'   => array(
                    'desktop' => 21,
                    'tablet' => 21,
                    'smartphone' => 21
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'preloader_option'  => false,
            'preloader_type'  => 6,
            'website_layout'    => 'full-width--layout',
            'website_content_layout'    => 'boxed--layout',
            'block_title_primary_color' =>  '#ff2453',
            'site_block_title_secondary_color' =>  '#be2edd',
            'frontpage_sidebar_layout'  => 'right-sidebar',
            'frontpage_sidebar_sticky_option'    => false,
            'archive_sidebar_layout'    => 'right-sidebar',
            'archive_sidebar_sticky_option'    => false,
            'single_sidebar_layout' => 'right-sidebar',
            'single_sidebar_sticky_option'    => false,
            'page_sidebar_layout'   => 'right-sidebar',
            'page_sidebar_sticky_option'    => false,
            'post_title_hover_effects'  => 'three',
            'site_image_hover_effects'  => 'none',
            'cursor_animation'  =>  'none',
            'site_breadcrumb_option'    => true,
            'site_breadcrumb_type'  => 'default',
            'site_schema_ready' => true,
            'site_date_format'  => 'default',
            'site_date_to_show' => 'published',
            'site_title_hover_textcolor'=> '--pubnews-global-preset-theme-color',
            'site_description_color'    => '#777',
            'homepage_content_order'    => array( 
                array( 'value'  => 'full_width_section', 'option'   => false ),
                array( 'value'  => 'leftc_rights_section', 'option'    => false ),
                array( 'value'   => 'lefts_rightc_section', 'option' => false ),
                array( 'value'   => 'video_playlist', 'option'    => false ),
                array( 'value'   => 'latest_posts', 'option'    => true ),
                array( 'value' => 'bottom_full_width_section', 'option'  => true ),
                array( 'value' => 'two_column_section', 'option'  => false ),
                array( 'value' => 'three_column_section', 'option'  => false )
            ),
            'pubnews_site_logo_width'    => array(
                'desktop'   => 230,
                'tablet'    => 200,
                'smartphone'    => 200
            ),
            'site_title_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 40,
                    'tablet' => 40,
                    'smartphone' => 40
                ),
                'line_height'   => array(
                    'desktop' => 45,
                    'tablet' => 42,
                    'smartphone' => 40,
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'site_tagline_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '500', 'label' => 'Medium 500' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 14,
                    'smartphone' => 14
                ),
                'line_height'   => array(
                    'desktop' => 15,
                    'tablet' => 15,
                    'smartphone' => 15
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'capitalize',
                'text_decoration'    => 'none',
            ),
            'top_header_option' => false,
            'top_header_date_time_option'   => true,
            'top_header_ticker_news_option' => true,
            'top_header_ticker_news_categories' => [],
            'top_header_ticker_news_posts' => [],
            'top_header_ticker_news_date_filter' => 'all',
            'top_header_social_option'  => true,
            'top_header_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '#151515',
                'gradient'  => null
            )),
            'header_layout' => 'one',
            'header_newsletter_option'   => true,
            'newsletter_label'   =>  esc_html__( 'Subscribe', 'pubnews' ),
            'header_newsletter_redirect_href_target'    => '_blank',
            'header_newsletter_redirect_href_link'  => '',
            'header_newsletter_background_color'    =>  json_encode([
                'initial'   => [
                    'type'  => 'solid',
                    'solid' => '--pubnews-global-preset-theme-color',
                    'gradient'  => null
                ],
                'hover'   => [
                    'type'  => 'solid',
                    'solid' => '--pubnews-global-preset-theme-color',
                    'gradient'  => null
                ]
            ]),
            'header_random_news_option'   => false,
            'header_random_news_redirect_href_target'    => '_blank',
            'header_ads_banner_responsive_option'  => array(
                'desktop'   => true,
                'tablet'   => true,
                'mobile'   => true
            ),
            'header_ads_banner_type'    => 'custom',
            'header_ads_banner_custom_image'  => '',
            'header_ads_banner_custom_url'  => '',
            'header_ads_banner_custom_target'  => '_self',
            'header_off_canvas_option'  => true,
            'off_canvas_position'  => 'left',
            'header_search_option'  => true,
            'header_theme_mode_toggle_option'  => true,
            'header_width_layout'   => 'contain',
            'header_vertical_padding'   => array(
                'desktop' => 30,
                'tablet' => 20,
                'smartphone' => 20
            ),
            'header_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '#000000',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' )
            )),
            'header_menu_hover_effect'  => 'none',
            'header_menu_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '#151515',
                'gradient'  => null
            )),
            'header_sub_menu_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '#070707',
                'gradient'  => null
            )),
            'header_menu_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '500', 'label' => 'Medium 500' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 14,
                    'smartphone' => 14
                ),
                'line_height'   => array(
                    'desktop' => 36,
                    'tablet' => 36,
                    'smartphone' => 36,
                ),
                'letter_spacing'   => array(
                    'desktop' => 1.28,
                    'tablet' => 1.28,
                    'smartphone' => 1.28
                ),
                'text_transform'    => 'uppercase',
                'text_decoration'    => 'none',
            ),
            'header_sub_menu_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '400', 'label' => 'Regular 400' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 14,
                    'smartphone' => 14
                ),
                'line_height'   => array(
                    'desktop' => 24,
                    'tablet' => 24,
                    'smartphone' => 24,
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'capitalize',
                'text_decoration'    => 'none',
            ),
            'social_icons_target' => '_blank',
            'social_icons' => json_encode(array(
                array(
                    'icon_class'    => 'fab fa-facebook-f',
                    'icon_url'      => '',
                    'item_option'   => 'show'
                ),
                array(
                    'icon_class'    => 'fab fa-instagram',
                    'icon_url'      => '',
                    'item_option'   => 'show'
                ),
                array(
                    'icon_class'    => 'fa-brands fa-x-twitter',
                    'icon_url'      => '',
                    'item_option'   => 'show'
                ),
                array(
                    'icon_class'    => 'fa-brands fa-whatsapp',
                    'icon_url'      => '',
                    'item_option'   => 'show'
                )
            )),
            'ticker_news_width_layout'  => 'global',
            'ticker_news_visible'   => 'none',
            'ticker_news_order_by'  => 'date-desc',
            'ticker_news_categories' => [],
            'ticker_news_posts' => [],
            'ticker_news_date_filter' => 'all',
            'ticker_news_title' => esc_html__( 'Headlines', 'pubnews' ),
            'main_banner_option'    => true,
            'main_banner_slider_order_by'   => 'date-desc',
            'main_banner_slider_categories' => [],
            'main_banner_posts' => [],
            'main_banner_date_filter' => 'all',
            'main_banner_six_trailing_posts_order_by'  => 'rand-desc',
            'main_banner_six_trailing_posts_categories'   => [],
            'main_banner_six_trailing_posts'   => [],
            'main_banner_six_trailing_posts_layout'   => 'row',
            'main_banner_width_layout'  => 'global',
            'banner_section_order'  => array( 
                array( 'value'  => 'banner_slider', 'option'   => true ),
                array( 'value'  => 'tab_slider', 'option'    => true )
            ),
            'full_width_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-grid',
                    'blockId'    => '',
                    'option'    => true,
                    'column'    => 'four',
                    'layout'    => 'one',
                    'title'     => esc_html__( 'Latest posts', 'pubnews' ),
                    'thumbOption'    => true,
                    'categoryOption'    => false,
                    'authorOption'  => false,
                    'dateOption'    => true,
                    'commentOption' => false,
                    'excerptOption' => false,
                    'excerptLength' => 10,
                    'query' => json_encode([
                        'order' => 'date-desc',
                        'count' => 4,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'buttonOption' => false,
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0
                    ]),
                    'imageSize' =>  "medium",
                )
            )),
            'full_width_blocks_width_layout'  => 'global',
            'full_width_blocks_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' ),
                'position'  => 'center center',
                'attachment'  => 'fixed',
                'repeat'  => 'no-repeat',
                'size'  => 'auto'
            )),
            'leftc_rights_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-alter',
                    'blockId'    => '',
                    'option'    => true,
                    'layout'    => 'four',
                    'title'     => esc_html__( 'Latest posts', 'pubnews' ),
                    'categoryOption'    => true,
                    'authorOption'  => true,
                    'dateOption'    => true,
                    'commentOption' => false,
                    'excerptOption' => false,
                    'excerptLength' => 10,
                    'query' => json_encode([
                        'order' => 'date-desc',
                        'count' => 6,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'buttonOption'    => false,
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0,
                    ])
                )
            )),
            'leftc_rights_blocks_width_layout'  => 'global',
            'leftc_rights_blocks_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' ),
                'position'  => 'center center',
                'attachment'  => 'fixed',
                'repeat'  => 'no-repeat',
                'size'  => 'auto'
            )),
            'lefts_rightc_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-list',
                    'blockId'    => '',
                    'option'    => true,
                    'layout'    => 'one',
                    'column'    => 'one',
                    'title'     => esc_html__( 'Latest posts', 'pubnews' ),
                    'thumbOption'    => true,
                    'categoryOption'    => true,
                    'authorOption'  => true,
                    'dateOption'    => true,
                    'commentOption' => false,
                    'excerptOption' => true,
                    'excerptLength' => 10,
                    'query' => json_encode([
                        'order' => 'date-desc',
                        'count' => 4,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'buttonOption'    => false,
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0,
                    ])
                )
            )),
            'lefts_rightc_blocks_width_layout'  => 'global',
            'lefts_rightc_blocks_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' ),
                'position'  => 'center center',
                'attachment'  => 'fixed',
                'repeat'  => 'no-repeat',
                'size'  => 'auto'
            )),
            'bottom_full_width_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-carousel',
                    'blockId'    => '',
                    'option'    => true,
                    'layout'    => 'one',
                    'title'     => esc_html__( 'You May Have Missed', 'pubnews' ),
                    'categoryOption'    => true,
                    'authorOption'  => true,
                    'dateOption'    => true,
                    'commentOption' => false,
                    'excerptOption' => false,
                    'excerptLength' => 10,
                    'columns' => 3,
                    'query' => json_encode([
                        'order' => 'rand-desc',
                        'count' => 8,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'buttonOption'    => false,
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'dots' => true,
                    'loop' => false,
                    'arrows' => true,
                    'auto' => false,
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0
                    ]),
                    'imageSize' =>  "medium_large",
                )
            )),
            'bottom_full_width_blocks_width_layout'  => 'global',
            'bottom_full_width_blocks_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' ),
                'position'  => 'center center',
                'attachment'  => 'fixed',
                'repeat'  => 'no-repeat',
                'size'  => 'auto'
            )),
            'two_column_first_column_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-list',
                    'blockId'    => '',
                    'option'    => true,
                    'layout'    => 'one',
                    'title'     => esc_html__( 'Latest posts', 'pubnews' ),
                    'thumbOption'    => true,
                    'categoryOption'    => true,
                    'authorOption'  => true,
                    'dateOption'    => true,
                    'commentOption' => false,
                    'query' => json_encode([
                        'order' => 'date-desc',
                        'count' => 4,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0
                    ])
                )
            )),
            'two_column_second_column_blocks'   => json_encode(array(
                array(
                    'type'  => 'news-list',
                    'blockId'    => '',
                    'option'    => true,
                    'layout'    => 'one',
                    'title'     => esc_html__( 'Latest posts', 'pubnews' ),
                    'thumbOption'    => true,
                    'categoryOption'    => true,
                    'authorOption'  => true,
                    'dateOption'    => true,
                    'commentOption' => true,
                    'query' => json_encode([
                        'order' => 'date-desc',
                        'count' => 4,
                        'offset' => 0,
                        'dateFilter' => 'all',
                        'posts' => [],
                        'categories' => [],
                        'ids' => []
                    ]),
                    'viewallUrl'   => '',
                    'viewallLabelOption'=> true,
                    'viewallLabel'   => esc_html__( 'View all', 'pubnews' ),
                    'imageRatio'   =>  json_encode([
                        'desktop'   =>  0,
                        'tablet'   =>  0,
                        'smartphone'   =>  0
                    ])
                )
            )),
            'two_column_section_layout'  => 'global',
            'two_column_background_color_group' =>  json_encode(array(
                'type'  => 'solid',
                'solid' => '',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' ),
                'position'  => 'center center',
                'attachment'  => 'fixed',
                'repeat'  => 'no-repeat',
                'size'  => 'auto'
            )),
            'footer_option' => false,
            'footer_section_width'  => 'boxed-width',
            'footer_widget_column'  => 'column-three',
            'footer_background_color_group' => json_encode(array(
                'type'  => 'solid',
                'solid' => '#0f0f11',
                'gradient'  => null,
                'image'     => array( 'media_id' => 0, 'media_url' => '' )
            )),
            'bottom_footer_option'  => true,
            'bottom_footer_social_option'   => true,
            'bottom_footer_menu_option'     => true,
            'bottom_footer_site_info'   => esc_html__( 'Pubnews - Modern WordPress Theme %year%.', 'pubnews' ),
            'bottom_footer_width_layout'    => 'global',
            'bottom_footer_background_color_group'  => json_encode(array(
                'type'  => 'solid',
                'solid' => null,
                'gradient'  => null
            )),
            'single_post_related_posts_option'  => true,
            'single_post_related_posts_title'   => esc_html__( 'Related News', 'pubnews' ),
            'single_post_show_original_image_option'=> false,
            'single_post_image_ratio'   =>  array(
                'desktop'   => 0,
                'tablet'    => 0,
                'smartphone'    => 0
            ),
            'single_post_width_layout'=> 'global',
            'single_post_title_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 35,
                    'tablet' => 35,
                    'smartphone' => 35
                ),
                'line_height'   => array(
                    'desktop' => 46,
                    'tablet' => 46,
                    'smartphone' => 46
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_meta_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '400', 'label' => 'Medium 400' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 14,
                    'smartphone' => 14
                ),
                'line_height'   => array(
                    'desktop' => 26,
                    'tablet' => 26,
                    'smartphone' => 26
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'capitalize',
                'text_decoration'    => 'none',
            ),
            'single_post_content_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '400', 'label' => 'Regular 400' ),
                'font_size'   => array(
                    'desktop' => 18,
                    'tablet' => 18,
                    'smartphone' => 18
                ),
                'line_height'   => array(
                    'desktop' => 29,
                    'tablet' => 29,
                    'smartphone' => 29
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h1_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 32,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'line_height'   => array(
                    'desktop' => 46,
                    'tablet' => 22,
                    'smartphone' => 22
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h2_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 26,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'line_height'   => array(
                    'desktop' => 38,
                    'tablet' => 22,
                    'smartphone' => 22
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h3_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 19,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'line_height'   => array(
                    'desktop' => 27,
                    'tablet' => 22,
                    'smartphone' => 22
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h4_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 17,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'line_height'   => array(
                    'desktop' => 24,
                    'tablet' => 22,
                    'smartphone' => 22
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h5_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 14,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'line_height'   => array(
                    'desktop' => 20,
                    'tablet' => 20,
                    'smartphone' => 20
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'single_post_content_h6_typo'=> array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 11,
                    'tablet' => 11,
                    'smartphone' => 10
                ),
                'line_height'   => array(
                    'desktop' => 16,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'archive_page_layout'   => 'one',
            'archive_page_title_prefix'   => false,
            'archive_page_category_option'   => true,
            'archive_excerpt_length'   => 20,
            'archive_pagination_type'   => 'number',
            'archive_image_size'   =>  'pubnews-list',
            'archive_post_element_order'    => array(
                array( 'value'  => 'title', 'option' => true ),
                array( 'value'  => 'meta', 'option' => true ),
                array( 'value'  => 'excerpt', 'option' => true ),
                array( 'value'  => 'button', 'option' => true )
            ),
            'archive_image_ratio'   =>  array(
                'desktop'   => 0,
                'tablet'    => 0,
                'smartphone'    => 0
            ),
            'archive_width_layout'=> 'global',
            'archive_vertical_spacing_top'    =>  [
                'desktop'   =>  25,
                'tablet'    =>  25,
                'smartphone'    =>  25
            ],
            'archive_vertical_spacing_bottom'    =>  [
                'desktop'   =>  25,
                'tablet'    =>  25,
                'smartphone'    =>  25
            ],
            'single_page_width_layout' => 'global',
            'single_page_show_original_image_option'  =>  false,
            'single_page_image_ratio'   =>  array(
                'desktop'   => 0,
                'tablet'    => 0,
                'smartphone'    => 0
            ),
            'error_page_width_layout' => 'global',
            'search_page_width_layout' => 'global',
            'site_section_block_title_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 20,
                    'tablet' => 30,
                    'smartphone' => 30
                ),
                'line_height'   => array(
                    'desktop' => 23,
                    'tablet' => 23,
                    'smartphone' => 23
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'capitalize',
                'text_decoration'    => 'none',
            ),
            'site_archive_post_title_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '700', 'label' => 'Bold 700' ),
                'font_size'   => array(
                    'desktop' => 22,
                    'tablet' => 18,
                    'smartphone' => 15
                ),
                'line_height'   => array(
                    'desktop' => 24,
                    'tablet' => 30,
                    'smartphone' => 26
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none',
            ),
            'site_archive_post_meta_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '400', 'label' => 'Regular 400' ),
                'font_size'   => array(
                    'desktop' => 12,
                    'tablet' => 12,
                    'smartphone' => 12
                ),
                'line_height'   => array(
                    'desktop' => 16,
                    'tablet' => 16,
                    'smartphone' => 16
                ),
                'letter_spacing'   => array(
                    'desktop' => 0.32,
                    'tablet' => 0.32,
                    'smartphone' => 0.32
                ),
                'text_transform'    => 'capitalize',
                'text_decoration'    => 'none',
            ),
            'site_archive_post_content_typo'    => array(
                'font_family'   => array( 'value' => 'DM Sans', 'label' => 'DM Sans' ),
                'font_weight'   => array( 'value' => '400', 'label' => 'Regular 400' ),
                'font_size'   => array(
                    'desktop' => 15,
                    'tablet' => 15,
                    'smartphone' => 15
                ),
                'line_height'   => array(
                    'desktop' => 24,
                    'tablet' => 24,
                    'smartphone' => 24
                ),
                'letter_spacing'   => array(
                    'desktop' => 0,
                    'tablet' => 0,
                    'smartphone' => 0
                ),
                'text_transform'    => 'unset',
                'text_decoration'    => 'none'
            ),
            'stt_responsive_option'    => array(
                'desktop'   => true,
                'tablet'   => true,
                'mobile'   => false
            ),
            'widgets_styles_image_border'   =>  array( "type"  => "none", "width"   => 1, "color"   => "#448bef" ),
            'widgets_styles_image_border_radius'    =>  array(
                'desktop'   => 4,
                'tablet'    => 4,
                'smartphone'    => 4
            ),
            'widgets_styles_image_box_shadow'   =>  array(
                'option'    => 'adjust',
                'hoffset'   => 0,
                'voffset'   => 4,
                'blur'  => 9,
                'spread'    => -3,
                'type'  => 'outset',
                'color' => 'rgb(7 10 25 / 35%)'
            ),
        ));
        $totalCats = get_categories();
        if( $totalCats ) :
            foreach( $totalCats as $singleCat ) :
                $array_defaults['category_' .absint($singleCat->term_id). '_color'] = pubnews_get_rcolor_code();
            endforeach;
        endif;
        return $array_defaults[$key];
    }
 endif;