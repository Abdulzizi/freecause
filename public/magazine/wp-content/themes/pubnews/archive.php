<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
get_header();

	if( did_action( 'elementor/loaded' ) && class_exists( 'Nekit_Render_Templates_Html' ) ) :
		$Nekit_render_templates_html = new Nekit_Render_Templates_Html();
		if( $Nekit_render_templates_html->is_template_available('archive') ) {
			$archive_rendered = true;
			echo $Nekit_render_templates_html->current_builder_template();
		} else {
			$archive_rendered = false;
		}
	else :
		$archive_rendered = false;
	endif;

	if( ! $archive_rendered ) :
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
									
									if ( have_posts() ) : ?>
									<header class="page-header">
										<?php
											if( ! is_author() ) :
												the_archive_title( '<h1 class="page-title pubnews-block-title">', '</h1>' );
												the_archive_description( '<div class="archive-description">', '</div>' );
											endif;
										?>
									</header><!-- .page-header -->
									<div class="post-inner-wrapper news-list-wrap">
										<?php
											$args['archive_post_element_order'] = PND\pubnews_get_customizer_option( 'archive_post_element_order' );
											$args['archive_page_category_option'] = PND\pubnews_get_customizer_option( 'archive_page_category_option' );
											$args['archive_image_size'] = PND\pubnews_get_customizer_option( 'archive_image_size' );
											/* Start the Loop */
											while ( have_posts() ) :
												the_post();
												/*
												* Include the Post-Type-specific template for the content.
												* If you want to override this in a child theme, then include a file
												* called content-___.php (where ___ is the Post Type name) and that will be used instead.
												*/
												get_template_part( 'template-parts/content', get_post_type(), $args );
											endwhile;
										else :
											get_template_part( 'template-parts/content', 'none' );
										endif;
										?>
									</div>
									<?php
										if( have_posts() ) :
											/**
											 * hook - pubnews_pagination_link_hook
											 * 
											 * @package Pubnews
											 * @since 1.0.0
											 */
											do_action( 'pubnews_pagination_link_hook' );
										endif;
									?>
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
