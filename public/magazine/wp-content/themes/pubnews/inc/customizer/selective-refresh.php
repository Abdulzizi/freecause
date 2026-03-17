<?php
/**
 * Includes functions for selective refresh
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
if( ! function_exists( 'pubnews_customize_selective_refresh' ) ) :
    /**
     * Adds partial refresh for the customizer preview
     * 
     */
    function pubnews_customize_selective_refresh( $wp_customize ) {
        if ( ! isset( $wp_customize->selective_refresh ) ) return;

        // top header show hide
        $wp_customize->selective_refresh->add_partial(
            'top_header_option',
            array(
                'selector'        => '#masthead .top-header',
                'render_callback' => 'pubnews_top_header_html'
            )
        );
        // top header social icons show hide
        $wp_customize->selective_refresh->add_partial(
            'top_header_social_option',
            array(
                'selector'        => '#masthead .top-header .social-icons-wrap',
                'render_callback' => 'pubnews_top_header_social_part_selective_refresh'
            )
        );
        // header off canvas show hide
        $wp_customize->selective_refresh->add_partial(
            'header_off_canvas_option',
            array(
                'selector'        => '#masthead .sidebar-toggle-wrap',
                'render_callback' => 'pubnews_header_sidebar_toggle_part_selective_refresh'
            )
        );
        // header search icon show hide
        $wp_customize->selective_refresh->add_partial(
            'header_search_option',
            array(
                'selector'        => '#masthead .search-wrap',
                'render_callback' => 'pubnews_header_search_part_selective_refresh'
            )
        );
        // theme mode toggle show hide
        $wp_customize->selective_refresh->add_partial(
            'header_theme_mode_toggle_option',
            array(
                'selector'        => '#masthead .mode_toggle_wrap',
                'render_callback' => 'pubnews_header_theme_mode_icon_part_selective_refresh'
            )
        );
        // site title
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            array(
                'selector'        => '.site-title a',
                'render_callback' => 'pubnews_customize_partial_blogname',
            )
        );
        // site description
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            array(
                'selector'        => '.site-description',
                'render_callback' => 'pubnews_customize_partial_blogdescription',
            )
        );
        
        // social icons target attribute
        $wp_customize->selective_refresh->add_partial(
            'social_icons_target',
            array(
                'selector'        => '.top-header .social-icons-wrap',
                'render_callback' => 'pubnews_customizer_social_icons',
            )
        );

        // social icons
        $wp_customize->selective_refresh->add_partial(
            'social_icons',
            array(
                'selector'        => '.top-header .social-icons-wrap',
                'render_callback' => 'pubnews_customizer_social_icons',
            )
        );

        // post read more button label
        $wp_customize->selective_refresh->add_partial(
            'global_button_label',
            array(
                'selector'        => 'article .post-link-button',
                'render_callback' => 'pubnews_customizer_read_more_button',
            )
        );

        // ticker news title
        $wp_customize->selective_refresh->add_partial(
            'ticker_news_title',
            array(
                'selector'        => '.ticker-news-wrap .ticker_label_title',
                'render_callback' => 'pubnews_customizer_ticker_label',
            )
        );

        // newsletter label
        $wp_customize->selective_refresh->add_partial(
            'newsletter_label',
            array(
                'selector'        => '.newsletter-element',
                'render_callback' => 'pubnews_customizer_newsletter_button_label',
            )
        );

        // single post related posts option
        $wp_customize->selective_refresh->add_partial(
            'single_post_related_posts_option',
            array(
                'selector'        => '.single-related-posts-section-wrap',
                'render_callback' => 'pubnews_single_related_posts',
            )
        );
        
        // footer option
        $wp_customize->selective_refresh->add_partial(
            'footer_option',
            array(
                'selector'        => 'footer .main-footer',
                'render_callback' => 'pubnews_footer_sections_html',
                'container_inclusive'=> true
            )
        );

        // footer column option
        $wp_customize->selective_refresh->add_partial(
            'footer_widget_column',
            array(
                'selector'        => 'footer .main-footer',
                'render_callback' => 'pubnews_footer_sections_html',
            )
        );

        // bottom footer option
        $wp_customize->selective_refresh->add_partial(
            'bottom_footer_option',
            array(
                'selector'        => 'footer .bottom-footer',
                'render_callback' => 'pubnews_bottom_footer_sections_html',
            )
        );

        // bottom footer menu option
        $wp_customize->selective_refresh->add_partial(
            'bottom_footer_menu_option',
            array(
                'selector'        => 'footer .bottom-footer .bottom-menu',
                'render_callback' => 'pubnews_bottom_footer_menu_part_selective_refresh',
            )
        );

        // bottom footer menu option
        $wp_customize->selective_refresh->add_partial(
            'bottom_footer_social_option',
            array(
                'selector'        => 'footer .bottom-footer .social-icons-wrap',
                'render_callback' => 'pubnews_botttom_footer_social_part_selective_refresh',
            )
        );
    }
    add_action( 'customize_register', 'pubnews_customize_selective_refresh' );
