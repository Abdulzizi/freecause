<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output one post from Grid listing.
 *
 * (!) Should be called in WP_Query fetching loop only.
 * @link https://codex.wordpress.org/Class_Reference/WP_Query#Standard_Loop
 *
 * @var $post_classes string CSS classes
 *
 * @action Before the template: 'us_before_template:templates/grid/listing-post'
 * @action After the template: 'us_after_template:templates/grid/listing-post'
 * @filter Template variables: 'us_template_vars:templates/grid/listing-post'
 */

$postID = get_the_ID();

$post_atts = array(
	'class' => 'w-grid-item',
	'data-id' => $postID,
);

// Add items appearance animation on loading
// TODO: add animation preview for US Builder
if ( $load_animation !== 'none' AND ! us_amp() AND ! usb_is_preview_page() ) {
	$post_atts['class'] .= ' us_animate_' . $load_animation;

	// We need to hide CSS animation before isotope.js initialization
	if ( $type === 'masonry' AND $columns > 1 ) {
		$post_atts['class'] .= ' off_autostart';
	}

	// Set "animation-delay" for every doubled amount of columns
	if ( $columns > 1 ) {
		global $us_grid_item_counter;
		$post_atts['style'] = sprintf( 'animation-delay:%ss', 0.1 * $us_grid_item_counter );

		// Calcualte columns factor for better population on single screen
		if ( $columns >= 7 ) {
			$columns_factor = 4;
		} elseif ( $columns >= 5 ) {
			$columns_factor = 3;
		} else {
			$columns_factor = 2;
		}

		if ( ( $us_grid_item_counter + 1 ) < $columns * $columns_factor ) {
			$us_grid_item_counter ++;
		} else {
			$us_grid_item_counter = 0;
		}
	}
}

// Size class for Portfolio Pages
if (
	! us_amp()
	AND ! $ignore_items_size
	AND $type != 'carousel'
	AND get_post_meta( $postID, 'us_tile_size', TRUE ) !== ''
) {
	$post_atts['class'] .= ' size_' . get_post_meta( $postID, 'us_tile_size', TRUE );
}

// Generate background property based on image and color
$bg_img_source = us_arr_path( $grid_layout_settings, 'default.options.bg_img_source' );

// Check if image source is set and it's not from Media Library (cause it's set in listing-start.php)
if ( ! empty( $bg_img_source ) AND ! in_array( $bg_img_source, array( 'none', 'media' ) ) ) {

	$bg_file_size = us_arr_path( $grid_layout_settings, 'default.options.bg_file_size', 'full' );

	// Featured image source
	if ( $bg_img_source == 'featured' ) {
		$bg_img_url = wp_get_attachment_image_url( get_post_thumbnail_id(), $bg_file_size );

		// Custom Field image source
	} elseif ( $_img_id = us_get_custom_field( $bg_img_source, FALSE ) ) {
		$bg_img_url = wp_get_attachment_image_url( $_img_id, $bg_file_size );
	}

	// If the image exists, combine it with other background properties
	if ( ! empty( $bg_img_url ) ) {
		$background_value = 'url(' . $bg_img_url . ') ';
		$background_value .= us_arr_path( $grid_layout_settings, 'default.options.bg_img_position' );
		$background_value .= '/';
		$background_value .= us_arr_path( $grid_layout_settings, 'default.options.bg_img_size' );
		$background_value .= ' ';
		$background_value .= us_arr_path( $grid_layout_settings, 'default.options.bg_img_repeat' );

		$bg_color = get_post_meta( $postID, 'us_tile_bg_color', TRUE )
			? get_post_meta( $postID, 'us_tile_bg_color', TRUE )
			: us_arr_path( $grid_layout_settings, 'default.options.color_bg' );

		$bg_color = us_get_color( $bg_color, /* Gradient */ TRUE );

		// If the color value contains gradient, add comma for correct appearance
		if ( strpos( $bg_color, 'gradient' ) !== FALSE ) {
			$background_value .= ',';
		}
		$background_value .= ' ' . $bg_color;
	}
}

