<?php
/**
 * Handles the customizer additional functionality.
 */
if( !function_exists( 'pubnews_customizer_up_panel' ) ) :
    /**
     * Register controls for upsell, notifications and addtional info.
     * 
     */
    function pubnews_customizer_up_panel( $wp_customize ) {
        // preloader up
        $wp_customize->add_setting( 'preloader_upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'preloader_upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'More effects, display option and background color field', 'pubnews' ),
                'section'     => PUBNEWS_PREFIX . 'preloader_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // website styles up
        $wp_customize->add_setting( 'website_style__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'website_style__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Website style section with website frame, meta icon picker, author image/icon option', 'pubnews' ),
                'section'     => 'widget_styles_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // website layout up
        $wp_customize->add_setting( 'website_layout__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'website_layout__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Block title layout', 'pubnews' ),
                'section'     => 'pubnews_website_layout_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // animation up
        $wp_customize->add_setting( 'animation__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'animation__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Wow animation, 5+ animation effects', 'pubnews' ),
                'section'     => 'pubnews_animation_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // social icons up
        $wp_customize->add_setting( 'social_icons__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'social_icons__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Unlimited social icons and social share', 'pubnews' ),
                'section'     => 'pubnews_social_icons_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // button up
        $wp_customize->add_setting( 'buttons__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'buttons__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Button icon picker, icon size, border, padding and all google font families', 'pubnews' ),
                'section'     => 'pubnews_buttons_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // scroll to top up
        $wp_customize->add_setting( 'scroll_to_top__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'scroll_to_top__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Button icon picker, icon size, alignment, color and background', 'pubnews' ),
                'section'     => 'pubnews_stt_options_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // typography up
        $wp_customize->add_setting( 'typography__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'typography__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'More than 600+ google fonts available', 'pubnews' ),
                'section'     => 'pubnews_typography_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // top header up
        $wp_customize->add_setting( 'top_header__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'top_header__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'No of posts to show, icon size, elements styles options', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // header general up
        $wp_customize->add_setting( 'header_general__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'header_general__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '3 layouts, stickey header, bottom box shadow', 'pubnews' ),
                'section'     => 'pubnews_header_general_settings_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // ads up
        $wp_customize->add_setting( 'ads__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'ads__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'ads slider, shortcode, ads banner sidebar', 'pubnews' ),
                'section'     => 'pubnews_header_ads_banner_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // newsletter up
        $wp_customize->add_setting( 'newsletter__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'newsletter__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'label color, box shadow, icon picker', 'pubnews' ),
                'section'     => 'pubnews_header_newsletter_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // random news up
        $wp_customize->add_setting( 'random_news__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'random_news__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Icon picker and label field', 'pubnews' ),
                'section'     => 'pubnews_header_random_news_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // menu up
        $wp_customize->add_setting( 'menu__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'menu__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'More styling options, submenu, mobile menu', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // off canvas up
        $wp_customize->add_setting( 'off_canvas__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'off_canvas__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Toggle bar color, canvas background color and canvas width', 'pubnews' ),
                'section'     => 'pubnews_header_off_canvas_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // live search up
        $wp_customize->add_setting( 'live_search__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'live_search__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '3 layouts, live search and icon colors', 'pubnews' ),
                'section'     => 'pubnews_header_live_search_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // theme mod up
        $wp_customize->add_setting( 'theme_mod__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'theme_mod__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Default theme mode option', 'pubnews' ),
                'section'     => 'pubnews_header_theme_mode_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // ticker up
        $wp_customize->add_setting( 'ticker__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'ticker__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '3 ticker layouts, icon picker, offset, no of posts, marquee settings, show/hide elements', 'pubnews' ),
                'section'     => 'pubnews_ticker_news_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // banner up
        $wp_customize->add_setting( 'banner__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'banner__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '6 layouts, elements show/hide, slider settings, offset, no of posts to display', 'pubnews' ),
                'section'     => 'pubnews_main_banner_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );
        
        // front section up
        $wp_customize->add_setting( 'front_section__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'front_section__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '4 block layouts, video playlist section, three column section, section spacing, border, box shadow settings', 'pubnews' ),
                'section'     => 'pubnews_front_sections_reorder_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // archive general up
        $wp_customize->add_setting( 'archive_general__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'archive_general__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '5 layouts, meta reorder, border, box shadow', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // single general up
        $wp_customize->add_setting( 'single_general__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'single_general__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '4 layouts, elements show/hide and reorder, border, box shadow and table of content available', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // single related up
        $wp_customize->add_setting( 'single_related__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'single_related__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( '2 layouts, elements show/hide, no of posts to dislplay, filter by categories or tags and table of content available', 'pubnews' ),
                'section'     => 'single_related_posts_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // page up
        $wp_customize->add_setting( 'page__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'page__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Table of content available', 'pubnews' ),
                'section'     => 'pubnews_page_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // 404 up
        $wp_customize->add_setting( '404__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, '404__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Custom text and image', 'pubnews' ),
                'section'     => 'pubnews_404_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // search up
        $wp_customize->add_setting( 'search__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'search__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Custom text fields', 'pubnews' ),
                'section'     => 'pubnews_search_page_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // footer up
        $wp_customize->add_setting( 'footer__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'footer__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Border top, text color, vertical top bottom spacing', 'pubnews' ),
                'section'     => 'pubnews_footer_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );

        // bottom footer up
        $wp_customize->add_setting( 'bottom_footer__upgrade_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'bottom_footer__upgrade_info', array(
                'label'	      => esc_html__( 'Premium Features', 'pubnews' ),
                'description' => esc_html__( 'Full Copyright text editor, text color, link color', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'priority'  => 200,
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Premium', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/theme/pubnews-pro/' )
                    )
                )
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_up_panel', 20 );
endif;