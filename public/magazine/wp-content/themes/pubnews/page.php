<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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

		if( is_front_page() ) :
			/**
			 * hook - pubnews_main_banner_hook
			 * 
			 * hooked - pubnews_main_banner_part - 10
			 */
			do_action( 'pubnews_main_banner_hook' );

			$homepage_content_order = PND\pubnews_get_customizer_option( 'homepage_content_order' );
			foreach( $homepage_content_order as $content_order_key => $content_order ) :
				if( $content_order['option'] ) :
					switch( $content_order['value'] ) {
						case "full_width_section": 
											/**
											 * hook - pubnews_full_width_blocks_hook
											 * 
											 * hooked- pubnews_full_width_blocks_part
											 * @since 1.0.0
											 * 
											 */
											do_action( 'pubnews_full_width_blocks_hook' );
										break;
						case "leftc_rights_section": 
											/**
											 * hook - pubnews_leftc_rights_blocks_hook
											 * 
											 * hooked- pubnews_leftc_rights_blocks_part
											 * @since 1.0.0
											 * 
											 */
											do_action( 'pubnews_leftc_rights_blocks_hook' );
										break;
						case "lefts_rightc_section": 
											/**
											 * hook - pubnews_lefts_rightc_blocks_hook
											 * 
											 * hooked- pubnews_lefts_rightc_blocks_part
											 * @since 1.0.0
											 * 
											 */
											do_action( 'pubnews_lefts_rightc_blocks_hook' );
										break;
						case "two_column_section": 
											/**
											 * hook - pubnews_two_column_section_hook
											 * 
											 * hooked- pubnews_two_column_section_columns_part
											 * @since 1.0.0
											 * 
											 */
											if( is_home() && is_front_page() ) do_action( 'pubnews_two_column_section_hook' );
										break;
						case "bottom_full_width_section": 
											/**
											 * hook - pubnews_bottom_full_width_blocks_hook
											 * 
											 * hooked- pubnews_bottom_full_width_blocks_part
											 * @since 1.0.0
											 * 
											 */
											do_action( 'pubnews_bottom_full_width_blocks_hook' );
										break;
							default: ?>
							<div id="theme-content">
								<?php
									/**
									 * hook - pubnews_before_main_content
									 * 
									 */
									do_action( 'pubnews_before_main_content' );
								?>
								<main id="primary" class="site-main">
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
												<div class="post-inner-wrapper pubnews-card">
													<?php
														while ( have_posts() ) :
															the_post();

															get_template_part( 'template-parts/content', 'page' );

															// If comments are open or we have at least one comment, load up the comment template.
															if ( comments_open() || get_comments_number() ) :
																comments_template();
															endif;

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
					}
				endif;
			endforeach;
		else :
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
								<div class="post-inner-wrapper pubnews-card">
									<?php
										while ( have_posts() ) :
											the_post();

											get_template_part( 'template-parts/content', 'page' );

											// If comments are open or we have at least one comment, load up the comment template.
											if ( comments_open() || get_comments_number() ) :
												comments_template();
											endif;

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
	endif;
get_footer();
