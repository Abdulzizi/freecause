<?php
/**
 * Includes the helper functions and hooks the theme. 
 * 
 * @package Pubnews
 * @since 1.0.0
 */
 use Pubnews\CustomizerDefault as PND;

if( !function_exists( 'pubnews_advertisement_block_html' ) ) :
    /**
     * Calls advertisement block
     * 
     * @since 1.0.0
     */
    function pubnews_advertisement_block_html($options,$echo) {
        $media = ( is_object( $options->media ) || is_array( $options->media ) ) ? $options->media :json_decode( $options->media );
        if( ! isset( $media->media_id ) ) return;
        ?>
        <div <?php if( isset( $options->blockId ) && !empty($options->blockId) ) echo 'id="' .esc_attr( $options->blockId ). '"'; ?> class="pubnews-advertisement-block is-large">
        <?php
            if( $echo ) {
                if( isset( $options->title ) && $options->title ) echo '<h2 class="pubnews-block-title">' .esc_html( $options->title ). '</h2>';
                if( $media->media_id != 0 ) {
                ?>
                    <figure class="inner-ad-block">
                        <a href="<?php echo esc_url( $options->url ); ?>" target="<?php echo esc_attr( $options->targetAttr ); ?>" rel="<?php echo esc_attr( $options->relAttr ); ?>"><img src="<?php echo esc_url( wp_get_attachment_url( $media->media_id ) ); ?>"></a>
                    </figure>
                <?php
                }
            }
        ?>
        </div>
    <?php
    }
 endif;

 if( !function_exists( 'pubnews_top_header_html' ) ) :
    /**
     * Calls top header hooks
     * 
     * @since 1.0.0
     */
    function pubnews_top_header_html() {
        if( ! PND\pubnews_get_customizer_option( 'top_header_option' ) ) return;
        require get_template_directory() . '/inc/hooks/top-header-hooks.php'; // top header hooks.
        echo '<div class="top-header">';
            echo '<div class="pubnews-container">';
                echo '<div class="row">';
                /**
                 * hook - pubnews_top_header_hook
                 * 
                 * @hooked - pubnews_top_header_ticker_news_part - 10
                 * @hooked - pubnews_top_header_social_part - 20
                 */
                if( has_action( 'pubnews_top_header_hook' ) ) do_action( 'pubnews_top_header_hook' );
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }
endif;

if( !function_exists( 'pubnews_header_html' ) ) :
    /**
     * Calls header hooks
     * 
     * @since 1.0.0
     */
    function pubnews_header_html() {
        require get_template_directory() . '/inc/hooks/header-hooks.php'; // top header hooks.
        ?>
        <div class="main-header">
            <?php
                // show search form
                if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) :
                    ?>
                        <div class="search-form-wrap hide">
                            <?php echo get_search_form(); ?>
                        </div>
                    <?php
                endif;
                ?>
            <div class="site-branding-section">
                <div class="pubnews-container">
                    <div class="row">
                        <?php
                            /**
                             * hook - pubnews_header__site_branding_section_hook
                             * 
                             * @hooked - pubnews_header_menu_part - 10
                             * @hooked - pubnews_header_ads_banner_part - 20
                             */
                            if( has_action( 'pubnews_header__site_branding_section_hook' ) ) do_action( 'pubnews_header__site_branding_section_hook' );
                        ?>
                    </div>
                </div>
            </div>
            <?php
                if( PND\pubnews_get_customizer_option( 'header_layout' ) != 'three' ) :
            ?>
                    <div class="menu-section">
                        <div class="search-form-wrap hide">
                            <?php echo get_search_form(); ?>
                        </div>
                        <div class="pubnews-container">
                            <div class="row">
                                <?php
                                    /**
                                     * hook - pubnews_header__menu_section_hook
                                     * 
                                     * @hooked - pubnews_header_menu_part - 10
                                     * @hooked - pubnews_header_search_part - 20
                                     */
                                    if( has_action( 'pubnews_header__menu_section_hook' ) ) do_action( 'pubnews_header__menu_section_hook' );
                                ?>
                            </div>
                        </div>
                    </div>
            <?php endif; ?>
        </div>
        <?php
    }
endif;

