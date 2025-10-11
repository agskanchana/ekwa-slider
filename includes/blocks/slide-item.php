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

	// Desktop image is required
	if ( ! $desktop_id ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<div class="ekwa-slide-item-missing">' . esc_html__( 'Desktop image is required.', 'ekwa-slider' ) . '</div>';
		}
		return ''; // Front-end: hide if incomplete.
	}

	// Get image URLs
	$desktop_url = wp_get_attachment_url( $desktop_id );

	if ( ! $desktop_url ) {
		return '';
	}

	// If mobile image not provided, use desktop image (will be auto-cropped)
	if ( ! $mobile_id ) {
		$mobile_id = $desktop_id;
	}

	$mobile_url = wp_get_attachment_url( $mobile_id );

	if ( ! $mobile_url ) {
		$mobile_url = $desktop_url;
	}

	// Get alt text
	$desktop_alt = get_post_meta( $desktop_id, '_wp_attachment_image_alt', true );
	$mobile_alt  = get_post_meta( $mobile_id, '_wp_attachment_image_alt', true );
	$alt_text = ! empty( $desktop_alt ) ? $desktop_alt : ( ! empty( $mobile_alt ) ? $mobile_alt : __( 'Slide image', 'ekwa-slider' ) );

	// Try to get WebP versions (mobile will be auto-cropped)
	$desktop_webp = ekwa_slider_get_webp_url( $desktop_id, false );
	$mobile_webp  = ekwa_slider_get_webp_url( $mobile_id, true );

	// Get cropped mobile fallback (JPG/PNG)
	$mobile_cropped_url = ekwa_slider_get_cropped_mobile_url( $mobile_id );

	// Get mobile crop width from settings
	$settings = get_option( 'ekwa_slider_settings', [ 'mobile_crop_width' => 767 ] );
	$mobile_width = ! empty( $settings['mobile_crop_width'] ) ? (int) $settings['mobile_crop_width'] : 767;

	// Process the saved InnerBlocks content
	$inner = ! empty( $content ) ? $content : '';

	ob_start();
	?>
	<div class="ekwa-slide-item" data-desktop="<?php echo esc_url( $desktop_url ); ?>" data-mobile="<?php echo esc_url( $mobile_url ); ?>">
		<div class="ekwa-slide-item-background">
			<picture>
				<?php if ( $mobile_webp ) : ?>
					<source media="(max-width: <?php echo esc_attr( $mobile_width ); ?>px)" srcset="<?php echo esc_url( $mobile_webp ); ?>" type="image/webp">
				<?php endif; ?>
				<source media="(max-width: <?php echo esc_attr( $mobile_width ); ?>px)" srcset="<?php echo esc_url( $mobile_cropped_url ? $mobile_cropped_url : $mobile_url ); ?>">

				<?php if ( $desktop_webp ) : ?>
					<source media="(min-width: <?php echo esc_attr( $mobile_width + 1 ); ?>px)" srcset="<?php echo esc_url( $desktop_webp ); ?>" type="image/webp">
				<?php endif; ?>
				<source media="(min-width: <?php echo esc_attr( $mobile_width + 1 ); ?>px)" srcset="<?php echo esc_url( $desktop_url ); ?>">

				<img
					src="<?php echo esc_url( $desktop_url ); ?>"
					alt="<?php echo esc_attr( $alt_text ); ?>"
					loading="lazy"
				>
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
 * Get cropped mobile version of original image (JPG/PNG fallback).
 *
 * @param int $attachment_id Attachment ID.
 * @return string|false Cropped mobile URL or false if not found.
 */
function ekwa_slider_get_cropped_mobile_url( $attachment_id ) {
	// Get the original image URL
	$original_url = wp_get_attachment_url( $attachment_id );

	if ( ! $original_url ) {
		return false;
	}

	// Get the file path
	$file_path = get_attached_file( $attachment_id );

	if ( ! $file_path ) {
		return false;
	}

	// Get file extension
	preg_match( '/\.(jpg|jpeg|png|gif)$/i', $file_path, $matches );
	if ( empty( $matches ) ) {
		return false;
	}
	$extension = $matches[0];

	// Create cropped path
	$cropped_path = preg_replace( '/\.(jpg|jpeg|png|gif)$/i', '-mobile-cropped' . $extension, $file_path );

	// Check if cropped file exists
	if ( file_exists( $cropped_path ) ) {
		// Return cropped URL
		return preg_replace( '/\.(jpg|jpeg|png|gif)$/i', '-mobile-cropped' . $extension, $original_url );
	}

	// Try to generate cropped version
	if ( function_exists( 'imagecreatefromjpeg' ) ) {
		$cropped_generated = ekwa_slider_generate_cropped_mobile( $file_path, $cropped_path );

		if ( $cropped_generated ) {
			return preg_replace( '/\.(jpg|jpeg|png|gif)$/i', '-mobile-cropped' . $extension, $original_url );
		}
	}

	return false;
}

/**
 * Generate cropped mobile version of original format (JPG/PNG).
 *
 * @param string $source_path Source image path.
 * @param string $dest_path Destination cropped image path.
 * @return bool True on success, false on failure.
 */
