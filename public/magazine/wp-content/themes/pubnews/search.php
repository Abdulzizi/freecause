<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
get_header();

	if( did_action( 'elementor/loaded' ) && class_exists( 'Nekit_Render_Templates_Html' ) ) :
		$Nekit_render_templates_html = new Nekit_Render_Templates_Html();
		if( $Nekit_render_templates_html->is_template_available('archive') ) {
			$search_rendered = true;
			echo $Nekit_render_templates_html->current_builder_template();
		} else {
			$search_rendered = false;
		}
	else :
		$search_rendered = false;
	endif;

	if( ! $search_rendered ) :
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
								<div class="news-list layout--two">
								<?php
									/**
									 * hook - pubnews_before_inner_content
									 * 
									 */
									do_action( 'pubnews_before_inner_content' );
									
									if ( have_posts() ) : ?>
										<header class="page-header">
											<h1 class="page-title pubnews-block-title">
												<?php
													$prefix = '<span class="search-page-title-prefix">'. esc_html__( 'Search Results for', 'pubnews' ) .'</span>';
													/* translators: %s: search query. */
													printf( esc_html( '%s %s' ), $prefix, '<span class="search-page-title-suffix">' . get_search_query() . '</span>' );
												?>
											</h1>
										</header><!-- .page-header -->
										<div class="post-inner-wrapper">
											<div class="news-list-post-wrap column--one">
												<?php
													/* Start the Loop */
													while ( have_posts() ) :
														the_post();
														/**
														 * Run the loop for the search to output the results.
														 * If you want to overload this in a child theme then include a file
														 * called content-search.php and that will be used instead.
														 */
														get_template_part( 'template-parts/content', 'search' );

													endwhile;
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
										<?php
										else :
											get_template_part( 'template-parts/content', 'none' );
										endif;
										?>
								</div>
							</div>
							<div class="secondary-sidebar">
								<?php get_sidebar(); ?>
							</div>
						</div>
					</div>
				</main><!-- #main -->
				<?php get_sidebar(); ?>
			</div><!-- #theme-content -->
		<?php
	endif;
get_footer();
