<?php
/**
 * Unlisted posts and pages (replaces the Unlist Posts & Pages plugin).
 *
 * An "Unlisted" checkbox in the editor sidebar hides the item from archives,
 * the homepage, search, feeds, sitemaps, prev/next navigation and page lists.
 * The item still opens from its direct link and carries a noindex robots tag.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

const SM_UNLIST_META = '_sm_unlisted';

add_action(
	'init',
	function () {
		foreach ( get_post_types( array( 'public' => true ) ) as $pt ) {
			register_post_meta(
				$pt,
				SM_UNLIST_META,
				array(
					'type'          => 'boolean',
					'single'        => true,
					'default'       => false,
					'show_in_rest'  => true,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
);

/**
 * IDs of every unlisted item, cached in an option for cheap lookups.
 *
 * @return int[]
 */
function sm_unlisted_ids() {
	$ids = get_option( 'sm_unlisted_ids', null );
	if ( null === $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'", SM_UNLIST_META ) ) );
		update_option( 'sm_unlisted_ids', $ids, false );
	}
	return array_map( 'intval', (array) $ids );
}

$sm_unlist_refresh = function () {
	delete_option( 'sm_unlisted_ids' );
};
add_action( 'updated_post_meta', function ( $mid, $pid, $key ) use ( $sm_unlist_refresh ) { if ( SM_UNLIST_META === $key ) { $sm_unlist_refresh(); } }, 10, 3 );
add_action( 'added_post_meta', function ( $mid, $pid, $key ) use ( $sm_unlist_refresh ) { if ( SM_UNLIST_META === $key ) { $sm_unlist_refresh(); } }, 10, 3 );
add_action( 'deleted_post_meta', function ( $mids, $pid, $key ) use ( $sm_unlist_refresh ) { if ( SM_UNLIST_META === $key ) { $sm_unlist_refresh(); } }, 10, 3 );
add_action( 'deleted_post', $sm_unlist_refresh );

/**
 * Whether a query should have unlisted items removed.
 *
 * @param WP_Query $query Query.
 * @return bool
 */
function sm_unlist_applies( $query ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( $query->is_singular() || $query->get( 'sm_include_unlisted' ) ) {
		return false;
	}
	// Preview and specific requests by ID are allowed.
	if ( $query->get( 'p' ) || $query->get( 'page_id' ) || $query->get( 'name' ) || $query->get( 'pagename' ) ) {
		return false;
	}
	return (bool) sm_unlisted_ids();
}

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! sm_unlist_applies( $query ) ) {
			return;
		}
		$not_in = array_merge( (array) $query->get( 'post__not_in' ), sm_unlisted_ids() );
		$query->set( 'post__not_in', array_unique( array_map( 'intval', $not_in ) ) );
	},
	20
);

// Prev/next post links.
$sm_unlist_nav = function ( $where ) {
	$ids = sm_unlisted_ids();
	if ( ! $ids || is_admin() ) {
		return $where;
	}
	return $where . ' AND p.ID NOT IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')';
};
add_filter( 'get_next_post_where', $sm_unlist_nav, 20 );
add_filter( 'get_previous_post_where', $sm_unlist_nav, 20 );

// wp_list_pages() and page dropdowns.
add_filter(
	'wp_list_pages_excludes',
	function ( $exclude ) {
		return array_merge( (array) $exclude, sm_unlisted_ids() );
	}
);

// Core sitemaps.
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args ) {
		$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), sm_unlisted_ids() );
		return $args;
	}
);

// noindex on the unlisted item itself.
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_singular() && get_post_meta( get_queried_object_id(), SM_UNLIST_META, true ) ) {
			return wp_robots_no_robots( $robots );
		}
		return $robots;
	}
);

/*
 * Editor UI: a "Visibility" style checkbox in the block editor sidebar
 * (registered from assets/js/editor.js) plus a classic meta box fallback.
 */
add_action(
	'add_meta_boxes',
	function ( $post_type ) {
		if ( ! is_post_type_viewable( $post_type ) ) {
			return;
		}
		add_meta_box(
			'sm-unlist',
			__( 'Listing', 'sm' ),
			function ( $post ) {
				wp_nonce_field( 'sm_unlist_' . $post->ID, 'sm_unlist_nonce' );
				$checked = get_post_meta( $post->ID, SM_UNLIST_META, true );
				echo '<label><input type="checkbox" name="sm_unlisted" value="1" ' . checked( $checked, true, false ) . ' /> ' . esc_html__( 'Unlisted: hide from lists, search and feeds. Reachable by direct link only.', 'sm' ) . '</label>';
			},
			null,
			'side',
			'default',
			array( '__back_compat_meta_box' => true )
		);
	}
);

add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['sm_unlist_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sm_unlist_nonce'] ) ), 'sm_unlist_' . $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! empty( $_POST['sm_unlisted'] ) ) {
			update_post_meta( $post_id, SM_UNLIST_META, true );
		} else {
			delete_post_meta( $post_id, SM_UNLIST_META );
		}
	}
);

// "Unlisted" state label in the posts list.
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		if ( get_post_meta( $post->ID, SM_UNLIST_META, true ) ) {
			$states['sm_unlisted'] = __( 'Unlisted', 'sm' );
		}
		return $states;
	},
	10,
	2
);
