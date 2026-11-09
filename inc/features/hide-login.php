<?php
/**
 * Hidden login URL (replaces the WPS Hide Login plugin).
 *
 * When enabled, wp-login.php and wp-admin return the configured redirect for
 * visitors who are not logged in, and the login form is served at /{slug}/.
 * Every URL WordPress generates for the login page is rewritten to the slug.
 *
 * Note: a theme loads after `plugins_loaded`, so the request is intercepted on
 * `init` (for wp-login.php) and served on `wp_loaded` (for the slug).
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feature on?
 *
 * @return bool
 */
function sm_hide_login_on() {
	return (bool) sm_get( 'login_enabled' );
}

/**
 * Login slug.
 *
 * @return string
 */
function sm_login_slug() {
	return sanitize_title( sm_get( 'login_slug' ) ) ?: 'login';
}

/**
 * Full URL of the hidden login page.
 *
 * @param string|null $scheme URL scheme.
 * @return string
 */
function sm_login_url( $scheme = null ) {
	if ( get_option( 'permalink_structure' ) ) {
		return home_url( user_trailingslashit( sm_login_slug() ), $scheme );
	}
	return home_url( '/', $scheme ) . '?' . sm_login_slug();
}

/**
 * Where blocked requests go.
 *
 * @return string
 */
function sm_login_blocked_url() {
	$path = ltrim( (string) sm_get( 'login_redirect' ), '/' ) ?: '404';
	return home_url( user_trailingslashit( $path ) );
}

/**
 * Current request path, relative to the site, without trailing slash.
 *
 * @return string
 */
function sm_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? rawurldecode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) - 1 );
	}
	return untrailingslashit( $path );
}

/**
 * Is this request for the hidden slug?
 *
 * @return bool
 */
function sm_is_login_slug_request() {
	$slug = sm_login_slug();
	if ( get_option( 'permalink_structure' ) ) {
		return sm_request_path() === '/' . $slug;
	}
	return isset( $_GET[ $slug ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Replace wp-login.php in a URL with the hidden slug, preserving the query.
 *
 * @param string $url    URL.
 * @param string $scheme Scheme.
 * @return string
 */
function sm_rewrite_login_php( $url, $scheme = null ) {
	if ( false === strpos( $url, 'wp-login.php' ) || false !== strpos( $url, 'wp-login.php?action=postpass' ) ) {
		return $url;
	}
	if ( is_ssl() ) {
		$scheme = 'https';
	}
	$parts = explode( '?', $url, 2 );
	$query = isset( $parts[1] ) ? $parts[1] : '';
	$new   = sm_login_url( $scheme );
	if ( $query ) {
		parse_str( $query, $args );
		if ( isset( $args['login'] ) ) {
			$args['login'] = rawurlencode( $args['login'] );
		}
		$new = add_query_arg( $args, $new );
	}
	return $new;
}

add_action(
	'init',
	function () {
		if ( ! sm_hide_login_on() ) {
			return;
		}
		global $pagenow;

		// Logged-out visitors never get sent to the login page from the dashboard: that redirect would reveal the hidden address.
		// AJAX and admin-post stay open because the front-end forms use them.
		if ( is_admin() && ! is_user_logged_in() && ! wp_doing_ajax() && ! in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
			wp_safe_redirect( sm_login_blocked_url(), 302 );
			exit;
		}

		$path     = sm_request_path();
		$is_login = ( 'wp-login.php' === $pagenow ) || false !== strpos( $path, 'wp-login.php' ) || '/wp-login' === $path;

		if ( sm_is_login_slug_request() ) {
			// Serve the login form at the slug.
			$pagenow = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			add_action(
				'wp_loaded',
				function () {
					global $error, $interim_login, $action, $user_login, $user, $redirect_to;
					if ( is_user_logged_in() && empty( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						wp_safe_redirect( admin_url() );
						exit;
					}
					require_once ABSPATH . 'wp-login.php';
					exit;
				},
				0
			);
			return;
		}

		if ( $is_login && ! is_user_logged_in() ) {
			// Allow post-password and logout confirmation flows to work.
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ( isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( in_array( $action, array( 'postpass', 'logout' ), true ) ) {
				return;
			}
			wp_safe_redirect( sm_login_blocked_url(), 302 );
			exit;
		}
	},
	1
);

// Block wp-admin for anonymous visitors (admin-ajax and admin-post stay open).
add_action(
	'admin_init',
	function () {
		if ( ! sm_hide_login_on() || is_user_logged_in() || wp_doing_ajax() ) {
			return;
		}
		global $pagenow;
		if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
			return;
		}
		wp_safe_redirect( sm_login_blocked_url(), 302 );
		exit;
	},
	1
);

add_filter(
	'site_url',
	function ( $url, $path, $scheme ) {
		return sm_hide_login_on() ? sm_rewrite_login_php( $url, $scheme ) : $url;
	},
	10,
	3
);
add_filter(
	'network_site_url',
	function ( $url, $path, $scheme ) {
		return sm_hide_login_on() ? sm_rewrite_login_php( $url, $scheme ) : $url;
	},
	10,
	3
);
add_filter(
	'wp_redirect',
	function ( $location ) {
		return sm_hide_login_on() ? sm_rewrite_login_php( $location ) : $location;
	}
);
add_filter(
	'login_url',
	function ( $login_url, $redirect, $force_reauth ) {
		if ( ! sm_hide_login_on() ) {
			return $login_url;
		}
		$url = sm_login_url();
		if ( $redirect ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
		}
		if ( $force_reauth ) {
			$url = add_query_arg( 'reauth', '1', $url );
		}
		return $url;
	},
	10,
	3
);
add_filter(
	'logout_url',
	function ( $url ) {
		return sm_hide_login_on() ? sm_rewrite_login_php( $url ) : $url;
	}
);
add_filter(
	'lostpassword_url',
	function ( $url ) {
		return sm_hide_login_on() ? sm_rewrite_login_php( $url ) : $url;
	}
);

// Tell the admin where the login lives.
add_action(
	'admin_notices',
	function () {
		if ( ! sm_hide_login_on() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'appearance_page_sm-settings' !== $screen->id ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p>%s <code>%s</code></p></div>',
			esc_html__( 'Your login page is now at:', 'sm' ),
			esc_html( sm_login_url() )
		);
	}
);
