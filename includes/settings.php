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

	// Transition style validation
	$allowed_transitions = [ 'fade', 'slide', 'slide-fade', 'zoom' ];
	$sanitized['transition_style'] = ! empty( $input['transition_style'] ) && in_array( $input['transition_style'], $allowed_transitions, true )
		? $input['transition_style']
		: 'fade';

	// Navigation controls
	$sanitized['show_arrows'] = ! empty( $input['show_arrows'] );
	$sanitized['show_dots'] = ! empty( $input['show_dots'] );

	// Arrow style
	$allowed_arrow_styles = [ 'default', 'chevron', 'angle', 'circle-arrow', 'square-arrow' ];
	$sanitized['arrow_style'] = ! empty( $input['arrow_style'] ) && in_array( $input['arrow_style'], $allowed_arrow_styles, true )
		? $input['arrow_style']
		: 'default';

	// Mobile crop width (default 767px)
	$mobile_width = ! empty( $input['mobile_crop_width'] ) ? (int) $input['mobile_crop_width'] : 767;
	$sanitized['mobile_crop_width'] = max( 320, min( 1200, $mobile_width ) ); // Between 320px and 1200px

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
		'transition_style' => 'fade',
		'show_arrows' => true,
		'show_dots' => true,
		'arrow_style' => 'default',
		'mobile_crop_width' => 767,
	] );

	// Ensure keys exist
	if ( ! isset( $settings['mobile_banner_enabled'] ) ) {
		$settings['mobile_banner_enabled'] = false;
	}
	if ( ! isset( $settings['mobile_banner_post_id'] ) ) {
		$settings['mobile_banner_post_id'] = 0;
	}
	if ( ! isset( $settings['transition_style'] ) ) {
		$settings['transition_style'] = 'fade';
	}
	if ( ! isset( $settings['show_arrows'] ) ) {
		$settings['show_arrows'] = true;
	}
	if ( ! isset( $settings['show_dots'] ) ) {
		$settings['show_dots'] = true;
	}
	if ( ! isset( $settings['arrow_style'] ) ) {
		$settings['arrow_style'] = 'default';
	}
	if ( ! isset( $settings['mobile_crop_width'] ) ) {
		$settings['mobile_crop_width'] = 767;
	}

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
					<th scope="row"><?php esc_html_e( 'Transition Style', 'ekwa-slider' ); ?></th>
					<td>
						<select name="ekwa_slider_settings[transition_style]">
							<option value="fade" <?php selected( $settings['transition_style'], 'fade' ); ?>>
								<?php esc_html_e( 'Fade - Smooth opacity transition', 'ekwa-slider' ); ?>
							</option>
							<option value="slide" <?php selected( $settings['transition_style'], 'slide' ); ?>>
								<?php esc_html_e( 'Slide - Horizontal sliding transition', 'ekwa-slider' ); ?>
							</option>
							<option value="slide-fade" <?php selected( $settings['transition_style'], 'slide-fade' ); ?>>
								<?php esc_html_e( 'Slide + Fade - Combined sliding and fading', 'ekwa-slider' ); ?>
							</option>
							<option value="zoom" <?php selected( $settings['transition_style'], 'zoom' ); ?>>
								<?php esc_html_e( 'Zoom - Scale in/out transition', 'ekwa-slider' ); ?>
							</option>
						</select>
						<p class="description"><?php esc_html_e( 'Choose the transition effect when slides change.', 'ekwa-slider' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Navigation Controls', 'ekwa-slider' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="ekwa_slider_settings[show_arrows]" value="1" <?php checked( $settings['show_arrows'] ); ?> />
								<?php esc_html_e( 'Show navigation arrows', 'ekwa-slider' ); ?>
							</label>
							<br><br>
							<label>
								<input type="checkbox" name="ekwa_slider_settings[show_dots]" value="1" <?php checked( $settings['show_dots'] ); ?> />
								<?php esc_html_e( 'Show navigation dots', 'ekwa-slider' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Control visibility of slider navigation elements.', 'ekwa-slider' ); ?></p>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Arrow Style', 'ekwa-slider' ); ?></th>
					<td>
						<select name="ekwa_slider_settings[arrow_style]">
							<option value="default" <?php selected( $settings['arrow_style'], 'default' ); ?>>
								<?php esc_html_e( 'Default (← →)', 'ekwa-slider' ); ?>
							</option>
							<option value="chevron" <?php selected( $settings['arrow_style'], 'chevron' ); ?>>
								<?php esc_html_e( 'Chevron (‹ ›)', 'ekwa-slider' ); ?>
							</option>
							<option value="angle" <?php selected( $settings['arrow_style'], 'angle' ); ?>>
								<?php esc_html_e( 'Angle (< >)', 'ekwa-slider' ); ?>
							</option>
							<option value="circle-arrow" <?php selected( $settings['arrow_style'], 'circle-arrow' ); ?>>
								<?php esc_html_e( 'Circle Arrow (⬅ ➡)', 'ekwa-slider' ); ?>
							</option>
							<option value="square-arrow" <?php selected( $settings['arrow_style'], 'square-arrow' ); ?>>
								<?php esc_html_e( 'Square Arrow (⯇ ⯈)', 'ekwa-slider' ); ?>
							</option>
						</select>
						<p class="description"><?php esc_html_e( 'Choose the arrow icon style for navigation.', 'ekwa-slider' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile Crop Width', 'ekwa-slider' ); ?></th>
					<td>
						<input type="number" name="ekwa_slider_settings[mobile_crop_width]" value="<?php echo esc_attr( $settings['mobile_crop_width'] ); ?>" min="320" max="1200" step="1" style="width: 100px;" />
						<span>px</span>
						<p class="description">
							<?php esc_html_e( 'Maximum width for mobile images (used in picture tag media query and auto-cropping). Default: 767px. Range: 320-1200px.', 'ekwa-slider' ); ?>
							<br>
							<strong><?php esc_html_e( 'Picture tag will use:', 'ekwa-slider' ); ?></strong> <code>&lt;source media="(max-width: <?php echo esc_html( $settings['mobile_crop_width'] ); ?>px)"&gt;</code>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile Banner from Existing Slides', 'ekwa-slider' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="ekwa_slider_settings[mobile_banner_enabled]" id="ekwa_mobile_banner_enabled" value="1" <?php checked( $settings['mobile_banner_enabled'] ); ?> />
								<?php esc_html_e( 'Enable mobile banner from existing slides', 'ekwa-slider' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, mobile visitors will see only the selected slide instead of the full slider.', 'ekwa-slider' ); ?></p>
						</fieldset>
					</td>
				</tr>

				<tr id="ekwa_mobile_banner_slide_row" style="<?php echo $settings['mobile_banner_enabled'] ? '' : 'display: none;'; ?>">
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

		<script>
		(function() {
			var checkbox = document.getElementById('ekwa_mobile_banner_enabled');
			var slideRow = document.getElementById('ekwa_mobile_banner_slide_row');
			
			if (checkbox && slideRow) {
				checkbox.addEventListener('change', function() {
					slideRow.style.display = this.checked ? '' : 'none';
				});
			}
		})();
		</script>
	</div>
	<?php
}
