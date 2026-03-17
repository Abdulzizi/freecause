<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function pubnews_body_classes( $classes ) {
	global $post;

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	$classes[] = esc_attr( 'pubnews-title-' . PND\pubnews_get_customizer_option( 'post_title_hover_effects'  ) ); // post title hover effects
	$classes[] = esc_attr( 'pubnews-image-hover--effect-' . PND\pubnews_get_customizer_option( 'site_image_hover_effects' ) ); // site image hover effects
	$classes[] = esc_attr( 'site-' . PND\pubnews_get_customizer_option( 'website_layout' ) ); // site layout
	$classes[] = 'pubnews_main_body pubnews_font_typography';
	$header_width_layout = PND\pubnews_get_customizer_option('header_width_layout');
	$classes[] = esc_attr('header-width--' . $header_width_layout);
	$classes[] = esc_attr( 'block-title--layout-seven' );

	if( PND\pubnews_get_customizer_option( 'header_search_option' ) ) :
		$classes[] = esc_attr('search-popup--style-three');
	endif;

	if( PND\pubnews_get_customizer_option( 'header_off_canvas_option' ) ) :
		$off_canvas_position = PND\pubnews_get_customizer_option('off_canvas_position');
		$classes[] = esc_attr('off-canvas-sidebar-appear--' . $off_canvas_position);
	endif;
	
	// page layout
	if( is_page() || is_404() || is_search() ) :
		if( is_front_page() ) {
			$frontpage_sidebar_layout = PND\pubnews_get_customizer_option( 'frontpage_sidebar_layout' );
			$frontpage_sidebar_sticky_option = PND\pubnews_get_customizer_option( 'frontpage_sidebar_sticky_option' );
			if( $frontpage_sidebar_sticky_option ) $classes[] = esc_attr( 'sidebar-sticky' );
			$classes[] = esc_attr( $frontpage_sidebar_layout );
		} else {
			$page_sidebar_layout = PND\pubnews_get_customizer_option( 'page_sidebar_layout' );
			$page_sidebar_sticky_option = PND\pubnews_get_customizer_option( 'page_sidebar_sticky_option' );
			if( $page_sidebar_sticky_option ) $classes[] = esc_attr( 'sidebar-sticky' );
			$classes[] = esc_attr( $page_sidebar_layout );
		}
	endif;

	// single post layout
	if( is_single() ) :
		$single_sidebar_layout = PND\pubnews_get_customizer_option( 'single_sidebar_layout' );
		$single_sidebar_sticky_option = PND\pubnews_get_customizer_option( 'single_sidebar_sticky_option' );
		if( $single_sidebar_sticky_option ) $classes[] = esc_attr( 'sidebar-sticky' );
		$classes[] = esc_attr( 'single-layout--three' );
		$classes[] = esc_attr( $single_sidebar_layout );
	endif;

	// archive layout
	if( is_archive() || is_home() ) :
		$archive_sidebar_layout = PND\pubnews_get_customizer_option( 'archive_sidebar_layout' );
		$archive_page_layout = PND\pubnews_get_customizer_option( 'archive_page_layout' );
		$archive_sidebar_sticky_option = PND\pubnews_get_customizer_option( 'archive_sidebar_sticky_option' );
		if( $archive_sidebar_sticky_option ) $classes[] = esc_attr( 'sidebar-sticky' );
		$classes[] = esc_attr( 'post-layout--'. $archive_page_layout );
		$classes[] = esc_attr( $archive_sidebar_layout );
	endif;

	$classes[] = 'clamp-title--on';
	$site_background_animation = PND\pubnews_get_customizer_option( 'site_background_animation' );
	$classes[] = 'background-animation--' . $site_background_animation;

	return $classes;
}
add_filter( 'body_class', 'pubnews_body_classes' );


/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function pubnews_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'pubnews_pingback_header' );

//define constant
define( 'PUBNEWS_INCLUDES_PATH', get_template_directory() . '/inc/' );

/**
 * Enqueue theme scripts and styles.
 */
