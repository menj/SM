<?php
/**
 * Image lightbox (bundles the official Lightbox2 2.12.0 by Lokesh Dhakar, MIT licence).
 *
 * Images and galleries whose links point at an image file get the
 * data-lightbox attribute automatically, so editors need to do nothing.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! sm_get( 'lb_enabled' ) || ! is_singular() ) {
			return;
		}
		wp_enqueue_style( 'sm-lightbox', SM_URI . 'assets/vendor/lightbox2/css/lightbox.min.css', array(), '2.12.0' );
		// Lightbox2 needs jQuery; WordPress core's copy is used, and only on pages that can show a lightbox.
		wp_enqueue_script( 'sm-lightbox', SM_URI . 'assets/vendor/lightbox2/js/lightbox.min.js', array( 'jquery' ), '2.12.0', array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'sm-lightbox',
			'window.lightbox && lightbox.option(' . wp_json_encode(
				array(
					'resizeDuration'       => 200,
					'fadeDuration'         => 200,
					'wrapAround'           => (bool) sm_get( 'lb_wrap' ),
					/* translators: 1 and 2 are placeholders replaced by the current and total image numbers; keep them as written. */
					'albumLabel'           => __( 'Image %1 of %2', 'sm' ),
					'disableScrolling'     => true,
					'showImageNumberLabel' => true,
				)
			) . ');'
		);
		wp_enqueue_script( 'sm-lightbox-a11y', SM_URI . 'assets/js/lightbox-a11y.js', array( 'sm-lightbox' ), SM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'sm-lightbox-a11y',
			'window.smLightboxA11y = ' . wp_json_encode(
				array(
					'close' => __( 'Close', 'sm' ),
					'prev'  => __( 'Previous image', 'sm' ),
					'next'  => __( 'Next image', 'sm' ),
				)
			) . ';',
			'before'
		);
	}
);

/**
 * Add data-lightbox to anchors that wrap an <img> and link to an image file.
 *
 * @param string $html  Rendered block.
 * @param array  $block Parsed block.
 * @return string
 */
function sm_lightbox_filter_block( $html, $block ) {
	if ( ! sm_get( 'lb_enabled' ) || is_admin() ) {
		return $html;
	}
	$name = $block['blockName'] ?? '';
	if ( ! in_array( $name, array( 'core/image', 'core/gallery', 'core/media-text', 'core/cover' ), true ) ) {
		return $html;
	}
	if ( 'core/gallery' === $name ) {
		// Inner image blocks were already tagged one by one; regroup them as a single album.
		$html = preg_replace( '#\sdata-lightbox="[^"]*"#', '', $html );
	}
	$group    = 'core/gallery' === $name ? 'gallery-' . substr( md5( $html ), 0, 8 ) : 'image-' . substr( md5( $html ), 0, 8 );
	$captions = (bool) sm_get( 'lb_captions' );

	return preg_replace_callback(
		'#<a\b([^>]*?)href="([^"]+\.(?:jpe?g|png|gif|webp|avif|svg))(\?[^"]*)?"([^>]*)>(\s*<img\b[^>]*>)#i',
		function ( $m ) use ( $group, $captions, $html ) {
			if ( false !== stripos( $m[1] . $m[4], 'data-lightbox' ) ) {
				return $m[0];
			}
			$title = '';
			if ( $captions && preg_match( '#alt="([^"]*)"#', $m[5], $alt ) ) {
				$title = $alt[1];
			}
			return '<a' . $m[1] . 'href="' . $m[2] . ( $m[3] ?? '' ) . '"' . $m[4] . ' data-lightbox="' . esc_attr( $group ) . '"' . ( $title ? ' data-title="' . esc_attr( $title ) . '"' : '' ) . '>' . $m[5];
		},
		$html
	);
}
add_filter( 'render_block', 'sm_lightbox_filter_block', 10, 2 );
