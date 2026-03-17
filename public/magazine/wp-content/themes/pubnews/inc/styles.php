<?php
/**
 * Includes the inline css
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
if( ! function_exists( 'pubnews_assign_preset_var' ) ) :
   /**
   * Generate css code for top header color options
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_assign_preset_var( $selector, $control) {
         $decoded_control =  PND\pubnews_get_customizer_option( $control );
         if( ! $decoded_control ) return;
         echo " body.pubnews_font_typography{ " . $selector . ": ".esc_html( $decoded_control ).  ";}\n";
   }
endif;

if( ! function_exists( 'pubnews_assign_var' ) ) :
   /**
   * Generate css code for top header color options
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_assign_var( $selector, $control) {
         $decoded_control =  json_decode(PND\pubnews_get_customizer_option( $control ),true);
         if( ! $decoded_control[$decoded_control['type']] ) return;
         echo " body.pubnews_font_typography{ " . $selector . ": ".pubnews_get_color_format($decoded_control[$decoded_control['type']]).  ";}\n";
   }
endif;

if( ! function_exists( 'pubnews_get_background_style' ) ) :
   /**
    * Generate css code for background control.
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_get_background_style( $selector, $control, $var = '' ) {
      $decoded_control = json_decode( PND\pubnews_get_customizer_option( $control ), true );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['type'] ) ) :
         $type = $decoded_control['type'];
         switch( $type ) {
            case 'image' : if( isset( $decoded_control[$type]['media_id'] ) ) echo $selector . " { background-image: url(" .esc_url( wp_get_attachment_url( $decoded_control[$type]['media_id'] ) ). ") }\n";
                  if( isset( $decoded_control['repeat'] ) ) echo $selector . "{ background-repeat: " .esc_html( $decoded_control['repeat'] ). "}\n";
                  if( isset( $decoded_control['position'] ) ) echo $selector . "{ background-position:" .esc_html( $decoded_control['position'] ). "}\n";
                  if( isset( $decoded_control['attachment'] ) ) echo $selector . "{ background-attachment: " .esc_html( $decoded_control['attachment'] ). "}\n";
                  if( isset( $decoded_control['size'] ) ) echo $selector . "{ background-size: " .esc_html( $decoded_control['size'] ). "}\n";
               break;
            default: if( isset( $decoded_control[$type] ) ) echo $selector . "{ background: " .pubnews_get_color_format( $decoded_control[$type] ). "}\n";
         }
      endif;
   }
endif;

if( ! function_exists( 'pubnews_get_background_style_var' ) ) :
   /**
    * Generate css code for background control.
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_get_background_style_var( $selector, $control) {
      $decoded_control = json_decode( PND\pubnews_get_customizer_option( $control ), true );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['type'] ) ) :
         $type = $decoded_control['type'];
         if( isset( $decoded_control[$type] ) ) echo ".pubnews_main_body { ".$selector.": " .pubnews_get_color_format( $decoded_control[$type] ). "}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_get_background_style_responsive' ) ) :
   /**
    * Generate css code for background control.
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_get_background_style_responsive( $selector, $control, $var = '' ) {
      $decoded_control = json_decode( PND\pubnews_get_customizer_option( $control ), true );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['type'] ) ) :
         $type = $decoded_control['type'];
         switch( $type ) {
            case 'image' : if( isset( $decoded_control[$type]['media_id'] ) ) echo $selector . " { background-image: url(" .esc_url( wp_get_attachment_url( $decoded_control[$type]['media_id'] ) ). ") }\n";
                  if( isset( $decoded_control['repeat'] ) ) echo $selector . "{ background-repeat: " .esc_html( $decoded_control['repeat'] ). "}\n";
                  if( isset( $decoded_control['position'] ) ) echo $selector . "{ background-position:" .esc_html( $decoded_control['position'] ). "}\n";
                  if( isset( $decoded_control['attachment'] ) ) echo $selector . "{ background-attachment: " .esc_html( $decoded_control['attachment'] ). "}\n";
                  if( isset( $decoded_control['size'] ) ) echo $selector . "{ background-size: " .esc_html( $decoded_control['size'] ). "}\n";
               break;
            default: if( isset( $decoded_control[$type] ) ) echo "@media(max-width: 768px){ ". $selector . "{ background: " .pubnews_get_color_format( $decoded_control[$type] ). "} }\n";
         }
      endif;
   }
endif;

if( ! function_exists( 'pubnews_get_typo_style' ) ) :
   /**
   * Generate css code for typography control.
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_get_typo_style( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['font_family'] ) ) :
         echo ".pubnews_font_typography { ".$selector."-family : " .esc_html( $decoded_control['font_family']['value'] ).  "; }\n";
      endif;

      if( isset( $decoded_control['font_weight'] ) ) :
         echo ".pubnews_font_typography { ".$selector."-weight : " .esc_html( $decoded_control['font_weight']['value'] ).  "; }\n";
      endif;

      if( isset( $decoded_control['text_transform'] ) ) :
         echo ".pubnews_font_typography { ".$selector."-texttransform : " .esc_html( $decoded_control['text_transform'] ).  "; }\n";
      endif;

      if( isset( $decoded_control['text_decoration'] ) ) :
         echo ".pubnews_font_typography { ".$selector."-textdecoration : " .esc_html( $decoded_control['text_decoration'] ).  "; }\n";
      endif;

      if( isset( $decoded_control['font_size'] ) ) :
         if( isset( $decoded_control['font_size']['desktop'] ) ) echo ".pubnews_font_typography { ".$selector."-size : " .absint( $decoded_control['font_size']['desktop'] ).  "px; }\n";
         if( isset( $decoded_control['font_size']['tablet'] ) ) echo ".pubnews_font_typography { ".$selector."-size-tab : " .absint( $decoded_control['font_size']['tablet'] ).  "px; }\n";
         if( isset( $decoded_control['font_size']['smartphone'] ) ) echo ".pubnews_font_typography { ".$selector."-size-mobile : " .absint( $decoded_control['font_size']['smartphone'] ).  "px; }\n";
      endif;
      if( isset( $decoded_control['line_height'] ) ) :
         if( isset( $decoded_control['line_height']['desktop'] ) ) echo ".pubnews_font_typography { ".$selector."-lineheight : " .absint( $decoded_control['line_height']['desktop'] ).  "px; }\n";
         if( isset( $decoded_control['line_height']['tablet'] ) ) echo ".pubnews_font_typography { ".$selector."-lineheight-tab : " .absint( $decoded_control['line_height']['tablet'] ).  "px; }\n";
         if( isset( $decoded_control['line_height']['smartphone'] ) ) echo ".pubnews_font_typography { ".$selector."-lineheight-mobile : " .absint( $decoded_control['line_height']['smartphone'] ).  "px; }\n";
      endif;
      if( isset( $decoded_control['letter_spacing'] ) ) :
         if( isset( $decoded_control['letter_spacing']['desktop'] ) ) echo ".pubnews_font_typography { ".$selector."-letterspacing : " .absint( $decoded_control['letter_spacing']['desktop'] ).  "px; }\n";
         if( isset( $decoded_control['letter_spacing']['tablet'] ) ) echo ".pubnews_font_typography { ".$selector."-letterspacing-tab : " .absint( $decoded_control['letter_spacing']['tablet'] ).  "px; }\n";
         if( isset( $decoded_control['letter_spacing']['smartphone'] ) ) echo ".pubnews_font_typography { ".$selector."-letterspacing-mobile : " .absint( $decoded_control['letter_spacing']['smartphone'] ).  "px; }\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_site_logo_width_fnc' ) ) :
   /**
   * Generate css code for Logo Width
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_site_logo_width_fnc( $selector, $control, $property = 'width'  ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) ) :
         $desktop = $decoded_control['desktop'];
         echo $selector . "{ " . esc_html( $property ). ": ".esc_html( $desktop ).  "px; }\n";
         endif;
         if( isset( $decoded_control['tablet'] ) ) :
         $tablet = $decoded_control['tablet'];
         echo "@media(max-width: 940px) { " .$selector . "{ " . esc_html( $property ). ": ".esc_html( $tablet ).  "px; } }\n";
         endif;
         if( isset( $decoded_control['smartphone'] ) ) :
         $smartphone = $decoded_control['smartphone'];
         echo "@media(max-width: 610px) { " .$selector . "{ " . esc_html( $property ). ": ".esc_html($smartphone).  "px; } }\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_top_border_color' ) ) :
   /**
    * Generate css code for top header color options
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_top_border_color( $selector, $control, $property = 'border-color' ) {
      $decoded_control = json_decode( PND\pubnews_get_customizer_option( $control ), true );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['type'] ) ) :
         $type = $decoded_control['type'];
         if( isset( $decoded_control[$type] ) ) echo $selector . "{ " . esc_html( $property ). ": ".esc_html( $decoded_control[$type] ).  "}\n";
         if($type == 'solid'){
            echo $selector . "{ border-color: ". pubnews_get_color_format( $decoded_control[$type] ) .";}\n";
            echo $selector . " li{ border-color: ". pubnews_get_color_format( $decoded_control[$type] ) .";}\n";
         }
         if($type == 'gradient') echo $selector . " li{ border: none;}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_color_options_one' ) ) :
   /**
    * Generate css code for Top header Text Color
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_color_options_one( $selector, $control, $property = 'color' ) {
         $decoded_control =  PND\pubnews_get_customizer_option( $control );
         if( ! $decoded_control ) return;
         echo $selector . " { " . esc_html( $property ). ": ".pubnews_get_color_format($decoded_control ).  " }\n";
   }
endif;

if( ! function_exists( 'pubnews_menu_text_color' ) ) :
   /**
    * Generate css code for top header color options
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_text_color_var( $selector, $control) {
      $decoded_control =  PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['color'] ) ) :
         $color = $decoded_control['color'];
         echo ".pubnews_font_typography  { " . $selector . ": ".pubnews_get_color_format($color).  ";}\n";
      endif;
      if( isset( $decoded_control['hover'] ) ) :
         $color_hover = $decoded_control['hover'];
         echo ".pubnews_font_typography  { " . $selector . "-hover : ".pubnews_get_color_format($color_hover).  "; }\n";
      endif;
   }
endif;

if( ! function_exists('pubnews_visibility_options') ):
   /**
    * Generate css code for top header color options
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_visibility_options( $selector, $control ) {
      $decoded_control =  PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) ) :
         if($decoded_control['desktop'] == false) echo $selector . "{ display : none;}\n";
      endif;

      if( isset( $decoded_control['tablet'] ) ) :
         if($decoded_control['tablet'] == false) echo "@media(max-width: 940px) and (min-width:611px) { " .$selector . "{ display : none;} }\n";
      endif;

      if( isset( $decoded_control['mobile'] ) ) :
         if($decoded_control['mobile'] == false) { 
            echo "@media(max-width: 610px) { " .$selector . "{ display : none;} }\n";
         }
         if($decoded_control['mobile'] == true){
            echo "@media(max-width: 610px) { " .$selector . "{ display : block;} }\n";
         }
      endif;
   }
endif;

if( ! function_exists( 'pubnews_border_option' ) ) :
   /**
    * Generate css code for Top header Text Color
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_border_option( $selector, $control, $property="border" ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['type'] ) || isset( $decoded_control['width'] ) || isset( $decoded_control['color'] ) ) :
      echo $selector. "{ ".$property.": ". $decoded_control['width'] ."px ".$decoded_control['type']." ". pubnews_get_color_format($decoded_control['color']) .";}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_theme_color' ) ) :
   /**
    * Generate css code for top header color options
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_theme_color( $selector, $control) {
      $decoded_control =  PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      echo " body.pubnews_main_body{ " . $selector . ": ".pubnews_get_color_format($decoded_control).  ";}\n";
      echo " body.pubnews_dark_mode{ " . $selector . ": ".pubnews_get_color_format($decoded_control).  ";}\n";
   }
endif;

if( ! function_exists( 'pubnews_header_padding' ) ) :
   /**
    * Generate css code for Top header Text Color
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_header_padding( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;

      if( isset( $decoded_control['desktop'] ) ) :
         echo ".pubnews_font_typography { ".$selector.": ". $decoded_control['desktop'] ."px;}\n";
      endif;

      if( isset( $decoded_control['tablet'] ) ) :
         echo " .pubnews_font_typography { ".$selector."-tablet: ". $decoded_control['tablet'] ."px;}\n";
      endif;

      if( isset( $decoded_control['smartphone'] ) ) :
         echo " .pubnews_font_typography { ".$selector."-smartphone: ". $decoded_control['smartphone'] ."px;}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_font_size_style' ) ) :
   /**
    * Generates css code for font size
    *
    * @package Pubnews
    * @since 1.0.0
    */
   function pubnews_font_size_style( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) ) :
         $desktop = $decoded_control['desktop'];
         echo "body { " . $selector . ": ".esc_html( $desktop ).  "px;}\n";
      endif;
      if( isset( $decoded_control['tablet'] ) ) :
         $tablet = $decoded_control['tablet'];
         echo "body { " . $selector . "-tablet: ".esc_html( $tablet ).  "px;}\n";
      endif;
      if( isset( $decoded_control['smartphone'] ) ) :
         $smartphone = $decoded_control['smartphone'];
         echo "body { " . $selector . "-smartphone: ".esc_html( $smartphone ).  "px;}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_category_colors_styles' ) ) :
   /**
    * Generates css code for font size
    *
    * @package Pubnews
    * @since 1.0.0
    */
   function pubnews_category_colors_styles() {
      $totalCats = get_categories();
      if( $totalCats ) :
         foreach( $totalCats as $singleCat ) :
            $category_color = PND\pubnews_get_customizer_option( 'category_' .absint($singleCat->term_id). '_color' );
            echo "body .post-categories .cat-item.cat-" . absint($singleCat->term_id) . " { background-color : " .pubnews_get_color_format( $category_color['color'] ). "}\n";
            echo "body .post-categories .cat-item.cat-" . absint($singleCat->term_id) . ":hover { background-color : " .pubnews_get_color_format( $category_color['hover'] ). "}\n";
            echo "body .pubnews-category-no-bk .post-categories .cat-item.cat-" . absint($singleCat->term_id) . " a { color : " .pubnews_get_color_format( $category_color['color'] ). "}\n";
            echo "body .pubnews-category-no-bk .post-categories .cat-item.cat-" . absint($singleCat->term_id) . " a:hover { color : " .pubnews_get_color_format( $category_color['hover'] ). ";}\n";
         endforeach;
      endif;
   }