function pubnews_scripts() {
	global $wp_query;
	require_once get_theme_file_path( 'inc/wptt-webfont-loader.php' );
	wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/assets/lib/fontawesome/css/all.min.css', array(), '6.5.1', 'all' );
	wp_enqueue_style( 'slick', get_template_directory_uri() . '/assets/lib/slick/slick.css', array(), '1.8.1', 'all' );
	wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/assets/lib/magnific-popup/magnific-popup.css', array(), '1.1.0', 'all' );
	wp_enqueue_style( 'pubnews-typo-fonts', wptt_get_webfont_url( pubnews_typo_fonts_url() ), array(), null );
	// enqueue inline style
	wp_enqueue_style( 'pubnews-style', get_stylesheet_uri(), array(), PUBNEWS_VERSION );
	wp_add_inline_style( 'pubnews-style', pubnews_current_styles() );
	wp_enqueue_style( 'pubnews-main-style', get_template_directory_uri().'/assets/css/main.css', array(), PUBNEWS_VERSION );
	// additional css
	wp_enqueue_style( 'pubnews-main-style-additional', get_template_directory_uri().'/assets/css/add.css', array(), PUBNEWS_VERSION );
	wp_enqueue_style( 'pubnews-loader-style', get_template_directory_uri().'/assets/css/loader.css', array(), PUBNEWS_VERSION );
	wp_enqueue_style( 'pubnews-responsive-style', get_template_directory_uri().'/assets/css/responsive.css', array(), PUBNEWS_VERSION );
	wp_style_add_data( 'pubnews-style', 'rtl', 'replace' );
	wp_enqueue_script( 'slick', get_template_directory_uri() . '/assets/lib/slick/slick.min.js', array( 'jquery' ), '1.8.1', true );
	wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/assets/lib/magnific-popup/magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );
	wp_enqueue_script( 'js-marquee', get_template_directory_uri() . '/assets/lib/js-marquee/jquery.marquee.min.js', array( 'jquery' ), '1.6.0', true );
	wp_enqueue_script( 'pubnews-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), PUBNEWS_VERSION, true );
	wp_enqueue_script( 'pubnews-theme', get_template_directory_uri() . '/assets/js/theme.js', array( 'jquery' ), PUBNEWS_VERSION, true );
	wp_enqueue_script( 'waypoint', get_template_directory_uri() . '/assets/lib/waypoint/jquery.waypoint.min.js', array( 'jquery' ), '4.0.1', true );
	$scriptVars['_wpnonce'] = wp_create_nonce( 'pubnews-nonce' );
	$scriptVars['ajaxUrl'] 	= esc_url(admin_url('admin-ajax.php'));
	$scriptVars['stt']	= PND\pubnews_get_multiselect_tab_option('stt_responsive_option');
	$scriptVars['ajaxPostsLoad']= false;
	$scriptVars['is_customizer']= is_customize_preview();
	if( is_home() || is_archive() || is_search() ) {
		global $wp_query;
		$scriptVars['ajaxPostsLoad']= ( PND\pubnews_get_customizer_option('archive_pagination_type') == 'ajax' );
		$scriptVars['paged']	= get_query_var('paged');
		$scriptVars['query_vars'] = $wp_query->query_vars;
	}

	// localize scripts
	wp_localize_script( 'pubnews-theme', 'pubnewsObject' , $scriptVars);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_localize_script( 'pubnews-navigation', 'pubnewsNavigatioObject', [
		'menuCutoffOption'	=>	true,
		'menuCutoffAfter'	=>	8,
		'menuCutoffText'	=>	esc_html__( 'More', 'pubnews' )
	]);
}
add_action( 'wp_enqueue_scripts', 'pubnews_scripts' );

