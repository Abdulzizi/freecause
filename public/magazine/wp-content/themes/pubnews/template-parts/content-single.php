<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
?>
<article <?php pubnews_schema_article_attributes(); ?> id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-inner pubnews-card">
		<?php pubnews_single_layout_part(); ?>

		<div <?php pubnews_schema_article_body_attributes(); ?> class="entry-content">
			<?php
				the_content(
					sprintf(
						wp_kses(
							/* translators: %s: Name of current post. Only visible to screen readers */
							__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'pubnews' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						),
						wp_kses_post( get_the_title() )
					)
				);

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'pubnews' ),
						'after'  => '</div>',
					)
				);
			?>
		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<?php pubnews_tags_list(); ?>
			<?php pubnews_entry_footer(); ?>
		</footer><!-- .entry-footer -->
		<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle"><i class="fas fa-angle-double-left"></i>' . esc_html__( 'Previous:', 'pubnews' ) . '</span> <span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'pubnews' ) . '<i class="fas fa-angle-double-right"></i></span> <span class="nav-title">%title</span>',
				)
			);
		?>
	</div>
	<?php
		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;
	?>
</article><!-- #post-<?php the_ID(); ?> -->
<?php
	/**
	 * hook - pubnews_single_post_append_hook
	 * 
	 */
	do_action( 'pubnews_single_post_append_hook' );
?>