endif;

// Image ratio change
if( ! function_exists( 'pubnews_image_ratio' ) ) :
   /**
   * Generate css code for variable change with responsive
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_image_ratio( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      $value = '100%';
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) && $decoded_control['desktop'] > 0 ) :
         $desktop = $decoded_control['desktop'];
         echo $selector . "{ padding-bottom : calc(".esc_html( $desktop ).  " * ". esc_html( $value ) ."); }";
      endif;
      if( isset( $decoded_control['tablet'] ) && $decoded_control['tablet'] > 0 ) :
         $tablet = $decoded_control['tablet'];
         echo "@media(max-width: 940px) { " .$selector . "{ padding-bottom : calc(".esc_html( $tablet ).  "* ". esc_html( $value ) ."); } }\n";
      endif;
      if( isset( $decoded_control['smartphone'] ) && $decoded_control['smartphone'] > 0 ) :
         $smartphone = $decoded_control['smartphone'];
         echo "@media(max-width: 610px) { " .$selector . "{ padding-bottom : calc(".esc_html($smartphone).  " * ". esc_html( $value ) ."); } }\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_image_ratio_variable' ) ) :
   /**
    * Generate css code for variable change with responsive
    *
    * @package Pubnews
    * @since 1.0.0
    */
   function pubnews_image_ratio_variable ( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) && $decoded_control['desktop'] > 0 ) :
         $desktop = $decoded_control['desktop'];
         echo "body { ". $selector . " : ". $desktop ."}\n";
         endif;
         if( isset( $decoded_control['tablet'] ) && $decoded_control['tablet'] > 0 ) :
         $tablet = $decoded_control['tablet'];
         echo "body { " .$selector . "-tab : ". $tablet . " } }\n";
         endif;
         if( isset( $decoded_control['smartphone'] ) && $decoded_control['smartphone'] > 0 ) :
         $smartphone = $decoded_control['smartphone'];
         echo "body { " .$selector . "-mobile : ". $smartphone .  " }\n";
      endif;
   }