endif;

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function pubnews_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function pubnews_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

// global button label
function pubnews_customizer_read_more_button() {
    $global_button_label = PND\pubnews_get_customizer_option( 'global_button_label' );
    $global_button_icon_picker = [
        'type'  => 'icon',
        'value' => 'fas fa-angle-right'
    ];
    return ( esc_html( $global_button_label ) . '<i class="' .esc_attr( $global_button_icon_picker['value'] ). '"></i>' );
}

// ticker label latest tab
function pubnews_customizer_ticker_label() {
    $ticker_news_title = PND\pubnews_get_customizer_option( 'ticker_news_title' );
    $ticker_news_title_icon = [
        'type'  => 'icon',
        'value' => 'fas fa-dot-circle'
    ];
    $partial_string = '';
    if( $ticker_news_title_icon['type'] == 'icon' ) $partial_string =  '<span class="icon"><i class="' .esc_attr( $ticker_news_title_icon['value'] ). '"></i></span>';
    return ( $partial_string .= '<span class="ticker_label_title_string">' .esc_html( $ticker_news_title ). '</span>' );
}

// newsletter button label
function pubnews_customizer_newsletter_button_label() {
    $newsletter_icon_picker = [
        'type'  => 'none',
        'value' => 'fa-solid fa-bell'
    ];
    $newsletter_label = PND\pubnews_get_customizer_option( 'newsletter_label' );
    $header_newsletter_redirect_href_target = PND\pubnews_get_customizer_option( 'header_newsletter_redirect_href_target' );
    $header_newsletter_redirect_href_link = PND\pubnews_get_customizer_option( 'header_newsletter_redirect_href_link' );
    ob_start();
    ?>
        <a href="<?php echo esc_url( $header_newsletter_redirect_href_link ); ?>" target="<?php echo esc_attr( $header_newsletter_redirect_href_target ); ?>" data-popup="redirect">
            <?php
                if( isset( $newsletter_icon_picker['value'] ) ) echo '<span class="title-icon"><i class="' .esc_attr( $newsletter_icon_picker['value'] ). '"></i></span>';
                if( isset( $newsletter_label ) ) echo '<span class="title-text">' .esc_html( $newsletter_label ). '</span>';
            ?>
        </a>
    <?php
    $content = ob_get_clean();
    return $content;
}

// top header social icons part
function pubnews_top_header_social_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'top_header_social_option' ) ) return;
    ?>
       <div class="social-icons-wrap">
          <?php pubnews_customizer_social_icons(); ?>
       </div>
    <?php
}

function pubnews_header_sidebar_toggle_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'header_off_canvas_option' ) ) return;
    ?>
       <div class="sidebar-toggle-wrap">
           <a class="off-canvas-trigger" href="javascript:void(0);">
               <div class="pubnews_sidetoggle_menu_burger">
                 <span></span>
                 <span></span>
                 <span></span>
             </div>
           </a>
           <div class="sidebar-toggle hide">
             <div class="pubnews-container">
               <div class="row">
                 <?php dynamic_sidebar( 'off-canvas-sidebar' ); ?>
               </div>
             </div>
           </div>
       </div>
    <?php
}

function pubnews_header_search_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'header_search_option' ) ) return;
    ?>
        <div class="search-wrap">
            <button class="search-trigger">
                <i class="fas fa-search"></i>
            </button>
            <div class="search-form-wrap hide">
                <?php echo get_search_form(); ?>
            </div>
            <div class="search_close_btn hide"><i class="fas fa-times"></i></div>
        </div>
    <?php
}

function pubnews_header_theme_mode_icon_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'header_theme_mode_toggle_option' ) ) return;
    ?>
        <div class="mode_toggle_wrap">
            <input class="mode_toggle" type="checkbox">
        </div>
    <?php
 }

// bottom footer menu part
function pubnews_bottom_footer_menu_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'bottom_footer_menu_option' ) ) return;
    ?>
       <div class="bottom-menu">
          <?php
          if( has_nav_menu( 'menu-2' ) ) :
             wp_nav_menu(
                array(
                   'theme_location' => 'menu-2',
                   'menu_id'        => 'bottom-footer-menu',
                   'depth' => 1
                )
             );
             else :
                if ( is_user_logged_in() && current_user_can( 'edit_theme_options' ) ) {
                   ?>
                      <a href="<?php echo esc_url( admin_url( '/nav-menus.php?action=locations' ) ); ?>"><?php esc_html_e( 'Setup Bottom Footer Menu', 'pubnews' ); ?></a>
                   <?php
                }
             endif;
          ?>
       </div>
    <?php
 }

// bottom footer social icons part
function pubnews_botttom_footer_social_part_selective_refresh() {
    if( ! PND\pubnews_get_customizer_option( 'bottom_footer_social_option' ) ) return;
    ?>
       <div class="social-icons-wrap">
          <?php pubnews_customizer_social_icons(); ?>
       </div>
    <?php
}