<?php
/**
 * Bottom Footer hooks and functions
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;

if( ! function_exists( 'pubnews_botttom_footer_social_part' ) ) :
   /**
    * Bottom Footer social element
    * 
    * @since 1.0.0
    */
   function pubnews_botttom_footer_social_part() {
     if( ! PND\pubnews_get_customizer_option( 'bottom_footer_social_option' ) ) return;
     ?>
        <div class="social-icons-wrap">
           <?php pubnews_customizer_social_icons(); ?>
        </div>
     <?php
   }
   add_action( 'pubnews_botttom_footer_hook', 'pubnews_botttom_footer_social_part', 10 );
endif;

 if( ! function_exists( 'pubnews_bottom_footer_menu_part' ) ) :
      /**
       * Bottom Footer menu element
       * 
       * @since 1.0.0
       */
      function pubnews_bottom_footer_menu_part() {
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
      add_action( 'pubnews_botttom_footer_hook', 'pubnews_bottom_footer_menu_part',20 );
 endif;

 if( ! function_exists( 'pubnews_bottom_footer_copyright_part' ) ) :
   /**
    * Bottom Footer copyright element
    * 
    * @since 1.0.0
    */
   function pubnews_bottom_footer_copyright_part() {
      $bottom_footer_site_info = PND\pubnews_get_customizer_option( 'bottom_footer_site_info' );
      if( ! $bottom_footer_site_info ) return;
      ?>
      <div class="site-info">
            <?php 
               echo wp_kses_post( str_replace( '%year%', date('Y'), $bottom_footer_site_info ) ); 
               echo sprintf( esc_html( 'Developed By %s.', 'pubnews' ), '<a href="https://blazethemes.com/">' .esc_html( 'BlazeThemes' ). '</a>'  );
            ?>
      </div>
      <?php
   }
   add_action( 'pubnews_botttom_footer_hook', 'pubnews_bottom_footer_copyright_part', 30 );
endif;

if( ! function_exists( 'pubnews_bottom_footer_inner_wrapper_open' ) ) :
   /**
    * Bottom Footer inner wrapper open
    * 
    * @since 1.0.0
    */
   function pubnews_bottom_footer_inner_wrapper_open() {
      ?>
         <div class="bottom-inner-wrapper">
      <?php
   }
   add_action( 'pubnews_botttom_footer_hook', 'pubnews_bottom_footer_inner_wrapper_open', 15 );
endif;

if( ! function_exists( 'pubnews_bottom_footer_inner_wrapper_close' ) ) :
   /**
    * Bottom Footer inner wrapper close
    * 
    * @since 1.0.0
    */
   function pubnews_bottom_footer_inner_wrapper_close() {
      ?>
         </div><!-- .bottom-inner-wrapper -->
      <?php
   }
   add_action( 'pubnews_botttom_footer_hook', 'pubnews_bottom_footer_inner_wrapper_close', 40 );
endif;