endif;

// box shadow
if( ! function_exists( 'pubnews_box_shadow_styles' ) ) :
   /**
    * Generates css code for box shadow
    *
    * @package Pubnews
    * @since 1.0.0
    */
   function pubnews_box_shadow_styles($selector,$value) {
      $pubnews_box_shadow = PND\pubnews_get_customizer_option($value);
      if( $pubnews_box_shadow['option'] == 'none' ) {
         echo $selector."{ box-shadow: 0px 0px 0px 0px;
         }\n";
      } else {
         if( $pubnews_box_shadow['type'] == 'outset') $pubnews_box_shadow['type'] = '';
         $box_shadow_value = esc_html( $pubnews_box_shadow['type'] ) ." ".esc_html( $pubnews_box_shadow['hoffset'] ).  "px ". esc_html( $pubnews_box_shadow['voffset'] ). "px ".esc_html( $pubnews_box_shadow['blur'] ).  "px ".esc_html( $pubnews_box_shadow['spread'] ).  "px ".pubnews_get_color_format( $pubnews_box_shadow['color'] );
         echo $selector."{ box-shadow : " . $box_shadow_value . "; -webkit-box-shadow: ". $box_shadow_value ."; -moz-box-shadow: " . $box_shadow_value . " }\n";
      }
   }
