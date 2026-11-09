<?php
/**
 * Contact form with stored submissions.
 *
 * Replaces Contact Form 7 + CFDB7 for this site: a configurable field list
 * (Appearance → Theme Options → Contact form), AJAX submission with nonce and
 * honeypot, email delivery, an entries list in the admin and CSV export.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'sm_entry',
			array(
				'labels'              => array(
					'name'          => __( 'Form entries', 'sm' ),
					'singular_name' => __( 'Form entry', 'sm' ),
					'menu_name'     => __( 'Form entries', 'sm' ),
					'all_items'     => __( 'Form entries', 'sm' ),
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
 * Parse the field DSL from settings into an array.
 *
 * Line format: type|name|label|required[:opt1,opt2]
 *
 * @return array[]
 */
function sm_contact_fields() {
	$fields = array();
	foreach ( explode( "\n", (string) sm_get( 'contact_fields' ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line ) );
		$type  = strtolower( $parts[0] ?? 'text' );
		$name  = sanitize_key( $parts[1] ?? '' );
		if ( ! $name || ! in_array( $type, array( 'text', 'email', 'tel', 'textarea', 'select', 'url', 'number' ), true ) ) {
			continue;
		}
		$req     = $parts[3] ?? 'no';
		$options = array();
		if ( false !== strpos( $req, ':' ) ) {
			list( $req, $opts ) = explode( ':', $req, 2 );
			$options            = array_map( 'trim', explode( ',', $opts ) );
		}
		$fields[] = array(
			'type'     => $type,
			'name'     => $name,
			'label'    => $parts[2] ?? ucfirst( $name ),
			'required' => in_array( strtolower( trim( $req ) ), array( 'yes', '1', 'true', 'required' ), true ),
			'options'  => $options,
		);
	}
	return $fields;
}

/**
 * Contact form markup.
 *
 * @param array $args { class }.
 * @return string
 */
function sm_contact_form_html( $args = array() ) {
	$args   = wp_parse_args( $args, array( 'class' => '' ) );
	$uid    = wp_unique_id( 'sm-cf-' );
	$fields = sm_contact_fields();

	$html  = '<form class="sm-contact-form sm-ajax-form ' . esc_attr( $args['class'] ) . '" method="post" action="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" data-sm-form="contact" novalidate>';
	$html .= '<div class="sm-contact-form__grid">';
	foreach ( $fields as $i => $f ) {
		$id   = $uid . '-' . $f['name'];
		$req  = $f['required'] ? ' required aria-required="true"' : '';
		$wide = in_array( $f['type'], array( 'textarea' ), true ) || ( 'text' === $f['type'] && 'subject' === $f['name'] ) ? ' is-wide' : '';
		$html .= '<div class="sm-field-wrap' . $wide . '">';
		$html .= '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</label>';
		if ( 'textarea' === $f['type'] ) {
			$html .= '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '" rows="6" placeholder="' . esc_attr( $f['label'] ) . '"' . $req . '></textarea>';
		} elseif ( 'select' === $f['type'] ) {
			$html .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . $req . '><option value="">' . esc_html( $f['label'] ) . '</option>';
			foreach ( $f['options'] as $o ) {
				$html .= '<option value="' . esc_attr( $o ) . '">' . esc_html( $o ) . '</option>';
			}
			$html .= '</select>';
		} else {
			$html .= '<input id="' . esc_attr( $id ) . '" type="' . esc_attr( $f['type'] ) . '" name="' . esc_attr( $f['name'] ) . '" placeholder="' . esc_attr( $f['label'] ) . '"' . $req . ' />';
		}
		$html .= '</div>';
	}
	$html .= '</div>';
	$html .= '<input type="text" name="website" class="sm-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />';
	$html .= '<input type="hidden" name="action" value="sm_contact" />';
	$html .= '<input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'sm_front' ) ) . '" />';
	$html .= '<input type="hidden" name="page" value="' . esc_attr( get_the_ID() ) . '" />';
	$html .= '<button type="submit" class="wp-element-button sm-contact-form__submit">' . esc_html__( 'Send message', 'sm' ) . '</button>';
	$html .= '<p class="sm-form__message" role="status" aria-live="polite"></p>';
	$html .= '</form>';
	return $html;
}

add_shortcode(
	'sm_contact_form',
	function ( $atts ) {
		return sm_contact_form_html( shortcode_atts( array( 'class' => '' ), $atts ) );
	}
);

/**
 * AJAX: validate, store, email.
 */
