<?php
/**
 * Social icons (replaces the Minimalist Social Icons Pack plugin).
 *
 * The 44 single-path SVG glyphs live in /assets/icons/social and inherit
 * `currentColor`, so they follow the theme colour scheme. Profiles are
 * entered under Appearance → SM Theme → Social and rendered through the
 * [sm_social] shortcode or the "Social icons" block.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Network slug → label map, ordered by relevance to a news site.
 *
 * @return array
 */
function sm_social_networks() {
	return array(
		'instagram'         => 'Instagram',
		'facebook'          => 'Facebook',
		'x'                 => 'X (Twitter)',
		'tiktok'            => 'TikTok',
		'youtube'           => 'YouTube',
		'threads'           => 'Threads',
		'whatsapp'          => 'WhatsApp',
		'telegram'          => 'Telegram',
		'linkedin'          => 'LinkedIn',
		'bluesky'           => 'Bluesky',
		'mastodon'          => 'Mastodon',
		'substack'          => 'Substack',
		'medium'            => 'Medium',
		'reddit'            => 'Reddit',
		'pinterest'         => 'Pinterest',
		'snapchat'          => 'Snapchat',
		'discord'           => 'Discord',
		'wechat'            => 'WeChat',
		'line'              => 'LINE',
		'signal'            => 'Signal',
		'tumblr'            => 'Tumblr',
		'twitch'            => 'Twitch',
		'vimeo'             => 'Vimeo',
		'spotify'           => 'Spotify',
		'soundcloud'        => 'SoundCloud',
		'suno'              => 'Suno',
		'github'            => 'GitHub',
		'behance'           => 'Behance',
		'dribbble'          => 'Dribbble',
		'flickr'            => 'Flickr',
		'issuu'             => 'Issuu',
		'scribd'            => 'Scribd',
		'goodreads'         => 'Goodreads',
		'quora'             => 'Quora',
		'wikipedia'         => 'Wikipedia',
		'wikidata'          => 'Wikidata',
		'academia'          => 'Academia.edu',
		'orcid'             => 'ORCID',
		'oclc'              => 'OCLC',
		'viaf'              => 'VIAF',
		'isni'              => 'ISNI',
		'gtribe'            => 'GTribe',
		'fiverr'            => 'Fiverr',
		'wordpress'         => 'WordPress',
		'wordpress-profile' => 'WordPress.org profile',
	);
}

/**
 * Inline SVG for one network, or empty string.
 *
 * @param string $slug Network slug.
 * @return string
 */
function sm_social_icon_svg( $slug ) {
	$slug = sanitize_key( $slug );
	$file = SM_DIR . 'assets/icons/social/' . $slug . '.svg';
	if ( ! is_readable( $file ) ) {
		return '';
	}
	static $cache = array();
	if ( ! isset( $cache[ $slug ] ) ) {
		$svg = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg = preg_replace( '/<title>.*?<\/title>/', '', $svg );
		$svg = str_replace( '<svg ', '<svg aria-hidden="true" focusable="false" fill="currentColor" ', $svg );
		$cache[ $slug ] = $svg;
	}
	return $cache[ $slug ];
}

/**
 * Networks that have a URL configured.
 *
 * @return array slug => url
 */
function sm_social_profiles() {
	$out = array();
	foreach ( sm_social_networks() as $slug => $label ) {
		$url = sm_get( 'social_' . $slug );
		if ( $url ) {
			$out[ $slug ] = $url;
		}
	}
	// Legacy keys from the General defaults.
	foreach ( array( 'instagram', 'facebook', 'x' ) as $slug ) {
		if ( empty( $out[ $slug ] ) && sm_get( $slug ) ) {
			$out[ $slug ] = sm_get( $slug );
		}
	}
	return $out;
}

/**
 * Render the icon list.
 *
 * @param array $args { style, size, class, label }.
 * @return string
 */
function sm_social_icons_html( $args = array() ) {
	$args     = wp_parse_args(
		$args,
		array(
			'style' => sm_get( 'social_style' ),
			'size'  => (int) sm_get( 'social_size' ),
			'class' => '',
			'label' => '',
		)
	);
	$profiles = sm_social_profiles();
	if ( ! $profiles ) {
		return '';
	}
	$networks = sm_social_networks();
	$html     = '<ul class="sm-social sm-social--' . esc_attr( $args['style'] ) . ' ' . esc_attr( $args['class'] ) . '" style="--sm-social-size:' . (int) $args['size'] . 'px">';
	foreach ( $profiles as $slug => $url ) {
		$html .= sprintf(
			'<li><a href="%1$s" rel="me noopener" target="_blank" aria-label="%2$s">%3$s</a></li>',
			esc_url( $url ),
			esc_attr( $networks[ $slug ] ),
			sm_social_icon_svg( $slug )
		);
	}
	$html .= '</ul>';
	if ( $args['label'] ) {
		$html = '<div class="sm-social-wrap"><span class="sm-social__label">' . esc_html( $args['label'] ) . '</span>' . $html . '</div>';
	}
	return $html;
}

add_shortcode(
	'sm_social',
	function ( $atts ) {
		return sm_social_icons_html( shortcode_atts( array( 'style' => sm_get( 'social_style' ), 'size' => sm_get( 'social_size' ), 'class' => '', 'label' => '' ), $atts ) );
	}
);

/**
 * Attribution required by the Font Awesome CC BY 4.0 licence for two icons.
 */
add_action(
	'wp_footer',
	function () {
		$profiles = sm_social_profiles();
		if ( isset( $profiles['linkedin'] ) || isset( $profiles['scribd'] ) ) {
			echo "\n<!-- LinkedIn and Scribd icons by Font Awesome (https://fontawesome.com), CC BY 4.0 -->\n";
		}
	},
	99
);