endif;

if( ! function_exists( 'pubnews_simple_range_style' ) ) :
   /**
    * Generates css code for font size
    *
    * @package Pubnews
    * @since 1.0.0
    */
   function pubnews_simple_range_style( $selector, $control ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control ) ) :
         echo "body{ " . $selector . ": ".esc_html( $decoded_control ).  "px;}\n";
      endif;
   }
endif;

if( ! function_exists( 'pubnews_variable_bk_color' ) ) :
   /**
    * Generate css code for bk color
    *
    * @package Pubnews
    * @since 1.0.0 
    */
   function pubnews_variable_bk_color( $selector, $control, $var = '' ) {
      $decoded_control = json_decode( PND\pubnews_get_customizer_option( $control ), true );
      if( ! $decoded_control ) return;
      if(isset($decoded_control['initial'] )):
         if( isset( $decoded_control['initial']['type'] ) ) :
            $type = $decoded_control['initial']['type'];
            if( isset( $decoded_control['initial'][$type] ) ) echo "body { ".$selector.": " .pubnews_get_color_format( $decoded_control['initial'][$type] ). "}\n";
         endif;
      endif;

      if(isset($decoded_control['hover'])):
         if( isset( $decoded_control['hover']['type'] ) ) :
            $type = $decoded_control['hover']['type'];
            if( isset( $decoded_control['hover'][$type] ) ) echo "body { ".$selector."-hover: " .pubnews_get_color_format( $decoded_control['hover'][$type] ). "}\n";
         endif;
      endif;
   }
