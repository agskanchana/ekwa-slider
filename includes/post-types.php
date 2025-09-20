<?php
/**
 * Post Types registration for Ekwa Slider.
 *
 * @package EkwaSlider
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Slides custom post type.
 */
function ekwa_slider_register_post_type() {
	$labels = [
		'name'                  => _x( 'Slides', 'Post Type General Name', 'ekwa-slider' ),
		'singular_name'         => _x( 'Slide', 'Post Type Singular Name', 'ekwa-slider' ),
		'menu_name'             => __( 'Slides', 'ekwa-slider' ),
		'name_admin_bar'        => __( 'Slide', 'ekwa-slider' ),
		'add_new'               => __( 'Add New', 'ekwa-slider' ),
		'add_new_item'          => __( 'Add New Slide', 'ekwa-slider' ),
		'edit_item'             => __( 'Edit Slide', 'ekwa-slider' ),
		'new_item'              => __( 'New Slide', 'ekwa-slider' ),
		'view_item'             => __( 'View Slide', 'ekwa-slider' ),
		'view_items'            => __( 'View Slides', 'ekwa-slider' ),
		'search_items'          => __( 'Search Slides', 'ekwa-slider' ),
		'not_found'             => __( 'No slides found', 'ekwa-slider' ),
		'not_found_in_trash'    => __( 'No slides found in Trash', 'ekwa-slider' ),
		'all_items'             => __( 'All Slides', 'ekwa-slider' ),
		'archives'              => __( 'Slide Archives', 'ekwa-slider' ),
	];

	$args = [
		'label'                 => __( 'Slides', 'ekwa-slider' ),
		'labels'                => $labels,
		'public'                => false, // Not publicly queryable.
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'show_ui'               => true,  // Visible in admin.
		'show_in_menu'          => 'ekwa-slider-dashboard', // Submenu under Ekwa Slider
		'show_in_rest'          => true, // Gutenberg compatibility if needed.
		'menu_position'         => null, // Not needed for submenu
		'menu_icon'             => 'dashicons-images-alt2',
		'supports'              => [ 'editor' ],
		'template'              => [ [ 'ekwa/slide-item', [] ] ],
		'template_lock'         => 'all',
		'has_archive'           => false,
		'rewrite'               => false,
		'query_var'             => false,
		'capability_type'       => 'post',
	];

	register_post_type( 'ekwa_slide', $args );
}
add_action( 'init', 'ekwa_slider_register_post_type' );

/**
 * Exclude CPT from Yoast SEO sitemap.
 *
 * @param bool   $excluded  Whether to exclude.
 * @param string $post_type Post type name.
 * @return bool
 */
function ekwa_slider_yoast_exclude_cpt( $excluded, $post_type ) {
	if ( 'ekwa_slide' === $post_type ) {
		return true;
	}
	return $excluded;
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'ekwa_slider_yoast_exclude_cpt', 10, 2 );
