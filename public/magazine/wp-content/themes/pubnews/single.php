<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
get_header();

	if( did_action( 'elementor/loaded' ) && class_exists( 'Nekit_Render_Templates_Html' ) ) :
		$Nekit_render_templates_html = new Nekit_Render_Templates_Html();
		if( $Nekit_render_templates_html->is_template_available('single') ) {
			$single_rendered = true;
			echo $Nekit_render_templates_html->current_builder_template();
		} else {
			$single_rendered = false;
		}
	else :
		$single_rendered = false;
	endif;
	
	if( ! $single_rendered ) :
		?>
		<div id="theme-content">
			<?php
				/**
				 * hook - pubnews_before_main_content
				 * 
				 */
				do_action( 'pubnews_before_main_content' );
			?>
			<main id="primary" class="site-main <?php echo esc_attr( 'width-' . pubnews_get_section_width_layout_val() ); ?>">
				<div class="pubnews-container">
					<div class="row">
						<div class="secondary-left-sidebar">
							<?php
								get_sidebar('left');
							?>
						</div>
						<div class="primary-content">
							<?php
								/**
								 * hook - pubnews_before_inner_content
								 * 
								 */
								do_action( 'pubnews_before_inner_content' );
							?>
							<div class="post-inner-wrapper">
								<?php
									while ( have_posts() ) : the_post();
										// get template parts
										get_template_part( 'template-parts/content', 'single' );
									endwhile; // End of the loop.
								?>
							</div>
						</div>
						<div class="secondary-sidebar">
							<?php get_sidebar(); ?>
						</div>
					</div>
				</div>
			</main><!-- #main -->
		</div><!-- #theme-content -->
		<?php
	endif;
get_footer();