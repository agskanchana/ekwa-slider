<?php
/**
 * Register Ekwa Slider Gutenberg Block.
 *
 * @package EkwaSlider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render callback for slider block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function ekwa_slider_render_block( $attributes ) {
	$class = isset( $attributes['className'] ) ? $attributes['className'] : '';

	// Ensure frontend assets are enqueued for block preview
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

	// Use the existing shortcode with optional custom class
	return ekwa_slider_shortcode( [ 'class' => $class ] );
}

/**
 * Register the Ekwa Slider block.
 */
function ekwa_slider_register_slider_block() {
	// Register block editor script
	wp_register_script(
		'ekwa-slider-block',
		EKWA_SLIDER_URL . 'assets/js/slider-block.js',
		[
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-block-editor',
			'wp-components',
			'wp-server-side-render',
		],
		EKWA_SLIDER_VERSION,
		true
	);

	// Register block editor styles (includes frontend styles for preview)
	wp_register_style(
		'ekwa-slider-block-editor',
		EKWA_SLIDER_URL . 'assets/css/slider-block-editor.css',
		[ 'wp-edit-blocks' ],
		EKWA_SLIDER_VERSION
	);

	// Register frontend styles for editor preview
	wp_register_style(
		'ekwa-slider-frontend',
		EKWA_SLIDER_URL . 'assets/css/slider-frontend.css',
		[],
		EKWA_SLIDER_VERSION
	);

	// Register the block
	register_block_type(
		'ekwa/slider',
		[
			'editor_script'   => 'ekwa-slider-block',
			'editor_style'    => [ 'ekwa-slider-block-editor', 'ekwa-slider-frontend' ],
			'render_callback' => 'ekwa_slider_render_block',
			'attributes'      => [
				'className' => [
					'type'    => 'string',
					'default' => '',
				],
				'align' => [
					'type'    => 'string',
					'default' => '',
				],
			],
		]
	);
}
add_action( 'init', 'ekwa_slider_register_slider_block' );

/**
 * Enqueue frontend JS in block editor for preview functionality.
 */
function ekwa_slider_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'ekwa-slider-frontend-editor',
		EKWA_SLIDER_URL . 'assets/js/slider-frontend.js',
		[],
		EKWA_SLIDER_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'ekwa_slider_enqueue_block_editor_assets' );
