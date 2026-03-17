<?php
use Pubnews\CustomizerDefault as PND;
/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
add_action( 'customize_preview_init', function() {
    wp_enqueue_script( 
        'pubnews-customizer-preview',
        get_template_directory_uri() . '/inc/customizer/assets/customizer-preview.min.js',
        ['customize-preview'],
        PUBNEWS_VERSION,
        true
    );
    // localize scripts
	wp_localize_script( 
        'pubnews-customizer-preview',
        'pubnewsPreviewObject', array(
            '_wpnonce'	=> wp_create_nonce( 'pubnews-customizer-nonce' ),
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'totalCats' => get_categories() ? get_categories() : [],
            'isArchive' =>  is_archive(),
            'isHome'    =>  is_home(),
            'isSingle'  =>  is_single()
        )
    );
});

add_action( 'customize_controls_enqueue_scripts', function() {
    $buildControlsDeps = apply_filters(  'pubnews_customizer_build_controls_dependencies', array(
        'wp-element',
        'react',
        'wp-blocks',
        'wp-editor',
        'wp-i18n',
        'wp-polyfill',
        'jquery',
        'wp-components'
    ));
	wp_enqueue_style( 
        'pubnews-customizer-control',
        get_template_directory_uri() . '/inc/customizer/assets/customizer-controls.min.css', 
        array('wp-components'),
        PUBNEWS_VERSION,
        'all'
    );
    wp_enqueue_script( 
        'pubnews-customizer-control',
        get_template_directory_uri() . '/inc/customizer/assets/customizer-extends.min.js',
        $buildControlsDeps,
        PUBNEWS_VERSION,
        true
    );

    // localize scripts
    wp_localize_script( 
        'pubnews-customizer-control', 
        'customizerControlsObject', array(
            'categories'    => pubnews_get_multicheckbox_categories_simple_array(),
            'posts' => pubnews_get_multicheckbox_posts_simple_array(),
            'imageSizes'    => pubnews_get_image_sizes_option_array(),
            '_wpnonce'  => wp_create_nonce( 'pubnews-customizer-controls-live-nonce' ),
            'ajaxUrl'   => esc_url(admin_url('admin-ajax.php')),
            'templateDirectoryUri'  =>  get_template_directory_uri()
        )
    );
});