if( ! function_exists( 'pubnews_current_styles' ) ) :
	/**
	 * Generates the current changes in styling of the theme.
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_current_styles() {
		// enqueue inline style
		ob_start();
			pubnews_preset_color_styles( 'solid_presets', '--pubnews-global-preset-color-' );
			pubnews_preset_color_styles( 'gradient_presets', '--pubnews-global-preset-gradient-color-' );

			pubnews_header_padding('--header-padding', 'header_vertical_padding');
			pubnews_header_padding('--archive-padding-top', 'archive_vertical_spacing_top');

			pubnews_header_padding('--archive-padding-bottom', 'archive_vertical_spacing_bottom');
			$nBackgroundCode = function($identifier,$id) {
				pubnews_get_background_style($identifier,$id);
			};
			$nBackgroundCode('.pubnews_main_body #full-width-section', 'full_width_blocks_background_color_group');
			$nBackgroundCode('.pubnews_main_body #leftc-rights-section', 'leftc_rights_blocks_background_color_group');
			$nBackgroundCode('.pubnews_main_body #lefts-rightc-section', 'lefts_rightc_blocks_background_color_group');
			$nBackgroundCode( ".pubnews_main_body #bottom-full-width-section", "bottom_full_width_blocks_background_color_group" );
			$nBackgroundCode( ".pubnews_main_body .two-column-section", "two_column_background_color_group" );
			$nBackgroundCode('.pubnews_main_body .site-header.layout--default .top-header','top_header_background_color_group');
			$nBackgroundCode('.pubnews_main_body .site-header.layout--default .menu-section, .search-popup--style-three .site-header.layout--one .search-form-wrap','header_menu_background_color_group');
			$nBackgroundCode('.pubnews_main_body .main-navigation ul.menu ul, .pubnews_main_body .main-navigation ul.nav-menu ul','header_sub_menu_background_color_group');

			$nTypoCode = function($identifier,$id) {
				pubnews_get_typo_style($identifier,$id);
			};
			$nTypoCode( "--site-title", 'site_title_typo' );
			$nTypoCode( "--site-tagline", 'site_tagline_typo' );
			$nTypoCode( "--block-title", 'site_section_block_title_typo');
			$nTypoCode("--post-title",'site_archive_post_title_typo');
			$nTypoCode("--meta", 'site_archive_post_meta_typo');
			$nTypoCode("--content", 'site_archive_post_content_typo');
			$nTypoCode("--menu", 'header_menu_typo');
			$nTypoCode("--submenu", 'header_sub_menu_typo');
			$nTypoCode("--post-link-btn", 'global_button_typo');
			$nTypoCode("--single-title",'single_post_title_typo');
			$nTypoCode("--single-meta", 'single_post_meta_typo');
			$nTypoCode("--single-content", 'single_post_content_typo');
			$nTypoCode("--single-content-h1", 'single_post_content_h1_typo');
			$nTypoCode("--single-content-h2", 'single_post_content_h2_typo');
			$nTypoCode("--single-content-h3", 'single_post_content_h3_typo');
			$nTypoCode("--single-content-h4", 'single_post_content_h4_typo');
			$nTypoCode("--single-content-h5", 'single_post_content_h5_typo');
			$nTypoCode("--single-content-h6", 'single_post_content_h6_typo');
			pubnews_site_logo_width_fnc("body .site-branding img", 'pubnews_site_logo_width');
			// pubnews_top_border_color('.pubnews_main_body .main-navigation ul.menu ul li, .pubnews_main_body .main-navigation ul.nav-menu ul li a','header_sub_menu_background_color_group');
			$nColorGroupCode = function($identifier,$id,$property='color') {
				pubnews_color_options_one($identifier,$id,$property);
			};
			$nColorCode = function($identifier,$id) {
				pubnews_text_color_var($identifier,$id);
			};
			$nColorGroupCode('body.block-title--layout-seven h2.pubnews-block-title:before, body.block-title--layout-seven h2.widget-title span:before, body.archive.block-title--layout-seven .page-header span:before, body.search.block-title--layout-seven .page-header span:before, body.archive.block-title--layout-seven .page-title:before, body.block-title--layout-seven h2.pubnews-widget-title span:before, body.block-title--layout-seven .pubnews-custom-title:before','block_title_primary_color', 'background-color');
			$nColorGroupCode('body.block-title--layout-seven h2.pubnews-block-title:after, body.block-title--layout-seven h2.widget-title span:after, body.archive.block-title--layout-seven .page-header span:after, body.search.block-title--layout-seven .page-header span:after, body.archive.block-title--layout-seven .page-title:after, body.block-title--layout-seven h2.pubnews-widget-title span:after, body.block-title--layout-seven .pubnews-custom-title:after','site_block_title_secondary_color', 'background-color');
			pubnews_get_background_style_var('--site-bk-color', 'site_background_color');
			pubnews_variable_bk_color('--newsletter-bk-color', 'header_newsletter_background_color');
			pubnews_visibility_options('.ads-banner','header_ads_banner_responsive_option');
			pubnews_visibility_options('body #pubnews-scroll-to-top.show','stt_responsive_option');
			pubnews_border_option('.widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts .post-thumb-wrap, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item','widgets_styles_image_border', 'border');
			$nBackgroundCode('body.pubnews_main_body .site-header.layout--default .site-branding-section', 'header_background_color_group');
			$nBackgroundCode('body.pubnews_main_body .site-footer .main-footer, body .dark_bk .posts-grid-wrap.layout-two .post-content-wrap', 'footer_background_color_group');
			pubnews_theme_color('--pubnews-global-preset-theme-color','theme_color');
			pubnews_theme_color('--pubnews-animation-object-color', 'animation_object_color');
			$nBackgroundCode('body.pubnews_main_body .site-footer .bottom-footer', 'bottom_footer_background_color_group');
			pubnews_image_ratio_variable('--pubnews-archive-image-ratio','archive_image_ratio');
			pubnews_image_ratio_variable('--pubnews-single-image-ratio','single_post_image_ratio');
			pubnews_image_ratio_variable('--pubnews-page-image-ratio','single_page_image_ratio');
			pubnews_box_shadow_styles('.widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item','widgets_styles_image_box_shadow');
			pubnews_value_change_responsive('.widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts .post-thumb-wrap, .author-wrap.layout-two .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .widget_pubnews_category_collection_widget .categories-wrap .category-item','widgets_styles_image_border_radius', 'border-radius');
			pubnews_category_colors_styles();
			// front sections image settings styles
		$current_styles = ob_get_clean();
		return apply_filters( 'pubnews_current_styles', wp_strip_all_tags($current_styles) );
	}
endif;

if( ! function_exists( 'pubnews_customizer_social_icons' ) ) :
	/**
	 * Functions get social icons
	 * 
	 */
	function pubnews_customizer_social_icons() {
		$social_icons = PND\pubnews_get_customizer_option( 'social_icons' );
		$social_icons_target = PND\pubnews_get_customizer_option( 'social_icons_target' );
		$decoded_social_icons = json_decode( $social_icons );
		echo '<div class="social-icons">';
			foreach( $decoded_social_icons as $icon ) :
				if( $icon->item_option === 'show' ) {
		?>
					<a class="social-icon" href="<?php echo esc_url( $icon->icon_url ); ?>" target="<?php echo esc_attr( $social_icons_target ); ?>"><i class="<?php echo esc_attr( $icon->icon_class ); ?>"></i></a>
		<?php
				}
			endforeach;
		echo '</div>';
	}