endif;


// Value change with responsive
if( ! function_exists( 'pubnews_value_change_responsive' ) ) :
   /**
   * Generate css code for variable change with responsive
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_value_change_responsive ( $selector, $control, $property ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) ) :
         $desktop = $decoded_control['desktop'];
         echo $selector . "{ " . esc_html( $property ). ": ".esc_html( $desktop ).  "px; }";
      endif;

      if( isset( $decoded_control['tablet'] ) ) :
         $tablet = $decoded_control['tablet'];
         echo "@media(max-width: 940px) { " .$selector . "{ " . esc_html( $property ). ": ".esc_html( $tablet ).  "px; } }\n";
      endif;
      
      if( isset( $decoded_control['smartphone'] ) ) :
         $smartphone = $decoded_control['smartphone'];
         echo "@media(max-width: 610px) { " .$selector . "{ " . esc_html( $property ). ": ".esc_html($smartphone).  "px; } }\n";
   endif;
   }
endif;

// spacing control
if( ! function_exists( 'pubnews_spacing_control' ) ) :
   /**
    * Generate css code for variable change with responsive for spacing controls
    *
    * @package Pubnews
    * @since 1.0.0
    */
    function pubnews_spacing_control( $selector, $control, $property ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      if( isset( $decoded_control['desktop'] ) ) :
         $desktop = $decoded_control['desktop'];
         echo $selector . '{ '. esc_html( $property ) .' : '. esc_html( $desktop['top'] ) .'px '. esc_html( $desktop['right'] ) .'px '. esc_html( $desktop['bottom'] ) .'px '. esc_html( $desktop['left'] ) .'px }';
      endif;

      if( isset( $decoded_control['tablet'] ) ) :
         $tablet = $decoded_control['tablet'];
         echo '@media(max-width: 940px) {' .$selector . '{ '. esc_html( $property ) .' : '. esc_html( $tablet['top'] ) .'px '. esc_html( $tablet['right'] ) .'px '. esc_html( $tablet['bottom'] ) .'px '. esc_html( $tablet['left'] ) .'px } }';
      endif;

      if( isset( $decoded_control['smartphone'] ) ) :
         $smartphone = $decoded_control['smartphone'];
         echo '@media(max-width: 610px) { ' . $selector . '{ '. esc_html( $property ) .' : '. esc_html( $smartphone['top'] ) .'px '. esc_html( $smartphone['right'] ) .'px '. esc_html( $smartphone['bottom'] ) .'px '. esc_html( $smartphone['left'] ) .'px } }';
      endif;
    }
