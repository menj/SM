<?php
/**
 * Small shared helpers.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default option values. Colours default to the Suara Malaysia mockup palette.
 *
 * @return array
 */
function sm_default_settings() {
	return array(
		// General.
		'phone'             => '+60 1234 1234',
		'email'             => 'contact@suaramalaysia.com',
		'instagram'         => 'https://www.instagram.com/',
		'facebook'          => '',
		'x'                 => '',
		'copyright'         => sprintf( 'Copyright © %s - Suara Malaysia - All rights reserved', gmdate( 'Y' ) ),
		'back_to_top'       => 1,
		// Colours.
		'scheme'            => 'suara',
		'color_primary'     => '#ce2127',
		'color_tag'         => '#fecd07',
		'color_newsletter'  => '#211f60',
		'color_link'        => '#211f60',
		'color_text'        => '#1b1a40',
		'color_surface'     => '#f2f3f9',
		// Homepage.
		'hero_source'       => 'sticky',
		'home_sections'     => array(),
		'section_posts'     => 2,
		'show_excerpt'      => 0,
		// Newsletter.
		'nl_heading'        => "Subscribe To Our\nLatest News & Update",
		'nl_action'         => '',
		'nl_notify'         => 1,
		'nl_success'        => 'Thank you. You are now subscribed.',
		// Contact and donate.
		'contact_to'        => '',
		'contact_intro'     => 'Have a story, a tip or a correction? Send us a message and we will get back to you.',
		'donate_url'        => '',
		'donate_amounts'    => '25,50,100',
		'donate_currency'   => 'RM',
		'donate_types'      => 'General support',
	);
}

/**
 * Read one setting with its default.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function sm_get( $key ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = wp_parse_args( (array) get_option( 'sm_settings', array() ), sm_default_settings() );
	}
	$value = isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	return apply_filters( 'sm_setting', $value, $key );
}

/**
 * Colour presets selectable from the Colours tab.
 *
 * @return array
 */
function sm_color_schemes() {
	return array(
		'suara'  => array(
			'label'            => __( 'Suara Brand (default, from the SM logo)', 'sm' ),
			'color_primary'    => '#ce2127',
			'color_tag'        => '#fecd07',
			'color_newsletter' => '#211f60',
			'color_link'       => '#211f60',
			'color_text'       => '#1b1a40',
			'color_surface'    => '#f2f3f9',
		),
		'rose'   => array(
			'label'            => __( 'Brand with rose panel', 'sm' ),
			'color_primary'    => '#ce2127',
			'color_tag'        => '#fecd07',
			'color_newsletter' => '#ef9a9d',
			'color_link'       => '#211f60',
			'color_text'       => '#1b1a40',
			'color_surface'    => '#f2f3f9',
		),
		'mockup' => array(
			'label'            => __( 'Mockup Red (brighter)', 'sm' ),
			'color_primary'    => '#ff0007',
			'color_tag'        => '#fdcb30',
			'color_newsletter' => '#ff9797',
			'color_link'       => '#162955',
			'color_text'       => '#202124',
			'color_surface'    => '#f4f4f4',
		),
		'navy'   => array(
			'label'            => __( 'Navy', 'sm' ),
			'color_primary'    => '#162955',
			'color_tag'        => '#fdcb30',
			'color_newsletter' => '#c9d3ea',
			'color_link'       => '#162955',
			'color_text'       => '#1a1a1a',
			'color_surface'    => '#f3f5f9',
		),
		'forest' => array(
			'label'            => __( 'Forest', 'sm' ),
			'color_primary'    => '#0f6b3e',
			'color_tag'        => '#f2c94c',
			'color_newsletter' => '#bfe3cf',
			'color_link'       => '#0b3d26',
			'color_text'       => '#1b1b1b',
			'color_surface'    => '#f2f7f4',
		),
		'mono'   => array(
			'label'            => __( 'Monochrome', 'sm' ),
			'color_primary'    => '#111111',
			'color_tag'        => '#e6e6e6',
			'color_newsletter' => '#e9e9e9',
			'color_link'       => '#111111',
			'color_text'       => '#111111',
			'color_surface'    => '#f5f5f5',
		),
		'custom' => array( 'label' => __( 'Custom', 'sm' ) ),
	);
}

/**
 * Pick black or white text for a given background hex.
 *
 * @param string $hex Hex colour.
 * @return string
 */
function sm_contrast_text( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	$l = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
	return $l > 0.6 ? '#202124' : '#ffffff';
}

/**
 * Categories shown as homepage sections, in order.
 *
 * Falls back to all non-empty categories (by name) when none are chosen.
 *
 * @return WP_Term[]
 */
