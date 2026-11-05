<?php
/**
 * Favicon and app icons.
 *
 * When a Site Icon is set (Appearance → Customize → Site Identity, or seeded
 * from the logo on activation) WordPress prints it. Otherwise the bundled SM
 * icons in assets/images/favicon are used, so the browser tab never shows a
 * blank icon.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			$base = SM_URI . 'assets/images/favicon/';
			echo '<link rel="icon" href="' . esc_url( $base . 'favicon.ico' ) . '" sizes="any">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $base . 'favicon-32x32.png' ) . '">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $base . 'favicon-16x16.png' ) . '">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="192x192" href="' . esc_url( $base . 'icon-192.png' ) . '">' . "\n";
			echo '<link rel="apple-touch-icon" href="' . esc_url( $base . 'apple-touch-icon.png' ) . '">' . "\n";
		}
		echo '<meta name="theme-color" content="' . esc_attr( sanitize_hex_color( sm_get( 'color_primary' ) ) ?: '#ce2127' ) . '">' . "\n";
	},
	2
);

// Same icons for the login screen and admin, so the tab matches everywhere.
foreach ( array( 'login_head', 'admin_head' ) as $sm_hook ) {
	add_action(
		$sm_hook,
		function () {
			if ( ! has_site_icon() ) {
				echo '<link rel="icon" href="' . esc_url( SM_URI . 'assets/images/favicon/favicon.ico' ) . '" sizes="any">' . "\n";
			}
		}
	);
}