endif;

// Value change with responsive
if( ! function_exists( 'pubnews_color_value_change_responsive' ) ) :
   /**
   * Generate css code for variable change with responsive
   *
   * @package Pubnews
   * @since 1.0.0 
   */
   function pubnews_color_value_change_responsive ( $selector, $control, $property = 'color' ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! $decoded_control ) return;
      
      if( isset( $decoded_control ) ) :
         echo "@media(max-width: 769px) { " .$selector . "{ " . esc_html( $property ). ": ".esc_html( pubnews_get_color_format( $decoded_control ) ).  "; } }\n";
   endif;
   }
endif;

// preset colors
if( ! function_exists( 'pubnews_preset_color_styles' ) ) :
   /**
    * Generate css code for preset colors
    * MARK: PRESET COLORS
    *
    * @since 1.0.0
    */
    function pubnews_preset_color_styles( $control, $variable ) {
      $decoded_control = PND\pubnews_get_customizer_option( $control );
      if( ! empty( $decoded_control ) && is_array( $decoded_control ) ) :
         foreach( $decoded_control as $index => $color ) :
            $count = ( $index + 1 );
            echo " body.pubnews_font_typography{ " . $variable . $count . ": ". pubnews_get_color_format( $color ).  ";}\n";
         endforeach;
      endif;
    }
endif;