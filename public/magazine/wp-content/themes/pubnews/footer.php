<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Pubnews
 */
?>
	<footer id="colophon" class="site-footer dark_bk">
		<?php
			/**
			 * Function - pubnews_footer_sections_html
			 * 
			 * @since 1.0.0
			 * 
			 */
			pubnews_footer_sections_html();

			/**
			 * Function - pubnews_bottom_footer_sections_html
			 * 
			 * @since 1.0.0
			 * 
			 */
			pubnews_bottom_footer_sections_html();
		?>
	</footer><!-- #colophon -->
	<?php
		/**
		* hook - pubnews_after_footer_hook
		*
		* @hooked - pubnews_scroll_to_top
		*
		*/
		if( has_action( 'pubnews_after_footer_hook' ) ) {
			do_action( 'pubnews_after_footer_hook' );
		}
	?>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>