if ( empty( $background_value ) ) {
	$background_value = get_post_meta( $postID, 'us_tile_bg_color', TRUE );
	$background_value = us_get_color( $background_value, /* Gradient */ TRUE );
}

$color_value = get_post_meta( $postID, 'us_tile_text_color', TRUE );

// Custom colors for Portfolio Pages
$inline_css = us_prepare_inline_css(
	array(
		'background' => $background_value,
		'color' => us_get_color( $color_value ),
	)
);

// Generate Overriding Link semantics to the whole grid item
$link_atts = array(
	'class' => 'w-grid-item-anchor',
);

// If overriding link for the whole grid item is set, check other params and the link tag
if ( in_array( $overriding_link, array( 'post', 'popup_post', 'popup_post_image' ) ) ) {

	// Force custom link from metabox option
	if (
		$tile_custom_link = json_decode( get_post_meta( $postID, 'us_tile_link', TRUE ), TRUE )
		AND ! empty( $tile_custom_link['url'] )
	) {
		$post_atts['class'] .= ' custom-link';
		$link_atts['href'] = $tile_custom_link['url'];

		if ( ! empty( $tile_custom_link['target'] ) ) {
			$link_atts['target'] = '_blank';
			$link_atts['rel'] = 'noopener';

			// If link to an image, open it in a popup
		} elseif ( ! us_amp() AND preg_match( "/\.(bmp|gif|jpeg|jpg|png)$/i", $link_atts['href'] ) ) {
			$link_atts['ref'] = 'magnificPopupGrid';
		}

		// Post image in popup
	} elseif ( $overriding_link == 'popup_post_image' ) {
		$tnail_id = get_post_thumbnail_id();

		if ( $link_atts['href'] = wp_get_attachment_image_url( $tnail_id, 'full' ) ) {
			if ( ! us_amp() ) {
				$link_atts['ref'] = 'magnificPopupGrid';
			}

			// Use the Caption as a title
			$attachment = get_post( $tnail_id );
			$link_atts['title'] = trim( strip_tags( $attachment->post_excerpt ) );

			// If it's empty, use the Alt
			if ( empty( $link_atts['title'] ) ) {
				$link_atts['title'] = trim( strip_tags( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', TRUE ) ) );
			}

			// If it's empty, use the Title
			if ( empty( $link_atts['title'] ) ) {
				$link_atts['title'] = trim( strip_tags( get_the_title() ) );
			}
		}

	} else {
		$link_atts['href'] = apply_filters( 'the_permalink', get_permalink() );
		$link_atts['rel'] = 'bookmark';
	}

	// Force opening in a new tab for "Link" post format
	if ( get_post_format() == 'link' ) {
		$link_atts['target'] = '_blank';
		$link_atts['rel'] = 'noopener';
	}

}

// Add aria-label if "title" attribute is absent for accessibility support
if ( empty( $link_atts['title'] ) ) {
	$link_atts['aria-label'] = strip_tags( get_the_title() );
}

// Apply theme filter
$post_atts['class'] = apply_filters( 'us_grid_item_classes', $post_atts['class'], get_the_id() );

// Append WP post classes
$post_atts['class'] .= ' ' . implode( ' ', get_post_class() );

ob_start();
?>
	<article<?= us_implode_atts( $post_atts ) ?>>
		<div class="w-grid-item-h"<?= $inline_css ?>>
			<?php if ( ! empty( $link_atts['href'] ) ): ?>
				<a<?= us_implode_atts( $link_atts ) ?>></a>
			<?php endif; ?>
			<?php us_output_builder_elms( $grid_layout_settings, 'default', 'middle_center', 'grid', 'post' ); ?>
		</div>
	</article>
<?php
echo apply_filters( 'us_grid_listing_post', ob_get_clean() );
