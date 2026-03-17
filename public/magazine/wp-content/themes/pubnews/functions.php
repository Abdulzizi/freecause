<?php
/**
 * Pubnews functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
if ( ! defined( 'PUBNEWS_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	$theme_info = wp_get_theme();
	define( 'PUBNEWS_VERSION', $theme_info->get( 'Version' ) );
}

if ( ! defined( 'PUBNEWS_PREFIX' ) ) {
	// Replace the prefix of theme if changed.
	define( 'PUBNEWS_PREFIX', 'pubnews_' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function pubnews_setup() {
	$nprefix = 'pubnews-';
	/*
	* Make theme available for translation.
	* Translations can be filed in the /languages/ directory.
	* If you're building a theme based on Pubnews, use a find and replace
	* to change 'pubnews' to the name of your theme in all the template files.
	*/
	load_theme_textdomain( 'pubnews', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	* Let WordPress manage the document title.
	* By adding theme support, we declare that this theme does not use a
	* hard-coded <title> tag in the document head, and expect WordPress to
	* provide it for us.
	*/
	add_theme_support( 'title-tag' );

	/*
	* Enable support for Post Thumbnails on posts and pages.
	*
	* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	*/
	add_theme_support( 'post-thumbnails' );

	// add_image_size( 'pubnews-large', 1400, 800, true );
	add_image_size( $nprefix . 'featured', 1020, 700, true );
	add_image_size( $nprefix . 'list', 600, 400, true );
	add_image_size( $nprefix . 'thumb', 300, 200, true );
	add_image_size( $nprefix . 'small', 150, 95, true );
	add_image_size( $nprefix . 'grid', 400, 250, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Main Header', 'pubnews' ),
			'menu-2' => esc_html__( 'Bottom Footer', 'pubnews' )
		)
	);

	/*
	* Switch default core markup for search form, comment form, and comments
	* to output valid HTML5.
	*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			PUBNEWS_VERSION . 'custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 100,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	$theme_activation_date = get_option( 'pubnews_theme_activation_date_timestamp' );
	if( ! $theme_activation_date ) update_option( 'pubnews_theme_activation_date_timestamp', current_time( 'timestamp' ) );
}
add_action( 'after_setup_theme', 'pubnews_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function pubnews_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'pubnews_content_width', 640 );
}
add_action( 'after_setup_theme', 'pubnews_content_width', 0 );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

add_filter( 'get_the_archive_title_prefix', 'pubnews_prefix_string' );
function pubnews_prefix_string($prefix) {
	$archive_page_title_prefix = PND\pubnews_get_customizer_option( 'archive_page_title_prefix' );
	if( $archive_page_title_prefix ) return apply_filters( 'pubnews_archive_page_title_prefix', $prefix );
	return apply_filters( 'pubnews_archive_page_title_prefix', false );
}