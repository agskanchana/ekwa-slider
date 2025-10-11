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

	// Build CSS classes for animation
	$css_classes = [ 'ekwa-slider-content-block' ];

	if ( ! empty( $animation_type ) ) {
		// Add animate.css classes
		$css_classes[] = 'animate__animated';
		$css_classes[] = 'animate__' . sanitize_html_class( $animation_type );
	}

	// Build inline style for animation delay
	$inline_style = '';
	if ( $animation_delay > 0 ) {
		$delay_seconds = $animation_delay / 1000;
		$inline_style = ' style="animation-delay: ' . esc_attr( $delay_seconds ) . 's; -webkit-animation-delay: ' . esc_attr( $delay_seconds ) . 's;"';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>"<?php echo $inline_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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

	// Note: Animate.css animations are now included in slider-frontend.css

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