endif;

if( ! function_exists( 'pubnews_get_multicheckbox_categories_simple_array' ) ) :
	/**
	 * Return array of categories prepended with "*" key.
	 * 
	 */
	function pubnews_get_multicheckbox_categories_simple_array() {
		$categories_list = get_categories(['number'	=> 6]);
		$cats_array = [];
		foreach( $categories_list as $cat ) :
			$cats_array[] = array( 
				'value'	=> esc_html( $cat->term_id ),
				'label'	=> esc_html(str_replace(array('\'', '"'), '', $cat->name)) . ' (' .absint( $cat->count ). ')'
			);
		endforeach;
		return $cats_array;
	}
endif;

if( ! function_exists( 'pubnews_get_multicheckbox_posts_simple_array' ) ) :
	/**
	 * Return array of posts prepended with "*" key.
	 * 
	 */
	function pubnews_get_multicheckbox_posts_simple_array() {
		$posts_list = get_posts( apply_filters( 'pubnews_query_args_filter', [ 'numberposts' => 6 ] ) );
		$posts_array = [];
		foreach( $posts_list as $postItem ) :
			$posts_array[] = array( 
				'value'	=> esc_html( $postItem->ID ),
				'label'	=> esc_html(str_replace(array('\'', '"'), '', $postItem->post_title))
			);
		endforeach;
		return $posts_array;
	}
endif;

if( ! function_exists( 'pubnews_get_date_filter_choices_array' ) ) :
	/**
	 * Return array of date filter choices.
	 * 
	 */
	function pubnews_get_date_filter_choices_array() {
		return apply_filters( 'pubnews_get_date_filter_choices_array_filter', [
			'all'	=> esc_html__('All', 'pubnews' ),
			'last-seven-days'	=> esc_html__('Last 7 days', 'pubnews' ),
			'today'	=> esc_html__('Today', 'pubnews' ),
			'this-week'	=> esc_html__('This Week', 'pubnews' ),
			'last-week'	=> esc_html__('Last Week', 'pubnews' ),
			'this-month'	=> esc_html__('This Month', 'pubnews' ),
			'last-month'	=> esc_html__('Last Month', 'pubnews' ),
			'this-year'	=> esc_html__('This Year', 'pubnews' )
		]);
	}
endif;

if( ! function_exists( 'pubnews_get_array_key_string_to_int' ) ) :
	/**
	 * Return array of int values.
	 * 
	 */
	function pubnews_get_array_key_string_to_int( $array ) {
		if( ! isset( $array ) || empty( $array ) ) return;
		$filtered_array = array_map( function($arr) {
			if( is_numeric( $arr ) ) return (int) $arr;
		}, $array);
		return apply_filters( 'pubnews_array_with_int_values', $filtered_array );
	}
endif;

/**
 * Return string with "implode" execution in param
 * 
 */
 function pubnews_get_categories_for_args($array) {
	if( ! $array ) return apply_filters( 'pubnews_categories_for_argument', '' );
	foreach( $array as $value ) {
		if( is_array( $value ) ) $string_array[] = $value['value'];
		if( is_object( $value ) ) $string_array[] = $value->value;
	}
	return apply_filters( 'pubnews_categories_for_argument', implode( ',', $string_array ) );
}

