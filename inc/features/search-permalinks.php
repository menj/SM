<?php
/**
 * Pretty search permalinks (replaces the Pretty Search Permalinks plugin).
 *
 * /?s=term  →  /search/term/   (base configurable under Search & Login).
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		global $wp_rewrite;
		$base = sanitize_title( sm_get( 'search_base' ) ) ?: 'search';
		$wp_rewrite->search_base = $base;
	},
	5
);

add_action(
	'template_redirect',
	function () {
		global $wp_rewrite;
		if ( ! is_search() || is_admin() || ! $wp_rewrite->using_permalinks() ) {
			return;
		}
		$base = $wp_rewrite->search_base;
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( empty( $base ) || false !== strpos( $uri, '/' . $base . '/' ) ) {
			return;
		}
		$term = get_query_var( 's' );
		if ( '' === trim( (string) $term ) ) {
			return;
		}
		wp_safe_redirect( home_url( user_trailingslashit( '/' . $base . '/' . rawurlencode( $term ) ) ), 301 );
		exit;
	}
);

// Keep the Permalinks screen field for people used to the plugin.
add_action(
	'load-options-permalink.php',
	function () {
		if ( isset( $_POST['sm_search_base'] ) ) {
			check_admin_referer( 'update-permalink' );
			$settings                = (array) get_option( 'sm_settings', array() );
			$settings['search_base'] = sanitize_title( wp_unslash( $_POST['sm_search_base'] ) ) ?: 'search';
			update_option( 'sm_settings', $settings );
		}
		add_settings_field(
			'sm_search_base',
			__( 'Search base', 'sm' ),
			function () {
				echo '<input type="text" class="regular-text" name="sm_search_base" value="' . esc_attr( sm_get( 'search_base' ) ) . '" />';
			},
			'permalink',
			'optional'
		);
	}
);
