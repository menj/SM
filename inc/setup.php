<?php
/**
 * Theme setup: supports, image sizes, pattern categories, block styles,
 * default navigation menus on first activation.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'sm', SM_DIR . 'languages' );
		add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
		add_image_size( 'sm-hero', 1200, 800, true );
		add_image_size( 'sm-card', 640, 400, true );
		add_image_size( 'sm-thumb', 400, 250, true );
		add_editor_style( 'assets/css/editor.css' );
	},
	11
);

add_action(
	'init',
	function () {
		register_block_pattern_category(
			'sm',
			array(
				'label'       => __( 'Suara Malaysia', 'sm' ),
				'description' => __( 'Sections designed for suaramalaysia.online.', 'sm' ),
			)
		);
		register_block_pattern_category(
			'sm_pages',
			array(
				'label'       => __( 'Suara Malaysia pages', 'sm' ),
				'description' => __( 'Full page layouts: About, Contact, Donate, Legal.', 'sm' ),
			)
		);

		register_block_style(
			'core/group',
			array(
				'name'  => 'sm-panel',
				'label' => __( 'Rose panel', 'sm' ),
			)
		);
		register_block_style(
			'core/heading',
			array(
				'name'  => 'sm-section-heading',
				'label' => __( 'Section heading with rule', 'sm' ),
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'  => 'sm-tag',
				'label' => __( 'Yellow tag', 'sm' ),
			)
		);
		register_block_style(
			'core/button',
			array(
				'name'  => 'sm-outline',
				'label' => __( 'Outline', 'sm' ),
			)
		);
		register_block_style(
			'core/image',
			array(
				'name'  => 'sm-rounded',
				'label' => __( 'Rounded', 'sm' ),
			)
		);
	}
);

/**
 * Let template parts reference navigation menus by slug (`smMenu` attribute)
 * so the seeded menus bind without hard-coded post IDs.
 */
add_filter(
	'render_block_data',
	function ( $parsed ) {
		if ( 'core/navigation' !== ( $parsed['blockName'] ?? '' ) || empty( $parsed['attrs']['smMenu'] ) || ! empty( $parsed['attrs']['ref'] ) ) {
			return $parsed;
		}
		$slug = sanitize_title( $parsed['attrs']['smMenu'] );
		$nav  = get_page_by_path( $slug, OBJECT, 'wp_navigation' );
		if ( $nav ) {
			$parsed['attrs']['ref'] = (int) $nav->ID;
		}
		unset( $parsed['attrs']['smMenu'] );
		return $parsed;
	}
);

/**
 * Give every search form an accessible name. Core omits it, so two search forms on one page
 * (for example the page's and the footer's) cannot be told apart by screen readers.
 */
add_filter(
	'render_block_core/search',
	function ( $html ) {
		static $n = 0;
		// Only look at the <form> tag itself: the field and button inside carry their own labels.
		if ( ! preg_match( '/<form role="search"[^>]*>/', $html, $m ) || false !== strpos( $m[0], 'aria-label=' ) ) {
			return $html;
		}
		$n++;
		if ( false !== strpos( $m[0], 'sm-footer__search' ) ) {
			$label = __( 'Search from the footer', 'sm' );
		} elseif ( 1 === $n ) {
			$label = __( 'Search the site', 'sm' );
		} else {
			/* translators: %d: number of this search form on the page */
			$label = sprintf( __( 'Search articles %d', 'sm' ), $n );
		}
		return preg_replace( '/<form role="search"/', '<form role="search" aria-label="' . esc_attr( $label ) . '"', $html, 1 );
	}
);

/**
 * Tell site owners what this theme stores, in Settings > Privacy > Policy Guide.
 */
add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$text  = '<p class="privacy-policy-tutorial">' . esc_html__( 'The SM theme does not use cookies, analytics or any third-party service on its own. Suggested text:', 'sm' ) . '</p>';
		$text .= '<p>' . esc_html__( 'Contact form: when you send us a message we receive your name, email address, message text, the page you sent it from, your IP address and browser details. We keep them so we can reply and to prevent abuse.', 'sm' ) . '</p>';
		$text .= '<p>' . esc_html__( 'Newsletter: when you subscribe we store your name, email address and IP address until you ask us to remove them.', 'sm' ) . '</p>';
		$text .= '<p>' . esc_html__( 'Article views: we count how many times each article is opened. The count is not linked to you.', 'sm' ) . '</p>';
		wp_add_privacy_policy_content( 'SM', wp_kses_post( wpautop( $text, false ) ) );
	}
);