/**
 * Return array with execution in param
 * 
 */
function pubnews_get_post_id_for_args($array) {
	if( ! $array ) return apply_filters( 'pubnews_posts_slugs_for_argument', '' );
	foreach( $array as $value ) {
		if( is_array( $value ) ) $string_array[] = $value['value'];
		if( is_object( $value ) ) $string_array[] = $value->value;
	}
	return apply_filters( 'pubnews_posts_slugs_for_argument', $string_array );
}

if( !function_exists( 'pubnews_typo_fonts_url' ) ) :
	/**
	 * Filter and Enqueue typography fonts
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_typo_fonts_url() {
		$filter = PUBNEWS_PREFIX . 'typo_combine_filter';
		$action = function($filter,$id) {
			return apply_filters(
				$filter,
				$id
			);
		};
		$site_title_typo_value = $action($filter,'site_title_typo');
		$site_tagline_typo_value = $action($filter,'site_tagline_typo');
		$header_menu_typo_value = $action($filter,'header_menu_typo');
		$header_sub_menu_typo_value = $action($filter,'header_sub_menu_typo');
		$site_section_block_title_typo_value = $action($filter,'site_section_block_title_typo');
		$site_archive_post_title_typo_value = $action($filter,'site_archive_post_title_typo');
		$site_archive_post_meta_typo_value = $action($filter,'site_archive_post_meta_typo');
		$site_archive_post_content_typo_value = $action($filter,'site_archive_post_content_typo');
		$single_post_title_typo_value = $action($filter,'single_post_title_typo');
		$single_post_meta_typo_value = $action($filter,'single_post_meta_typo');
		$single_post_content_typo_value = $action($filter,'single_post_content_typo');
		$single_post_content_h1_typo_value = $action($filter,'single_post_content_h1_typo');
		$single_post_content_h2_typo_value = $action($filter,'single_post_content_h2_typo');
		$single_post_content_h3_typo_value = $action($filter,'single_post_content_h3_typo');
		$single_post_content_h4_typo_value = $action($filter,'single_post_content_h4_typo');
		$single_post_content_h5_typo_value = $action($filter,'single_post_content_h5_typo');
		$single_post_content_h6_typo_value = $action($filter,'single_post_content_h6_typo');
		$global_button_typo = $action($filter,'global_button_typo');
		$typo1 = "Frank Ruhl Libre:100,300,500,600";
		$typo2 = "Noto Sans JP:100,300,500,600,700";

		$get_fonts = apply_filters( 'pubnews_get_fonts_toparse', [$site_title_typo_value, $site_tagline_typo_value, $header_menu_typo_value, $header_sub_menu_typo_value, $site_section_block_title_typo_value, $site_archive_post_title_typo_value, $site_archive_post_meta_typo_value, $site_archive_post_content_typo_value, $single_post_title_typo_value, $single_post_meta_typo_value, $single_post_content_typo_value, $single_post_content_h1_typo_value, $single_post_content_h2_typo_value, $single_post_content_h3_typo_value, $single_post_content_h4_typo_value, $single_post_content_h5_typo_value, $single_post_content_h6_typo_value, $global_button_typo, $typo1, $typo2] );
		$font_weight_array = array();

		foreach ( $get_fonts as $fonts ) {
			$each_font = explode( ':', $fonts );
			if ( ! isset ( $font_weight_array[$each_font[0]] ) ) {
				$font_weight_array[$each_font[0]][] = $each_font[1];
			} else {
				if ( ! in_array( $each_font[1], $font_weight_array[$each_font[0]] ) ) {
					$font_weight_array[$each_font[0]][] = $each_font[1];
				}
			}
		}
		$final_font_array = array();
		foreach ( $font_weight_array as $font => $font_weight ) {
			$each_font_string = $font.':'.implode( ',', $font_weight );
			$final_font_array[] = $each_font_string;
		}

		$final_font_string = implode( '|', $final_font_array );
		$google_fonts_url = '';
		$subsets   = 'cyrillic,cyrillic-ext';
		if ( $final_font_string ) {
			$query_args = array(
				'family' => urlencode( $final_font_string ),
				'subset' => urlencode( $subsets )
			);
			$google_fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
		}
		return $google_fonts_url;
	}
endif;

if(! function_exists('pubnews_get_color_format')):
    function pubnews_get_color_format($color) {
		if( str_contains( $color, '--pubnews-global-preset' ) ) {
			return( 'var( ' .esc_html( $color ). ' )' );
		} else {
			return $color;
		}
    }
endif;

if( ! function_exists( 'pubnews_get_rcolor_code' ) ) :
	/**
	 * Returns randon color code
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_get_rcolor_code() {
		$color_array["color"] = "#333333";
		$color_array["hover"] = "#448bef";
		return apply_filters( 'pubnews_apply_random_color_shuffle_value', $color_array );
	}
endif;

require get_template_directory() . '/inc/theme-starter.php'; // theme starter functions.
require get_template_directory() . '/inc/customizer/customizer.php'; // Customizer additions.
require get_template_directory() . '/inc/extras/helpers.php'; // helpers files.
require get_template_directory() . '/inc/extras/extras.php'; // extras files.
require get_template_directory() . '/inc/extras/extend-api.php'; // extras files.
require get_template_directory() . '/inc/widgets/widgets.php'; // widget handlers
include get_template_directory() . '/inc/styles.php';
include get_template_directory() . '/inc/admin/admin.php';
new Pubnews_Admin\Admin_Page();
new Pubnews_Extend_Api();

/**
 * Filter posts ajax function
 *
 * @package Pubnews
 * @since 1.0.0
 */
