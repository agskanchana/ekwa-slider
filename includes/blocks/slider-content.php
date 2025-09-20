<?php
/**
 * Register Slider Content inner block.
 *
 * @package EkwaSlider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render callback for slider content block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Inner blocks content.
 * @return string
 */
function ekwa_slider_render_slider_content_block( $attributes, $content ) {
	$animation_delay = isset( $attributes['animationDelay'] ) ? (int) $attributes['animationDelay'] : 0;
	$animation_type  = isset( $attributes['animationType'] ) ? sanitize_text_field( $attributes['animationType'] ) : '';

	// Build data attributes for animation
	$data_attrs = '';
	if ( ! empty( $animation_type ) ) {
		$data_attrs .= ' data-animation="' . esc_attr( $animation_type ) . '"';
	}
	if ( $animation_delay > 0 ) {
		$data_attrs .= ' data-animation-delay="' . esc_attr( $animation_delay ) . '"';
	}

	// CSS classes for animation
	$css_classes = [ 'ekwa-slider-content-block' ];
	if ( ! empty( $animation_type ) ) {
		$css_classes[] = 'has-animation';
		$css_classes[] = 'animation-' . sanitize_html_class( $animation_type );
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>"<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Register slider content block.
 */
function ekwa_slider_register_slider_content_block() {
	$handle = 'ekwa-slider-content-block';
	$js_url = EKWA_SLIDER_URL . 'assets/js/slider-content-block.js';

	wp_register_script(
		$handle,
		$js_url,
		[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-data' ],
		EKWA_SLIDER_VERSION,
		true
	);

	register_block_type( 'ekwa/slider-content', [
		'editor_script'   => $handle,
		'render_callback' => 'ekwa_slider_render_slider_content_block',
		'attributes'      => [
			'animationDelay' => [
				'type' => 'number',
				'default' => 0
			],
			'animationType'  => [
				'type' => 'string',
				'default' => ''
			],
		],
		'supports'        => [
			'html' => false,
		],
	] );
}
add_action( 'init', 'ekwa_slider_register_slider_content_block' );