<?php
/**
 * Lightweight SEO output: document title, meta description, canonical,
 * Open Graph / Twitter cards and NewsArticle JSON-LD.
 *
 * Steps aside automatically when Yoast SEO, Rank Math, SEOPress or
 * All in One SEO is active.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Another SEO plugin present?
 *
 * @return bool
 */
function sm_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( sm_seo_plugin_active() || ! is_singular() ) {
			return $title;
		}
		$custom = get_post_meta( get_queried_object_id(), '_sm_seo_title', true );
		return $custom ? $custom : $title;
	}
);

/**
 * Description for the current request.
 *
 * @return string
 */
function sm_seo_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
	} elseif ( is_category() || is_tag() ) {
		$desc = term_description() ?: sprintf( /* translators: term name */ __( 'The latest %s stories from Suara Malaysia.', 'sm' ), single_term_title( '', false ) );
	} else {
		$desc = sm_get( 'meta_description' ) ?: get_bloginfo( 'description' );
	}
	return trim( wp_strip_all_tags( (string) $desc ) );
}

add_action(
	'wp_head',
	function () {
		if ( sm_seo_plugin_active() ) {
			return;
		}
		$desc  = sm_seo_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
		$image = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = get_the_post_thumbnail_url( null, 'sm-hero' );
		} elseif ( get_theme_mod( 'custom_logo' ) ) {
			$image = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
		}

		echo "\n<!-- SM SEO -->\n";
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( $url ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
		}
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc ) {
			echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( $url ) {
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		}
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
		if ( is_singular( 'post' ) ) {
			echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
			$ld = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'NewsArticle',
				'headline'         => get_the_title(),
				'description'      => $desc,
				'datePublished'    => get_the_date( 'c' ),
				'dateModified'     => get_the_modified_date( 'c' ),
				'mainEntityOfPage' => get_permalink(),
				'author'           => array( '@type' => 'Person', 'name' => get_the_author() ),
				'publisher'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'logo' => $image ? array( '@type' => 'ImageObject', 'url' => $image ) : null ),
				'image'            => $image ? array( $image ) : null,
			);
			echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $ld ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
		}
		echo "<!-- /SM SEO -->\n";
	},
	1
);

/* "SEO title" field in the editor sidebar (block editor via REST meta, classic via meta box). */
add_action(
	'init',
	function () {
		foreach ( array( 'post', 'page' ) as $pt ) {
			register_post_meta(
				$pt,
				'_sm_seo_title',
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
);