function pubnews_filter_posts_load_tab_content() {
	check_ajax_referer( 'pubnews-nonce', 'security' );
	$options = isset( $_GET['options'] ) ? json_decode( stripslashes( $_GET['options'] ) ): '';
	if( empty( $options ) ) return;
	$query = json_decode( $options->query );
	$layout = $options->layout;
	$orderArray = explode( '-', $query->order );
	$posts_args = array(
		'posts_per_page'   => absint( $query->count ),
		'order' => esc_html( $orderArray[1] ),
		'orderby' => esc_html( $orderArray[0] ),
		'cat' => esc_html( $options->category_id ),
		'ignore_sticky_posts'    => true
	);
	if( $query->ids ) $post_args['post__not_in'] = pubnews_get_array_key_string_to_int( $query->ids );
	$n_posts = new \WP_Query( apply_filters( 'pubnews_query_args_filter', $posts_args ) );
	$total_posts = $n_posts->post_count;
	if( $n_posts -> have_posts() ):
		ob_start();
		echo '<div class="tab-content content-' .esc_html( $options->category_id ). '">';
			while( $n_posts->have_posts() ) : $n_posts->the_post();
				$options->featuredPosts = false;
				$res['loaded'] = true;
				$current_post = $n_posts->current_post;
				if( $layout == 'four' ) {
					if( $current_post === 0 ) echo '<div class="featured-post">';
					if( $current_post === 0 || $current_post === 1 ) $options->featuredPosts = true;
					if( $current_post === 2 ) {
						?>
						<div class="trailing-post">
						<?php
					}
				} else {
					if( ($current_post % 5) === 0 ) echo '<div class="row-wrap">';
						if( $current_post === 0 ) {
							echo '<div class="featured-post">';
							$options->featuredPosts = true;
						}
							if( $current_post === 1 || $current_post === 5 ) {
								?>
								<div class="trailing-post <?php if($current_post === 5) echo esc_attr('bottom-trailing-post'); ?>">
								<?php
							}
				}
								// get template file w.r.t par
								get_template_part( 'template-parts/news-filter/content', 'one', $options );
				if( $layout == 'four' ) {
					if( $total_posts === $current_post + 1 ) echo '</div><!-- .trailing-post -->';
						if( $current_post === 1 ) echo '</div><!-- .featured-post-->';
				} else {
							if( $current_post === 4 || ( $total_posts === $current_post + 1 ) ) echo '</div><!-- .trailing-post -->';
						if( $current_post === 0 ) echo '</div><!-- .featured-post-->';
					if( ($current_post % 5) === 4 || ( $total_posts === $current_post + 1 ) ) echo '</div><!-- .row-wrap -->';
				}
			endwhile;
		echo '</div>';	
		$res['posts'] = ob_get_clean();
	else :
		$res['loaded'] = false;
		$res['posts'] = esc_html__( 'No posts found', 'pubnews' );
	endif;
	wp_send_json_success( $res );
	wp_die();
}
add_action( 'wp_ajax_pubnews_filter_posts_load_tab_content', 'pubnews_filter_posts_load_tab_content');
add_action( 'wp_ajax_nopriv_pubnews_filter_posts_load_tab_content', 'pubnews_filter_posts_load_tab_content' );

