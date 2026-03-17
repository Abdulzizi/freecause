<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
get_header();

	if( did_action( 'elementor/loaded' ) && class_exists( 'Nekit_Render_Templates_Html' ) ) :
		$Nekit_render_templates_html = new Nekit_Render_Templates_Html();
		if( $Nekit_render_templates_html->is_template_available('404') ) {
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
								<section class="error-404 not-found">
									<?php
										/**
										 * hook - pubnews_before_inner_content
										 * 
										 */
										do_action( 'pubnews_before_inner_content' );
									?>
									<div class="post-inner-wrapper pubnews-card">
										<header class="page-header">
											<h1 class="page-title pubnews-block-title"><?php echo esc_html__( '404 Not Found', 'pubnews' ); ?></h1>
										</header><!-- .page-header -->

										<div class="page-content">
											<p><?php echo esc_html__( 'It looks like nothing was found at this location. Maybe try another search?', 'pubnews' ); ?></p>
										</div><!-- .page-content -->

										<div class="page-footer">
											<a class="button-404" href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html__( 'Go back to home', 'pubnews' ); ?></a>
										</div>
									</div><!-- .post-inner-wrapper -->
								</section><!-- .error-404 -->
							</div>
							<div class="secondary-sidebar">
								<?php
									get_sidebar();
								?>
							</div>
						</div>
					</div>
				</main><!-- #main -->
			</div><!-- #theme-content -->
		<?php
	endif;
	get_footer();
