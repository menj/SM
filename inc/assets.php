<?php
/**
 * Front-end and editor asset loading, plus the colour-scheme CSS variables.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the :root custom-property block from the saved colour settings.
 *
 * Overriding the theme.json preset variables means every block that uses the
 * palette (buttons, backgrounds, text colours) follows the chosen scheme too.
 *
 * @return string
 */
function sm_color_css() {
	$primary    = sanitize_hex_color( sm_get( 'color_primary' ) ) ?: '#ff0007';
	$tag        = sanitize_hex_color( sm_get( 'color_tag' ) ) ?: '#fdcb30';
	$newsletter = sanitize_hex_color( sm_get( 'color_newsletter' ) ) ?: '#ff9797';
	$link       = sanitize_hex_color( sm_get( 'color_link' ) ) ?: '#162955';
	$text       = sanitize_hex_color( sm_get( 'color_text' ) ) ?: '#202124';
	$surface    = sanitize_hex_color( sm_get( 'color_surface' ) ) ?: '#f4f4f4';

	$vars = array(
		'--wp--preset--color--accent-1' => $primary,
		'--wp--preset--color--accent-2' => $tag,
		'--wp--preset--color--accent-3' => $newsletter,
		'--wp--preset--color--accent-4' => $link,
		'--wp--preset--color--contrast' => $text,
		'--wp--preset--color--accent-5' => $surface,
		'--sm-on-primary'               => sm_contrast_text( $primary ),
		'--sm-on-tag'                   => sm_contrast_text( $tag ),
		'--sm-on-newsletter'            => sm_contrast_text( $newsletter ),
	);
	$css  = ':root{';
	foreach ( $vars as $k => $v ) {
		$css .= $k . ':' . $v . ';';
	}
	return $css . '}';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'sm-theme', SM_URI . 'assets/css/theme.css', array( 'twentytwentyfive-style' ), SM_VERSION );
		wp_add_inline_style( 'sm-theme', sm_color_css() );

		wp_enqueue_script(
			'sm-theme',
			SM_URI . 'assets/js/theme.js',
			array(),
			SM_VERSION,
			array(
				'strategy' => 'defer',
				'in_footer' => true,
			)
		);
		wp_localize_script(
			'sm-theme',
			'smTheme',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				// Count views from the browser, so cached pages are counted too (see inc/features/views.php).
				'view'    => is_singular( 'post' ) && ! is_user_logged_in() ? array(
					'id'  => get_queried_object_id(),
					'url' => rest_url( 'sm/v1/view' ),
				) : null,
				'i18n'    => array(
					'sending' => __( 'Sending…', 'sm' ),
					'error'   => __( 'Something went wrong. Please try again.', 'sm' ),
				),
			)
		);
	},
	20
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_add_inline_style( 'wp-edit-blocks', sm_color_css() );
	}
);