if( ! function_exists( 'pubnews_posts_content' ) ) :
	/**
	 * Posts ajax function
	 *
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_posts_content() {
		check_ajax_referer( 'pubnews-nonce', 'security' );
		$query_vars = isset( $_POST['query_vars'] ) ? json_decode( stripslashes( $_POST['query_vars'] ), true ): '';
		$query_vars['paged'] = isset( $_POST['paged'] ) ? esc_attr( $_POST['paged'] + 1 ): 1;
		$query_vars['ignore_sticky_posts'] = true;
		$n_posts = new WP_Query( apply_filters( 'pubnews_query_args_filter', $query_vars ) );
		$res['loaded'] = false;
		$res['continue'] = false;
		if ( $n_posts->have_posts() ) :
			ob_start();
			$res['loaded'] = true;
			if( $n_posts->max_num_pages != ( $_POST['paged'] + 1 ) ) $res['continue'] = true;
			$args['archive_post_element_order'] = PND\pubnews_get_customizer_option( 'archive_post_element_order' );
			$args['archive_page_category_option'] = PND\pubnews_get_customizer_option( 'archive_page_category_option' );
			/* Start the Loop */
			while ( $n_posts->have_posts() ) :
				$n_posts->the_post();
				/*
				* Include the Post-Type-specific template for the content.
				* If you want to override this in a child theme, then include a file
				* called content-___.php (where ___ is the Post Type name) and that will be used instead.
				*/
				get_template_part( 'template-parts/content', get_post_type(), $args );
			endwhile;
		$res['posts'] = ob_get_clean();
		endif;
		wp_send_json_success( $res );
		wp_die();
	}
	add_action( 'wp_ajax_pubnews_posts_content', 'pubnews_posts_content');
	add_action( 'wp_ajax_nopriv_pubnews_posts_content', 'pubnews_posts_content' );
endif;

if( ! function_exists( 'pubnews_lazy_load_value' ) ) :
	/**
	 * Echos lazy load attribute value.
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_lazy_load_value() {
		echo esc_attr( apply_filters( 'pubnews_lazy_load_value', 'lazy' ) );
	}
endif;

if( ! function_exists( 'pubnews_add_menu_description' ) ) :
	// merge menu description element to the menu 
	function pubnews_add_menu_description( $item_output, $item, $depth, $args ) {
		if($args->theme_location != 'menu-1') return $item_output;
		
		if ( !empty( $item->description ) ) {
			$item_output = str_replace( $args->link_after . '</a>', '<span class="menu-item-description">' . $item->description . '</span>' . $args->link_after . '</a>', $item_output );
		}
		return $item_output;
	}
	add_filter( 'walker_nav_menu_start_el', 'pubnews_add_menu_description', 10, 4 );
endif;

if( ! function_exists( 'pubnews_bool_to_string' ) ) :
	// boolean value to string 
	function pubnews_bool_to_string( $bool ) {
		$string = ( $bool ) ? '1' : '0';
		return $string;
	}
endif;

 if( ! function_exists( 'pubnews_get_image_sizes_option_array' ) ) :
	/**
	 * Get list of image sizes
	 * 
	 * @since 1.0.0
	 * @package Pubnews
	 */
	function pubnews_get_image_sizes_option_array() {
		$image_sizes = get_intermediate_image_sizes();
		foreach( $image_sizes as $image_size ) :
			$sizes[] = [
				'label'	=> esc_html( $image_size ),
				'value'	=> esc_html( $image_size )
			];
		endforeach;
		return $sizes;
	}
endif;

if( ! function_exists( 'pubnews_get_style_tag' ) ) :
	/**
	 * Generate Style tag for image ratio and image radius for news grid, list, carousel
	 * 
	 * @since 1.0.0
	 * @package Pubnews
	 */
	function pubnews_get_style_tag( $variables, $selectors = '' ) {
		echo '<style id="'. esc_attr( $variables['unique_id'] ) .'-style">';
			if( isset( $variables['image_ratio'] ) ) {
				$image_ratio = json_decode( $variables['image_ratio'] );
				if( $image_ratio->desktop > 0 ) echo "#" . $variables['unique_id']. " article figure.post-thumb-wrap { padding-bottom: calc( " . $image_ratio->desktop . " * 100% ) }\n";
				if( $image_ratio->tablet > 0 ) echo " @media (max-width: 769px){ #" . $variables['unique_id']. " article figure.post-thumb-wrap { padding-bottom: calc( " . $image_ratio->tablet . " * 100% ) } }\n";
				if( $image_ratio->smartphone > 0 ) echo " @media (max-width: 548px){ #" . $variables['unique_id']. " article figure.post-thumb-wrap { padding-bottom: calc( " . $image_ratio->smartphone . " * 100% ) }}\n";
			}
		echo "</style>";
	}