function ekwa_slider_generate_cropped_mobile( $source_path, $dest_path ) {
	// Check if source exists
	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	// Check if destination already exists
	if ( file_exists( $dest_path ) ) {
		return true;
	}

	// Get image info
	$image_info = @getimagesize( $source_path );

	if ( ! $image_info ) {
		return false;
	}

	$mime_type = $image_info['mime'];

	// Create image resource based on type
	$image = false;
	switch ( $mime_type ) {
		case 'image/jpeg':
			$image = @imagecreatefromjpeg( $source_path );
			break;
		case 'image/png':
			$image = @imagecreatefrompng( $source_path );
			if ( $image ) {
				// Preserve transparency
				imagealphablending( $image, false );
				imagesavealpha( $image, true );
			}
			break;
		case 'image/gif':
			$image = @imagecreatefromgif( $source_path );
			break;
		default:
			return false;
	}

	if ( ! $image ) {
		return false;
	}

	// Crop to mobile size
	$cropped_image = ekwa_slider_crop_mobile_image( $image );
	if ( ! $cropped_image ) {
		imagedestroy( $image );
		return false;
	}

	// Save based on original format
	$result = false;
	switch ( $mime_type ) {
		case 'image/jpeg':
			$result = @imagejpeg( $cropped_image, $dest_path, 85 );
			break;
		case 'image/png':
			$result = @imagepng( $cropped_image, $dest_path, 8 ); // Compression level 8 (0-9)
			break;
		case 'image/gif':
			$result = @imagegif( $cropped_image, $dest_path );
			break;
	}

	// Free memory
	imagedestroy( $cropped_image );

	return $result;
}

/**
 * Get WebP version of an image if it exists.
 *
 * @param int  $attachment_id Attachment ID.
 * @param bool $is_mobile Whether this is a mobile image (for cropping).
 * @return string|false WebP URL or false if not found.
 */
function ekwa_slider_get_webp_url( $attachment_id, $is_mobile = false ) {
	// Get the original image URL
	$original_url = wp_get_attachment_url( $attachment_id );

	if ( ! $original_url ) {
		return false;
	}

	// Get the file path
	$file_path = get_attached_file( $attachment_id );

	if ( ! $file_path ) {
		return false;
	}

	// For mobile images, use cropped version
	$suffix = $is_mobile ? '-mobile-cropped' : '';
	$webp_path = preg_replace( '/\.(jpg|jpeg|png)$/i', $suffix . '.webp', $file_path );

	// Check if WebP file exists
	if ( file_exists( $webp_path ) ) {
		// Return WebP URL
		return preg_replace( '/\.(jpg|jpeg|png)$/i', $suffix . '.webp', $original_url );
	}

	// Try to generate WebP if GD or Imagick supports it
	if ( function_exists( 'imagewebp' ) || extension_loaded( 'imagick' ) ) {
		$webp_generated = ekwa_slider_generate_webp( $file_path, $webp_path, $is_mobile );

		if ( $webp_generated ) {
			return preg_replace( '/\.(jpg|jpeg|png)$/i', $suffix . '.webp', $original_url );
		}
	}

	return false;
}

/**
 * Generate WebP version of an image.
 *
 * @param string $source_path Source image path.
 * @param string $dest_path Destination WebP path.
 * @param bool   $is_mobile Whether to crop for mobile (max 767px width, centered crop).
 * @return bool True on success, false on failure.
 */
function ekwa_slider_generate_webp( $source_path, $dest_path, $is_mobile = false ) {
	// Check if source exists
	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	// Check if destination already exists
	if ( file_exists( $dest_path ) ) {
		return true;
	}

	// Get image info
	$image_info = @getimagesize( $source_path );

	if ( ! $image_info ) {
		return false;
	}

	$mime_type = $image_info['mime'];

	// Create image resource based on type
	$image = false;
	switch ( $mime_type ) {
		case 'image/jpeg':
			$image = @imagecreatefromjpeg( $source_path );
			break;
		case 'image/png':
			$image = @imagecreatefrompng( $source_path );
			if ( $image ) {
				// Preserve transparency
				imagealphablending( $image, false );
				imagesavealpha( $image, true );
			}
			break;
		case 'image/gif':
			$image = @imagecreatefromgif( $source_path );
			break;
		default:
			return false;
	}

	if ( ! $image ) {
		return false;
	}

	// If mobile, crop to max 767px width and maintain aspect ratio
	if ( $is_mobile ) {
		$image = ekwa_slider_crop_mobile_image( $image );
		if ( ! $image ) {
			return false;
		}
	}

	// Convert to WebP with quality 85
	$result = @imagewebp( $image, $dest_path, 85 );

	// Free memory
	imagedestroy( $image );

	return $result;
}

/**
 * Crop/resize image for mobile display (max 767px width).
 *
 * @param resource $image GD image resource.
 * @return resource|false Cropped image resource or false on failure.
 */
function ekwa_slider_crop_mobile_image( $image ) {
	if ( ! $image ) {
		return false;
	}

	$original_width  = imagesx( $image );
	$original_height = imagesy( $image );

	// Get mobile crop width from settings
	$settings = get_option( 'ekwa_slider_settings', [ 'mobile_crop_width' => 767 ] );
	$target_width = ! empty( $settings['mobile_crop_width'] ) ? (int) $settings['mobile_crop_width'] : 767;

	// If image is already smaller than target, return original
	if ( $original_width <= $target_width ) {
		return $image;
	}

	// Calculate new dimensions maintaining aspect ratio
	$target_height = (int) ( $original_height * ( $target_width / $original_width ) );

	// Create new image with target dimensions
	$new_image = imagecreatetruecolor( $target_width, $target_height );

	if ( ! $new_image ) {
		return false;
	}

	// Preserve transparency for PNG
	imagealphablending( $new_image, false );
	imagesavealpha( $new_image, true );
	$transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127 );
	imagefill( $new_image, 0, 0, $transparent );

	// Resize image
	$success = imagecopyresampled(
		$new_image,
		$image,
		0,
		0,
		0,
		0,
		$target_width,
		$target_height,
		$original_width,
		$original_height
	);

	if ( ! $success ) {
		imagedestroy( $new_image );
		return false;
	}

	// Free original image memory
	imagedestroy( $image );

	return $new_image;
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
