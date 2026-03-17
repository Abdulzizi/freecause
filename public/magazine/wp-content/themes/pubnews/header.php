<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php pubnews_schema_body_attributes(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'pubnews' ); ?></a>
	<div class="pubnews_ovelay_div"></div>
	<?php
		/**
		 * hook - pubnews_page_prepend_hook
		 * 
		 * @package Pubnews
		 * @since 1.0.0
		 */
		do_action( "pubnews_page_prepend_hook" );
	?>
	
	<header id="masthead" class="site-header layout--default <?php echo esc_attr( 'layout--' . PND\pubnews_get_customizer_option('header_layout') ); ?>">
		<?php
			/**
			 * Function - pubnews_top_header_html
			 * 
			 * @since 1.0.0
			 * 
			 */
			pubnews_top_header_html();

			/**
			 * Function - pubnews_header_html
			 * 
			 * @since 1.0.0
			 * 
			 */
			pubnews_header_html();
		?>
	</header><!-- #masthead -->
	
	<?php

	pubnews_breadcrumb_html();
	/**
	 * function - pubnews_after_header_html
	 * 
	 * @since 1.0.0
	 */
	pubnews_after_header_html();