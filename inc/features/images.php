<?php
/**
 * Modern image formats: AVIF first, WebP as the fallback.
 *
 * New JPEG and PNG uploads (and every generated size) are converted to AVIF when
 * the server can write it, otherwise to WebP, otherwise left alone. The original
 * upload is always kept. The choice is under Appearance → SM Theme → Reading.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Can this server's image library write the given format?
 *
 * @param string $format 'avif' or 'webp'.
 * @return bool
 */
function sm_image_format_supported( $format ) {
	static $cache = array();
	if ( ! isset( $cache[ $format ] ) ) {
		$cache[ $format ] = function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/' . $format ) );
	}
	return $cache[ $format ];
}

/**
 * Format to write for new images, after checking what the server supports.
 *
 * @return string avif|webp|original
 */
function sm_image_format() {
	$want = sm_get( 'image_format' );
	if ( 'original' === $want ) {
		return 'original';
	}
	if ( 'avif' === $want && sm_image_format_supported( 'avif' ) ) {
		return 'avif';
	}
	return sm_image_format_supported( 'webp' ) ? 'webp' : 'original';
}

add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		$format = sm_image_format();
		if ( 'original' === $format ) {
			return $formats;
		}
		$formats['image/jpeg'] = 'image/' . $format;
		$formats['image/png']  = 'image/' . $format;
		return $formats;
	}
);

// AVIF reaches the same look at a lower quality number than WebP.
add_filter(
	'wp_editor_set_quality',
	function ( $quality, $mime ) {
		if ( 'image/avif' === $mime ) {
			return 60;
		}
		return 'image/webp' === $mime ? 82 : $quality;
	},
	10,
	2
);

// Keep oversized originals from bloating the media library.
add_filter(
	'big_image_size_threshold',
	function ( $threshold ) {
		return 'original' === sm_get( 'image_format' ) ? $threshold : 2560;
	}
);

/**
 * Admin notice on the SM settings screen when the chosen format is unavailable.
 */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'appearance_page_sm-settings' !== $screen->id ) {
			return;
		}
		$want = sm_get( 'image_format' );
		if ( 'avif' === $want && ! sm_image_format_supported( 'avif' ) ) {
			$msg = sm_image_format_supported( 'webp' )
				? __( 'This server cannot write AVIF images, so WebP is used instead. Ask your host to enable AVIF in GD or Imagick for the smallest files.', 'sm' )
				: __( 'This server cannot write AVIF or WebP images, so uploads are left in their original format.', 'sm' );
			echo '<div class="notice notice-warning"><p>' . esc_html( $msg ) . '</p></div>';
		}
	}
);