function sm_ajax_contact() {
	// Public form: no nonce (it would be frozen into cached pages). Honeypot below plus a rate limit instead.
	if ( sm_rate_limited( 'contact' ) ) {
		wp_send_json_error( array( 'message' => __( 'Too many attempts. Please wait a few minutes and try again.', 'sm' ) ), 429 );
	}
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => sm_get( 'contact_success' ) ) );
	}

	$data   = array();
	$errors = array();
	foreach ( sm_contact_fields() as $f ) {
		$raw = isset( $_POST[ $f['name'] ] ) ? wp_unslash( $_POST[ $f['name'] ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		switch ( $f['type'] ) {
			case 'email':
				$val = sanitize_email( $raw );
				if ( $val && ! is_email( $val ) ) {
					$errors[ $f['name'] ] = __( 'Please enter a valid email address.', 'sm' );
				}
				break;
			case 'textarea':
				$val = sanitize_textarea_field( $raw );
				break;
			case 'url':
				$val = esc_url_raw( $raw );
				break;
			case 'select':
				$val = in_array( $raw, $f['options'], true ) ? $raw : '';
				break;
			default:
				$val = sanitize_text_field( $raw );
		}
		if ( $f['required'] && '' === trim( (string) $val ) ) {
			$errors[ $f['name'] ] = sprintf( /* translators: field label */ __( '%s is required.', 'sm' ), $f['label'] );
		}
		$data[ $f['name'] ] = $val;
	}
	if ( $errors ) {
		wp_send_json_error( array( 'message' => reset( $errors ), 'errors' => $errors ), 400 );
	}

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 0;
	$from = $data['name'] ?? ( $data['email'] ?? __( 'Visitor', 'sm' ) );

	if ( sm_get( 'contact_store' ) ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'sm_entry',
				'post_status' => 'publish',
				'post_title'  => sprintf( '%s — %s', $from, wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_sm_fields', $data );
			update_post_meta( $id, '_sm_page', $page );
			update_post_meta( $id, '_sm_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
			update_post_meta( $id, '_sm_ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );
		}
	}

	$to      = sm_get( 'contact_to' ) ?: get_option( 'admin_email' );
	$subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $data['subject'] ?? __( 'New contact message', 'sm' ) );
	$body    = '';
	foreach ( sm_contact_fields() as $f ) {
		$body .= $f['label'] . ": " . $data[ $f['name'] ] . "\n";
	}
	$body   .= "\n" . sprintf( /* translators: page url */ __( 'Sent from: %s', 'sm' ), $page ? get_permalink( $page ) : home_url( '/' ) );
	$headers = array();
	if ( ! empty( $data['email'] ) ) {
		$headers[] = 'Reply-To: ' . ( $data['name'] ?? '' ) . ' <' . $data['email'] . '>';
	}
	$sent = wp_mail( $to, $subject, $body, $headers );

	do_action( 'sm_contact_submitted', $data, $sent );
	wp_send_json_success( array( 'message' => sm_get( 'contact_success' ) ) );
}
add_action( 'wp_ajax_sm_contact', 'sm_ajax_contact' );
add_action( 'wp_ajax_nopriv_sm_contact', 'sm_ajax_contact' );

/* ---- Admin: columns and a read-only detail box ---- */

add_filter(
	'manage_sm_entry_posts_columns',
	function ( $cols ) {
		return array(
			'cb'      => $cols['cb'],
			'title'   => __( 'Entry', 'sm' ),
			'email'   => __( 'Email', 'sm' ),
			'message' => __( 'Message', 'sm' ),
			'date'    => __( 'Received', 'sm' ),
		);
	}
);
add_action(
	'manage_sm_entry_posts_custom_column',
	function ( $col, $id ) {
		$data = (array) get_post_meta( $id, '_sm_fields', true );
		if ( 'email' === $col ) {
			echo esc_html( $data['email'] ?? '' );
		} elseif ( 'message' === $col ) {
			echo esc_html( wp_trim_words( $data['message'] ?? '', 18 ) );
		}
	},
	10,
	2
);
add_action(
	'add_meta_boxes_sm_entry',
	function () {
		add_meta_box(
			'sm-entry-details',
			__( 'Submission', 'sm' ),
			function ( $post ) {
				$data = (array) get_post_meta( $post->ID, '_sm_fields', true );
				echo '<table class="widefat striped sm-entry-table"><tbody>';
				foreach ( $data as $k => $v ) {
					echo '<tr><th scope="row">' . esc_html( ucfirst( $k ) ) . '</th><td>' . nl2br( esc_html( $v ) ) . '</td></tr>';
				}
				$page = (int) get_post_meta( $post->ID, '_sm_page', true );
				echo '<tr><th scope="row">' . esc_html__( 'Page', 'sm' ) . '</th><td>' . ( $page ? '<a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a>' : '—' ) . '</td></tr>';
				echo '<tr><th scope="row">IP</th><td>' . esc_html( get_post_meta( $post->ID, '_sm_ip', true ) ) . '</td></tr>';
				echo '</tbody></table>';
			},
			'sm_entry',
			'normal',
			'high'
		);
	}
);
