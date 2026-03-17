<?php
/**
 * Handles the functionality required for the theme
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;

if ( ! function_exists( 'pubnews_typography_value' ) ) :
	/**
	 * Adds two typography parameter
	 *
	 * @echo html markup attributes
	 */
	function pubnews_typography_value( $id ) {
		$typo = PND\pubnews_get_customizer_option( $id );
		$font_family = $typo['font_family']['value'];
		$font_weight = $typo['font_weight']['value'];
		$typo_value = $font_family.":".$font_weight;
		return apply_filters( 'pubnews_combined_typo', $typo_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	add_filter( 'pubnews_typo_combine_filter', 'pubnews_typography_value', 10, 1 );
endif;

if ( ! function_exists( 'pubnews_schema_body_attributes' ) ) :
	/**
	 * Adds schema tags to the body tag.
	 *
	 * @echo html markup attributes
	 */
	function pubnews_schema_body_attributes() {
		$site_schema_ready = PND\pubnews_get_customizer_option( 'site_schema_ready' );
		if( ! $site_schema_ready ) return;
		$is_blog = ( is_home() || is_archive() || is_attachment() || is_tax() || is_single() );
		$itemtype = 'WebPage'; // default itemtype
		$itemtype = ( $is_blog ) ? 'Blog' : $itemtype; // itemtype for blog page
		$itemtype = ( is_search() ) ? 'SearchResultsPage' : $itemtype; // itemtype for earch results page
		$itemtype_final = apply_filters( 'pubnews_schema_body_attributes_itemtype', $itemtype ); // itemtype
		echo apply_filters( 'pubnews_schema_body_attributes', "itemtype='https://schema.org/" . esc_attr( $itemtype_final ) . "' itemscope='itemscope'" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'pubnews_schema_article_attributes' ) ) :
	/**
	 * Adds schema tags to the article tag.
	 *
	 * @echo html markup attributes
	 */
	function pubnews_schema_article_attributes() {
		$site_schema_ready = PND\pubnews_get_customizer_option( 'site_schema_ready' );
		if( ! $site_schema_ready ) return;
		$itemtype = 'Article'; // default itemtype.
		$itemtype_final = apply_filters( 'pubnews_schema_article_attributes_itemtype', $itemtype ); // itemtype
		echo apply_filters( 'pubnews_schema_article_attributes', "itemtype='https://schema.org/" . esc_attr( $itemtype_final ) . "' itemscope='itemscope'" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'pubnews_schema_article_name_attributes' ) ) :
	/**
	 * Adds schema tags to the article name tag.
	 *
	 * @echo html markup attributes
	 */
	function pubnews_schema_article_name_attributes() {
		$site_schema_ready = PND\pubnews_get_customizer_option( 'site_schema_ready' );
		if( ! $site_schema_ready ) return;
		$itemprop = 'name'; // default itemprop.
		$itemprop_final = apply_filters( 'pubnews_schema_article_name_attributes_itemprop', $itemprop ); // itemprop
		return apply_filters( 'pubnews_schema_article_name_attributes', "itemprop='" . esc_attr( $itemprop_final ) . "'" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'pubnews_schema_article_body_attributes' ) ) :
	/**
	 * Adds schema tags to the article body tag.
	 *
	 * 
	 * @echo html markup attributes
	 */
	function pubnews_schema_article_body_attributes() {
		$site_schema_ready = PND\pubnews_get_customizer_option( 'site_schema_ready' );
		if( ! $site_schema_ready ) return;
		$itemprop = 'articleBody'; // default itemprop.
		$itemprop_final = apply_filters( 'pubnews_schema_article_body_attributes_itemprop', $itemprop ); // itemprop
		echo apply_filters( 'pubnews_schema_article_body_attributes', "itemprop='" . esc_attr( $itemprop_final ) . "'" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if( ! function_exists( 'pubnews_compare_wand' ) ) :
	/**
	 * Compares parameter valaues
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_compare_wand($params) {
		$returnval = true;
		foreach($params as $val) {
			if( ! $val ) {
				$returnval = false;
				break;
			}
		}
		return $returnval;
	}
endif;

if( ! function_exists( 'pubnews_function_exists' ) ) :
	/**
	 * Checks exists
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_function_exists($function) {
		if( function_exists( $function ) ) return true;
		return;
	}
endif;

if( ! function_exists( 'pubnews_get_date_format_array_args' ) ) :
	/**
	 * Generate date format array for query arguments
	 * 
	 * @package Pubnews
	 * @since 1.0.0
	 */
	function pubnews_get_date_format_array_args($date_key) {
		switch($date_key) {
			case 'today': $todayDate = getdate();
							return array(
								'year'  => $todayDate['year'],
								'month' => $todayDate['mon'],
								'day'   => $todayDate['mday'],
							);
						break;
			case 'this-week': return array(
								'year'  => date( 'Y' ),
								'week'  => date( 'W' )
							);
						break;
			case 'last-seven-days': return array(
							'after'  => '1 week ago'
						);
					break;
			case 'this-month': $todayDate = getdate();
							return array(
								'month' => $todayDate['mon']
							);
						break;
			case 'last-month': 
						$thisdate = getdate();
						if ($thisdate['mon'] != 1) :
							$lastmonth = $thisdate['mon'] - 1;
						else : 
							$lastmonth = 12;
						endif; 
						$thisyear = date('Y');
						if ($lastmonth != 12) :
							$thisyear = date('Y');
						else: 
							$thisyear = date('Y') - 1;
						endif;
							return array(
								'year'  => $thisyear,
								'month'  => $lastmonth
							);
						break;
			case 'last-week':
						$thisweek = date('W');
						if ($thisweek != 1) :
							$lastweek = $thisweek - 1;
						else : 
							$lastweek = 52;
						endif; 
						$thisyear = date('Y');
						if ($lastweek != 52) :
							$thisyear = date('Y');
						else: 
							$thisyear = date('Y') -1; 
						endif;
						return array(
							'year'  => $thisyear,
							'week'  => $lastweek
						);
				break;
			default: return [];
		}
	}
endif;

if( ! function_exists( 'pubnews_is_elementor_activated' ) ) :
	/**
	 * check if elementor is active
	 * 
	 * @package Pubnews
	 * @since  1.0.0
	 */
	function pubnews_is_elementor_activated( $id ) {
		if( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		if ( version_compare( ELEMENTOR_VERSION, '1.5.0', '<' ) ) {
			return ( 'builder' === Elementor\Plugin::$instance->db->get_edit_mode( $id ) );
		} else {
			$document = Elementor\Plugin::$instance->documents->get( $id );
			if ( $document ) {
				return $document->is_built_with_elementor();
			} else {
				return false;
			}
		}
	}
endif;

if( ! function_exists( 'pubnews_numbering_with_pad_format' ) ) :
	/**
	 * convert single digit number to double digit
	 * 
	 * @package Pubnews
	 * @since  1.0.0
	 */
	function pubnews_numbering_with_pad_format( $number ) {
		if( $number > 9 ) return apply_filters( 'pubnews_numbering_with_pad_format_filter', $number );
		$number = str_pad($number, 2, '0', STR_PAD_LEFT);
		return apply_filters( 'pubnews_numbering_with_pad_format_filter', $number );
	}
endif;

if( !function_exists( 'pubnews_get_customizer_sidebar_array' ) ) :
    /**
     * Gets customizer "sidebar layouts" array
     * 
     * @package Pubnews
     * @since 1.0.0
     * 
     */
    function pubnews_get_customizer_sidebar_array() {
        return array(
            'no-sidebar' => array(
                'label' => esc_html__( 'No Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/no_sidebar.jpg'
            ),
            'left-sidebar' => array(
                'label' => esc_html__( 'Left Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/left_sidebar.jpg'
            ),
            'right-sidebar' => array(
                'label' => esc_html__( 'Right Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/right_sidebar.jpg'
            ),
            'both-sidebar' => array(
                'label' => esc_html__( 'Both Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/both_sidebar.jpg'
            ),
            'left-both-sidebar' => array(
                'label' => esc_html__( 'Left Both Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/left_both_sidebar.jpg'
            ),
            'right-both-sidebar' => array(
                'label' => esc_html__( 'Right Both Sidebar', 'pubnews' ),
                'url'   => '%s/assets/images/customizer/right_both_sidebar.jpg'
            )
        );
    }
 endif;

 /**
  * Checks if the page url is occupied by theme query params 
  *
  * @package Pubnews
  * @since 1.0.0
  */
  function pubnews_get_block_animation_attr($delay = 100) {
	return apply_filters( 'pubnews_get_block_animation_attr_filter', 'data-aos=fade-up data-aos-delay=' . $delay );
  }

/**
 * Checks if the the post exists or returns site url 
*
* @package Pubnews
* @since 1.0.0
*/
function pubnews_get_random_news_url() {
	$random_news = get_posts( apply_filters( 'pubnews_query_args_filter',  [
		'numberposts' => 1,
		'post_type' => 'post',
		'ignore_sticky_posts'    => true,
		'orderby' => 'rand'
	]));
	$url = ($random_news) ? get_the_permalink($random_news[0]->ID ) : home_url();
	return apply_filters( 'pubnews_get_random_news_url_filter', esc_url($url) );
}

 /**
  * Checks if the page url is occupied by theme query params 
  *
  * @package Pubnews
  * @since 1.0.0
  */
 function pubnews_is_paged_filtered() {
	if( ! isset( $_GET['pubnewsargs'] ) ) return;
	return true;
 }

 /**
  * Returns the width layout value of each section with given param 
  *
  * @package Pubnews
  * @since 1.0.0
  */
  function pubnews_get_section_width_layout_val($control = '') {
	if( $control ) :
		$single_control_value = PND\pubnews_get_customizer_option( $control );
		$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
		return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
	else :
		if( is_404() ) {
			$single_control_value = PND\pubnews_get_customizer_option( 'error_page_width_layout' );
			$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
			return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
		} else if( is_search() ) {
			$single_control_value = PND\pubnews_get_customizer_option( 'search_page_width_layout' );
			$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
			return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
		} else if( is_page() ) {
			$single_control_value = PND\pubnews_get_customizer_option( 'single_page_width_layout' );
			$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
			return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
		} else if( is_single() ) {
			$single_control_value = PND\pubnews_get_customizer_option( 'single_post_width_layout' );
			$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
			return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
		} else if( is_archive() || is_home() ) {
			$single_control_value = PND\pubnews_get_customizer_option( 'archive_width_layout' );
			$control_val = ( $single_control_value == 'global' ) ? PND\pubnews_get_customizer_option( 'website_content_layout' ) : $single_control_value;
			return apply_filters( 'pubnews_width_layout_filter', esc_html( $control_val ) );
		}
	endif;
 }