endif;

if( ! function_exists( 'pubnews_get_style_tag_fb' ) ) :
	/**
	 * Generates style tag for image ratio and image radius for news filter and new block
	 * 
	 * @since 1.0.0
	 * @package Pubnews
	 */
	function pubnews_get_style_tag_fb( $variables, $selectors = '' ) {
		echo '<style id="'. esc_attr( $variables['unique_id'] ) .'-style">';
			if( isset( $variables['image_ratio'] ) ) {
				$image_ratio = json_decode( $variables['image_ratio'] );
				// for featured post
				if( $variables['layout'] == 'three' ) {
					if( $image_ratio->desktop > 0 ) echo "#" . $variables['unique_id']. ".pubnews-block.layout--three .featured-post figure { padding-bottom: calc( " . ( $image_ratio->desktop * 0.4 ) . " * 100% ) }\n";
					if( $image_ratio->tablet > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block.layout--three .featured-post figure { padding-bottom: calc( " . ( $image_ratio->tablet * 0.4 ) . " * 100% ) } }\n";
					if( $image_ratio->smartphone > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block.layout--three .featured-post figure { padding-bottom: calc( " . ( $image_ratio->smartphone * 0.4 ) . " * 100% ) } }\n";
				} else {
					if( $image_ratio->desktop > 0 ) echo "#" . $variables['unique_id']. ".pubnews-block .featured-post figure { padding-bottom: calc( " . $image_ratio->desktop . " * 100% ) }\n";
					if( $image_ratio->tablet > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block .featured-post figure { padding-bottom: calc( " . $image_ratio->tablet . " * 100% ) } }\n";
					if( $image_ratio->smartphone > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block .featured-post figure { padding-bottom: calc( " . $image_ratio->smartphone . " * 100% ) } }\n";
				}

				// for trailing post
				if( $variables['layout'] == 'two' ) {
					if( $image_ratio->desktop > 0 ) echo "#" . $variables['unique_id']. ".pubnews-block.layout--two .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->desktop * 0.78 ) . " * 100% ) }\n";
					if( $image_ratio->tablet > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->tablet * 0.78 ) . " * 100% ) } }\n";
					if( $image_ratio->smartphone > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block.layout--two .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->smartphone * 0.78 ) . " * 100% ) } }\n";
				} else {
					if( $image_ratio->desktop > 0 ) echo "#" . $variables['unique_id']. ".pubnews-block .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->desktop * 0.3 ) . " * 100% ) }\n";
					if( $image_ratio->tablet > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->tablet * 0.3 ) . " * 100% ) } }\n";
					if( $image_ratio->smartphone > 0 ) echo "@media (max-width: 769px) {#" . $variables['unique_id']. ".pubnews-block .trailing-post figure { padding-bottom: calc( " . ( $image_ratio->smartphone * 0.3 ) . " * 100% ) } }\n";
				}
			}
		echo "</style>";
	}
endif;

if( ! function_exists( 'pubnews_get_image_sizes' ) ) :
	/**
	 * get a list of all images sizes
	 * 
	 * @since 1.0.0
	 * @package Pubnews
	 */
	function pubnews_get_image_sizes() {
		$image_sizes_args = get_intermediate_image_sizes();
		$image_size_elements_args = [];
		if( ! empty( $image_sizes_args ) ) :
			foreach( $image_sizes_args as $image_size ) :
				$image_size_elements_args[$image_size] = esc_html( $image_size );
			endforeach;
		endif;
		return $image_size_elements_args;
	}
endif;

if( ! function_exists( 'pubnews_parse_icon_picker_value' ) ) :
	/**
	 * Function to return image url for icon picker
	 */
	function pubnews_parse_icon_picker_value ( $control ) {
		if( $control['type'] == 'svg' ) :
			$control['url'] = wp_get_attachment_image_url( $control['value'], 'full' );
		endif;
		return $control;
	}
endif;

if ( ! function_exists( 'pubnews_create_elementor_kit' ) ) {
    /**
     * Create Elementor default kit
     *
     * @since 1.0.0
     */
    function pubnews_create_elementor_kit() {
        if ( ! did_action( 'elementor/loaded' ) ) return;
        $kit = Elementor\Plugin::$instance->kits_manager->get_active_kit();
        if ( ! $kit->get_id() ) :
            $created_default_kit = Elementor\Plugin::$instance->kits_manager->create_default();
            update_option( 'elementor_active_kit', $created_default_kit );
		endif;
    }
    add_action( 'init', 'pubnews_create_elementor_kit' );
}