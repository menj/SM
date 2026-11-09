<?php
/**
 * Feed images, security headers and Mastodon verification.
 *
 * The feed and header code is adapted from the "ChrisWiegman.com" plugin by Chris Wiegman
 * (GPLv2). Differences: the deprecated X-XSS-Protection header is not sent, the Permissions-Policy
 * does not block fullscreen for video embeds, and everything is switchable from Theme Options.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the featured image as an <enclosure> so feed readers can show a thumbnail.
 */
add_action(
	'rss2_item',
	function () {
		$post = get_post();
		if ( ! $post || ! has_post_thumbnail( $post->ID ) ) {
			return;
		}
		$attachment_id = get_post_thumbnail_id( $post->ID );
		$image         = wp_get_attachment_image_src( $attachment_id, 'large' );
		$file          = get_attached_file( $attachment_id );
		if ( ! $image || ! $file || ! file_exists( $file ) ) {
			return;
		}
		printf(
			'<enclosure url="%s" length="%d" type="%s" />',
			esc_url( $image[0] ),
			(int) filesize( $file ),
			esc_attr( (string) get_post_mime_type( $attachment_id ) )
		);
	}
);

/**
 * Put the featured image at the top of each feed item's content.
 *
 * @param string $content Feed content.
 * @return string
 */
add_filter(
	'the_content_feed',
	function ( $content ) {
		$post = get_post();
		if ( ! $post || ! has_post_thumbnail( $post->ID ) ) {
			return $content;
		}
		add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
		$img = get_the_post_thumbnail(
			$post->ID,
			'large',
			array(
				'fetchpriority' => false,
				'decoding' => false,
				'class' => false,
				'style' => 'margin-bottom:15px;',
			)
		);
		remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
		return '<div>' . $img . '</div>' . $content;
	}
);

/**
 * Basic hardening headers for public pages.
 */
add_action(
	'send_headers',
	function () {
		if ( is_admin() || ! sm_get( 'security_headers' ) ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), camera=(), microphone=(), midi=(), accelerometer=(), gyroscope=(), magnetometer=(), payment=()' );
	}
);

/**
 * Verify authorship for Mastodon (fediverse:creator) when a handle is set.
 */
add_action(
	'wp_head',
	function () {
		$handle = (string) sm_get( 'fediverse_handle' );
		if ( preg_match( '/^@[A-Za-z0-9_]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $handle ) ) {
			echo '<meta name="fediverse:creator" content="' . esc_attr( $handle ) . '">' . "\n";
		}
	}
);
