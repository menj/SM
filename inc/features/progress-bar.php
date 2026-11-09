<?php
/**
 * Reading progress bar (replaces the Reading Progress Bar plugin).
 *
 * Output: a single <div class="sm-progress"> element styled from
 * assets/css/theme.css and driven by assets/js/theme.js. No jQuery.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Should the bar show on the current request?
 *
 * @return bool
 */
function sm_progress_bar_active() {
	if ( ! sm_get( 'rp_enabled' ) || ! is_singular() ) {
		return false;
	}
	return in_array( get_post_type(), (array) sm_get( 'rp_post_types' ), true );
}

add_action(
	'wp_body_open',
	function () {
		if ( ! sm_progress_bar_active() ) {
			return;
		}
		$color = sanitize_hex_color( sm_get( 'rp_color' ) );
		printf(
			'<div class="sm-progress sm-progress--%1$s" aria-hidden="true" data-label="%2$s" style="--sm-progress-height:%3$dpx;%4$s"><span class="sm-progress__bar"></span></div>',
			esc_attr( 'bottom' === sm_get( 'rp_position' ) ? 'bottom' : 'top' ),
			esc_attr__( 'Reading progress', 'sm' ),
			(int) sm_get( 'rp_height' ),
			$color ? '--sm-progress-color:' . esc_attr( $color ) : ''
		);
	}
);
