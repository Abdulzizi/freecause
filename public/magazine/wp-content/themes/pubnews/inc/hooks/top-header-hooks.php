<?php
/**
 * Top Header hooks and functions
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
   if( ! function_exists( 'pubnews_top_header_date_time_part' ) ) :
      /**
       * Top header menu element
      * 
      * @since 1.0.0
      */
      function pubnews_top_header_date_time_part() {
      if( ! PND\pubnews_get_customizer_option( 'top_header_date_time_option' ) ) return;
      ?>
         <div class="top-date-time">
            <div class="top-date-time-inner">
              <span class="time"></span>
              <span class="date"><?php echo date_i18n(get_option('date_format'), current_time('timestamp')); ?></span>
              
            </div>
         </div>
      <?php
      }
      add_action( 'pubnews_top_header_hook', 'pubnews_top_header_date_time_part', 10 );
   endif;

 if( ! function_exists( 'pubnews_top_header_ticker_news_part' ) ) :
    /**
     * Top header menu element
     * 
     * @since 1.0.0
     */
    function pubnews_top_header_ticker_news_part() {
      if( ! PND\pubnews_get_customizer_option( 'top_header_ticker_news_option' ) ) return;
      $ticker_args['posts_per_page'] = 4;
      $top_header_ticker_news_categories = PND\pubnews_get_customizer_option( 'top_header_ticker_news_categories' );
      if( PND\pubnews_get_customizer_option( 'top_header_ticker_news_date_filter' ) != 'all' ) $ticker_args['date_query'] = pubnews_get_date_format_array_args(PND\pubnews_get_customizer_option( 'top_header_ticker_news_date_filter' ));
      if( $top_header_ticker_news_categories ) $ticker_args['cat'] = pubnews_get_categories_for_args($top_header_ticker_news_categories);
      $top_header_ticker_news_posts = PND\pubnews_get_customizer_option( 'top_header_ticker_news_posts' );
      if( $top_header_ticker_news_posts ) {
         $ticker_args['post__in'] = pubnews_get_post_id_for_args($top_header_ticker_news_posts);
      }
      ?>
         <div class="top-ticker-news">
            <ul class="ticker-item-wrap" data-dir="<?php echo json_encode( 'false' ); ?>" data-auto="<?php echo json_encode( 'true' ); ?>">
               <?php
               if( isset( $ticker_args ) ) :
                     $ticker_args['ignore_sticky_posts'] = true;
                     $ticker_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $ticker_args ) );
                     if( $ticker_query->have_posts() ) :
                        while( $ticker_query->have_posts() ) : $ticker_query->the_post();
                        ?>
                           <li class="ticker-item"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2></li>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                     endif;
                  endif;
               ?>
            </ul>
			</div>
      <?php
    }
    add_action( 'pubnews_top_header_hook', 'pubnews_top_header_ticker_news_part', 10 );
 endif;

 if( ! function_exists( 'pubnews_top_header_social_part' ) ) :
   /**
    * Top header social element
    * 
    * @since 1.0.0
    */
   function pubnews_top_header_social_part() {
     if( ! PND\pubnews_get_customizer_option( 'top_header_social_option' ) ) return;
     $elementClass = 'social-icons-wrap';
     $elementClass .= ' pubnews-show-hover-animation';
     ?>
        <div class="<?php echo esc_html( $elementClass ); ?>">
           <?php pubnews_customizer_social_icons(); ?>
        </div>
     <?php
   }
   if( PND\pubnews_get_customizer_option( 'header_layout' ) === 'two' || PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) { 
      add_action( 'pubnews_top_header_hook', 'pubnews_top_header_social_part', 15 );
   }
endif;

if( ! function_exists( 'pubnews_top_header_newsletter_part' ) && PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) :
   /**
    * Header newsletter element
    * 
    * @since 1.0.0
    */
    function pubnews_top_header_newsletter_part() {
       if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) ) return;
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
           <div class="<?php echo esc_html( $elementClass ); ?>" <?php if( isset( $newsletter_label ) && !empty( $newsletter_label ) ) echo 'title="' . esc_attr( $newsletter_label ) . '"'; ?>>
               <a href="<?php echo esc_url( $header_newsletter_redirect_href_link ); ?>" target="<?php echo esc_attr( $header_newsletter_redirect_href_target ); ?>" data-popup="redirect">
                   <?php
                       if( isset( $newsletter_icon_picker['value'] ) && !empty( $newsletter_icon_picker['value'] ) ) echo '<span class="title-icon"><i class="' .esc_attr( $newsletter_icon_picker['value'] ). '"></i></span>';
                       if( isset( $newsletter_label ) && !empty( isset( $newsletter_label ) ) ) echo '<span class="title-text">' .esc_html( $newsletter_label ). '</span>';
                   ?>
               </a>
           </div><!-- .newsletter-element -->
       <?php
    }
    add_action( 'pubnews_top_header_hook', 'pubnews_top_header_newsletter_part', 20 );
endif;

if( ! function_exists( 'pubnews_top_header_random_news_part' ) && PND\pubnews_get_customizer_option( 'header_layout' ) === 'three' ) :
   /**
    * Header random news element
    * 
    * @since 1.0.0
    */
    function pubnews_top_header_random_news_part() {
       if( ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) ) return;
       $random_news_icon_picker = [
         'type'  => 'icon',
         'value' => 'fas fa-random'
     ];
       $random_news_label = esc_html__( 'Random News', 'pubnews' );
       $header_random_news_redirect_href_target = PND\pubnews_get_customizer_option( 'header_random_news_redirect_href_target' );
       $button_url = pubnews_get_random_news_url();
       ?>
           <div class="random-news-element" title="<?php echo esc_attr( $random_news_label ); ?>">
               <a href="<?php echo esc_url($button_url); ?>" target="<?php echo esc_attr( $header_random_news_redirect_href_target ); ?>">
                   <?php
                       if( isset( $random_news_icon_picker['value'] ) && !empty( $random_news_icon_picker['value'] ) ) echo '<span class="title-icon"><i class="' .esc_attr( $random_news_icon_picker['value'] ). '"></i></span>';
                       ?>
                     <span class="title-text"><?php echo esc_html( $random_news_label )?></span>
               </a>
           </div><!-- .random-news-element -->
       <?php
    }
    add_action( 'pubnews_top_header_hook', 'pubnews_top_header_random_news_part', 20 );
endif;

add_action( 'pubnews_top_header_hook', function() {
   if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) && ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) ) return;
   $header_layout = PND\pubnews_get_customizer_option( 'header_layout' );
   if( in_array( $header_layout, [ 'one', 'two' ] ) ) return;
   echo '<div class="top-header-nrn-button-wrap">';
}, 18 ); // newsletter wrapper open
add_action( 'pubnews_top_header_hook', function() {
   if( ! PND\pubnews_get_customizer_option( 'header_newsletter_option' ) && ! PND\pubnews_get_customizer_option( 'header_random_news_option' ) ) return;
   $header_layout = PND\pubnews_get_customizer_option( 'header_layout' );
   if( in_array( $header_layout, [ 'one', 'two' ] ) ) return;
   echo '</div><!-- .top-header-nrn-button-wrap -->';
}, 22 ); // newsletter wrapper end