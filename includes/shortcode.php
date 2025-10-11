<?php
/**
 * Shortcode functionality for Ekwa Slider.
 *
 * @package EkwaSlider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect if current request is mobile.
 *
 * @return bool
 */
function ekwa_slider_is_mobile() {
	return wp_is_mobile();
}

/**
 * Get slider settings from options.
 *
 * @return array
 */
function ekwa_slider_get_settings() {
	return get_option( 'ekwa_slider_settings', [
		'mobile_banner_enabled' => false,
		'mobile_banner_post_id' => 0,
		'transition_style' => 'fade',
		'show_arrows' => true,
		'show_dots' => true,
		'arrow_style' => 'default',
	] );
}

/**
 * Get slides for display based on mobile settings.
 *
 * @return array Array of slide post objects.
 */
function ekwa_slider_get_slides() {
	$settings = ekwa_slider_get_settings();
	$is_mobile = ekwa_slider_is_mobile();

	// If mobile and mobile banner is enabled, return only the selected post
	if ( $is_mobile && $settings['mobile_banner_enabled'] && $settings['mobile_banner_post_id'] ) {
		$mobile_post = get_post( $settings['mobile_banner_post_id'] );
		if ( $mobile_post && 'ekwa_slide' === $mobile_post->post_type && 'publish' === $mobile_post->post_status ) {
			return [ $mobile_post ];
		}
	}

	// Default: get all published slides
	$slides = get_posts([
		'post_type'      => 'ekwa_slide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]);

	return $slides;
}

/**
 * Render the slider shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ekwa_slider_shortcode( $atts = [] ) {
	$atts = shortcode_atts([
		'class' => '',
	], $atts, 'ekwa_slider' );

	$slides = ekwa_slider_get_slides();

	if ( empty( $slides ) ) {
		return '<div class="ekwa-slider-empty">' . esc_html__( 'No slides available.', 'ekwa-slider' ) . '</div>';
	}

	$is_mobile = ekwa_slider_is_mobile();
	$settings = ekwa_slider_get_settings();
	$is_mobile_banner = $is_mobile && $settings['mobile_banner_enabled'] && count( $slides ) === 1;

	$wrapper_classes = [ 'ekwa-slider' ];
	if ( $is_mobile_banner ) {
		$wrapper_classes[] = 'ekwa-slider-mobile-banner';
	}
	if ( ! empty( $atts['class'] ) ) {
		$wrapper_classes[] = sanitize_html_class( $atts['class'] );
	}

	// Add transition style class
	$transition_style = isset( $settings['transition_style'] ) ? $settings['transition_style'] : 'fade';
	$wrapper_classes[] = 'transition-' . sanitize_html_class( $transition_style );

	// Get navigation settings
	$show_arrows = isset( $settings['show_arrows'] ) ? $settings['show_arrows'] : true;
	$show_dots = isset( $settings['show_dots'] ) ? $settings['show_dots'] : true;
	$arrow_style = isset( $settings['arrow_style'] ) ? $settings['arrow_style'] : 'default';

	// Add arrow style class
	$wrapper_classes[] = 'arrow-style-' . sanitize_html_class( $arrow_style );

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-slides-count="<?php echo count( $slides ); ?>" data-transition="<?php echo esc_attr( $transition_style ); ?>" data-arrow-style="<?php echo esc_attr( $arrow_style ); ?>">
		<div class="ekwa-slider-container">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<div class="ekwa-slider-slide" data-slide="<?php echo $index; ?>" <?php echo $index === 0 ? '' : 'style="display:none;"'; ?>>
					<?php echo apply_filters( 'the_content', $slide->post_content ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! $is_mobile_banner && count( $slides ) > 1 ) : ?>
			<?php if ( $show_arrows ) : ?>
				<div class="ekwa-slider-controls">
					<button class="ekwa-slider-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'ekwa-slider' ); ?>">
						<span class="arrow-icon"></span>
					</button>
					<button class="ekwa-slider-next" aria-label="<?php esc_attr_e( 'Next slide', 'ekwa-slider' ); ?>">
						<span class="arrow-icon"></span>
					</button>
				</div>
			<?php endif; ?>
			<?php if ( $show_dots ) : ?>
				<div class="ekwa-slider-dots">
					<?php for ( $i = 0; $i < count( $slides ); $i++ ) : ?>
						<button class="ekwa-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>" aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'ekwa-slider' ), $i + 1 ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ekwa_slider', 'ekwa_slider_shortcode' );

/**
 * Enqueue frontend assets for slider.
 */
function ekwa_slider_enqueue_frontend_assets() {
	if ( ! has_shortcode( get_post()->post_content ?? '', 'ekwa_slider' ) && ! is_home() && ! is_front_page() ) {
		return; // Only load when shortcode is used or on home/front page
	}

	wp_enqueue_style(
		'ekwa-slider-frontend',
		EKWA_SLIDER_URL . 'assets/css/slider-frontend.css',
		[],
		EKWA_SLIDER_VERSION
	);

	wp_enqueue_script(
		'ekwa-slider-frontend',
		EKWA_SLIDER_URL . 'assets/js/slider-frontend.js',
		[],
		EKWA_SLIDER_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ekwa_slider_enqueue_frontend_assets' );