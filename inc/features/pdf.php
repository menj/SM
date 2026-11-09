<?php
/**
 * "Save as PDF" for articles, using html2pdf.js (MIT, bundled in assets/vendor/html2pdf).
 *
 * The ~950KB library is not enqueued. assets/js/pdf.js loads it on the first click only,
 * so there is no cost for readers who never use the button.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! sm_get( 'pdf_enabled' ) || ! is_singular( 'post' ) ) {
			return;
		}
		wp_enqueue_script(
			'sm-pdf',
			SM_URI . 'assets/js/pdf.js',
			array(),
			SM_VERSION,
			array(
				'strategy' => 'defer',
				'in_footer' => true,
			)
		);
		wp_add_inline_script(
			'sm-pdf',
			'window.smPdfCfg = ' . wp_json_encode(
				array(
					'lib'      => SM_URI . 'assets/vendor/html2pdf/html2pdf.bundle.min.js?ver=0.14.0',
					'filename' => sanitize_title( get_the_title() ) . '.pdf',
					'source'   => home_url( '/' ),
					'url'      => get_permalink(),
				)
			) . ';',
			'before'
		);
	}
);
