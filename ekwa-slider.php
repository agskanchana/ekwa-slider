<?php
/**
 * Plugin Name: Ekwa Slider
 * Description: Provides an admin-managed set of Slides (private CPT), mobile-responsive slider shortcode, Gutenberg block, and mobile banner functionality.
 * Version: 0.7.4
 * Author: Your Name / Ekwa
 * License: GPL2+
 * Text Domain: ekwa-slider
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/agskanchana/ekwa-slider/',
	__FILE__,
	'ekwa-slider'
);

// Constants.
if ( ! defined( 'EKWA_SLIDER_VERSION' ) ) {
	define( 'EKWA_SLIDER_VERSION', '0.8.0' );
}
if ( ! defined( 'EKWA_SLIDER_FILE' ) ) {
	define( 'EKWA_SLIDER_FILE', __FILE__ );
}
if ( ! defined( 'EKWA_SLIDER_DIR' ) ) {
	define( 'EKWA_SLIDER_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'EKWA_SLIDER_URL' ) ) {
	define( 'EKWA_SLIDER_URL', plugin_dir_url( __FILE__ ) );
}


// Load post types and related hooks.
require_once EKWA_SLIDER_DIR . 'includes/post-types.php';
require_once EKWA_SLIDER_DIR . 'includes/settings.php';
require_once EKWA_SLIDER_DIR . 'includes/auto-titles.php';
require_once EKWA_SLIDER_DIR . 'includes/blocks/slide-item.php';
require_once EKWA_SLIDER_DIR . 'includes/blocks/slider-content.php';
require_once EKWA_SLIDER_DIR . 'includes/blocks/slider-block.php';
require_once EKWA_SLIDER_DIR . 'includes/shortcode.php';



/**
 * Activation hook - ensure CPT registered then flush.
 */
function ekwa_slider_activate() {
	if ( function_exists( 'ekwa_slider_register_post_type' ) ) {
		ekwa_slider_register_post_type();
	}
	if ( function_exists( 'ekwa_slider_ensure_next_number_option' ) ) {
		ekwa_slider_ensure_next_number_option();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ekwa_slider_activate' );

/**
 * Deactivation hook.
 */
function ekwa_slider_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ekwa_slider_deactivate' );