if( !function_exists( 'pubnews_customizer_about_theme_panel' ) ) :
    /**
     * Register blog archive section settings
     * 
     */
    function pubnews_customizer_about_theme_panel( $wp_customize ) {
        /**
         * About theme section
         * 
         * @since 1.0.0
         */
        $wp_customize->add_section( PUBNEWS_PREFIX . 'about_section', array(
            'title' => esc_html__( 'About Theme', 'pubnews' ),
            'priority'  => 1
        ));

        // theme documentation info box
        $wp_customize->add_setting( 'site_documentation_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'site_documentation_info', array(
                'label'	      => esc_html__( 'Theme Documentation', 'pubnews' ),
                'description' => esc_html__( 'We have well prepared documentation which includes overall instructions and recommendations that are required in this theme.', 'pubnews' ),
                'section'     => PUBNEWS_PREFIX . 'about_section',
                'settings'    => 'site_documentation_info',
                'choices' => array(
                    array(
                        'label' => esc_html__( 'View Documentation', 'pubnews' ),
                        'url'   => esc_url( '//doc.blazethemes.com/pubnews' )
                    )
                )
            ))
        );

        // theme documentation info box
        $wp_customize->add_setting( 'site_support_info', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Info_Box_Control( $wp_customize, 'site_support_info', array(
                'label'	      => esc_html__( 'Theme Support', 'pubnews' ),
                'description' => esc_html__( 'We provide 24/7 support regarding any theme issue. Our support team will help you to solve any kind of issue. Feel free to contact us.', 'pubnews' ),
                'section'     => PUBNEWS_PREFIX . 'about_section',
                'settings'    => 'site_support_info',
                'choices' => array(
                    array(
                        'label' => esc_html__( 'Support Form', 'pubnews' ),
                        'url'   => esc_url( '//blazethemes.com/support' )
                    )
                )
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_about_theme_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_global_panel' ) ) :
    /**
     * Register global options settings
     * 
     */
    function pubnews_customizer_global_panel( $wp_customize ) {
        /**
         * Global panel
         * 
         * @package Pubnews
         * @since 1.0.0
         */
        $wp_customize->add_panel( 'pubnews_global_panel', array(
            'title' => esc_html__( 'Global', 'pubnews' ),
            'priority'  => 5
        ));

        // section- seo/misc settings section
        $wp_customize->add_section( 'pubnews_seo_misc_section', array(
            'title' => esc_html__( 'SEO / Misc', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // site schema ready option
        $wp_customize->add_setting( 'site_schema_ready', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_schema_ready' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport'    => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Toggle_Control( $wp_customize, 'site_schema_ready', array(
                'label'	      => esc_html__( 'Make website schema ready', 'pubnews' ),
                'section'     => 'pubnews_seo_misc_section',
                'settings'    => 'site_schema_ready'
            ))
        );

        // site date to show
        $wp_customize->add_setting( 'site_date_to_show', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'site_date_to_show' )
        ));
        $wp_customize->add_control( 'site_date_to_show', array(
            'type'      => 'select',
            'section'   => 'pubnews_seo_misc_section',
            'label'     => esc_html__( 'Date to display', 'pubnews' ),
            'description' => esc_html__( 'Whether to show date published or modified date.', 'pubnews' ),
            'choices'   => array(
                'published'  => __( 'Published date', 'pubnews' ),
                'modified'   => __( 'Modified date', 'pubnews' )
            )
        ));

        // site date format
        $wp_customize->add_setting( 'site_date_format', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'site_date_format' )
        ));
        $wp_customize->add_control( 'site_date_format', array(
            'type'      => 'select',
            'section'   => 'pubnews_seo_misc_section',
            'label'     => esc_html__( 'Date format', 'pubnews' ),
            'description' => esc_html__( 'Date format applied to single and archive pages.', 'pubnews' ),
            'choices'   => array(
                'theme_format'  => __( 'Default by theme', 'pubnews' ),
                'default'   => __( 'Wordpress default date', 'pubnews' )
            )
        ));

        // solid preset
        $wp_customize->add_setting( 'solid_presets', array(
            'default'   => PND\pubnews_get_customizer_default( 'solid_presets' ),
            'sanitize_callback' => 'pubnews_sanitize_array',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Preset_Colors_Control( $wp_customize, 'solid_presets', array(
                'label'	      => esc_html__( 'Solid Presets', 'pubnews' ),
                'section'     => 'colors',
                'settings'    => 'solid_presets'
            ))
        );

        // gradient presets
        $wp_customize->add_setting( 'gradient_presets', array(
            'default'   => PND\pubnews_get_customizer_default( 'gradient_presets' ),
            'sanitize_callback' => 'pubnews_sanitize_array',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Preset_Colors_Control( $wp_customize, 'gradient_presets', array(
                'label'	      => esc_html__( 'Gradient Presets', 'pubnews' ),
                'section'     => 'colors',
                'settings'    => 'gradient_presets',
                'blend' =>  'gradient'
            ))
        );

        // section- category colors section
        $wp_customize->add_section( 'pubnews_category_colors_section', array(
            'title' => esc_html__( 'Category Colors', 'pubnews' ),
            'panel' => 'pubnews_colors_panel',
            'priority'  => 40
        ));

        $totalCats = get_categories();
        if( $totalCats ) :
            foreach( $totalCats as $singleCat ) :
                // category colors control
                $wp_customize->add_setting( 'category_' .absint($singleCat->term_id). '_color', array(
                    'default'   => PND\pubnews_get_customizer_default( 'category_' .absint($singleCat->term_id). '_color' ),
                    'sanitize_callback' => 'pubnews_sanitize_color_group_picker_control',
                    'transport' =>  'postMessage'
                ));
                $wp_customize->add_control( 
                    new Pubnews_WP_Color_Group_Picker_Control( $wp_customize, 'category_' .absint($singleCat->term_id). '_color', array(
                        'label'	      => esc_html($singleCat->name),
                        'section'     => 'pubnews_category_colors_section',
                        'settings'    => 'category_' .absint($singleCat->term_id). '_color'
                    ))
                );
            endforeach;
        endif;

        // section- preloader section
        $wp_customize->add_section( 'pubnews_preloader_section', array(
            'title' => esc_html__( 'Preloader', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));
        
        // preloader option
        $wp_customize->add_setting( 'preloader_option', array(
            'default'   => PND\pubnews_get_customizer_default('preloader_option'),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'preloader_option', array(
                'label'	      => esc_html__( 'Enable site preloader', 'pubnews' ),
                'section'     => 'pubnews_preloader_section',
                'settings'    => 'preloader_option'
            ))
        );

        // post title animation effects 
        $wp_customize->add_setting( 'preloader_type', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'preloader_type' ),
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'preloader_type', array(
            'label'     => esc_html__( 'Preloader Type', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_preloader_section',
            'choices'   => array(
                '6' => __( 'One', 'pubnews' ),
                '7' => __( 'Two', 'pubnews' ),
                '8' => __( 'Three', 'pubnews' ),
                '15'    => __( 'Four', 'pubnews' ),
                '16'    => __( 'Five', 'pubnews' )
            )
        ));

        // widget styles section
        $wp_customize->add_section( 'widget_styles_section', array(
            'title' => esc_html__( 'Widget Styles', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // widget styles image settings heading
        $wp_customize->add_setting( 'widgets_styles_image_settings_heading', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'widgets_styles_image_settings_heading', array(
                'label'	      => esc_html__( 'Image Settings', 'pubnews' ),
                'section'     => 'widget_styles_section',
                'settings'    => 'widgets_styles_image_settings_heading'
            ))
        );

        // widget styles image border
        $wp_customize->add_setting( 'widgets_styles_image_border', [
            'default'   =>  PND\pubnews_get_customizer_default( 'widgets_styles_image_border' ),
            'sanitize_callback' =>  'pubnews_sanitize_array',
            'transport' =>  'postMessage'
        ]);

        $wp_customize->add_control(
            new Pubnews_WP_Border_Control( $wp_customize, 'widgets_styles_image_border', [
                'label' =>  esc_html__( 'Border', 'pubnews' ),
                'settings'  =>  'widgets_styles_image_border',
                'section'   =>  'widget_styles_section'
            ])
        );

        // // widget styles image border radius
        $wp_customize->add_setting( 'widgets_styles_image_border_radius', array(
            'default'   => PND\pubnews_get_customizer_default( 'widgets_styles_image_border_radius' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'widgets_styles_image_border_radius', array(
                'label'	      => esc_html__( 'Border Radius (px)', 'pubnews' ),
                'section'     => 'widget_styles_section',
                'settings'    => 'widgets_styles_image_border_radius',
                'unit'        => 'px',
                'input_attrs' => array(
                    'max'         => 200,
                    'min'         => 0,
                    'step'        => 1,
                    'reset' => true
                )
            ))
        );

        // widget styles image box shadow
        $wp_customize->add_setting( 'widgets_styles_image_box_shadow', [
            'default'   => PND\pubnews_get_customizer_default( 'widgets_styles_image_box_shadow' ),
            'sanitize_callback' => 'pubnews_sanitize_box_shadow_control',
            'transport' => 'postMessage'
        ]);
        $wp_customize->add_control( 
            new Pubnews_WP_Box_Shadow_Control( $wp_customize, 'widgets_styles_image_box_shadow', [
                'label'	      => esc_html__( 'Box Shadow', 'pubnews' ),
                'section'     => 'widget_styles_section',
                'settings'    => 'widgets_styles_image_box_shadow'
            ])
        );
        
        // section- website layout section
        $wp_customize->add_section( 'pubnews_website_layout_section', array(
            'title' => esc_html__( 'Website Layout', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));
        
        // website layout heading
        $wp_customize->add_setting( 'website_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'website_layout_header', array(
                'label'	      => esc_html__( 'Website Layout', 'pubnews' ),
                'section'     => 'pubnews_website_layout_section',
                'settings'    => 'website_layout_header'
            ))
        );

        // website layout
        $wp_customize->add_setting( 'website_layout',
            array(
                'default'           => PND\pubnews_get_customizer_default( 'website_layout' ),
                'sanitize_callback' => 'pubnews_sanitize_select_control',
                'transport' => 'postMessage'
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'website_layout',
            array(
                'section'  => 'pubnews_website_layout_section',
                'choices'  => array(
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed-width.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full-width.jpg'
                    )
                )
            )
        ));

        // website content layout heading
        $wp_customize->add_setting( 'website_content_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'website_content_layout_header', array(
                'label'	      => esc_html__( 'Website Content Global Layout', 'pubnews' ),
                'section'     => 'pubnews_website_layout_section',
                'settings'    => 'website_content_layout_header'
            ))
        );

        // website content layout
        $wp_customize->add_setting( 'website_content_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'website_content_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'website_content_layout',
            array(
                'section'  => 'pubnews_website_layout_section',
                'choices'  => array(
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // website block title layout 7 color headin
        $wp_customize->add_setting( 'website_block_title_color_heading', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'website_block_title_color_heading', array(
                'label'	      => esc_html__( 'Block Title colors', 'pubnews' ),
                'section'     => 'pubnews_website_layout_section',
                'settings'    => 'website_block_title_color_heading'
            ))
        );

        // block title primary and secondary color
        $block_title_colors = [
            'block_title_primary_color' =>  __( 'Primary Color', 'pubnews' ),
            'site_block_title_secondary_color' =>  __( 'Secondary Color', 'pubnews' ),
        ];
        if( ! empty( $block_title_colors ) && is_array( $block_title_colors ) ) :
            foreach( $block_title_colors as $id => $label ) :
                $wp_customize->add_setting( $id, array(
                    'default'   => PND\pubnews_get_customizer_default( $id ),
                    'transport' => 'postMessage',
                    'sanitize_callback' => 'pubnews_sanitize_color_picker_control'
                ));
                $wp_customize->add_control( 
                    new Pubnews_WP_Color_Picker_Control( $wp_customize, $id, array(
                        'label'	      => esc_html( $label ),
                        'section'     => 'pubnews_website_layout_section',
                        'settings'    => $id
                    ))
                );
            endforeach;
        endif;

        // section- animation section
        $wp_customize->add_section( 'pubnews_animation_section', array(
            'title' => esc_html__( 'Animation / Hover Effects', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // post title animation effects 
        $wp_customize->add_setting( 'post_title_hover_effects', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'post_title_hover_effects' ),
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'post_title_hover_effects', array(
            'type'      => 'select',
            'section'   => 'pubnews_animation_section',
            'label'     => esc_html__( 'Post title hover effects', 'pubnews' ),
            'description' => esc_html__( 'Applied to post titles listed in archive pages.', 'pubnews' ),
            'choices'   => array(
                'none'  => __( 'None', 'pubnews' ),
                'three' => __( 'Effect One', 'pubnews' ),
                'ten'   => __( 'Effect Two', 'pubnews' )
            )
        ));

        // site image animation effects 
        $wp_customize->add_setting( 'site_image_hover_effects', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'site_image_hover_effects' ),
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'site_image_hover_effects', array(
            'type'      => 'select',
            'section'   => 'pubnews_animation_section',
            'label'     => esc_html__( 'Image hover effects', 'pubnews' ),
            'description' => esc_html__( 'Applied to post thumbanails listed in archive pages.', 'pubnews' ),
            'choices'   => array(
                'none'  => __( 'None', 'pubnews' ),
                'four'  => __( 'Effect One', 'pubnews' ),
                'eight' => __( 'Effect Two', 'pubnews' )
            )
        ));

        // cursor animation effects
        $wp_customize->add_setting( 'cursor_animation', [
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'cursor_animation' )
        ]);
        $wp_customize->add_control( 'cursor_animation', [
            'type'      => 'select',
            'section'   => 'pubnews_animation_section',
            'label'     => esc_html__( 'Cursor animation', 'pubnews' ),
            'description' => esc_html__( 'Applied to mouse pointer.', 'pubnews' ),
            'choices'   => [
                'none' => esc_html__( 'None', 'pubnews' ),
                'two'  => esc_html__( 'Animation 1', 'pubnews' )
            ]
        ]);

        // section- social icons section
        $wp_customize->add_section( 'pubnews_social_icons_section', array(
            'title' => esc_html__( 'Social Icons', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));
        
        // social icons setting heading
        $wp_customize->add_setting( 'social_icons_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'social_icons_settings_header', array(
                'label'	      => esc_html__( 'Social Icons Settings', 'pubnews' ),
                'section'     => 'pubnews_social_icons_section',
                'settings'    => 'social_icons_settings_header'
            ))
        );

        // social icons target attribute value
        $wp_customize->add_setting( 'social_icons_target', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'social_icons_target' ),
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'social_icons_target', array(
            'type'      => 'select',
            'section'   => 'pubnews_social_icons_section',
            'label'     => esc_html__( 'Social Icon Link Open in', 'pubnews' ),
            'description' => esc_html__( 'Sets the target attribute according to the value.', 'pubnews' ),
            'choices'   => array(
                '_blank' => esc_html__( 'Open link in new tab', 'pubnews' ),
                '_self'  => esc_html__( 'Open link in same tab', 'pubnews' )
            )
        ));

        // social icons items
        $wp_customize->add_setting( 'social_icons', array(
            'default'   => PND\pubnews_get_customizer_default( 'social_icons' ),
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Custom_Repeater( $wp_customize, 'social_icons', array(
                'label'         => esc_html__( 'Social Icons', 'pubnews' ),
                'description'   => esc_html__( 'Hold and drag vertically to re-order the icons', 'pubnews' ),
                'section'       => 'pubnews_social_icons_section',
                'settings'      => 'social_icons',
                'row_label'     => 'inherit-icon_class',
                'add_new_label' => esc_html__( 'Add New Icon', 'pubnews' ),
                'fields'        => array(
                    'icon_class'   => array(
                        'type'          =>  'fontawesome-icon-picker',
                        'label'         =>  esc_html__( 'Social Icon', 'pubnews' ),
                        'description'   =>  esc_html__( 'Select from dropdown.', 'pubnews' ),
                        'default'       =>  esc_attr( 'fab fa-instagram' ),
                        'families'      =>  'social'
                    ),
                    'icon_url'  => array(
                        'type'      => 'url',
                        'label'     => esc_html__( 'URL for icon', 'pubnews' ),
                        'default'   => ''
                    ),
                    'item_option'             => 'show'
                )
            ))
        );

        // section- buttons section
        $wp_customize->add_section( 'pubnews_buttons_section', array(
            'title' => esc_html__( 'Buttons', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // site title typo
        $wp_customize->add_setting( 'global_button_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'global_button_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'global_button_typo', array(
                'label'	      => esc_html__( 'Typography', 'pubnews' ),
                'section'     => 'pubnews_buttons_section',
                'settings'    => 'global_button_typo',
                'tab'   => 'design',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        // global button label
        $wp_customize->add_setting( 'global_button_label', [
            'default'   =>  PND\pubnews_get_customizer_default( 'global_button_label' ),
            'sanitize_callback' =>  'sanitize_text_field',
            'transport' => 'postMessage'
        ]);
        $wp_customize->add_control('global_button_label', [
            'label' =>  esc_html__( 'Button Label', 'pubnews' ),
            'section'   =>  'pubnews_buttons_section',
            'type'  =>  'text'
        ]);
        
        // section- sidebar options section
        $wp_customize->add_section( 'pubnews_sidebar_options_section', array(
            'title' => esc_html__( 'Sidebar Options', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // frontpage sidebar layout heading
        $wp_customize->add_setting( 'frontpage_sidebar_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'frontpage_sidebar_layout_header', array(
                'label'	      => esc_html__( 'Frontpage Sidebar Layouts', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'frontpage_sidebar_layout_header'
            ))
        );

        // frontpage sidebar layout
        $wp_customize->add_setting( 'frontpage_sidebar_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'frontpage_sidebar_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'frontpage_sidebar_layout',
            array(
                'section'  => 'pubnews_sidebar_options_section',
                'choices'  => pubnews_get_customizer_sidebar_array()
            )
        ));

        // frontpage sidebar sticky option
        $wp_customize->add_setting( 'frontpage_sidebar_sticky_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'frontpage_sidebar_sticky_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'frontpage_sidebar_sticky_option', array(
                'label'	      => esc_html__( 'Enable sidebar sticky', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'frontpage_sidebar_sticky_option'
            ))
        );

        // archive sidebar layouts heading
        $wp_customize->add_setting( 'archive_sidebar_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'archive_sidebar_layout_header', array(
                'label'	      => esc_html__( 'Archive Sidebar Layouts', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'archive_sidebar_layout_header'
            ))
        );

        // archive sidebar layout
        $wp_customize->add_setting( 'archive_sidebar_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'archive_sidebar_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'archive_sidebar_layout',
            array(
                'section'  => 'pubnews_sidebar_options_section',
                'choices'  => pubnews_get_customizer_sidebar_array()
            )
        ));

        // archive sidebar sticky option
        $wp_customize->add_setting( 'archive_sidebar_sticky_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'archive_sidebar_sticky_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'archive_sidebar_sticky_option', array(
                'label'	      => esc_html__( 'Enable sidebar sticky', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'archive_sidebar_sticky_option'
            ))
        );

        // single sidebar layouts heading
        $wp_customize->add_setting( 'single_sidebar_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'single_sidebar_layout_header', array(
                'label'	      => esc_html__( 'Post Sidebar Layouts', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'single_sidebar_layout_header'
            ))
        );

        // single sidebar layout
        $wp_customize->add_setting( 'single_sidebar_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'single_sidebar_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'single_sidebar_layout',
            array(
                'section'  => 'pubnews_sidebar_options_section',
                'choices'  => pubnews_get_customizer_sidebar_array()
            )
        ));

        // single sidebar sticky option
        $wp_customize->add_setting( 'single_sidebar_sticky_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_sidebar_sticky_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'single_sidebar_sticky_option', array(
                'label'	      => esc_html__( 'Enable sidebar sticky', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'single_sidebar_sticky_option'
            ))
        );

        // page sidebar layouts heading
        $wp_customize->add_setting( 'page_sidebar_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'page_sidebar_layout_header', array(
                'label'	      => esc_html__( 'Page Sidebar Layouts', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'page_sidebar_layout_header'
            ))
        );

        // page sidebar layout
        $wp_customize->add_setting( 'page_sidebar_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'page_sidebar_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'page_sidebar_layout',
            array(
                'section'  => 'pubnews_sidebar_options_section',
                'choices'  => pubnews_get_customizer_sidebar_array()
            )
        ));

        // page sidebar sticky option
        $wp_customize->add_setting( 'page_sidebar_sticky_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'page_sidebar_sticky_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'page_sidebar_sticky_option', array(
                'label'	      => esc_html__( 'Enable sidebar sticky', 'pubnews' ),
                'section'     => 'pubnews_sidebar_options_section',
                'settings'    => 'page_sidebar_sticky_option'
            ))
        );

        // section- breadcrumb options section
        $wp_customize->add_section( 'pubnews_breadcrumb_options_section', array(
            'title' => esc_html__( 'Breadcrumb Options', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // breadcrumb option
        $wp_customize->add_setting( 'site_breadcrumb_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_breadcrumb_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'site_breadcrumb_option', array(
                'label'	      => esc_html__( 'Show breadcrumb trails', 'pubnews' ),
                'section'     => 'pubnews_breadcrumb_options_section',
                'settings'    => 'site_breadcrumb_option'
            ))
        );

        // breadcrumb type 
        $wp_customize->add_setting( 'site_breadcrumb_type', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'site_breadcrumb_type' )
        ));
        $wp_customize->add_control( 'site_breadcrumb_type', array(
            'type'      => 'select',
            'section'   => 'pubnews_breadcrumb_options_section',
            'label'     => esc_html__( 'Breadcrumb type', 'pubnews' ),
            'description' => esc_html__( 'If you use other than "default" one you will need to install and activate respective plugins Breadcrumb NavXT, Yoast SEO and Rank Math SEO', 'pubnews' ),
            'choices'   => array(
                'default' => __( 'Default', 'pubnews' ),
                'bcn'  => __( 'NavXT', 'pubnews' ),
                'yoast'  => __( 'Yoast SEO', 'pubnews' ),
                'rankmath'  => __( 'Rank Math', 'pubnews' )
            )
        ));

        // section- scroll to top options
        $wp_customize->add_section( 'pubnews_stt_options_section', array(
            'title' => esc_html__( 'Scroll To Top', 'pubnews' ),
            'panel' => 'pubnews_global_panel'
        ));

        // Resposive vivibility option
        $wp_customize->add_setting( 'stt_responsive_option', array(
            'default' => PND\pubnews_get_customizer_default( 'stt_responsive_option' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_multiselect_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Responsive_Multiselect_Tab_Control( $wp_customize, 'stt_responsive_option', array(
                'label'	      => esc_html__( 'Scroll To Top Visibility', 'pubnews' ),
                'section'     => 'pubnews_stt_options_section',
                'settings'    => 'stt_responsive_option'
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_global_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_site_identity_panel' ) ) :
    /**
     * Register site identity settings
     * 
     */
    function pubnews_customizer_site_identity_panel( $wp_customize ) {
        /**
         * Register "Site Identity Options" panel
         * 
         */
        $wp_customize->add_panel( 'pubnews_site_identity_panel', array(
            'title' => esc_html__( 'Site Identity', 'pubnews' ),
            'priority' => 5
        ));
        $wp_customize->get_section( 'title_tagline' )->panel = 'pubnews_site_identity_panel'; // assing title tagline section to site identity panel
        $wp_customize->get_section( 'title_tagline' )->title = esc_html__( 'Logo & Site Icon', 'pubnews' ); // modify site logo label

        /**
         * Site Title Section
         * 
         * panel - pubnews_site_identity_panel
         */
        $wp_customize->add_section( 'pubnews_site_title_section', array(
            'title' => esc_html__( 'Site Title & Tagline', 'pubnews' ),
            'panel' => 'pubnews_site_identity_panel',
            'priority'  => 30,
        ));
        $wp_customize->get_control( 'blogname' )->section = 'pubnews_site_title_section';
        $wp_customize->get_control( 'display_header_text' )->section = 'pubnews_site_title_section';
        $wp_customize->get_control( 'display_header_text' )->label = esc_html__( 'Display site title', 'pubnews' );
        $wp_customize->get_control( 'blogdescription' )->section = 'pubnews_site_title_section';
        
        // site logo width
        $wp_customize->add_setting( 'pubnews_site_logo_width', array(
            'default'   => PND\pubnews_get_customizer_default( 'pubnews_site_logo_width' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'pubnews_site_logo_width', array(
                    'label'	      => esc_html__( 'Logo Width (px)', 'pubnews' ),
                    'section'     => 'title_tagline',
                    'settings'    => 'pubnews_site_logo_width',
                    'unit'        => 'px',
                    'input_attrs' => array(
                    'max'         => 400,
                    'min'         => 1,
                    'step'        => 1,
                    'reset' => true
                )
            ))
        );

        // site title section tab
        $wp_customize->add_setting( 'site_title_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'site_title_section_tab', array(
                'section'     => 'pubnews_site_title_section',
                'priority'  => 1,
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // blog description option
        $wp_customize->add_setting( 'blogdescription_option', array(
            'default'        => true,
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'blogdescription_option', array(
            'label'    => esc_html__( 'Display site description', 'pubnews' ),
            'section'  => 'pubnews_site_title_section',
            'type'     => 'checkbox',
            'priority' => 50
        ));

        $wp_customize->get_control( 'header_textcolor' )->section = 'pubnews_site_title_section';
        $wp_customize->get_control( 'header_textcolor' )->priority = 60;
        $wp_customize->get_control( 'header_textcolor' )->label = esc_html__( 'Site Title Color', 'pubnews' );

        // header text hover color
        $wp_customize->add_setting( 'site_title_hover_textcolor', array(
            'default' => PND\pubnews_get_customizer_default( 'site_title_hover_textcolor' ),
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Default_Color_Control( $wp_customize, 'site_title_hover_textcolor', array(
                'label'      => esc_html__( 'Site Title Hover Color', 'pubnews' ),
                'section'    => 'pubnews_site_title_section',
                'settings'   => 'site_title_hover_textcolor',
                'priority'    => 70,
                'tab'   => 'design'
            ))
        );

        // site description color
        $wp_customize->add_setting( 'site_description_color', array(
            'default' => PND\pubnews_get_customizer_default( 'site_description_color' ),
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Default_Color_Control( $wp_customize, 'site_description_color', array(
                'label'      => esc_html__( 'Site Description Color', 'pubnews' ),
                'section'    => 'pubnews_site_title_section',
                'settings'   => 'site_description_color',
                'priority'    => 70,
                'tab'   => 'design'
            ))
        );

        // site title typo
        $wp_customize->add_setting( 'site_title_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_title_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_title_typo', array(
                'label'	      => esc_html__( 'Site Title Typography', 'pubnews' ),
                'section'     => 'pubnews_site_title_section',
                'settings'    => 'site_title_typo',
                'tab'   => 'design',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        // site tagline typo
        $wp_customize->add_setting( 'site_tagline_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_tagline_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_tagline_typo', array(
                'label'	      => esc_html__( 'Site Tagline Typography', 'pubnews' ),
                'section'     => 'pubnews_site_title_section',
                'settings'    => 'site_tagline_typo',
                'tab'   => 'design',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_site_identity_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_top_header_panel' ) ) :
    /**
     * Register header options settings
     * 
     */
    function pubnews_customizer_top_header_panel( $wp_customize ) {
        /**
         * Top header section
         * 
         */
        $wp_customize->add_section( 'pubnews_top_header_section', array(
            'title' => esc_html__( 'Top Header', 'pubnews' ),
            'priority'  => 68
        ));
        
        // section tab
        $wp_customize->add_setting( 'top_header_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'top_header_section_tab', array(
                'section'     => 'pubnews_top_header_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );
        
        // Top header option
        $wp_customize->add_setting( 'top_header_option', array(
            'default'         => PND\pubnews_get_customizer_default( 'top_header_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
    
        $wp_customize->add_control( 
            new Pubnews_WP_Toggle_Control( $wp_customize, 'top_header_option', array(
                'label'	      => esc_html__( 'Show top header', 'pubnews' ),
                'description' => esc_html__( 'Toggle to enable or disable top header bar', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_option'
            ))
        );

        // Top header date time option
        $wp_customize->add_setting( 'top_header_date_time_option', array(
            'default'         => PND\pubnews_get_customizer_default( 'top_header_date_time_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'top_header_date_time_option', array(
                'label'	      => esc_html__( 'Show date and time', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_date_time_option',
            ))
        );

        // Top header ticker news option
        $wp_customize->add_setting( 'top_header_ticker_news_option', array(
            'default'         => PND\pubnews_get_customizer_default( 'top_header_ticker_news_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'top_header_ticker_news_option', array(
                'label'	      => esc_html__( 'Show ticker news', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_ticker_news_option'
            ))
        );

        // Ticker News categories
        $wp_customize->add_setting( 'top_header_ticker_news_categories', array(
            'default' => PND\pubnews_get_customizer_default( 'top_header_ticker_news_categories' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'top_header_ticker_news_categories', array(
                'label'     => esc_html__( 'Posts Categories', 'pubnews' ),
                'section'   => 'pubnews_top_header_section',
                'settings'  => 'top_header_ticker_news_categories',
                'endpoint' =>  'extend/get_taxonomy',
                'purpose'   =>  'category'
            ))
        );

        // Ticker News posts
        $wp_customize->add_setting( 'top_header_ticker_news_posts', array(
            'default' => PND\pubnews_get_customizer_default( 'top_header_ticker_news_posts' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'top_header_ticker_news_posts', array(
                'label'     => esc_html__( 'Posts To Include', 'pubnews' ),
                'section'   => 'pubnews_top_header_section',
                'settings'  => 'top_header_ticker_news_posts'
            ))
        );

        // top header post query date range
        $wp_customize->add_setting( 'top_header_ticker_news_date_filter', array(
            'default' => PND\pubnews_get_customizer_default( 'top_header_ticker_news_date_filter' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'top_header_ticker_news_date_filter', array(
            'label'     => __( 'Date Range', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_top_header_section',
            'choices'   => pubnews_get_date_filter_choices_array()
        ));

        // top header social option
        $wp_customize->add_setting( 'top_header_social_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'top_header_social_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'top_header_social_option', array(
                'label'	      => esc_html__( 'Show social icons', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_social_option',
            ))
        );

        // Redirect header social icons link
        $wp_customize->add_setting( 'top_header_social_icons_redirects', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'top_header_social_icons_redirects', array(
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_social_icons_redirects',
                'choices'     => array(
                    'header-social-icons' => array(
                        'type'  => 'section',
                        'id'    => 'pubnews_social_icons_section',
                        'label' => esc_html__( 'Manage social icons', 'pubnews' )
                    )
                )
            ))
        );

        // Top header background colors group control
        $wp_customize->add_setting( 'top_header_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'top_header_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Group_Control( $wp_customize, 'top_header_background_color_group', array(
                'label'	      => esc_html__( 'Section Background', 'pubnews' ),
                'section'     => 'pubnews_top_header_section',
                'settings'    => 'top_header_background_color_group',
                'tab'   => 'design'
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_top_header_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_header_panel' ) ) :
    /**
     * Register header options settings
     * 
     */
    function pubnews_customizer_header_panel( $wp_customize ) {
        /**
         * Header panel
         * 
         */
        $wp_customize->add_panel( 'pubnews_header_panel', array(
            'title' => esc_html__( 'Theme Header', 'pubnews' ),
            'priority'  => 69
        ));
        
        // Header ads banner section
        $wp_customize->add_section( 'pubnews_header_ads_banner_section', array(
            'title' => esc_html__( 'Ads Banner', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 10
        ));

        // Header Ads Banner setting heading
        $wp_customize->add_setting( 'pubnews_header_ads_banner_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'pubnews_header_ads_banner_header', array(
                'label'	      => esc_html__( 'Ads Banner Setting', 'pubnews' ),
                'section'     => 'pubnews_header_ads_banner_section',
                'settings'    => 'pubnews_header_ads_banner_header'
            ))
        );

        // Resposive vivibility option
        $wp_customize->add_setting( 'header_ads_banner_responsive_option', array(
            'default' => PND\pubnews_get_customizer_default( 'header_ads_banner_responsive_option' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_multiselect_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Responsive_Multiselect_Tab_Control( $wp_customize, 'header_ads_banner_responsive_option', array(
                'label'	      => esc_html__( 'Ads Banner Visibility', 'pubnews' ),
                'section'     => 'pubnews_header_ads_banner_section',
                'settings'    => 'header_ads_banner_responsive_option'
            ))
        );

        // Header ads banner type
        $wp_customize->add_setting( 'header_ads_banner_type', array(
            'default' => PND\pubnews_get_customizer_default( 'header_ads_banner_type' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'header_ads_banner_type', array(
            'type'      => 'select',
            'section'   => 'pubnews_header_ads_banner_section',
            'label'     => __( 'Ads banner type', 'pubnews' ),
            'description' => __( 'Choose to display ads content from.', 'pubnews' ),
            'choices'   => array(
                'none'  => esc_html__( 'None', 'pubnews' ),
                'custom' => esc_html__( 'Custom', 'pubnews' )
            ),
        ));

        // ads image field
        $wp_customize->add_setting( 'header_ads_banner_custom_image', array(
            'default' => PND\pubnews_get_customizer_default( 'header_ads_banner_custom_image' ),
            'sanitize_callback' => 'absint',
        ));
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'header_ads_banner_custom_image', array(
            'section' => 'pubnews_header_ads_banner_section',
            'mime_type' => 'image',
            'label' => esc_html__( 'Ads Image', 'pubnews' ),
            'description' => esc_html__( 'Recommended size for ad image is 900 (width) * 350 (height)', 'pubnews' ),
            'active_callback'   => function( $setting ) {
                if ( $setting->manager->get_setting( 'header_ads_banner_type' )->value() === 'custom' ) {
                    return true;
                }
                return false;
            }
        )));

        // ads url field
        $wp_customize->add_setting( 'header_ads_banner_custom_url', array(
            'default' => PND\pubnews_get_customizer_default( 'header_ads_banner_custom_url' ),
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control( 'header_ads_banner_custom_url', array(
            'type'  => 'url',
            'section'   => 'pubnews_header_ads_banner_section',
            'label'     => esc_html__( 'Ads url', 'pubnews' ),
            'active_callback'   => function( $setting ) {
                if ( $setting->manager->get_setting( 'header_ads_banner_type' )->value() === 'custom' ) {
                    return true;
                }
                return false;
            }
        ));

        // ads link show on
        $wp_customize->add_setting( 'header_ads_banner_custom_target', array(
            'default' => PND\pubnews_get_customizer_default( 'header_ads_banner_custom_target' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'header_ads_banner_custom_target', array(
            'type'      => 'select',
            'section'   => 'pubnews_header_ads_banner_section',
            'label'     => __( 'Open Ads link on', 'pubnews' ),
            'choices'   => array(
                '_self' => esc_html__( 'Open in same tab', 'pubnews' ),
                '_blank' => esc_html__( 'Open in new tab', 'pubnews' )
            ),
            'active_callback'   => function( $setting ) {
                if ( $setting->manager->get_setting( 'header_ads_banner_type' )->value() === 'custom' ) {
                    return true;
                }
                return false;
            }
        ));

        // header general settings section
        $wp_customize->add_section( 'pubnews_header_general_settings_section', array(
            'title' => esc_html__( 'General Settings', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 5
        ));

        // section tab
        $wp_customize->add_setting( 'main_header_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'main_header_section_tab', array(
                'section'     => 'pubnews_header_general_settings_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // header width layout
        $wp_customize->add_setting( 'header_width_layout', array(
            'default' => PND\pubnews_get_customizer_default( 'header_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'transport' =>  'postMessage'
        ));
        $wp_customize->add_control( new Pubnews_WP_Radio_Image_Control(
            $wp_customize,
            'header_width_layout',
            array(
                'label'     => __( 'Width Layout', 'pubnews' ),
                'section'  => 'pubnews_header_general_settings_section',
                'priority' => 10,
                'choices'  => array(
                    'global' => array(
                        'label' =>  esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'contain' => array(
                        'label' =>  esc_html__( 'Container', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width' => array(
                        'label' =>  esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // redirect site logo section
        $wp_customize->add_setting( 'header_site_logo_redirects', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'header_site_logo_redirects', array(
                'section'     => 'pubnews_header_general_settings_section',
                'settings'    => 'header_site_logo_redirects',
                'choices'     => array(
                    'header-social-icons' => array(
                        'type'  => 'section',
                        'id'    => 'title_tagline',
                        'label' => esc_html__( 'Manage Site Logo', 'pubnews' )
                    )
                )
            ))
        );

        // redirect site title section
        $wp_customize->add_setting( 'header_site_title_redirects', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'header_site_title_redirects', array(
                'section'     => 'pubnews_header_general_settings_section',
                'settings'    => 'header_site_title_redirects',
                'choices'     => array(
                    'header-social-icons' => array(
                        'type'  => 'section',
                        'id'    => 'pubnews_site_title_section',
                        'label' => esc_html__( 'Manage Site & Tagline', 'pubnews' )
                    )
                )
            ))
        );

        // header top and bottom padding
        $wp_customize->add_setting( 'header_vertical_padding', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_vertical_padding' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'header_vertical_padding', array(
                    'label'	      => esc_html__( 'Vertical Padding (px)', 'pubnews' ),
                    'section'     => 'pubnews_header_general_settings_section',
                    'settings'    => 'header_vertical_padding',
                    'unit'        => 'px',
                    'tab'   => 'design',
                    'input_attrs' => array(
                    'max'         => 30,
                    'min'         => 1,
                    'step'        => 1,
                    'reset' => true
                )
            ))
        );

        // Header background colors setting heading
        $wp_customize->add_setting( 'header_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_background_color_group' ),
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'header_background_color_group', array(
                'label'	      => esc_html__( 'Background', 'pubnews' ),
                'section'     => 'pubnews_header_general_settings_section',
                'settings'    => 'header_background_color_group',
                'tab'   => 'design'
            ))
        );

        // Header newsletter section
        $wp_customize->add_section( 'pubnews_header_newsletter_section', array(
            'title' => esc_html__( 'Newsletter / Subscribe Button', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 15
        ));

        // section tab
        $wp_customize->add_setting( 'pubnews_header_newsletter_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'pubnews_header_newsletter_tab', array(
                'section'     => 'pubnews_header_newsletter_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // header newsletter button option
        $wp_customize->add_setting( 'header_newsletter_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_newsletter_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'header_newsletter_option', array(
                'label'	      => esc_html__( 'Show newsletter button', 'pubnews' ),
                'section'     => 'pubnews_header_newsletter_section',
                'settings'    => 'header_newsletter_option'
            ))
        );

        // newsletter label
        $wp_customize->add_setting( 'newsletter_label', [
            'default'   =>  PND\pubnews_get_customizer_default( 'newsletter_label' ),
            'sanitize_callback' =>  'sanitize_text_field',
            'transport' => 'postMessage'
        ]);
        $wp_customize->add_control('newsletter_label', [
            'label' =>  esc_html__( 'Button Label', 'pubnews' ),
            'section'   =>  'pubnews_header_newsletter_section',
            'type'  =>  'text'
        ]);
        
        // newsletter redirect href target
        $wp_customize->add_setting( 'header_newsletter_redirect_href_target', array(
            'default' => PND\pubnews_get_customizer_default( 'header_newsletter_redirect_href_target' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'header_newsletter_redirect_href_target', array(
            'type'      => 'select',
            'section'   => 'pubnews_header_newsletter_section',
            'label'     => __( 'Open link on', 'pubnews' ),
            'choices'   => array(
                '_self' => esc_html__( 'Open in same tab', 'pubnews' ),
                '_blank' => esc_html__( 'Open in new tab', 'pubnews' )
            )
        ));

        // newsletter redirect url
        $wp_customize->add_setting( 'header_newsletter_redirect_href_link', array(
            'default' => PND\pubnews_get_customizer_default( 'header_newsletter_redirect_href_link' ),
            'sanitize_callback' => 'pubnews_sanitize_url',
        ));
        $wp_customize->add_control( 'header_newsletter_redirect_href_link', array(
            'label' => esc_html__( 'Redirect URL.', 'pubnews' ),
            'description'   => esc_html__( 'Add url for the button to redirect.', 'pubnews' ),
            'section'   => 'pubnews_header_newsletter_section',
            'type'  => 'url'
        ));

        // header menu active background
        $wp_customize->add_setting( 'header_newsletter_background_color', [
            'default'   => PND\pubnews_get_customizer_default( 'header_newsletter_background_color' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ]);
        $wp_customize->add_control( 
            new Pubnews_WP_Background_Color_Group_Picker_Control( $wp_customize, 'header_newsletter_background_color', [
                'label'	      => esc_html__( 'Background Color', 'pubnews' ),
                'section'     => 'pubnews_header_newsletter_section',
                'settings'    => 'header_newsletter_background_color',
                'tab'   =>  'design'
            ])
        );

        // Header random news section
        $wp_customize->add_section( 'pubnews_header_random_news_section', array(
            'title' => esc_html__( 'Random News', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 15
        ));

        // header random news button option
        $wp_customize->add_setting( 'header_random_news_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_random_news_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'header_random_news_option', array(
                'label'	      => esc_html__( 'Show random news button', 'pubnews' ),
                'section'     => 'pubnews_header_random_news_section',
                'settings'    => 'header_random_news_option'
            ))
        );

        // random news redirect href target
        $wp_customize->add_setting( 'header_random_news_redirect_href_target', array(
            'default' => PND\pubnews_get_customizer_default( 'header_random_news_redirect_href_target' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'header_random_news_redirect_href_target', array(
            'type'      => 'select',
            'section'   => 'pubnews_header_random_news_section',
            'label'     => __( 'Open link on', 'pubnews' ),
            'choices'   => array(
                '_self' => esc_html__( 'Open in same tab', 'pubnews' ),
                '_blank' => esc_html__( 'Open in new tab', 'pubnews' )
            )
        ));

        /**
         * Menu Options Section
         * 
         * panel - pubnews_header_options_panel
         */
        $wp_customize->add_section( 'pubnews_header_menu_option_section', array(
            'title' => esc_html__( 'Menu Options', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 30,
        ));

        // menu section tab
        $wp_customize->add_setting( 'header_menu_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'header_menu_section_tab', array(
                'section'     => 'pubnews_header_menu_option_section',
                'choices'  => array(
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    ),
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'Typography', 'pubnews' )
                    )
                )
            ))
        );

        // live search target
        $wp_customize->add_setting( 'header_menu_hover_effect', array(
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'default'   => PND\pubnews_get_customizer_default( 'header_menu_hover_effect' ),
            'transport' =>  'postMessage'
        ));
        $wp_customize->add_control( 'header_menu_hover_effect', array(
            'type'  => 'select',
            'section'   => 'pubnews_header_menu_option_section',
            'label' => esc_html__( 'Hover Effect', 'pubnews' ),
            'tab'   => 'design',
            'choices'   => array(
                'none' => esc_html__( 'None', 'pubnews' ),
                'one'  => esc_html__( 'One', 'pubnews' )
            )
        ));

        // header menu background color group
        $wp_customize->add_setting( 'header_menu_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_menu_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Group_Control( $wp_customize, 'header_menu_background_color_group', array(
                'label'	      => esc_html__( 'Background', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'settings'    => 'header_menu_background_color_group',
                'tab'   => 'design'
            ))
        );

        // sub menu header
        $wp_customize->add_setting( 'header_sub_menu_color_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'header_sub_menu_color_header', array(
                'label'	      => esc_html__( 'Sub Menu', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'settings'    => 'header_sub_menu_color_header',
                'tab'   => 'design'
            ))
        );

        // header sub menu background color group
        $wp_customize->add_setting( 'header_sub_menu_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_sub_menu_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Group_Control( $wp_customize, 'header_sub_menu_background_color_group', array(
                'label'	      => esc_html__( 'Background', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'settings'    => 'header_sub_menu_background_color_group',
                'tab'   => 'design'
            ))
        );

        // menu typo
        $wp_customize->add_setting( 'header_menu_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_menu_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'header_menu_typo', array(
                'label'	      => esc_html__( 'Main Menu', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'settings'    => 'header_menu_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        // sub menu typo
        $wp_customize->add_setting( 'header_sub_menu_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_sub_menu_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'header_sub_menu_typo', array(
                'label'	      => esc_html__( 'Sub Menu', 'pubnews' ),
                'section'     => 'pubnews_header_menu_option_section',
                'settings'    => 'header_sub_menu_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        /**
         * Off Canvas Section
         * 
         * panel - pubnews_header_options_panel
         */
        $wp_customize->add_section( 'pubnews_header_off_canvas_section', array(
            'title' => esc_html__( 'Off Canvas', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 40
        ));

        // header off canvas button option
        $wp_customize->add_setting( 'header_off_canvas_option', array(
            'default'         => PND\pubnews_get_customizer_default( 'header_off_canvas_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
    
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'header_off_canvas_option', array(
                'label'	      => esc_html__( 'Show off canvas', 'pubnews' ),
                'section'     => 'pubnews_header_off_canvas_section',
                'settings'    => 'header_off_canvas_option'
            ))
        );

        // header off canvas alignment
        $wp_customize->add_setting( 'off_canvas_position', array(
            'default' => PND\pubnews_get_customizer_default( 'off_canvas_position' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Tab_Control( $wp_customize, 'off_canvas_position', array(
                'label'	      => esc_html__( 'Canvas sidebar position', 'pubnews' ),
                'section'     => 'pubnews_header_off_canvas_section',
                'settings'    => 'off_canvas_position',
                'choices' => array(
                    array(
                        'value' => 'left',
                        'label' => esc_html__('Left', 'pubnews' )
                    ),
                    array(
                        'value' => 'right',
                        'label' => esc_html__('Right', 'pubnews' )
                    )
                )
            ))
        );

        // redirect off canvas button link
        $wp_customize->add_setting( 'header_sidebar_toggle_button_redirects', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));

        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'header_sidebar_toggle_button_redirects', array(
                'section'     => 'pubnews_header_off_canvas_section',
                'settings'    => 'header_sidebar_toggle_button_redirects',
                'choices'     => array(
                    'header-social-icons' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-off-canvas-sidebar',
                        'label' => esc_html__( 'Manage sidebar from here', 'pubnews' )
                    )
                )
            ))
        );

        /**
         * Live Search Section
         * 
         * panel - pubnews_header_options_panel
         */
        $wp_customize->add_section( 'pubnews_header_live_search_section', array(
            'title' => esc_html__( 'Live Search', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 50
        ));

        // header search option
        $wp_customize->add_setting( 'header_search_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_search_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
    
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'header_search_option', array(
                'label'	      => esc_html__( 'Show search icon', 'pubnews' ),
                'section'     => 'pubnews_header_live_search_section',
                'settings'    => 'header_search_option'
            ))
        );

        /**
         * Theme Mode Section
         * 
         * panel - pubnews_header_options_panel
         */
        $wp_customize->add_section( 'pubnews_header_theme_mode_section', array(
            'title' => esc_html__( 'Theme Mode', 'pubnews' ),
            'panel' => 'pubnews_header_panel',
            'priority'  => 50
        ));

        // header theme mode toggle option
        $wp_customize->add_setting( 'header_theme_mode_toggle_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'header_theme_mode_toggle_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'header_theme_mode_toggle_option', array(
                'label'	      => esc_html__( 'Show dark/light toggle icon', 'pubnews' ),
                'section'     => 'pubnews_header_theme_mode_section',
                'settings'    => 'header_theme_mode_toggle_option'
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_header_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_ticker_news_panel' ) ) :
    // Register header options settings
    function pubnews_customizer_ticker_news_panel( $wp_customize ) {
        // Header ads banner section
        $wp_customize->add_section( 'pubnews_ticker_news_section', array(
            'title' => esc_html__( 'Ticker News', 'pubnews' ),
            'priority'  => 70
        ));

        // Ticker News Width Layouts setting heading
        $wp_customize->add_setting( 'ticker_news_width_layouts_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'ticker_news_width_layouts_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_ticker_news_section',
                'settings'    => 'ticker_news_width_layouts_header'
            ))
        );

        // website content layout
        $wp_customize->add_setting( 'ticker_news_width_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'ticker_news_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'ticker_news_width_layout',
            array(
                'section'  => 'pubnews_ticker_news_section',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // Header menu hover effect
        $wp_customize->add_setting( 'ticker_news_visible', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_visible' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'ticker_news_visible', array(
            'type'      => 'select',
            'section'   => 'pubnews_ticker_news_section',
            'label'     => esc_html__( 'Show ticker on', 'pubnews' ),
            'choices'   => array(
                'all' => esc_html__( 'Show in all', 'pubnews' ),
                'front-page' => esc_html__( 'Frontpage', 'pubnews' ),
                'innerpages' => esc_html__( 'Show only in innerpages', 'pubnews' ),
                'none' => esc_html__( 'Hide in all pages', 'pubnews' ),
            ),
        ));
        
        // Ticker News content setting heading
        $wp_customize->add_setting( 'ticker_news_content_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'ticker_news_content_header', array(
                'label'	      => esc_html__( 'Content Setting', 'pubnews' ),
                'section'     => 'pubnews_ticker_news_section',
                'settings'    => 'ticker_news_content_header',
                'type'        => 'section-heading',
            ))
        );

        // ticker News title
        $wp_customize->add_setting( 'ticker_news_title', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_title' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'ticker_news_title', array(
                'label' => esc_html__( 'Ticker title', 'pubnews' ),
                'type'  => 'text',
                'section'   => 'pubnews_ticker_news_section'
            )
        );

        // Ticker News orderby
        $wp_customize->add_setting( 'ticker_news_order_by', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_order_by' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'ticker_news_order_by', array(
            'type'      => 'select',
            'section'   => 'pubnews_ticker_news_section',
            'label'     => esc_html__( 'Orderby', 'pubnews' ),
            'choices'   => pubnews_customizer_orderby_options_array(),
        ));

        // Ticker News categories
        $wp_customize->add_setting( 'ticker_news_categories', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_categories' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'ticker_news_categories', array(
                'label'     => esc_html__( 'Posts Categories', 'pubnews' ),
                'section'   => 'pubnews_ticker_news_section',
                'settings'  => 'ticker_news_categories',
                'endpoint' =>  'extend/get_taxonomy',
                'purpose'   =>  'category'
            ))
        );

        // Ticker News posts
        $wp_customize->add_setting( 'ticker_news_posts', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_posts' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'ticker_news_posts', array(
                'label'     => esc_html__( 'Posts To Include', 'pubnews' ),
                'section'   => 'pubnews_ticker_news_section',
                'settings'  => 'ticker_news_posts'
            ))
        );

        // ticker news post query date range
        $wp_customize->add_setting( 'ticker_news_date_filter', array(
            'default' => PND\pubnews_get_customizer_default( 'ticker_news_date_filter' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'ticker_news_date_filter', array(
            'label'     => __( 'Date Range', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_ticker_news_section',
            'choices'   => pubnews_get_date_filter_choices_array()
        ));
    }
    add_action( 'customize_register', 'pubnews_customizer_ticker_news_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_main_banner_panel' ) ) :
    /**
     * Register main banner section settings
     * 
     */
    function pubnews_customizer_main_banner_panel( $wp_customize ) {
        /**
         * Main Banner section
         * 
         */
        $wp_customize->add_section( 'pubnews_main_banner_section', array(
            'title' => esc_html__( 'Main Banner', 'pubnews' ),
            'priority'  => 70
        ));

        // main banner section tab
        $wp_customize->add_setting( 'main_banner_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'main_banner_section_tab', array(
                'section'     => 'pubnews_main_banner_section',
                'priority'  => 1,
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // main banner option
        $wp_customize->add_setting( 'main_banner_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'main_banner_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Toggle_Control( $wp_customize, 'main_banner_option', array(
                'label'	      => esc_html__( 'Show main banner', 'pubnews' ),
                'section'     => 'pubnews_main_banner_section',
                'settings'    => 'main_banner_option'
            ))
        );

        // main banner slider setting heading
        $wp_customize->add_setting( 'main_banner_slider_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'main_banner_slider_settings_header', array(
                'label'	      => esc_html__( 'Slider Setting', 'pubnews' ),
                'section'     => 'pubnews_main_banner_section',
                'settings'    => 'main_banner_slider_settings_header',
                'type'        => 'section-heading',
            ))
        );

        // Main Banner slider orderby
        $wp_customize->add_setting( 'main_banner_slider_order_by', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_slider_order_by' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'main_banner_slider_order_by', array(
            'type'      => 'select',
            'section'   => 'pubnews_main_banner_section',
            'label'     => esc_html__( 'Orderby', 'pubnews' ),
            'choices'   => pubnews_customizer_orderby_options_array(),
        ));
        
        // Main Banner slider categories
        $wp_customize->add_setting( 'main_banner_slider_categories', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_slider_categories' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'main_banner_slider_categories', array(
                'label'     => esc_html__( 'Posts Categories', 'pubnews' ),
                'section'   => 'pubnews_main_banner_section',
                'settings'  => 'main_banner_slider_categories',
                'endpoint' =>  'extend/get_taxonomy',
                'purpose'   =>  'category'
            ))
        );

        // main banner posts
        $wp_customize->add_setting( 'main_banner_posts', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_posts' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'main_banner_posts', array(
                'label'     => esc_html__( 'Posts To Include', 'pubnews' ),
                'section'   => 'pubnews_main_banner_section',
                'settings'  => 'main_banner_posts'
            ))
        );

        // main banner post query date range
        $wp_customize->add_setting( 'main_banner_date_filter', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_date_filter' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'main_banner_date_filter', array(
            'label'     => __( 'Date Range', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_main_banner_section',
            'choices'   => pubnews_get_date_filter_choices_array()
        ));

        // Main banner six trailing posts setting heading
        $wp_customize->add_setting( 'main_banner_six_trailing_posts_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'main_banner_six_trailing_posts_settings_header', array(
                'label'	      => esc_html__( 'Trailing Posts Setting', 'pubnews' ),
                'section'     => 'pubnews_main_banner_section',
                'settings'    => 'main_banner_six_trailing_posts_settings_header'
            ))
        );

        // Main banner trailing posts layouts
        $wp_customize->add_setting( 'main_banner_six_trailing_posts_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'main_banner_six_trailing_posts_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( new Pubnews_WP_Radio_Image_Control(
            $wp_customize,
            'main_banner_six_trailing_posts_layout',
            array(
                'section'  => 'pubnews_main_banner_section',
                'priority' => 10,
                'choices'  => array(
                    'row' => array(
                        'label' => esc_html__( 'Row Layout', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/main_banner_six_trailing_posts_layout_row.jpg'
                    ),
                    'column' => array(
                        'label' => esc_html__( 'Column Layout', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/main_banner_six_trailing_posts_layout_column.jpg'
                    )
                )
            )
        ));
        
        // Main banner six trailing posts slider orderby
        $wp_customize->add_setting( 'main_banner_six_trailing_posts_order_by', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_six_trailing_posts_order_by' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'main_banner_six_trailing_posts_order_by', array(
            'type'      => 'select',
            'section'   => 'pubnews_main_banner_section',
            'label'     => esc_html__( 'Orderby', 'pubnews' ),
            'choices'   => pubnews_customizer_orderby_options_array()
        ));

        // Main banner six trailing posts categories
        $wp_customize->add_setting( 'main_banner_six_trailing_posts_categories', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_six_trailing_posts_categories' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'main_banner_six_trailing_posts_categories', array(
                'label'     => esc_html__( 'Posts categories', 'pubnews' ),
                'section'   => 'pubnews_main_banner_section',
                'settings'  => 'main_banner_six_trailing_posts_categories',
                'endpoint' =>  'extend/get_taxonomy',
                'purpose'   =>  'category'
            ))
        );

        // main banner posts
        $wp_customize->add_setting( 'main_banner_six_trailing_posts', array(
            'default' => PND\pubnews_get_customizer_default( 'main_banner_six_trailing_posts' ),
            'sanitize_callback' => 'pubnews_sanitize_array'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Async_Multiselect_Control( $wp_customize, 'main_banner_six_trailing_posts', array(
                'label'     => esc_html__( 'Posts to Include', 'pubnews' ),
                'section'   => 'pubnews_main_banner_section',
                'settings'  => 'main_banner_six_trailing_posts'
            ))
        );

        // banner layout
        $wp_customize->add_setting( 'main_banner_width_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'main_banner_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'main_banner_width_layout',
            array(
                'section'  => 'pubnews_main_banner_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));
        
        // banner section order
        $wp_customize->add_setting( 'banner_section_order', array(
            'default'   => PND\pubnews_get_customizer_default( 'banner_section_order' ),
            'sanitize_callback' => 'pubnews_sanitize_sortable_control'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Item_Sortable_Control( $wp_customize, 'banner_section_order', array(
                'label'         => esc_html__( 'Column Re-order', 'pubnews' ),
                'section'       => 'pubnews_main_banner_section',
                'settings'      => 'banner_section_order',
                'tab'   => 'design',
                'fields'    => array(
                    'banner_slider'  => array(
                        'label' => esc_html__( 'Banner Slider Column', 'pubnews' )
                    ),
                    'tab_slider'  => array(
                        'label' => esc_html__( 'Tabbed / Slider Column / Posts', 'pubnews' )
                    )
                )
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_main_banner_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_footer_panel' ) ) :
    /**
     * Register footer options settings
     * 
     */
    function pubnews_customizer_footer_panel( $wp_customize ) {
        /**
         * Theme Footer Section
         * 
         * panel - pubnews_footer_panel
         */
        $wp_customize->add_section( 'pubnews_footer_section', array(
            'title' => esc_html__( 'Theme Footer', 'pubnews' ),
            'priority'  => 74
        ));
        
        // section tab
        $wp_customize->add_setting( 'footer_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'footer_section_tab', array(
                'section'     => 'pubnews_footer_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // Footer Option
        $wp_customize->add_setting( 'footer_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'footer_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport'   => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Toggle_Control( $wp_customize, 'footer_option', array(
                'label'	      => esc_html__( 'Enable footer section', 'pubnews' ),
                'section'     => 'pubnews_footer_section',
                'settings'    => 'footer_option',
                'tab'   => 'general'
            ))
        );

        /// Add the footer layout control.
        $wp_customize->add_setting( 'footer_widget_column', array(
            'default'           => PND\pubnews_get_customizer_default( 'footer_widget_column' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'transport'   => 'postMessage'
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'footer_widget_column', array(
                'section'  => 'pubnews_footer_section',
                'tab'   => 'general',
                'choices'  => array(
                    'column-one' => array(
                        'label' => esc_html__( 'Column One', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/footer_column_one.jpg'
                    ),
                    'column-two' => array(
                        'label' => esc_html__( 'Column Two', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/footer_column_two.jpg'
                    ),
                    'column-three' => array(
                        'label' => esc_html__( 'Column Three', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/footer_column_three.jpg'
                    ),
                    'column-four' => array(
                        'label' => esc_html__( 'Column Four', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/footer_column_four.jpg'
                    )
                )
            )
        ));

        // archive pagination type
        $wp_customize->add_setting( 'footer_section_width', array(
            'default' => PND\pubnews_get_customizer_default( 'footer_section_width' ),
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Tab_Control( $wp_customize, 'footer_section_width', array(
                'label'	      => esc_html__( 'Section Width', 'pubnews' ),
                'section'     => 'pubnews_footer_section',
                'settings'    => 'footer_section_width',
                'choices' => array(
                    array(
                        'value' => 'full-width',
                        'label' => esc_html__('Full Width', 'pubnews' )
                    ),
                    array(
                        'value' => 'boxed-width',
                        'label' => esc_html__('Boxed Width', 'pubnews' )
                    )
                )
            ))
        );
        
        // Redirect widgets link
        $wp_customize->add_setting( 'footer_widgets_redirects', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'footer_widgets_redirects', array(
                'label'	      => esc_html__( 'Widgets', 'pubnews' ),
                'section'     => 'pubnews_footer_section',
                'settings'    => 'footer_widgets_redirects',
                'tab'   => 'general',
                'choices'     => array(
                    'footer-column-one' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-footer-sidebar--column-1',
                        'label' => esc_html__( 'Manage footer widget one', 'pubnews' )
                    ),
                    'footer-column-two' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-footer-sidebar--column-2',
                        'label' => esc_html__( 'Manage footer widget two', 'pubnews' )
                    ),
                    'footer-column-three' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-footer-sidebar--column-3',
                        'label' => esc_html__( 'Manage footer widget three', 'pubnews' )
                    ),
                    'footer-column-four' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-footer-sidebar--column-4',
                        'label' => esc_html__( 'Manage footer widget four', 'pubnews' )
                    )
                )
            ))
        );

        // footer background property
        $wp_customize->add_setting( 'footer_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'footer_background_color_group' ),
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control',
            'transport' => 'postMessage'
        ));
        
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'footer_background_color_group', array(
                'label'	      => esc_html__( 'Background', 'pubnews' ),
                'section'     => 'pubnews_footer_section',
                'settings'    => 'footer_background_color_group',
                'tab'   => 'design'
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_footer_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_bottom_footer_panel' ) ) :
    /**
     * Register bottom footer options settings
     * 
     */
    function pubnews_customizer_bottom_footer_panel( $wp_customize ) {
        /**
         * Bottom Footer Section
         * 
         * panel - pubnews_footer_panel
         */
        $wp_customize->add_section( 'pubnews_bottom_footer_section', array(
            'title' => esc_html__( 'Bottom Footer', 'pubnews' ),
            'priority'  => 75
        ));

        // section tab
        $wp_customize->add_setting( 'bottom_footer_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'bottom_footer_section_tab', array(
                'section'     => 'pubnews_bottom_footer_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // Bottom Footer Option
        $wp_customize->add_setting( 'bottom_footer_option', array(
            'default'         => PND\pubnews_get_customizer_default( 'bottom_footer_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Toggle_Control( $wp_customize, 'bottom_footer_option', array(
                'label'	      => esc_html__( 'Enable bottom footer', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'settings'    => 'bottom_footer_option'
            ))
        );

        // Main Banner slider categories option
        $wp_customize->add_setting( 'bottom_footer_social_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'bottom_footer_social_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'bottom_footer_social_option', array(
                'label'	      => esc_html__( 'Show bottom social icons', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'settings'    => 'bottom_footer_social_option'
            ))
        );

        // Main Banner slider categories option
        $wp_customize->add_setting( 'bottom_footer_menu_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'bottom_footer_menu_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'bottom_footer_menu_option', array(
                'label'	      => esc_html__( 'Show bottom footer menu', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'settings'    => 'bottom_footer_menu_option'
            ))
        );

        // Bottom footer site info
        $wp_customize->add_setting( 'bottom_footer_site_info', array(
            'default'    => PND\pubnews_get_customizer_default( 'bottom_footer_site_info' ),
            'sanitize_callback' => 'wp_kses_post'
        ));
        $wp_customize->add_control( 'bottom_footer_site_info', array(
            'label'	      => esc_html__( 'Copyright Text', 'pubnews' ),
            'description' => esc_html__( 'Add %year% to retrieve current year', 'pubnews' ),
            'section'     => 'pubnews_bottom_footer_section',
            'type'  =>  'textarea'
        ));

        // bottom footer width layout heading
        $wp_customize->add_setting( 'bottom_footer_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'bottom_footer_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'settings'    => 'bottom_footer_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // bottom footer width layout
        $wp_customize->add_setting( 'bottom_footer_width_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'bottom_footer_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'bottom_footer_width_layout',
            array(
                'section'  => 'pubnews_bottom_footer_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // bottom footer background
        $wp_customize->add_setting( 'bottom_footer_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'bottom_footer_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Group_Control( $wp_customize, 'bottom_footer_background_color_group', array(
                'label'	      => esc_html__( 'Background', 'pubnews' ),
                'section'     => 'pubnews_bottom_footer_section',
                'settings'    => 'bottom_footer_background_color_group',
                'tab'   => 'design'
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_bottom_footer_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_typography_panel' ) ) :
    /**
     * Register typography options settings
     * 
     */
    function pubnews_customizer_typography_panel( $wp_customize ) {
        // typography options panel settings
        $wp_customize->add_section( 'pubnews_typography_section', array(
            'title' => esc_html__( 'Typography', 'pubnews' ),
            'priority'  => 55
        ));

        // block title typo
        $wp_customize->add_setting( 'site_section_block_title_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_section_block_title_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_section_block_title_typo', array(
                'label'	      => esc_html__( 'Block Title', 'pubnews' ),
                'section'     => 'pubnews_typography_section',
                'settings'    => 'site_section_block_title_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        // post title typo
        $wp_customize->add_setting( 'site_archive_post_title_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_archive_post_title_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_archive_post_title_typo', array(
                'label'	      => esc_html__( 'Post Title', 'pubnews' ),
                'section'     => 'pubnews_typography_section',
                'settings'    => 'site_archive_post_title_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );
        
        // post meta typo
        $wp_customize->add_setting( 'site_archive_post_meta_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_archive_post_meta_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_archive_post_meta_typo', array(
                'label'	      => esc_html__( 'Post Meta', 'pubnews' ),
                'section'     => 'pubnews_typography_section',
                'settings'    => 'site_archive_post_meta_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );

        // post content typo
        $wp_customize->add_setting( 'site_archive_post_content_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'site_archive_post_content_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'site_archive_post_content_typo', array(
                'label'	      => esc_html__( 'Post Content', 'pubnews' ),
                'section'     => 'pubnews_typography_section',
                'settings'    => 'site_archive_post_content_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration')
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_typography_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_front_sections_panel' ) ) :
    /**
     * Register front sections settings
     * 
     */
    function pubnews_customizer_front_sections_panel( $wp_customize ) {
        // Front sections panel
        $wp_customize->add_panel( 'pubnews_front_sections_panel', array(
            'title' => esc_html__( 'Front sections', 'pubnews' ),
            'priority'  => 71
        ));

        // full width content section
        $wp_customize->add_section( 'pubnews_full_width_section', array(
            'title' => esc_html__( 'Full Width', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 10
        ));

        // section tab
        $wp_customize->add_setting( 'full_width_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'full_width_section_tab', array(
                'section'     => 'pubnews_full_width_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // full width repeater control
        $wp_customize->add_setting( 'full_width_blocks', array(
            'default'   => PND\pubnews_get_customizer_default( 'full_width_blocks' ),
            'sanitize_callback' => 'pubnews_sanitize_repeater_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'full_width_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this section', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_full_width_section',
                'settings'    => 'full_width_blocks'
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'full_width_blocks_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'full_width_blocks_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_full_width_section',
                'settings'    => 'full_width_blocks_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'full_width_blocks_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'full_width_blocks_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'full_width_blocks_width_layout',
            array(
                'section'  => 'pubnews_full_width_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));
        // full width background setting
        $wp_customize->add_setting( 'full_width_blocks_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'full_width_blocks_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'full_width_blocks_background_color_group', array(
                'label'	      => esc_html__( 'Section background', 'pubnews' ),
                'section'     => 'pubnews_full_width_section',
                'settings'    => 'full_width_blocks_background_color_group',
                'tab'   => 'design'
            ))
        );

        // Left content -right sidebar section
        $wp_customize->add_section( 'pubnews_leftc_rights_section', array(
            'title' => esc_html__( 'Left Content  - Right Sidebar', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 10
        ));

        // section tab
        $wp_customize->add_setting( 'leftc_rights_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'leftc_rights_section_tab', array(
                'section'     => 'pubnews_leftc_rights_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // redirect to manage sidebar
        $wp_customize->add_setting( 'leftc_rights_section_sidebar_redirect', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'leftc_rights_section_sidebar_redirect', array(
                'label'	      => esc_html__( 'Widgets', 'pubnews' ),
                'section'     => 'pubnews_leftc_rights_section',
                'settings'    => 'leftc_rights_section_sidebar_redirect',
                'tab'   => 'general',
                'choices'     => array(
                    'footer-column-one' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-front-right-sidebar',
                        'label' => esc_html__( 'Manage right sidebar', 'pubnews' )
                    )
                )
            ))
        );

        // Block Repeater control
        $wp_customize->add_setting( 'leftc_rights_blocks', array(
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'default'   => PND\pubnews_get_customizer_default( 'leftc_rights_blocks' )
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'leftc_rights_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this section', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_leftc_rights_section',
                'settings'    => 'leftc_rights_blocks'
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'leftc_rights_blocks_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'leftc_rights_blocks_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_leftc_rights_section',
                'settings'    => 'leftc_rights_blocks_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'leftc_rights_blocks_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'leftc_rights_blocks_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'leftc_rights_blocks_width_layout',
            array(
                'section'  => 'pubnews_leftc_rights_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));
        
        // section background settings
        $wp_customize->add_setting( 'leftc_rights_blocks_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'leftc_rights_blocks_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'leftc_rights_blocks_background_color_group', array(
                'label'	      => esc_html__( 'Section background', 'pubnews' ),
                'section'     => 'pubnews_leftc_rights_section',
                'settings'    => 'leftc_rights_blocks_background_color_group',
                'tab'   => 'design'
            ))
        );

        // Left sidebar - Right content section
        $wp_customize->add_section( 'pubnews_lefts_rightc_section', array(
            'title' => esc_html__( 'Left Sidebar - Right Content', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 10
        ));

        // section tab
        $wp_customize->add_setting( 'lefts_rightc_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'lefts_rightc_section_tab', array(
                'section'     => 'pubnews_lefts_rightc_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // redirect to manage sidebar
        $wp_customize->add_setting( 'lefts_rightc_section_sidebar_redirect', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'lefts_rightc_section_sidebar_redirect', array(
                'label'	      => esc_html__( 'Widgets', 'pubnews' ),
                'section'     => 'pubnews_lefts_rightc_section',
                'settings'    => 'lefts_rightc_section_sidebar_redirect',
                'tab'   => 'general',
                'choices'     => array(
                    'footer-column-one' => array(
                        'type'  => 'section',
                        'id'    => 'sidebar-widgets-front-left-sidebar',
                        'label' => esc_html__( 'Manage left sidebar', 'pubnews' )
                    )
                )
            ))
        );

        // Block Repeater control
        $wp_customize->add_setting( 'lefts_rightc_blocks', array(
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'default'   => PND\pubnews_get_customizer_default( 'lefts_rightc_blocks' )
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'lefts_rightc_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this section', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_lefts_rightc_section',
                'settings'    => 'lefts_rightc_blocks'
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'lefts_rightc_blocks_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'lefts_rightc_blocks_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_lefts_rightc_section',
                'settings'    => 'lefts_rightc_blocks_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'lefts_rightc_blocks_width_layout',
            array(
            'default'           => PND\pubnews_get_customizer_default( 'lefts_rightc_blocks_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            )
        );
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'lefts_rightc_blocks_width_layout',
            array(
                'section'  => 'pubnews_lefts_rightc_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // Left Sidebar Right background setting
        $wp_customize->add_setting( 'lefts_rightc_blocks_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'lefts_rightc_blocks_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'lefts_rightc_blocks_background_color_group', array(
                'label'	      => esc_html__( 'Section Background', 'pubnews' ),
                'section'     => 'pubnews_lefts_rightc_section',
                'settings'    => 'lefts_rightc_blocks_background_color_group',
                'tab'   => 'design'
            ))
        );

        // bottom full width content section
        $wp_customize->add_section( 'pubnews_bottom_full_width_section', array(
            'title' => esc_html__( 'Bottom Full Width', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 50
        ));

        // section tab
        $wp_customize->add_setting( 'bottom_full_width_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'bottom_full_width_section_tab', array(
                'section'     => 'pubnews_bottom_full_width_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // bottom full width blocks control
        $wp_customize->add_setting( 'bottom_full_width_blocks', array(
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'default'   => PND\pubnews_get_customizer_default( 'bottom_full_width_blocks' )
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'bottom_full_width_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this section', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_bottom_full_width_section',
                'settings'    => 'bottom_full_width_blocks'
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'bottom_full_width_blocks_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'bottom_full_width_blocks_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_bottom_full_width_section',
                'settings'    => 'bottom_full_width_blocks_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'bottom_full_width_blocks_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'bottom_full_width_blocks_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'bottom_full_width_blocks_width_layout',
            array(
                'section'  => 'pubnews_bottom_full_width_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // Bottom Full Width background setting
        $wp_customize->add_setting( 'bottom_full_width_blocks_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'bottom_full_width_blocks_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'bottom_full_width_blocks_background_color_group', array(
                'label'	      => esc_html__( 'Section Background', 'pubnews' ),
                'section'     => 'pubnews_bottom_full_width_section',
                'settings'    => 'bottom_full_width_blocks_background_color_group',
                'tab'   => 'design'
            ))
        );

        // two column section
        $wp_customize->add_section( 'pubnews_two_column_section', array(
            'title' => esc_html__( 'Two Column Section', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 60
        ));

        // section tab
        $wp_customize->add_setting( 'two_column_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'two_column_section_tab', array(
                'section'     => 'pubnews_two_column_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // two column first column blocks heading
        $wp_customize->add_setting( 'two_column_first_column_blocks_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'two_column_first_column_blocks_header', array(
                'label'	      => esc_html__( 'First Column Blocks', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'settings'    => 'two_column_first_column_blocks_header'
            ))
        );

        // two column first column blocks control
        $wp_customize->add_setting( 'two_column_first_column_blocks', array(
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'default'   => PND\pubnews_get_customizer_default( 'two_column_first_column_blocks' )
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'two_column_first_column_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this column', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'fields_to_exclude'     => ['column']
            ))
        );

        // two column second column blocks heading
        $wp_customize->add_setting( 'two_column_second_column_blocks_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'two_column_second_column_blocks_header', array(
                'label'	      => esc_html__( 'Second Column Blocks', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'settings'    => 'two_column_second_column_blocks_header'
            ))
        );

        // two column second column blocks control
        $wp_customize->add_setting( 'two_column_second_column_blocks', array(
            'sanitize_callback' => 'pubnews_sanitize_repeater_control',
            'default'   => PND\pubnews_get_customizer_default( 'two_column_second_column_blocks' )
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Block_Repeater_Control( $wp_customize, 'two_column_second_column_blocks', array(
                'label'	      => esc_html__( 'Blocks to show in this column', 'pubnews' ),
                'description' => esc_html__( 'Hold bar icon at right of block item and drag vertically to re-order blocks', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'fields_to_exclude'     => ['column']
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'two_column_section_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'two_column_section_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'settings'    => 'two_column_section_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'two_column_section_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'two_column_section_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'two_column_section_layout',
            array(
                'section'  => 'pubnews_two_column_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // two column background setting
        $wp_customize->add_setting( 'two_column_background_color_group', array(
            'default'   => PND\pubnews_get_customizer_default( 'two_column_background_color_group' ),
            'transport' => 'postMessage',
            'sanitize_callback' => 'pubnews_sanitize_color_image_group_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Color_Image_Group_Control( $wp_customize, 'two_column_background_color_group', array(
                'label'	      => esc_html__( 'Section background', 'pubnews' ),
                'section'     => 'pubnews_two_column_section',
                'settings'    => 'two_column_background_color_group',
                'tab'   => 'design'
            ))
        );

        // front sections reorder section
        $wp_customize->add_section( 'pubnews_front_sections_reorder_section', array(
            'title' => esc_html__( 'Reorder sections', 'pubnews' ),
            'panel' => 'pubnews_front_sections_panel',
            'priority'  => 70
        ));

        /**
         * Frontpage sections options
         * 
         * @package Pubnews
         * @since 1.0.0
         */
        $wp_customize->add_setting( 'homepage_content_order', array(
            'default'   => PND\pubnews_get_customizer_default( 'homepage_content_order' ),
            'sanitize_callback' => 'pubnews_sanitize_sortable_control'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Item_Sortable_Control( $wp_customize, 'homepage_content_order', array(
                'label'         => esc_html__( 'Section Re-order', 'pubnews' ),
                'description'   => esc_html__( 'Hold item and drag vertically to re-order the items', 'pubnews' ),
                'section'       => 'pubnews_front_sections_reorder_section',
                'settings'      => 'homepage_content_order',
                'fields'    => array(
                    'full_width_section'  => array(
                        'label' => esc_html__( 'Full width Section', 'pubnews' )
                    ),
                    'leftc_rights_section'  => array(
                        'label' => esc_html__( 'Left Content - Right Sidebar', 'pubnews' )
                    ),
                    'lefts_rightc_section'  => array(
                        'label' => esc_html__( 'Left Sidebar - Right Content', 'pubnews' )
                    ),
                    'bottom_full_width_section'  => array(
                        'label' => esc_html__( 'Bottom Full width Section', 'pubnews' )
                    ),
                    'two_column_section'  => array(
                        'label' => esc_html__( 'Two Column Section', 'pubnews' )
                    ),
                    'three_column_section'  => array(
                        'label' => esc_html__( 'Three Column Section', 'pubnews' )
                    ),
                    'video_playlist'  => array(
                        'label' => esc_html__( 'Video Playlist Section', 'pubnews' )
                    ),
                    'latest_posts'  => array(
                        'label' => esc_html__( 'Latest Posts / Page Content', 'pubnews' )
                    )
                )
            ))
        );
    }
    add_action( 'customize_register', 'pubnews_customizer_front_sections_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_blog_post_archive_panel' ) ) :
    /**
     * Register global options settings
     * 
     */
    function pubnews_customizer_blog_post_archive_panel( $wp_customize ) {
        // Blog/Archives panel
        $wp_customize->add_panel( 'pubnews_blog_post_archive_panel', array(
            'title' => esc_html__( 'Blog / Archives', 'pubnews' ),
            'priority'  => 72
        ));
        
        // blog / archive section
        $wp_customize->add_section( 'pubnews_blog_archive_general_setting_section', array(
            'title' => esc_html__( 'General Settings', 'pubnews' ),
            'panel' => 'pubnews_blog_post_archive_panel',
            'priority'  => 10
        ));

        // archive tab section tab
        $wp_customize->add_setting( 'archive_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'archive_section_tab', array(
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // archive layout settings heading
        $wp_customize->add_setting( 'archive_page_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Toggle_Control( $wp_customize, 'archive_page_layout_header', array(
                'label'	      => esc_html__( 'Layout Settings', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section'
            ))
        );

        // archive title prefix option
        $wp_customize->add_setting( 'archive_page_title_prefix', array(
            'default' => PND\pubnews_get_customizer_default( 'archive_page_title_prefix' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'archive_page_title_prefix', array(
                'label'	      => esc_html__( 'Show archive title prefix', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_page_title_prefix'
            ))
        );

        // archive post layouts
        $wp_customize->add_setting( 'archive_page_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'archive_page_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
            'transport' =>  'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'archive_page_layout',
            array(
                'section'  => 'pubnews_blog_archive_general_setting_section',
                'priority' => 10,
                'choices'  => array(
                    'one' => array(
                        'label' => esc_html__( 'Layout One', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/archive_one.jpg'
                    ),
                    'two' => array(
                        'label' => esc_html__( 'Layout Two', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/archive_two.jpg'
                    )
                )
            )
        ));

        // live search heading
        $wp_customize->add_setting( 'archive_page_elements_setting_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Toggle_Control( $wp_customize, 'archive_page_elements_setting_header', array(
                'label'	      => esc_html__( 'Elements Settings', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section'
            ))
        );

        // archive categories option
        $wp_customize->add_setting( 'archive_page_category_option', array(
            'default' => PND\pubnews_get_customizer_default( 'archive_page_category_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'archive_page_category_option', array(
                'label'	      => esc_html__( 'Show archive categories', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_page_category_option'
            ))
        );
        
        // archive elements sort
        $wp_customize->add_setting( 'archive_post_element_order', array(
            'default'   => PND\pubnews_get_customizer_default( 'archive_post_element_order' ),
            'sanitize_callback' => 'pubnews_sanitize_sortable_control'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Item_Sortable_Control( $wp_customize, 'archive_post_element_order', array(
                'label'         => esc_html__( 'Elements Re-order', 'pubnews' ),
                'section'       => 'pubnews_blog_archive_general_setting_section',
                'settings'      => 'archive_post_element_order',
                'tab'   => 'general',
                'fields'    => array(
                    'title'  => array(
                        'label' => esc_html__( 'Title', 'pubnews' )
                    ),
                    'meta'  => array(
                        'label' => esc_html__( 'Meta', 'pubnews' )
                    ),
                    'excerpt'  => array(
                        'label' => esc_html__( 'Excerpt', 'pubnews' )
                    ),
                    'button'  => array(
                        'label' => esc_html__( 'Button', 'pubnews' )
                    ),
                )
            ))
        );

        // Redirect continue reading button
        $wp_customize->add_setting( 'archive_button_redirect', array(
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Redirect_Control( $wp_customize, 'archive_button_redirect', array(
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_button_redirect',
                'choices'     => array(
                    'header-social-icons' => array(
                        'type'  => 'section',
                        'id'    => 'pubnews_buttons_section',
                        'label' => esc_html__( 'Edit button styles', 'pubnews' )
                    )
                )
            ))
        );

        // archive image settings
        $wp_customize->add_setting( 'archive_image_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Toggle_Control( $wp_customize, 'archive_image_settings_header', array(
                'label'	      => esc_html__( 'Image Settings', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section'
            ))
        );

        // ticker news image ratio
        $wp_customize->add_setting( 'archive_image_ratio', array(
            'default'   => PND\pubnews_get_customizer_default( 'archive_image_ratio' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'archive_image_ratio', array(
                    'label'	      => esc_html__( 'Image Ratio', 'pubnews' ),
                    'section'     => 'pubnews_blog_archive_general_setting_section',
                    'settings'    => 'archive_image_ratio',
                    'unit'        => 'px',
                    'input_attrs' => array(
                    'max'         => 2,
                    'min'         => 0,
                    'step'        => 0.1,
                    'reset' => true
                )
            ))
        );
        
        // archive iamge size
        $wp_customize->add_setting( 'archive_image_size', array(
            'default' => PND\pubnews_get_customizer_default( 'archive_image_size' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'archive_image_size', array(
            'label'     => esc_html__( 'Image Size', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_blog_archive_general_setting_section',
            'choices'   => pubnews_get_image_sizes()
        ));

        // Width Layouts setting heading
        $wp_customize->add_setting( 'archive_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'archive_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'archive_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'archive_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'archive_width_layout',
            array(
                'section'  => 'pubnews_blog_archive_general_setting_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // archive vertical spacing top
        $wp_customize->add_setting( 'archive_vertical_spacing_top', array(
            'default'   => PND\pubnews_get_customizer_default( 'archive_vertical_spacing_top' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'archive_vertical_spacing_top', array(
                'label'	      => esc_html__( 'Vertical Spacing Top (px)', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_vertical_spacing_top',
                'unit'        => 'px',
                'input_attrs' => array(
                    'max'         => 200,
                    'min'         => 0,
                    'step'        => 1,
                    'reset' => true
                ),
                'tab'   =>  'design'
            ))
        );

        // archive vertical spacing bottom
        $wp_customize->add_setting( 'archive_vertical_spacing_bottom', array(
            'default'   => PND\pubnews_get_customizer_default( 'archive_vertical_spacing_bottom' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'archive_vertical_spacing_bottom', array(
                'label'	      => esc_html__( 'Vertical Spacing Bottom (px)', 'pubnews' ),
                'section'     => 'pubnews_blog_archive_general_setting_section',
                'settings'    => 'archive_vertical_spacing_bottom',
                'unit'        => 'px',
                'input_attrs' => array(
                    'max'         => 200,
                    'min'         => 0,
                    'step'        => 1,
                    'reset' => true
                ),
                'tab'   =>  'design'
            ))
        );

        //  single post panel
        $wp_customize->add_panel( 'pubnews_single_panel', array(
            'title' => esc_html__( 'Single Post', 'pubnews' ),
            'priority'  => 73
        ));

        //  single general settings section
        $wp_customize->add_section( 'single_general_settings_section', array(
            'title' => esc_html__( 'General Settings', 'pubnews' ),
            'panel' =>  'pubnews_single_panel'
        ));

        // single tab section tab
        $wp_customize->add_setting( 'single_post_section_tab', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'   => 'general'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Tab_Control( $wp_customize, 'single_post_section_tab', array(
                'section'     => 'single_general_settings_section',
                'choices'  => array(
                    array(
                        'name'  => 'general',
                        'title'  => esc_html__( 'General', 'pubnews' )
                    ),
                    array(
                        'name'  => 'design',
                        'title'  => esc_html__( 'Design', 'pubnews' )
                    )
                )
            ))
        );

        // single post show original image
        $wp_customize->add_setting( 'single_post_show_original_image_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_show_original_image_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Checkbox_Control( $wp_customize, 'single_post_show_original_image_option', array(
                'label'	      => esc_html__( 'Show original image size', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_show_original_image_option'
            ))
        );

        // single post related news heading
        $wp_customize->add_setting( 'single_post_image_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'single_post_image_settings_header', array(
                'label'	      => esc_html__( 'Image Ratio', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_image_settings_header'
            ))
        );

        // ticker news image ratio
        $wp_customize->add_setting( 'single_post_image_ratio', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_image_ratio' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'single_post_image_ratio', array(
                    'label'	      => esc_html__( 'Image Ratio', 'pubnews' ),
                    'section'     => 'single_general_settings_section',
                    'settings'    => 'single_post_image_ratio',
                    'unit'        => 'px',
                    'input_attrs' => array(
                    'max'         => 2,
                    'min'         => 0,
                    'step'        => 0.1,
                    'reset' => true
                )
            ))
        );

        // Width Layouts setting heading
        $wp_customize->add_setting( 'single_post_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'single_post_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'single_post_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'single_post_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'single_post_width_layout',
            array(
                'section'  => 'single_general_settings_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // single post typography heading
        $wp_customize->add_setting( 'single_post_typo_heading', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Toggle_Control( $wp_customize, 'single_post_typo_heading', array(
                'label' => esc_html__( 'Typography', 'pubnews' ),
                'section'   => 'single_general_settings_section',
                'tab'   => 'design'
            ))
        );

        // single post title typo
        $wp_customize->add_setting( 'single_post_title_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_title_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_title_typo', array(
                'label'	      => esc_html__( 'Post Title', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_title_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // single post meta typo
        $wp_customize->add_setting( 'single_post_meta_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_meta_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_meta_typo', array(
                'label'	      => esc_html__( 'Post Meta', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_meta_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );
        
        // single post content typo
        $wp_customize->add_setting( 'single_post_content_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_typo', array(
                'label'	      => esc_html__( 'Post Content', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h1 typo
        $wp_customize->add_setting( 'single_post_content_h1_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h1_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h1_typo', array(
                'label'	      => esc_html__( 'H1 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h1_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h2 typo
        $wp_customize->add_setting( 'single_post_content_h2_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h2_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h2_typo', array(
                'label'	      => esc_html__( 'H2 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h2_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h3 typo
        $wp_customize->add_setting( 'single_post_content_h3_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h3_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h3_typo', array(
                'label'	      => esc_html__( 'H3 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h3_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h4 typo
        $wp_customize->add_setting( 'single_post_content_h4_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h4_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h4_typo', array(
                'label'	      => esc_html__( 'H4 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h4_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h5 typo
        $wp_customize->add_setting( 'single_post_content_h5_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h5_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h5_typo', array(
                'label'	      => esc_html__( 'H5 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h5_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // h6 typo
        $wp_customize->add_setting( 'single_post_content_h6_typo', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_content_h6_typo' ),
            'sanitize_callback' => 'pubnews_sanitize_typo_control',
            'transport' => 'postMessage',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Typography_Control( $wp_customize, 'single_post_content_h6_typo', array(
                'label'	      => esc_html__( 'H6 Typography', 'pubnews' ),
                'section'     => 'single_general_settings_section',
                'settings'    => 'single_post_content_h6_typo',
                'fields'    => array( 'font_family', 'font_weight', 'font_size', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration'),
                'tab'   => 'design'
            ))
        );

        // single table of content
        $wp_customize->add_section( 'single_table_of_content_section', array(
            'title' => esc_html__( 'Table of content', 'pubnews' ),
            'panel' =>  'pubnews_single_panel'
        ));

        // single related posts
        $wp_customize->add_section( 'single_related_posts_section', array(
            'title' => esc_html__( 'Related Posts', 'pubnews' ),
            'panel' =>  'pubnews_single_panel'
        ));

        // related news option
        $wp_customize->add_setting( 'single_post_related_posts_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_post_related_posts_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Simple_Toggle_Control( $wp_customize, 'single_post_related_posts_option', array(
                'label'	      => esc_html__( 'Show related news', 'pubnews' ),
                'section'     => 'single_related_posts_section',
                'settings'    => 'single_post_related_posts_option'
            ))
        );

        // related news title
        $wp_customize->add_setting( 'single_post_related_posts_title', array(
            'default' => PND\pubnews_get_customizer_default( 'single_post_related_posts_title' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 'single_post_related_posts_title', array(
            'type'      => 'text',
            'section'   => 'single_related_posts_section',
            'label'     => esc_html__( 'Related news title', 'pubnews' )
        ));

        // blog archive pagination section
        $wp_customize->add_section( 'pubnews_blog_archive_pagination_section', array(
            'title' => esc_html__( 'Pagination', 'pubnews' ),
            'panel' => 'pubnews_blog_post_archive_panel',
            'priority'  => 10
        ));
        
        // archive pagination type
        $wp_customize->add_setting( 'archive_pagination_type', array(
            'default' => PND\pubnews_get_customizer_default( 'archive_pagination_type' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control'
        ));
        $wp_customize->add_control( 'archive_pagination_type', array(
            'label'     => esc_html__( 'Pagination Type', 'pubnews' ),
            'type'      => 'select',
            'section'   => 'pubnews_blog_archive_pagination_section',
            'choices'   => array(
                'default'   => esc_html__( 'Default', 'pubnews' ),
                'number'    => esc_html__( 'Number', 'pubnews' ),
                'ajax'    => esc_html__( 'Ajax Load More', 'pubnews' )
            ),
        ));
    }
    add_action( 'customize_register', 'pubnews_customizer_blog_post_archive_panel', 10 );
endif;

if( !function_exists( 'pubnews_customizer_page_panel' ) ) :
    /**
     * Register global options settings
     * 
     */
    function pubnews_customizer_page_panel( $wp_customize ) {
        // page panel
        $wp_customize->add_panel( 'pubnews_page_panel', array(
            'title' => esc_html__( 'Page Settings', 'pubnews' ),
            'priority'  => 74
        ));

        // 404 section
        $wp_customize->add_section( 'pubnews_page_section', array(
            'title' => esc_html__( 'Page Setting', 'pubnews' ),
            'panel' => 'pubnews_page_panel',
            'priority'  => 10
        ));

        // Width Layouts setting heading
        $wp_customize->add_setting( 'single_page_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'single_page_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_page_section',
                'settings'    => 'single_page_width_layout_header'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'single_page_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'single_page_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'single_page_width_layout',
            array(
                'section'  => 'pubnews_page_section',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // show original image
        $wp_customize->add_setting( 'single_page_show_original_image_option', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_page_show_original_image_option' ),
            'sanitize_callback' => 'pubnews_sanitize_toggle_control',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Checkbox_Control( $wp_customize, 'single_page_show_original_image_option', array(
                'label'	      => esc_html__( 'Show original image size', 'pubnews' ),
                'section'     => 'pubnews_page_section',
                'settings'    => 'single_page_show_original_image_option'
            ))
        );

        // single post related news heading
        $wp_customize->add_setting( 'single_page_image_settings_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'single_page_image_settings_header', array(
                'label'	      => esc_html__( 'Image Ratio', 'pubnews' ),
                'section'     => 'pubnews_page_section',
                'settings'    => 'single_page_image_settings_header'
            ))
        );

        // ticker news image ratio
        $wp_customize->add_setting( 'single_page_image_ratio', array(
            'default'   => PND\pubnews_get_customizer_default( 'single_page_image_ratio' ),
            'sanitize_callback' => 'pubnews_sanitize_responsive_range',
            'transport' => 'postMessage'
        ));
        $wp_customize->add_control(
            new Pubnews_WP_Responsive_Range_Control( $wp_customize, 'single_page_image_ratio', array(
                    'label'	      => esc_html__( 'Image Ratio', 'pubnews' ),
                    'section'     => 'pubnews_page_section',
                    'settings'    => 'single_page_image_ratio',
                    'unit'        => 'px',
                    'input_attrs' => array(
                    'max'         => 2,
                    'min'         => 0,
                    'step'        => 0.1,
                    'reset' => true
                )
            ))
        );

        // 404 section
        $wp_customize->add_section( 'pubnews_404_section', array(
            'title' => esc_html__( '404', 'pubnews' ),
            'panel' => 'pubnews_page_panel',
            'priority'  => 20
        ));
        
        // Width Layouts setting heading
        $wp_customize->add_setting( 'error_page_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'error_page_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_404_section',
                'settings'    => 'error_page_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'error_page_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'error_page_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'error_page_width_layout',
            array(
                'section'  => 'pubnews_404_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));

        // search page section
        $wp_customize->add_section( 'pubnews_search_page_section', array(
            'title' => esc_html__( 'Search Page', 'pubnews' ),
            'panel' => 'pubnews_page_panel',
            'priority'  => 30
        ));

        // Width Layouts setting heading
        $wp_customize->add_setting( 'search_page_width_layout_header', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Section_Heading_Control( $wp_customize, 'search_page_width_layout_header', array(
                'label'	      => esc_html__( 'Width Layouts', 'pubnews' ),
                'section'     => 'pubnews_search_page_section',
                'settings'    => 'search_page_width_layout_header',
                'tab'   => 'design'
            ))
        );

        // width layout
        $wp_customize->add_setting( 'search_page_width_layout', array(
            'default'           => PND\pubnews_get_customizer_default( 'search_page_width_layout' ),
            'sanitize_callback' => 'pubnews_sanitize_select_control',
        ));
        $wp_customize->add_control( 
            new Pubnews_WP_Radio_Image_Control( $wp_customize, 'search_page_width_layout',
            array(
                'section'  => 'pubnews_search_page_section',
                'tab'   => 'design',
                'choices'  => array(
                    'global' => array(
                        'label' => esc_html__( 'Global', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/global.jpg'
                    ),
                    'boxed--layout' => array(
                        'label' => esc_html__( 'Boxed', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/boxed_content.jpg'
                    ),
                    'full-width--layout' => array(
                        'label' => esc_html__( 'Full Width', 'pubnews' ),
                        'url'   => '%s/assets/images/customizer/full_content.jpg'
                    )
                )
            )
        ));
    }
    add_action( 'customize_register', 'pubnews_customizer_page_panel', 10 );
endif;

// extract to the customizer js
$pubnewsAddAction = function() {
    $action_prefix = "wp_ajax_" . "pubnews_";
    // retrieve posts with search key
    add_action( $action_prefix . 'get_multicheckbox_posts_simple_array', function() {
        check_ajax_referer( 'pubnews-customizer-controls-live-nonce', 'security' );
        $searchKey = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])): '';
        $posts_list = get_posts( apply_filters( 'pubnews_query_args_filter', [ 'numberposts' => -1, 's' => esc_html( $searchKey ) ] ) );
        $posts_array = [];
        foreach( $posts_list as $postItem ) :
            $posts_array[] = array( 
                'value'	=> esc_html( $postItem->ID ),
                'label'	=> esc_html(str_replace(array('\'', '"'), '', $postItem->post_title))
            );
        endforeach;
        wp_send_json_success($posts_array);
        wp_die();
    });
    // retrieve categories with search key
    add_action( $action_prefix . 'get_multicheckbox_categories_simple_array', function() {
        check_ajax_referer( 'pubnews-customizer-controls-live-nonce', 'security' );
        $searchKey = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])): '';
        $categories_list = get_categories(array('number'=>-1, 'search'=>esc_html($searchKey)));
        $categories_array = [];
        foreach( $categories_list as $categoryItem ) :
            $categories_array[] = array( 
                'value'	=> esc_html( $categoryItem->term_id ),
                'label'	=> esc_html(str_replace(array('\'', '"'), '', $categoryItem->name)) . ' (' . absint( $categoryItem->count ) . ')'
            );
        endforeach;
        wp_send_json_success($categories_array);
        wp_die();
    });

    // typography fonts url
    add_action( $action_prefix . 'typography_fonts_url', function() {
        check_ajax_referer( 'pubnews-customizer-nonce', 'security' );
		// enqueue inline style
		ob_start();
			echo pubnews_typo_fonts_url();
        $pubnews_typography_fonts_url = ob_get_clean();
		echo apply_filters( 'pubnews_typography_fonts_url', esc_url($pubnews_typography_fonts_url) );
		wp_die();
	});
};
$pubnewsAddAction();