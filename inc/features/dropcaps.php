<?php
/**
 * Drop caps (bundles the Dropcaps font by its author, SIL OFL licence).
 *
 * Adds a drop cap to the first paragraph of articles, controlled from
 * Appearance → SM Theme → Reading, plus a "Drop cap" paragraph block style.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_singular() ) {
			return;
		}
		wp_enqueue_style( 'sm-dropcaps', SM_URI . 'assets/css/dropcaps.css', array( 'sm-theme' ), SM_VERSION );
		wp_add_inline_style( 'sm-dropcaps', ':root{--dc-lines:' . max( 2, min( 5, (int) sm_get( 'dc_lines' ) ) ) . ';}' );
	},
	30
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_singular( 'post' ) && sm_get( 'dc_enabled' ) ) {
			$classes[] = 'sm-dropcaps-on';
			if ( 'open' === sm_get( 'dc_variant' ) ) {
				$classes[] = 'sm-dropcaps-open';
			}
		}
		return $classes;
	}
);

add_action(
	'init',
	function () {
		register_block_style(
			'core/paragraph',
			array(
				'name'  => 'sm-dropcap',
				'label' => __( 'Drop cap', 'sm' ),
			)
		);
	}
);
