<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
get_header();
/**
 * hook - pubnews_main_banner_hook
 * 
 * hooked - pubnews_main_banner_part - 10
 */
if( is_home() && is_front_page() ) do_action( 'pubnews_main_banner_hook' );

$homepage_content_order = PND\pubnews_get_customizer_option( 'homepage_content_order' );
foreach( $homepage_content_order as $content_order_key => $content_order ) :
	if( $content_order['value'] == 'latest_posts' && is_home() && ! is_front_page()  ) $content_order['option'] = true;
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
								if( is_home() && is_front_page() ) do_action( 'pubnews_full_width_blocks_hook' );
							break;
			case "leftc_rights_section": 
								/**
								 * hook - pubnews_leftc_rights_blocks_hook
								 * 
								 * hooked- pubnews_leftc_rights_blocks_part
								 * @since 1.0.0
								 * 
								 */
								if( is_home() && is_front_page() ) do_action( 'pubnews_leftc_rights_blocks_hook' );
							break;
			case "lefts_rightc_section": 
								/**
								 * hook - pubnews_lefts_rightc_blocks_hook
								 * 
								 * hooked- pubnews_lefts_rightc_blocks_part
								 * @since 1.0.0
								 * 
								 */
								if( is_home() && is_front_page() ) do_action( 'pubnews_lefts_rightc_blocks_hook' );
							break;
			case "bottom_full_width_section": 
								/**
								 * hook - pubnews_bottom_full_width_blocks_hook
								 * 
								 * hooked- pubnews_bottom_full_width_blocks_part
								 * @since 1.0.0
								 * 
								 */
								if( is_home() && is_front_page() ) do_action( 'pubnews_bottom_full_width_blocks_hook' );
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
			default: ?>
					<div id="theme-content">
						<main id="primary" class="site-main <?php echo esc_attr( 'width-' . pubnews_get_section_width_layout_val() ); ?>">
							<div class="pubnews-container">
                    			<div class="row">
									<div class="secondary-left-sidebar">
										<?php get_sidebar('left'); ?>
									</div>
                    				<div class="primary-content">
										<?php
											if ( have_posts() ) :
												if ( is_home() && ! is_front_page() ) :
													?>
													<header>
														<h1 class="page-title pubnews-block-title screen-reader-text"><?php single_post_title(); ?></h1>
													</header>
													<?php
												endif;
												$args['archive_post_element_order'] = PND\pubnews_get_customizer_option( 'archive_post_element_order' );
												$args['archive_page_category_option'] = PND\pubnews_get_customizer_option( 'archive_page_category_option' );
												$args['archive_image_size'] = PND\pubnews_get_customizer_option( 'archive_image_size' );
												echo '<div class="news-list-wrap">';
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
												echo '</div>';
												/**
												 * hook - pubnews_pagination_link_hook
												 * 
												 * @package Pubnews
												 * @since 1.0.0
												 */
												do_action( 'pubnews_pagination_link_hook' );
											else :
												get_template_part( 'template-parts/content', 'none' );
											endif;
										?>
									</div>
									<div class="secondary-sidebar">
										<?php
											get_sidebar();
										?>
									</div>
								</div>
							</div> <!-- pubnews-container end -->
						</main><!-- #main -->
					</div><!-- #theme-content -->
			<?php
		}
	endif;
endforeach;
get_footer();