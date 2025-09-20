<?php
/**
 * Settings page for Ekwa Slider.
 *
 * @package EkwaSlider
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Ekwa Slider main menu and settings page.
 */

function ekwa_slider_register_menu() {
       add_menu_page(
	       __( 'Ekwa Slider', 'ekwa-slider' ),
	       __( 'Ekwa Slider', 'ekwa-slider' ),
	       'manage_options',
	       'ekwa-slider-dashboard',
	       'ekwa_slider_render_settings_page',
	       'dashicons-images-alt',
	       60
       );

       // Add a 'Settings' submenu (points to dashboard)
       add_submenu_page(
	       'ekwa-slider-dashboard',
	       __( 'Dashboard', 'ekwa-slider' ),
	       __( 'Dashboard', 'ekwa-slider' ),
	       'manage_options',
	       'ekwa-slider-dashboard',
	       'ekwa_slider_render_settings_page'
       );
}
add_action( 'admin_menu', 'ekwa_slider_register_menu', 9 );

/**
 * Register settings for the plugin.
 */
function ekwa_slider_register_settings() {
	register_setting(
		'ekwa_slider_settings_group',
		'ekwa_slider_settings',
		'ekwa_slider_sanitize_settings'
	);
}
add_action( 'admin_init', 'ekwa_slider_register_settings' );

/**
 * Sanitize settings input.
 *
 * @param array $input Raw input from form.
 * @return array Sanitized settings.
 */
function ekwa_slider_sanitize_settings( $input ) {
	$sanitized = [];

	$sanitized['mobile_banner_enabled'] = ! empty( $input['mobile_banner_enabled'] );
	$sanitized['mobile_banner_post_id'] = ! empty( $input['mobile_banner_post_id'] ) ? (int) $input['mobile_banner_post_id'] : 0;

	return $sanitized;
}

/**
 * Process form submission.
 */
function ekwa_slider_handle_settings_save() {
	if ( ! isset( $_POST['ekwa_slider_settings_nonce'] ) || ! wp_verify_nonce( $_POST['ekwa_slider_settings_nonce'], 'ekwa_slider_settings_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['ekwa_slider_settings'] ) ) {
		$settings = ekwa_slider_sanitize_settings( $_POST['ekwa_slider_settings'] );
		update_option( 'ekwa_slider_settings', $settings );

		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'ekwa-slider' ) . '</p></div>';
		});
	}
}
add_action( 'admin_init', 'ekwa_slider_handle_settings_save' );

/**
 * Render the settings/dashboard page.
 */
function ekwa_slider_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = get_option( 'ekwa_slider_settings', [
		'mobile_banner_enabled' => false,
		'mobile_banner_post_id' => 0,
	] );

	// Get published slides for dropdown
	$slides = get_posts([
		'post_type'      => 'ekwa_slide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	]);

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Ekwa Slider Dashboard', 'ekwa-slider' ); ?></h1>

		<form method="post" action="">
			<?php wp_nonce_field( 'ekwa_slider_settings_save', 'ekwa_slider_settings_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Shortcode Usage', 'ekwa-slider' ); ?></th>
					<td>
						<code>[ekwa_slider]</code>
						<p class="description"><?php esc_html_e( 'Use this shortcode to display the slider on any page or post.', 'ekwa-slider' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile Banner Mode', 'ekwa-slider' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="ekwa_slider_settings[mobile_banner_enabled]" value="1" <?php checked( $settings['mobile_banner_enabled'] ); ?> />
								<?php esc_html_e( 'Enable mobile banner (shows single slide on mobile devices)', 'ekwa-slider' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, mobile visitors will see only the selected slide instead of the full slider.', 'ekwa-slider' ); ?></p>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile Banner Slide', 'ekwa-slider' ); ?></th>
					<td>
						<select name="ekwa_slider_settings[mobile_banner_post_id]">
							<option value=""><?php esc_html_e( 'Select a slide...', 'ekwa-slider' ); ?></option>
							<?php foreach ( $slides as $slide ) : ?>
								<option value="<?php echo esc_attr( $slide->ID ); ?>" <?php selected( $settings['mobile_banner_post_id'], $slide->ID ); ?>>
									<?php echo esc_html( $slide->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Choose which slide to display as mobile banner when mobile banner mode is enabled.', 'ekwa-slider' ); ?></p>
						<?php if ( empty( $slides ) ) : ?>
							<p class="description" style="color: #d63638;"><?php esc_html_e( 'No slides available. Create some slides first.', 'ekwa-slider' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