if( !function_exists( 'pubnews_after_header_html' ) ) :
    /**
     * Calls after header hooks
     * 
     * @since 1.0.0
     */
    function pubnews_after_header_html() {
        $ticker_news_width_layout = pubnews_get_section_width_layout_val('ticker_news_width_layout');
        $classes[] = esc_attr('ticker-news-section--' . $ticker_news_width_layout);

        ?>
        <div class="after-header header-layout-banner-two ticker-news-section--<?php echo esc_attr($ticker_news_width_layout); ?>">
            <div class="pubnews-container">
                <div class="row">
                    <?php
                        /**
                         * hook - pubnews_after_header_hook
                         * 
                         * @hooked - pubnews_ticker_news_part - 10
                         */
                        if( has_action( 'pubnews_after_header_hook' ) ) do_action( 'pubnews_after_header_hook' );
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
endif;

require get_template_directory() . '/inc/hooks/footer-hooks.php'; // footer hooks.
if( !function_exists( 'pubnews_footer_sections_html' ) ) :
    /**
     * Calls footer hooks
     * 
     * @since 1.0.0
     */
    function pubnews_footer_sections_html() {
        if( ! PND\pubnews_get_customizer_option( 'footer_option' ) ) return;
        $footer_section_width = PND\pubnews_get_customizer_option( 'footer_section_width' );
        ?>
        <div class="main-footer <?php echo esc_attr( $footer_section_width ); ?>">
            <div class="footer-inner <?php if( $footer_section_width == 'boxed-width' ) { echo esc_attr( 'pubnews-container' ); } else { echo esc_attr( 'pubnews-container-fluid' ); } ?>">
                <div class="row">
                    <?php
                        /**
                         * hook - pubnews_footer_hook
                         * 
                         * @hooked - pubnews_footer_widgets_area_part - 10
                         */
                        if( has_action( 'pubnews_footer_hook' ) ) do_action( 'pubnews_footer_hook' );
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
endif;

if( !function_exists( 'pubnews_bottom_footer_sections_html' ) ) :
    /**
     * Calls bottom footer hooks
     * 
     * @since 1.0.0
     */
    function pubnews_bottom_footer_sections_html() {
        if( ! PND\pubnews_get_customizer_option( 'bottom_footer_option' ) ) return;
        require get_template_directory() . '/inc/hooks/bottom-footer-hooks.php'; // footer hooks.
        $bottom_footer_width_layout = pubnews_get_section_width_layout_val('bottom_footer_width_layout');
        ?>
        <div class="bottom-footer <?php echo esc_attr( 'width-' . $bottom_footer_width_layout ); ?>">
            <div class="pubnews-container">
                <div class="row">
                    <?php
                        /**
                         * hook - pubnews_bottom_footer_sections_html
                         * 
                         * @hooked - bottom_footer_social_option - 10
                         * @hooked - pubnews_bottom_footer_menu_part - 20
                         * @hooked - pubnews_bottom_footer_copyright_part - 3020
                         */
                        if( has_action( 'pubnews_botttom_footer_hook' ) ) do_action( 'pubnews_botttom_footer_hook' );
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
endif;
require get_template_directory() . '/inc/hooks/inner-hooks.php'; // inner hooks.
require get_template_directory() . '/inc/hooks/frontpage-sections-hooks.php'; // frontpage sections hooks.

if ( ! function_exists( 'pubnews_breadcrumb_trail' ) ) :
    /**
     * Theme default breadcrumb function.
     *
     * @since 1.0.0
     */
    function pubnews_breadcrumb_trail() {
        if ( ! function_exists( 'breadcrumb_trail' ) ) {
            // load class file
            require_once get_template_directory() . '/inc/breadcrumb-trail/breadcrumb-trail.php';
        }

        // arguments variable
        $breadcrumb_args = array(
            'container' => 'div',
            'show_browse' => false,
        );
        breadcrumb_trail( $breadcrumb_args );
    }
    add_action( 'pubnews_breadcrumb_trail_hook', 'pubnews_breadcrumb_trail' );
endif;

if( ! function_exists( 'pubnews_breadcrumb_html' ) ) :
    /**
     * Theme breadcrumb
     *
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_breadcrumb_html() {
        $site_breadcrumb_option = PND\pubnews_get_customizer_option( 'site_breadcrumb_option' );
        if ( ! $site_breadcrumb_option ) return;
        if ( is_front_page() || is_home() ) return;
        $site_breadcrumb_type = PND\pubnews_get_customizer_option( 'site_breadcrumb_type' );
        ?>
            <div class="pubnews-breadcrumb-wrapper">
                <div class="pubnews-container">
                    <div class="row">
                        <div class="pubnews-breadcrumb-wrap pubnews-card">
                            <?php
                                switch( $site_breadcrumb_type ) {
                                    case 'yoast': if( pubnews_compare_wand([pubnews_function_exists( 'yoast_breadcrumb' )] ) ) yoast_breadcrumb();
                                            break;
                                    case 'rankmath': if( pubnews_compare_wand([pubnews_function_exists( 'rank_math_the_breadcrumbs' )] ) ) rank_math_the_breadcrumbs();
                                            break;
                                    case 'bcn': if( pubnews_compare_wand([pubnews_function_exists( 'bcn_display' )] ) ) bcn_display();
                                            break;
                                    default: do_action( 'pubnews_breadcrumb_trail_hook' );
                                            break;
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
endif;

if( ! function_exists( 'pubnews_category_archive_featured_posts_html' ) ) :
    /**
     * Html for category archive page featured post
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_category_archive_featured_posts_html() {
        if( ! is_category() ) return;
        $sticky_posts  =  get_option( 'sticky_posts' );
        if( ! $sticky_posts ) return;
        $current_object = get_queried_object();
        $current_object_id = $current_object->term_id;
        foreach( $sticky_posts as $sticky_post_id ) :
            $cat_ids =  wp_get_post_categories( $sticky_post_id, array( 'fields' => 'ids' ) );
            if( in_array( $current_object_id, $cat_ids ) ) {
                $post_to_get = $sticky_post_id;
                break;
            }
        endforeach;
        if( ! isset($post_to_get) ) return;
        ?>
            <div class="pubnews-featured-posts-wrapper">
                <div class="pubnews-container">
                    <div class="row">
                        <article class="featured-post is-sticky" data-id="<?php echo esc_attr( $post_to_get ); ?>">
                            <figure class="post-thumb-wrap">
                                <a href="<?php the_permalink($post_to_get); ?>" title="<?php the_title_attribute(array('post'  => $post_to_get)); ?>">
                                    <?php if( has_post_thumbnail($post_to_get) ) {
                                            echo get_the_post_thumbnail($post_to_get, 'full', array(
                                                'title' => the_title_attribute(array(
                                                    'post'  => $post_to_get,
                                                    'echo'  => false
                                                ))
                                            ));
                                        }
                                    ?>
                                </a>
                                
                            </figure>
                            <div class="post-element">
                                <?php pubnews_get_post_categories( $post_to_get, 2 ); ?>
                                <h2 class="post-title"><a href="<?php the_permalink($post_to_get); ?>" title="<?php the_title_attribute(array('post'  => $post_to_get)); ?>"><?php echo wp_kses_post( get_the_title($post_to_get) ); ?></a></h2>
                                <div class="post-meta">
                                    <?php
                                        pubnews_posted_by($post_to_get);
                                        pubnews_posted_on($post_to_get);
                                    ?>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        <?php
    }
    add_action( 'pubnews_before_main_content', 'pubnews_category_archive_featured_posts_html', 20 );
endif;

if( ! function_exists( 'pubnews_category_archive_author_html' ) ) :
    /**
     * Html for category archive page featured post
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_category_archive_author_html() {
        if( ! is_author() ) return;
        $author_id =  get_query_var( 'author' );
        $ticker_news_width_layout = pubnews_get_section_width_layout_val('ticker_news_width_layout');
        $classes[] = esc_attr('ticker-news-section--' . $ticker_news_width_layout);
        ?>
        <div class="ticker-news-section--<?php echo esc_attr($ticker_news_width_layout); ?>">
          <div class="pubnews-container pubnews-author-section">
            <div class="row">
            <?php echo wp_kses_post( get_avatar($author_id, 125) ); ?>
            <div class="author-content">
                <h2 class="author-name"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></h2>
                <p class="author-desc"><?php echo wp_kses_post( get_the_author_meta('description', $author_id) ); ?></p>
            </div>
            </div>
          </div>
        </div>
        <?php
    }
    add_action( 'pubnews_before_main_content', 'pubnews_category_archive_author_html', 20 );
endif;

if( ! function_exists( 'pubnews_button_html' ) ) :
    /**
     * View all html
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_button_html( $args ) {
        if( ! $args['option'] ) return;
        $global_button_label = PND\pubnews_get_customizer_option( 'global_button_label' );
        $global_button_icon_picker =  [
            'type'  => 'icon',
            'value' => 'fas fa-angle-right'
        ];
        $classes = isset( $args['classes'] ) ? 'post-link-button' . ' ' .$args['classes'] : 'post-link-button';
        $link = isset( $args['link'] ) ? $args['link'] : get_the_permalink();
        $text = isset( $args['text'] ) ? $args['text'] : $global_button_label;
        $icon = isset( $args['icon'] ) ? $args['icon'] : $global_button_icon_picker['value'];
        $view_all_label_option = isset( $args['text_option'] ) ? $args['text_option'] : false;
        if( $view_all_label_option ) $text = isset( $args['text'] ) ? $args['text'] : esc_htm__( 'View all', 'pubnews' );
        echo apply_filters( 'pubnews_button_html', sprintf( '<a class="%1$s" href="%2$s">%3$s<i class="%4$s"></i></a>', esc_attr( $classes ), esc_url( $link ), esc_html( $text ), esc_attr( $icon ) ) );
    }
    add_action( 'pubnews_section_block_view_all_hook', 'pubnews_button_html', 10, 1 );
endif;

if( ! function_exists( 'pubnews_archive_excerpt_more_string' ) ) :
    /**
     * Excerpt more string filter
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_archive_excerpt_more_string( $more ) {
        return '...';
    }
    add_filter('excerpt_more', 'pubnews_archive_excerpt_more_string');
endif;

if( ! function_exists( 'pubnews_pagination_fnc' ) ) :
    /**
     * Renders pagination html
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_pagination_fnc() {
        if( is_null( paginate_links() ) ) {
            return;
        }
        $pagination_type = PND\pubnews_get_customizer_option( 'archive_pagination_type' );
        switch( $pagination_type ) {
            case 'ajax' : echo '<div class="pagination"><button class="ajax-load-more">' .esc_html( "Load More" ). '</button></div>';
                    break;
            case 'number' : echo '<div class="pagination">' .wp_kses_post( paginate_links( array( 'prev_text' => '<i class="fas fa-chevron-left"></i>', 'next_text' => '<i class="fas fa-chevron-right"></i>', 'type' => 'list' ) ) ). '</div>';
                    break;
            default : echo '<div class="pagination">' .wp_kses_post( get_the_posts_navigation() ). '</div>';
                    break;
        }
    }
    add_action( 'pubnews_pagination_link_hook', 'pubnews_pagination_fnc' );
 endif;

 if( ! function_exists( 'pubnews_header_theme_mode_icon_part' ) ) :
    /**
     * Header theme mode element
     * 
     * @since 1.0.0
     */
     function pubnews_header_theme_mode_icon_part() {
        if( ! PND\pubnews_get_customizer_option( 'header_theme_mode_toggle_option' ) ) return;
        ?>
            <div class="blaze-switcher-button">
                <div class="blaze-switcher-button-inner-left"></div>
                <div class="blaze-switcher-button-inner"></div>
            </div>
        <?php
     }
     add_action( 'pubnews_after_footer_hook', 'pubnews_header_theme_mode_icon_part', 10 );
 endif;

 if( ! function_exists( 'pubnews_scroll_to_top_html' ) ) :
    /**
     * Scroll to top fnc
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_scroll_to_top_html() {
        if( ! PND\pubnews_get_multiselect_tab_option('stt_responsive_option') ) return;
        $stt_label = esc_html__( 'Back To Top', 'pubnews' );
        $stt_icon_picker = [
            'type'  => 'icon',
            'value' => 'fa-solid fa-angle-up'
        ];
        $icon = ( $stt_icon_picker['type'] == 'icon' && ! empty( $stt_icon_picker['value'] ) ) ? $stt_icon_picker['value'] : 'fas fa-ban';
        $icon_text = isset( $stt_label ) ? $stt_label : '';
    ?>
        <div id="pubnews-scroll-to-top" class="align--right">
            <?php 
            if( $icon_text ) echo '<span class="icon-text">' .esc_html( $icon_text ). '</span>';
            if( $icon != 'fas fa-ban' ) : ?>
                <span class="icon-holder"><i class="<?php echo esc_attr( $icon ); ?>"></i></span>
            <?php endif; ?>
        </div><!-- #pubnews-scroll-to-top -->
    <?php
    }
    add_action( 'pubnews_after_footer_hook', 'pubnews_scroll_to_top_html', 20 );
 endif;

 if( ! function_exists( 'pubnews_get_background_and_cursor_animation' ) ) :
    /**
     * Renders html for cursor and background animation
     * 
     * @since 1.0.0
     */
    function pubnews_get_background_and_cursor_animation() {
        $site_background_animation = PND\pubnews_get_customizer_option( 'site_background_animation' );
        if( $site_background_animation ) pubnews_shooting_star_animation_html();
        $cursor_animation = PND\pubnews_get_customizer_option( 'cursor_animation' );
        $cursorclass = 'pubnews-cursor';
        if( $cursor_animation != 'none' ) $cursorclass .= ' type--' . $cursor_animation;
        if( in_array( $cursor_animation, [ 'one', 'two' ] ) ) echo '<div class="'. esc_attr( $cursorclass ) .'"></div>';
    }
    add_action( 'pubnews_after_footer_hook', 'pubnews_get_background_and_cursor_animation', 30 );
 endif;

 if( ! function_exists( 'pubnews_shooting_star_animation_html' ) ) :
    /**
     * Background animation one
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_shooting_star_animation_html() {
        $elementClass = 'pubnews-background-animation';
        ?>
            <div class="<?php echo esc_attr( $elementClass ); ?>">
                <?php
                    for( $i = 0; $i < 13; $i++ ) :
                        echo '<span class="item"></span>';
                    endfor;
                ?>
            </div>
        <?php
    }
endif;

if( ! function_exists( 'pubnews_loader_html' ) ) :
	/**
     * Preloader html
     * 
     * @package Pubnews
     * @since 1.0.0
     */
	function pubnews_loader_html() {
        if( ! PND\pubnews_get_customizer_option( 'preloader_option' ) ) return;
	?>
		<div class="pubnews_loading_box">
			<div class="box">
				<div class="loader-item loader-<?php echo esc_attr( PND\pubnews_get_customizer_option( 'preloader_type' ) ); ?>"></div>
			</div>
		</div>
	<?php
	}
    add_action( 'pubnews_page_prepend_hook', 'pubnews_loader_html', 1 );
endif;

 if( ! function_exists( 'pubnews_custom_header_html' ) ) :
    /**
     * Site custom header html
     * 
     * @package Pubnews
     * @since 1.0.0
     */
    function pubnews_custom_header_html() {
        /**
         * Get custom header markup
         * 
         * @since 1.0.0 
         */
        the_custom_header_markup();
    }
    add_action( 'pubnews_page_prepend_hook', 'pubnews_custom_header_html', 20 );
 endif;

 if( ! function_exists( 'pubnews_single_layout_part' ) ) :
    /**
     * Renders part of single layout including meta, title, thumb and category
     * 
     * @since 1.0.0
     */
    function pubnews_single_layout_part() {
        if( ! is_single() ) return;
        $elementClass = 'entry-header';
        $author_id = get_the_author_meta( 'ID' );

        $single_post_show_original_image_option = PND\pubnews_get_customizer_option( 'single_post_show_original_image_option' );
        ?>
            <header class="<?php echo esc_attr( $elementClass ); ?>">
                <?php pubnews_post_thumbnail( $single_post_show_original_image_option ); ?>
                <div class="elements-wrapper">
                    <?php
                        pubnews_get_post_categories( get_the_ID(), 2 );
                        the_title( '<h1 class="entry-title"' .pubnews_schema_article_name_attributes(). '>', '</h1>' );
                        if ( 'post' === get_post_type() ) :
                            ?>
                                <div class="entry-meta">
                                    <?php
                                        pubnews_posted_by();    // author
                                        pubnews_posted_on();    // date
                                        pubnews_comments_number();  // comment
                                        $website_read_time_before_icon = [
                                            'type'  => 'icon',
                                            'value' => 'fas fa-clock'
                                        ];  // read time
                                        echo '<span class="read-time ' .esc_attr( $website_read_time_before_icon['value'] ). '">' .pubnews_post_read_time( get_the_content() ). ' ' .esc_html__( 'mins', 'pubnews' ). '</span>';
                                    ?>
                                </div><!-- .entry-meta -->
                            <?php 
                        endif;
                    ?>
                </div>
            </header><!-- .entry-header -->
        <?php
    }
 endif;

 if( ! function_exists( 'pubnews_convert_number_to_numeric_string' )) :
    /**
     * Function to convert int parameter to numeric string
     * MARK: CONVERT NUMBER TO STRING
     * 
     * @return string
     */
    function pubnews_convert_number_to_numeric_string( $int ) {
        switch( $int ){
            case 2:
                return "two";
                break;
            case 3:
                return "three";
                break;
            case 4:
                return "four";
                break;
            case 5:
                return "five";
                break;
            case 6:
                return "six";
                break;
            case 7:
                return "seven";
                break;
            case 8:
                return "eight";
                break;
            case 9:
                return "nine";
                break;
            case 10:
                return "ten";
                break;
            default:
                return "one";
        }
    }
 endif;