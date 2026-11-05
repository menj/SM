<?php
/**
 * Newsletter sign-up: AJAX handler, subscriber post type, CSV export.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'sm_subscriber',
			array(
				'labels'              => array(
					'name'          => __( 'Subscribers', 'sm' ),
					'singular_name' => __( 'Subscriber', 'sm' ),
					'menu_name'     => __( 'Subscribers', 'sm' ),
					'all_items'     => __( 'Subscribers', 'sm' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'themes.php',
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}
);

/**
 * Newsletter form markup. Used by the block and the pattern.
 *
 * @param array $args { heading, class }.
 * @return string
 */
function sm_newsletter_form_html( $args = array() ) {
	$args    = wp_parse_args( $args, array( 'heading' => sm_get( 'nl_heading' ), 'class' => '' ) );
	$action  = sm_get( 'nl_action' );
	$heading = nl2br( esc_html( $args['heading'] ) );
	$uid     = wp_unique_id( 'sm-nl-' );

	$html  = '<aside class="sm-newsletter alignwide ' . esc_attr( $args['class'] ) . '" aria-labelledby="' . $uid . '-h">';
	$html .= '<h2 class="sm-newsletter__heading" id="' . $uid . '-h">' . $heading . '</h2>';
	$html .= '<form class="sm-newsletter__form sm-ajax-form" method="post" action="' . esc_url( $action ? $action : admin_url( 'admin-ajax.php' ) ) . '" data-sm-form="newsletter"' . ( $action ? ' data-external="1" target="_blank"' : '' ) . ' novalidate>';
	$html .= '<label class="screen-reader-text" for="' . $uid . '-name">' . esc_html__( 'Name', 'sm' ) . '</label>';
	$html .= '<input id="' . $uid . '-name" type="text" name="' . ( $action ? 'FNAME' : 'name' ) . '" placeholder="' . esc_attr__( 'Name', 'sm' ) . '" autocomplete="name" />';
	$html .= '<label class="screen-reader-text" for="' . $uid . '-email">' . esc_html__( 'E-mail', 'sm' ) . '</label>';
	$html .= '<input id="' . $uid . '-email" type="email" name="' . ( $action ? 'EMAIL' : 'email' ) . '" placeholder="' . esc_attr__( 'E-mail', 'sm' ) . '" autocomplete="email" required />';
	$html .= '<input type="text" name="website" class="sm-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />';
	$html .= '<input type="hidden" name="action" value="sm_subscribe" />';
	$html .= '<input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'sm_front' ) ) . '" />';
	$html .= '<button type="submit" class="sm-newsletter__submit">' . esc_html__( 'Submit', 'sm' ) . '</button>';
	$html .= '<p class="sm-form__message" role="status" aria-live="polite"></p>';
	$html .= '</form></aside>';
	return $html;
}

add_shortcode(
	'sm_newsletter',
	function ( $atts ) {
		return sm_newsletter_form_html( shortcode_atts( array( 'heading' => sm_get( 'nl_heading' ), 'class' => '' ), $atts ) );
	}
);

/**
 * AJAX: store a subscriber.
 */
function sm_ajax_subscribe() {
	check_ajax_referer( 'sm_front', 'nonce' );

	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		wp_send_json_success( array( 'message' => sm_get( 'nl_success' ) ) );
	}
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'sm' ) ), 400 );
	}

	$existing = get_posts(
		array(
			'post_type'      => 'sm_subscriber',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_sm_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $existing ) {
		wp_send_json_success( array( 'message' => __( 'You are already subscribed. Thank you!', 'sm' ) ) );
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'sm_subscriber',
			'post_status' => 'publish',
			'post_title'  => $name ? $name . ' (' . $email . ')' : $email,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not save your subscription. Please try again.', 'sm' ) ), 500 );
	}
	update_post_meta( $id, '_sm_email', $email );
	update_post_meta( $id, '_sm_name', $name );
	update_post_meta( $id, '_sm_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );

	if ( sm_get( 'nl_notify' ) ) {
		wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'New newsletter subscriber', 'sm' ) ),
			sprintf( "%s\n%s\n\n%s", $name, $email, admin_url( 'edit.php?post_type=sm_subscriber' ) )
		);
	}
	do_action( 'sm_subscribed', $id, $email, $name );
	wp_send_json_success( array( 'message' => sm_get( 'nl_success' ) ) );
}
add_action( 'wp_ajax_sm_subscribe', 'sm_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_sm_subscribe', 'sm_ajax_subscribe' );

/* ---- Admin list columns and CSV export ---- */

add_filter(
	'manage_sm_subscriber_posts_columns',
	function ( $cols ) {
		return array(
			'cb'    => $cols['cb'],
			'title' => __( 'Subscriber', 'sm' ),
			'email' => __( 'Email', 'sm' ),
			'name'  => __( 'Name', 'sm' ),
			'date'  => __( 'Subscribed', 'sm' ),
		);
	}
);
add_action(
	'manage_sm_subscriber_posts_custom_column',
	function ( $col, $id ) {
		if ( 'email' === $col ) {
			echo esc_html( get_post_meta( $id, '_sm_email', true ) );
		} elseif ( 'name' === $col ) {
			echo esc_html( get_post_meta( $id, '_sm_name', true ) );
		}
	},
	10,
	2
);

add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( in_array( $post_type, array( 'sm_subscriber', 'sm_entry' ), true ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=sm_export&type=' . $post_type ), 'sm_export' );
			echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Export CSV', 'sm' ) . '</a>';
		}
	}
);

add_action(
	'admin_post_sm_export',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'sm' ) );
		}
		check_admin_referer( 'sm_export' );
		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'sm_subscriber';
		if ( ! in_array( $type, array( 'sm_subscriber', 'sm_entry' ), true ) ) {
			wp_die( esc_html__( 'Unknown export.', 'sm' ) );
		}
		$posts = get_posts( array( 'post_type' => $type, 'posts_per_page' => -1, 'post_status' => 'any', 'orderby' => 'date', 'order' => 'DESC' ) );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $type . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( 'sm_subscriber' === $type ) {
			fputcsv( $out, array( 'date', 'name', 'email' ) );
			foreach ( $posts as $p ) {
				fputcsv( $out, array( $p->post_date, get_post_meta( $p->ID, '_sm_name', true ), get_post_meta( $p->ID, '_sm_email', true ) ) );
			}
		} else {
			$keys = array();
			$rows = array();
			foreach ( $posts as $p ) {
				$data = (array) get_post_meta( $p->ID, '_sm_fields', true );
				$keys = array_unique( array_merge( $keys, array_keys( $data ) ) );
				$rows[] = array_merge( array( 'date' => $p->post_date ), $data );
			}
			$header = array_merge( array( 'date' ), $keys );
			fputcsv( $out, $header );
			foreach ( $rows as $r ) {
				$line = array();
				foreach ( $header as $k ) {
					$line[] = isset( $r[ $k ] ) ? $r[ $k ] : '';
				}
				fputcsv( $out, $line );
			}
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
);
