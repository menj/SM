<?php
/**
 * Image format: AVIF.
 *
 * New JPEG and PNG uploads, and every size WordPress generates from them, are
 * written as AVIF. The original upload is always kept, so switching the format
 * off loses nothing. Needs AVIF support in GD (PHP 8.1+) or Imagick; the
 * settings screen says so when the server cannot write AVIF.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Can this server's image library write AVIF?
 *
 * @return bool
 */
function sm_avif_supported() {
	static $ok = null;
	if ( null === $ok ) {
		$ok = function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );
	}
	return $ok;
}

add_filter(
	'image_editor_output_format',
	function ( $formats, $filename = '' ) {
		if ( 'avif' !== sm_get( 'image_format' ) || ! sm_avif_supported() ) {
			return $formats;
		}
		// Site icons (favicons) stay PNG: browsers do not reliably show AVIF favicons.
		if ( 0 === strpos( basename( (string) $filename ), 'cropped-' ) ) {
			return $formats;
		}
		$formats['image/jpeg'] = 'image/avif';
		$formats['image/png']  = 'image/avif';
		return $formats;
	},
	10,
	2
);

// AVIF reaches the same look at a lower quality number than JPEG.
add_filter(
	'wp_editor_set_quality',
	function ( $quality, $mime ) {
		return 'image/avif' === $mime ? 60 : $quality;
	},
	10,
	2
);

// Keep oversized originals from bloating the media library.
add_filter(
	'big_image_size_threshold',
	function ( $threshold ) {
		return 'avif' === sm_get( 'image_format' ) ? 2560 : $threshold;
	}
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_sm-settings' === $screen->id && 'avif' === sm_get( 'image_format' ) && ! sm_avif_supported() ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'AVIF is selected but this server cannot write AVIF images. Enable AVIF in GD (PHP 8.1 or newer with libavif) or Imagick, or choose "Keep original format".', 'sm' ) . '</p></div>';
		}
	}
);
