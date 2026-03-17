<?php
/**
 * Header hooks and functions
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;

if( ! function_exists( 'pubnews_header_social_part' ) ) :
    /**
     * header social element
     * 
     * @since 1.0.0
     */
    function pubnews_header_social_part() {
    $elementClass = 'social-icons-wrap';
    $elementClass .= ' pubnews-show-hover-animation';
      ?>
         <div class="<?php echo esc_html( $elementClass ); ?>">
            <?php
               if( PND\pubnews_get_customizer_option( 'top_header_social_option' ) ) {
                  pubnews_customizer_social_icons();
               }
            ?>
         </div>
      <?php
    }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'one' ) { 
       add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_social_part', 5 );
    }
 endif;

 if( ! function_exists( 'pubnews_header_site_branding_part' ) ) :
    /**
     * Header site branding element
     * 
     * @since 1.0.0
     */
     function pubnews_header_site_branding_part() {
         ?>
            <div class="site-branding">
                <?php
                    the_custom_logo();
                    if ( is_front_page() && is_home() ) :
                ?>
                        <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <?php
                    else :
                ?>
                        <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                <?php
                    endif;
                    $pubnews_description = get_bloginfo( 'description', 'display' );
                    if ( $pubnews_description || is_customize_preview() ) :
                ?>
                    <p class="site-description"><?php echo $pubnews_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                <?php endif; ?>
            </div><!-- .site-branding -->
         <?php
     }
    add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_site_branding_part', 10 );
 endif;

 if( ! function_exists( 'pubnews_header_ads_banner_part' ) ) :
    /**
     * Header ads banner element
     * 
     * @since 1.0.0
     */
     function pubnews_header_ads_banner_part() {
        if( ! PND\pubnews_get_multiselect_tab_option( 'header_ads_banner_responsive_option' ) ) return;
        $header_ads_banner_type = PND\pubnews_get_customizer_option( 'header_ads_banner_type' );
        if( $header_ads_banner_type == 'none' ) return;
        $header_ads_banner_custom_image = PND\pubnews_get_customizer_option( 'header_ads_banner_custom_image' );
        $header_ads_banner_custom_url = PND\pubnews_get_customizer_option( 'header_ads_banner_custom_url' );
        $header_ads_banner_custom_target = PND\pubnews_get_customizer_option( 'header_ads_banner_custom_target' );
        if( ! empty( $header_ads_banner_custom_image ) ) :
        ?>
            <div class="ads-banner">
                <a href="<?php echo esc_url( $header_ads_banner_custom_url ); ?>" target="<?php echo esc_html( $header_ads_banner_custom_target ); ?>"><img src="<?php echo wp_get_attachment_url( $header_ads_banner_custom_image ); ?>"></a>
            </div><!-- .ads-banner -->
        <?php
        endif;
     }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'two' ) { 
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_ads_banner_part', 20 );
    } else {
        add_action( 'pubnews_after_header_hook', 'pubnews_header_ads_banner_part', 10 );
    }
 endif;

 if( ! function_exists( 'pubnews_header_newsletter_part' ) ) :
    /**
     * Header newsletter element
     * 
     * @since 1.0.0
     */
     function pubnews_header_newsletter_part() {
        if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) || PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) return;
        $newsletter_label = PND\pubnews_get_customizer_option( 'newsletter_label' );
        $newsletter_icon_picker = [
            'type'  => 'none',
            'value' => 'fa-solid fa-bell'
        ];
        $header_newsletter_redirect_href_target = PND\pubnews_get_customizer_option( 'header_newsletter_redirect_href_target' );
        $header_newsletter_redirect_href_link = PND\pubnews_get_customizer_option( 'header_newsletter_redirect_href_link' );
        $elementClass = 'newsletter-element';
        $elementClass .= ' pubnews-show-hover-animation';
        ?>
            <div class="<?php echo esc_html( $elementClass ); ?>" <?php if( isset($newsletter_label) && !empty($newsletter_label) ) echo 'title="' . esc_attr( $newsletter_label ) . '"'; ?>>
                <a href="<?php echo esc_url( $header_newsletter_redirect_href_link ); ?>" target="<?php echo esc_attr( $header_newsletter_redirect_href_target ); ?>" data-popup="redirect">
                    <?php
                        if( isset( $newsletter_icon_picker['value'] ) && ! empty( $newsletter_icon_picker['value'] ) ) echo '<span class="title-icon"><i class="' .esc_attr( $newsletter_icon_picker['value'] ). '"></i></span>';
                        if( isset( $newsletter_label ) && !empty( isset( $newsletter_label ) ) ) echo '<span class="title-text">' .esc_html( $newsletter_label ). '</span>';
                    ?>
                </a>
            </div><!-- .newsletter-element -->
        <?php
     }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'one' ) {
        add_action( 'pubnews_header__site_branding_section_hook', function() {
            if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) && ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) ) return;
            echo '<div class="header-right-button-wrap">';
        }, 29 );
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_newsletter_part', 30 );
    } else {
        add_action( 'pubnews_header__menu_section_hook', 'pubnews_header_newsletter_part', 45 );
    }
 endif;

 if( ! function_exists( 'pubnews_header_random_news_part' ) ) :
    /**
     * Header random news element
     * 
     * @since 1.0.0
     */
     function pubnews_header_random_news_part() {
        if( ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) || PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) return;
        $random_news_icon_picker = [
            'type'  => 'icon',
            'value' => 'fas fa-random'
        ];
        $random_news_label = esc_html__( 'Random News', 'pubnews' );
        $header_random_news_redirect_href_target = PND\pubnews_get_customizer_option( 'header_random_news_redirect_href_target' );
        $button_url = pubnews_get_random_news_url();
        ?>
            <div class="random-news-element" title="<?php echo esc_attr( $random_news_label ) ; ?>">
                <a href="<?php echo esc_url($button_url); ?>" target="<?php echo esc_attr( $header_random_news_redirect_href_target ); ?>">
                    <?php
                        if( isset( $random_news_icon_picker['value'] ) && !empty( $random_news_icon_picker['value'] ) ) echo '<span class="title-icon"><i class="' .esc_attr( $random_news_icon_picker['value'] ). '"></i></span>';
                        ?>
                    <span class="title-text"><?php echo esc_html( $random_news_label ); ?></span>
                </a>
            </div><!-- .random-news-element -->
        <?php
     }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'one' ) { 
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_random_news_part', 30 );
        add_action( 'pubnews_header__site_branding_section_hook', function() {
            if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) && ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) ) return;
            echo '</div><!-- .header-right-button-wrap -->';
        }, 31 );
    } else {
        add_action( 'pubnews_header__menu_section_hook', 'pubnews_header_random_news_part', 45 );
    }
 endif;

 if( ! function_exists( 'pubnews_header_sidebar_toggle_part' ) ) :
    /**
     * Header off canvas element
     * 
     * @since 1.0.0
     */
     function pubnews_header_sidebar_toggle_part() {
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
                <span class="off-canvas-close"><i class="fas fa-times"></i></span>
                  <div class="pubnews-container">
                    <div class="row">
                      <?php dynamic_sidebar( 'off-canvas-sidebar' ); ?>
                    </div>
                  </div>
                </div>
            </div>
         <?php
     }
     if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) {
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_sidebar_toggle_part', 100 );
     } else {
        add_action( 'pubnews_header__menu_section_hook', 'pubnews_header_sidebar_toggle_part', ( ( PND\pubnews_get_customizer_option( 'header_layout' ) == 'one' ) ? 20 : 30 ) );
     }
 endif;

 if( ! function_exists( 'pubnews_header_menu_part' ) ) :
    /**
     * Header menu element
     * 
     * @since 1.0.0
     */
    function pubnews_header_menu_part() {
      ?>
        <nav id="site-navigation" class="main-navigation <?php echo esc_attr( 'hover-effect--' . PND\pubnews_get_customizer_option( 'header_menu_hover_effect' ) ); ?>">
            <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <div id="pubnews_menu_burger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="menu_txt"><?php esc_html_e( 'Menu', 'pubnews' ); ?></span></button>
            <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'menu-1',
                        'menu_id'        => 'header-menu',
                    )
                );
            ?>
        </nav><!-- #site-navigation -->
      <?php
    }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) {
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_menu_part', 50 );
    } else {
        add_action( 'pubnews_header__menu_section_hook', 'pubnews_header_menu_part', ( ( PND\pubnews_get_customizer_option( 'header_layout' ) == 'one' ) ? 30 : 40 ) );
    }
 endif;

 if( ! function_exists( 'pubnews_header_search_part' ) ) :
   /**
    * Header search element
    * 
    * @since 1.0.0
    */
    function pubnews_header_search_part() {
        if( ! PND\pubnews_get_customizer_option( 'header_search_option' ) ) return;
        ?>
            <div class="search-wrap">
                <button class="search-trigger">
                    <i class="fas fa-search"></i>
                </button>
                <button class="search_close_btn hide"><i class="fas fa-times"></i></button>
            </div>
        <?php
    }
    if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) {
        add_action( 'pubnews_header__site_branding_section_hook', 'pubnews_header_search_part', 60 );
    } else {
        add_action( 'pubnews_header__menu_section_hook', 'pubnews_header_search_part', 50 );
    }
