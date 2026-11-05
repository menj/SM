<?php
/**
 * Lightweight view counter that powers the "Most Read" list.
 *
 * Stores a single integer per post. No visitor data is kept. Logged-in users,
 * previews and obvious bots are not counted.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

const SM_VIEWS_META = '_sm_views';

add_action(
	'template_redirect',
	function () {
		if ( ! is_singular( 'post' ) || is_user_logged_in() || is_preview() || is_feed() ) {
			return;
		}
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
		if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|headless|preview|facebookexternalhit|curl|wget/', $ua ) ) {
			return;
		}
		$id    = get_queried_object_id();
		$views = (int) get_post_meta( $id, SM_VIEWS_META, true );
		update_post_meta( $id, SM_VIEWS_META, $views + 1 );
	}
);

/**
 * Most read posts, falling back to the latest when nothing has been read yet.
 *
 * @param int $count Number of posts.
 * @return WP_Post[]
 */
function sm_most_read_posts( $count = 5 ) {
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'meta_key'            => SM_VIEWS_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_query'          => array( array( 'key' => SM_VIEWS_META, 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);
	if ( count( $posts ) < $count ) {
		$more  = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $count - count( $posts ),
				'post__not_in'        => wp_list_pluck( $posts, 'ID' ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		$posts = array_merge( $posts, $more );
	}
	return $posts;
}
