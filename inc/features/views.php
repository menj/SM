<?php
/**
 * Lightweight view counter that powers the "Most Read" list.
 *
 * Stores a single integer per post. No visitor data is kept. Logged-in users
 * and obvious bots are not counted.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

const SM_VIEWS_META = '_sm_views';

/**
 * Views are counted by a small beacon from the browser (assets/js/theme.js) rather than while the
 * page is built, so pages served from a page cache are counted as well.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'sm/v1',
			'/view',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'type' => 'integer',
						'required' => true,
						'sanitize_callback' => 'absint',
					),
				),
				'callback'            => function ( WP_REST_Request $req ) {
					$id   = (int) $req['id'];
					$post = $id ? get_post( $id ) : null;
					if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || post_password_required( $post ) ) {
						return new WP_REST_Response( null, 204 );
					}
					$ua = strtolower( (string) $req->get_header( 'user_agent' ) );
					if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|headless|preview|facebookexternalhit|curl|wget/', $ua ) ) {
						return new WP_REST_Response( null, 204 );
					}
					// One count per visitor and article every 30 minutes; no visitor data is stored, only a hashed throttle key.
					$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
					$key = 'sm_vw_' . md5( $id . '|' . $ip . '|' . $ua );
					if ( false === get_transient( $key ) ) {
						set_transient( $key, 1, 30 * MINUTE_IN_SECONDS );
						$views = (int) get_post_meta( $id, SM_VIEWS_META, true );
						update_post_meta( $id, SM_VIEWS_META, $views + 1 );
					}
					return new WP_REST_Response( null, 204 );
				},
			)
		);
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
			'post__not_in'        => sm_shown_ids( array(), 'hero' ),
			'no_found_rows'       => true,
			'meta_query'          => array(
		array(
		'key' => SM_VIEWS_META,
		'value' => 0,
		'compare' => '>',
		'type' => 'NUMERIC',
			),
		), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);
	if ( count( $posts ) < $count ) {
		$more  = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $count - count( $posts ),
				'post__not_in'        => array_merge( wp_list_pluck( $posts, 'ID' ), sm_shown_ids( array(), 'hero' ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		$posts = array_merge( $posts, $more );
	}
	return $posts;
}
