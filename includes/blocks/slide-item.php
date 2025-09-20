<?php
/**
 * Register Slide Item dynamic block.
 *
 * @package EkwaSlider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render callback for slide item block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Inner blocks content.
 * @return string
 */
function ekwa_slider_render_slide_item_block( $attributes, $content ) {
	$desktop_id = isset( $attributes['desktopImageId'] ) ? (int) $attributes['desktopImageId'] : 0;
	$mobile_id  = isset( $attributes['mobileImageId'] ) ? (int) $attributes['mobileImageId'] : 0;

	$desktop_attachment = $desktop_id ? get_post( $desktop_id ) : null;
	$mobile_attachment  = $mobile_id ? get_post( $mobile_id ) : null;
	$desktop_src = $desktop_id ? wp_get_attachment_image_src( $desktop_id, 'full' ) : null;
	$mobile_src  = $mobile_id ? wp_get_attachment_image_src( $mobile_id, 'full' ) : null;

	// Basic required check – if missing required images, return comment to assist editors.
	if ( ! $desktop_src || ! $mobile_src ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<div class="ekwa-slide-item-missing">' . esc_html__( 'Required images missing (desktop and/or mobile).', 'ekwa-slider' ) . '</div>';
		}
		return ''; // Front-end: hide if incomplete.
	}

	$desktop_url = esc_url( $desktop_src[0] );
	$mobile_url  = esc_url( $mobile_src[0] );
	$desktop_alt = $desktop_attachment ? get_post_meta( $desktop_id, '_wp_attachment_image_alt', true ) : '';
	$mobile_alt  = $mobile_attachment ? get_post_meta( $mobile_id, '_wp_attachment_image_alt', true ) : '';

	// Use desktop alt as fallback, or generic text if both empty
	$alt_text = ! empty( $desktop_alt ) ? $desktop_alt : ( ! empty( $mobile_alt ) ? $mobile_alt : __( 'Slide image', 'ekwa-slider' ) );

	// Process the saved InnerBlocks content
	$inner = ! empty( $content ) ? $content : '';

	ob_start();
	?>
	<div class="ekwa-slide-item" data-desktop="<?php echo $desktop_url; ?>" data-mobile="<?php echo $mobile_url; ?>">
		<div class="ekwa-slide-item-background">
			<picture>
				<source media="(max-width: 767px)" srcset="<?php echo $mobile_url; ?>" />
				<img src="<?php echo $desktop_url; ?>" alt="<?php echo esc_attr( $alt_text ); ?>" loading="lazy" />
			</picture>
		</div>
		<div class="ekwa-slide-item-content">
			<?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Register block assets and block type.
 */

function ekwa_slider_register_blocks() {
       $handle = 'ekwa-slide-item-block';
       $js_url = EKWA_SLIDER_URL . 'assets/js/slide-item-block.js';
       $css_handle = 'ekwa-slide-item-block-css';
       $css_url = EKWA_SLIDER_URL . 'assets/css/slide-item-block.css';

       wp_register_script(
	       $handle,
	       $js_url,
	       [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-data' ],
	       EKWA_SLIDER_VERSION,
	       true
       );
       wp_register_style(
	       $css_handle,
	       $css_url,
	       [],
	       EKWA_SLIDER_VERSION
       );

       register_block_type( 'ekwa/slide-item', [
	       'editor_script'   => $handle,
	       'editor_style'    => $css_handle,
	       'render_callback' => 'ekwa_slider_render_slide_item_block',
	       'attributes'      => [
		       'desktopImageId' => [ 'type' => 'number' ],
		       'mobileImageId'  => [ 'type' => 'number' ],
	       ],
	       'supports'        => [
		       'html' => false,
	       ],
       ] );
}
add_action( 'init', 'ekwa_slider_register_blocks' );
