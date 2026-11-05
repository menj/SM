<?php
/**
 * Image lightbox (bundles Lightbox2 by Lokesh Dhakar, MIT licence).
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
		wp_enqueue_script( 'sm-lightbox', SM_URI . 'assets/vendor/lightbox2/js/lightbox.min.js', array( 'jquery' ), '2.12.0', true );
		wp_add_inline_script(
			'sm-lightbox',
			'window.lightbox && lightbox.option(' . wp_json_encode(
				array(
					'resizeDuration'    => 200,
					'fadeDuration'      => 200,
					'wrapAround'        => (bool) sm_get( 'lb_wrap' ),
					'albumLabel'        => __( 'Image %1 of %2', 'sm' ),
					'disableScrolling'  => true,
					'showImageNumberLabel' => true,
				)
			) . ');'
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