function sm_home_section_terms() {
	$ids = array_filter( array_map( 'absint', (array) sm_get( 'home_sections' ) ) );
	if ( $ids ) {
		$terms = array();
		foreach ( $ids as $id ) {
			$t = get_term( $id, 'category' );
			if ( $t && ! is_wp_error( $t ) ) {
				$terms[] = $t;
			}
		}
		return $terms;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
			'orderby'    => 'name',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Category tag markup (yellow label) for a post.
 *
 * @param int|WP_Post $post Post.
 * @return string
 */
function sm_category_tag( $post ) {
	$cats = get_the_category( $post );
	if ( ! $cats ) {
		return '';
	}
	$cat = $cats[0];
	return sprintf(
		'<a class="sm-tag" href="%s">%s</a>',
		esc_url( get_category_link( $cat ) ),
		esc_html( $cat->name )
	);
}

/**
 * Single card markup used by hero side stories, sections and related posts.
 *
 * @param WP_Post $post    Post.
 * @param array   $args    { show_tag, show_image, show_excerpt, image_size, class }.
 * @return string
 */
function sm_post_card( $post, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'show_tag'     => false,
			'show_image'   => true,
			'show_excerpt' => false,
			'image_size'   => 'sm-card',
			'class'        => '',
			'heading'      => 'h3',
		)
	);
	$link  = get_permalink( $post );
	$cats  = get_the_category( $post );
	$color = $cats ? sm_cat_color( $cats[0] ) : 'var(--sm-primary)';
	$html  = '<article class="sm-card ' . esc_attr( $args['class'] ) . '" style="--sm-cat:' . esc_attr( $color ) . '">';
	if ( $args['show_image'] ) {
		$html .= '<a class="sm-card__media" href="' . esc_url( $link ) . '" aria-hidden="true" tabindex="-1">';
		if ( $cats ) {
			$html .= '<span class="sm-card__tab">' . esc_html( $cats[0]->name ) . '</span>';
		}
		if ( has_post_thumbnail( $post ) ) {
			$html .= get_the_post_thumbnail( $post, $args['image_size'], array( 'loading' => 'lazy' ) );
		} else {
			$html .= sm_placeholder_html( $post );
		}
		$html .= '</a>';
	}
	$html .= '<div class="sm-card__body">';
	if ( $args['show_tag'] ) {
		$html .= sm_category_tag( $post );
	}
	$h     = tag_escape( $args['heading'] );
	$html .= '<' . $h . ' class="sm-card__title"><a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $post ) ) . '</a></' . $h . '>';
	if ( $args['show_excerpt'] ) {
		$html .= '<p class="sm-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $post ), 22 ) ) . '</p>';
	}
	$html .= '</div></article>';
	return $html;
}

/**
 * Branded fallback shown where a story has no featured image.
 *
 * @param int|WP_Post $post Post.
 * @return string
 */
function sm_placeholder_html( $post ) {
	$cats  = get_the_category( $post );
	$label = $cats ? $cats[0]->name : get_bloginfo( 'name' );
	return '<span class="sm-card__placeholder"><span class="sm-card__placeholder-label">' . esc_html( $label ) . '</span></span>';
}

/**
 * Estimated reading time in minutes.
 *
 * @param int|WP_Post $post Post.
 * @return int
 */
function sm_reading_minutes( $post = null ) {
	$post  = get_post( $post );
	$words = $post ? str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ) : 0;
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Brand colour assigned to a category, cycling through four logo-derived hues.
 *
 * @param int|WP_Term $term Category.
 * @return string CSS colour (a custom property).
 */
function sm_cat_color( $term ) {
	$id = is_object( $term ) ? (int) $term->term_id : (int) $term;
	return 'var(--sm-c' . ( ( $id % 4 ) + 1 ) . ')';
}

/**
 * One-time repair: earlier versions blanked every setting whenever one value was saved
 * programmatically (for example by the demo importer). If the tell-tale blanks are
 * present, drop the blanked keys so the defaults apply again. Runs once.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'sm_settings_repaired' ) ) {
			return;
		}
		$s = get_option( 'sm_settings', null );
		if ( is_array( $s ) && isset( $s['nl_heading'], $s['phone'], $s['copyright'] ) && '' === $s['nl_heading'] && '' === $s['phone'] && '' === $s['copyright'] ) {
			$keep = array();
			foreach ( array( 'home_sections' ) as $k ) {
				if ( isset( $s[ $k ] ) ) {
					$keep[ $k ] = $s[ $k ];
				}
			}
			update_option( 'sm_settings', $keep );
		}
		update_option( 'sm_settings_repaired', 1, false );
	},
	1
);

/**
 * Give every category link its brand colour and a tab class, so core Query Loop and
 * Post Terms blocks show the same coloured tabs as the theme's own cards.
 */
add_filter(
	'term_links-category',
	function ( $links ) {
		return array_map(
			function ( $link ) {
				if ( ! preg_match( '#/category/(?:[^"/]+/)*([^"/]+)/?"#', $link, $m ) ) {
					return $link;
				}
				$term = get_category_by_slug( $m[1] );
				if ( ! $term ) {
					return $link;
				}
				return preg_replace( '#<a #', '<a class="sm-cat-tab" style="--sm-cat:' . esc_attr( sm_cat_color( $term ) ) . '" ', $link, 1 );
			},
			(array) $links
		);
	}
);

/**
 * Stories already shown on this page, so one block never repeats another's stories.
 *
 * @param int[]  $add   IDs to record (omit to just read).
 * @param string $scope 'all' for everything shown so far, 'hero' for the lead block only.
 * @return int[]
 */
function sm_shown_ids( $add = array(), $scope = 'all' ) {
	static $all  = array();
	static $hero = array();
	$add = array_map( 'intval', (array) $add );
	if ( $add ) {
		$all = array_values( array_unique( array_merge( $all, $add ) ) );
		if ( 'hero' === $scope ) {
			$hero = array_values( array_unique( array_merge( $hero, $add ) ) );
		}
	}
	return 'hero' === $scope ? $hero : $all;
}

/**
 * Simple per-visitor rate limit for the public forms.
 *
 * Page caches freeze nonces into the HTML, which breaks forms after the nonce expires,
 * and an anonymous nonce adds no real protection. A honeypot plus this limit does the job.
 *
 * @param string $bucket Name of the form.
 * @param int    $max    Submissions allowed per window.
 * @param int    $window Window in seconds.
 * @return bool True when the visitor has sent too many.
 */
function sm_rate_limited( $bucket, $max = 5, $window = 600 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'sm_rl_' . md5( $bucket . '|' . $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return true;
	}
	set_transient( $key, $n + 1, $window );
	return false;
}