endif;

if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) {
    add_action( 'pubnews_header__site_branding_section_hook', function() {
        echo '<div class="header-smh-button-wrap menu-section">';
    }, 45 ); // search wrapper open
    add_action( 'pubnews_header__site_branding_section_hook', function() {
        echo '</div><!-- .header-smh-button-wrap -->';
    }, 120 ); // search wrapper end
}

 if( ! function_exists( 'pubnews_ticker_news_part' ) ) :
    /**
     * Ticker news element
     * 
     * @since 1.0.0
     */
     function pubnews_ticker_news_part() {
        $ticker_news_visible = PND\pubnews_get_customizer_option( 'ticker_news_visible' );
        if( $ticker_news_visible === 'none' ) return;
        if( $ticker_news_visible === 'front-page' && ! is_front_page() ) {
            return;
        } else if( $ticker_news_visible === 'innerpages' && is_front_page()  ) {
            return;
        }
        $ticker_news_layout = 'three';
        $ticker_news_order_by = PND\pubnews_get_customizer_option( 'ticker_news_order_by' );
        $orderArray = explode( '-', $ticker_news_order_by );
        $ticker_args = array(
            'order' => esc_html( $orderArray[1] ),
            'orderby' => esc_html( $orderArray[0] ),
            'ignore_sticky_posts'    => true
        );
        $ticker_args['posts_per_page'] = 6;
        $ticker_news_categories = PND\pubnews_get_customizer_option( 'ticker_news_categories' );
        if( PND\pubnews_get_customizer_option( 'ticker_news_date_filter' ) != 'all' ) $ticker_args['date_query'] = pubnews_get_date_format_array_args(PND\pubnews_get_customizer_option( 'ticker_news_date_filter' ));
        if( $ticker_news_categories ) $ticker_args['cat'] = pubnews_get_categories_for_args($ticker_news_categories);
        $ticker_news_posts = PND\pubnews_get_customizer_option( 'ticker_news_posts' );
        if( $ticker_news_posts ) $ticker_args['post__in'] = pubnews_get_post_id_for_args($ticker_news_posts);
         ?>
            <div class="ticker-news-wrap pubnews-ticker no-feat-img <?php echo esc_attr( 'layout--' . $ticker_news_layout ); ?>" data-speed="<?php echo esc_attr( 15000 ); ?>">
                <?php
                    $ticker_news_title = PND\pubnews_get_customizer_option( 'ticker_news_title' );
                    $ticker_news_title_icon = [
                        'type'  => 'icon',
                        'value' => 'fas fa-dot-circle'
                    ];
                ?>
                <?php if( $ticker_news_title_icon['type'] != 'none' || $ticker_news_title ) : ?>
                    <div class="ticker_label_title ticker-title pubnews-ticker-label">
                        <?php 
                            if( $ticker_news_title_icon['type'] == 'icon' ) echo '<span class="icon"><i class="' .esc_attr( $ticker_news_title_icon['value'] ). '"></i></span>';
                            if( $ticker_news_title ) :
                            ?>
                                <span class="ticker_label_title_string"><?php echo esc_html( $ticker_news_title ); ?></span>
                            <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="pubnews-ticker-box">
                  <?php
                    $pubnews_direction = 'left';
                    $pubnews_dir = 'ltr';
                    if( is_rtl() ){
                      $pubnews_direction = 'right';
                      $pubnews_dir = 'ltr';
                    }
                  ?>

                    <ul class="ticker-item-wrap" direction="<?php echo esc_attr($pubnews_direction); ?>" dir="<?php echo esc_attr($pubnews_dir); ?>">
                        <?php get_template_part( 'template-parts/ticker-news/template', esc_html( $ticker_news_layout ), $ticker_args ); ?>
                    </ul>
                </div>
            </div>
         <?php
     }
    add_action( 'pubnews_after_header_hook', 'pubnews_ticker_news_part', 10 );
 endif;
