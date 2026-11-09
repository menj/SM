<?php
/**
 * Image lightbox (own dependency-free script, assets/js/lightbox.js).
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
		wp_enqueue_style( 'sm-lightbox', SM_URI . 'assets/css/lightbox.css', array(), SM_VERSION );
		wp_enqueue_script( 'sm-lightbox', SM_URI . 'assets/js/lightbox.js', array(), SM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'sm-lightbox',
			'window.smLightboxCfg = ' . wp_json_encode(
				array(
					'wrap'     => (bool) sm_get( 'lb_wrap' ),
					'captions' => (bool) sm_get( 'lb_captions' ),
					'i18n'     => array(
						'label' => __( 'Image viewer', 'sm' ),
						'close' => __( 'Close', 'sm' ),
						'prev'  => __( 'Previous image', 'sm' ),
						'next'  => __( 'Next image', 'sm' ),
						/* translators: 1: current image number, 2: total images */
						'count' => __( 'Image %1 of %2', 'sm' ),
					),
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
