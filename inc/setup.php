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
