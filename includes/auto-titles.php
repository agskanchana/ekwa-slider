<?php
/**
 * Auto-generate sequential titles for Slides when title support is removed.
 *
 * @package EkwaSlider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EKWA_SLIDER_NEXT_TITLE_OPTION = 'ekwa_slider_next_slide_number';

/**
 * Ensure the next slide number option exists.
 */
function ekwa_slider_ensure_next_number_option() {
	if ( false === get_option( EKWA_SLIDER_NEXT_TITLE_OPTION ) ) {
		// Initialize based on current count of posts to avoid collisions if existing slides exist.
		$count = wp_count_posts( 'ekwa_slide' );
		$published = 0;
		if ( $count && isset( $count->publish ) ) {
			$published = (int) $count->publish;
		}
		update_option( EKWA_SLIDER_NEXT_TITLE_OPTION, $published + 1 );
	}
}
add_action( 'init', 'ekwa_slider_ensure_next_number_option', 20 );

/**
 * Generate the next auto title.
 *
 * @return string
 */
function ekwa_slider_generate_next_title() {
	$next = (int) get_option( EKWA_SLIDER_NEXT_TITLE_OPTION, 1 );
	$title = sprintf( __( 'Slide %d', 'ekwa-slider' ), $next );
	update_option( EKWA_SLIDER_NEXT_TITLE_OPTION, $next + 1 );
	return $title;
}

/**
 * Assign an auto title before insert if missing.
 */
function ekwa_slider_auto_title( $data, $postarr ) {
	if ( 'ekwa_slide' === $data['post_type'] ) {
		// If title empty or default auto-draft states.
		$needs_title = empty( $data['post_title'] ) || 'Auto Draft' === $data['post_title'];
		if ( $needs_title ) {
			$data['post_title'] = ekwa_slider_generate_next_title();
		}
	}
	return $data;
}
add_filter( 'wp_insert_post_data', 'ekwa_slider_auto_title', 10, 2 );

/**
 * Admin list table column adjustment (optional) - ensure title column still displays.
 */
function ekwa_slider_adjust_columns( $columns ) {
	return $columns; // Keeping default columns; title will display generated value.
}
add_filter( 'manage_edit-ekwa_slide_columns', 'ekwa_slider_adjust_columns' );
