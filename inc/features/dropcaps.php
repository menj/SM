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

// Tell the editor canvas whether to preview the drop cap, and how tall.
add_filter(
	'admin_body_class',
	function ( $classes ) {
		return $classes;
	}
);
add_filter(
	'block_editor_settings_all',
	function ( $settings ) {
		if ( ! sm_get( 'dc_enabled' ) ) {
			return $settings;
		}
		$lines  = max( 2, min( 5, (int) sm_get( 'dc_lines' ) ) );
		$css    = '.editor-styles-wrapper{--dc-lines:' . $lines . ';}';
		$class  = 'sm-dropcaps-preview' . ( 'open' === sm_get( 'dc_variant' ) ? ' sm-dropcaps-preview--open' : '' );
		$settings['styles'][] = array( 'css' => $css );
		$settings['__sm_dropcap_class'] = $class;
		return $settings;
	}
);
add_action(
	'enqueue_block_editor_assets',
	function () {
		if ( ! sm_get( 'dc_enabled' ) ) {
			return;
		}
		$class = 'sm-dropcaps-preview' . ( 'open' === sm_get( 'dc_variant' ) ? ' sm-dropcaps-preview--open' : '' );
		wp_add_inline_script( 'sm-editor', 'document.addEventListener("DOMContentLoaded",function(){var add=function(){var w=document.querySelector(".editor-styles-wrapper");if(w){"' . esc_js( $class ) . '".split(" ").forEach(function(c){w.classList.add(c);});return true;}return false;};if(!add()){var t=setInterval(function(){if(add()){clearInterval(t);}},500);setTimeout(function(){clearInterval(t);},15000);}});' );
	},
	20
);
