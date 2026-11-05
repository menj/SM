<?php
/**
 * Server-rendered blocks used by the templates and patterns.
 *
 * sm/hero          Featured story + two side stories.
 * sm/sections      Per-category story grid for the homepage.
 * sm/related       "You may be interested…" on single posts.
 * sm/newsletter    Rose newsletter panel.
 * sm/contact-form  Contact form with stored entries.
 * sm/donate-form   Donation chooser that hands off to the checkout URL.
 * sm/social-icons  Social profile icons.
 * sm/section-heading  "Politics — View All" heading row.
 *
 * Editor previews come from assets/js/editor.js using ServerSideRender.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		$blocks = array(
			'hero'            => 'sm_render_hero',
			'sections'        => 'sm_render_sections',
			'related'         => 'sm_render_related',
			'newsletter'      => 'sm_render_newsletter_block',
			'contact-form'    => 'sm_render_contact_block',
			'donate-form'     => 'sm_render_donate_block',
			'social-icons'    => 'sm_render_social_block',
			'section-heading' => 'sm_render_section_heading_block',
		);
		foreach ( $blocks as $name => $cb ) {
			register_block_type(
				'sm/' . $name,
				array(
					'api_version'     => 3,
					'render_callback' => $cb,
					'attributes'      => sm_block_attributes( $name ),
					'supports'        => array( 'html' => false, 'align' => array( 'wide', 'full' ) ),
				)
			);
		}
	}
);

/**
 * Attribute schema per block.
 *
 * @param string $name Block short name.
 * @return array
 */
function sm_block_attributes( $name ) {
	$common = array(
		'align'     => array( 'type' => 'string', 'default' => 'wide' ),
		'className' => array( 'type' => 'string', 'default' => '' ),
	);
	switch ( $name ) {
		case 'hero':
			return $common + array( 'sideCount' => array( 'type' => 'number', 'default' => 2 ) );
		case 'sections':
			return $common + array(
				'columns' => array( 'type' => 'number', 'default' => 3 ),
				'perSection' => array( 'type' => 'number', 'default' => 0 ),
			);
		case 'related':
			return $common + array(
				'count' => array( 'type' => 'number', 'default' => 3 ),
				'title' => array( 'type' => 'string', 'default' => '' ),
			);
		case 'newsletter':
			return $common + array( 'heading' => array( 'type' => 'string', 'default' => '' ) );
		case 'social-icons':
			return $common + array(
				'style' => array( 'type' => 'string', 'default' => '' ),
				'size'  => array( 'type' => 'number', 'default' => 0 ),
				'label' => array( 'type' => 'string', 'default' => '' ),
			);
		case 'section-heading':
			return $common + array(
				'title' => array( 'type' => 'string', 'default' => '' ),
				'link'  => array( 'type' => 'string', 'default' => '' ),
				'level' => array( 'type' => 'number', 'default' => 2 ),
			);
		default:
			return $common;
	}
}

/**
 * Wrapper attributes helper.
 *
 * @param array  $attrs Block attributes.
 * @param string $class Extra class.
 * @return string
 */
function sm_block_wrap( $attrs, $class ) {
	$classes = array( $class );
	if ( ! empty( $attrs['align'] ) ) {
		$classes[] = 'align' . $attrs['align'];
	}
	if ( ! empty( $attrs['className'] ) ) {
		$classes[] = $attrs['className'];
	}
	return 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
}

/**
 * Section heading row: title, red rule, "View All".
 *
 * @param string $title Title.
 * @param string $link  Optional URL.
 * @param int    $level Heading level.
 * @return string
 */
function sm_section_heading_html( $title, $link = '', $level = 2 ) {
	$tag  = 'h' . max( 1, min( 6, (int) $level ) );
	$html = '<div class="sm-section-heading"><' . $tag . ' class="sm-section-heading__title">';
	$html .= $link ? '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a>' : esc_html( $title );
	$html .= '</' . $tag . '><span class="sm-section-heading__rule" aria-hidden="true"></span>';
	if ( $link ) {
		$html .= '<a class="sm-section-heading__all" href="' . esc_url( $link ) . '">' . esc_html__( 'View All', 'sm' ) . ' <span aria-hidden="true">&#8599;</span></a>';
	}
	return $html . '</div>';
}

function sm_render_section_heading_block( $attrs ) {
	return sm_section_heading_html( $attrs['title'] ?: __( 'Section', 'sm' ), $attrs['link'], $attrs['level'] );
}

/**
 * Hero: one big story with overlay plus side stories.
 */
function sm_render_hero( $attrs ) {
	$side_count = max( 0, (int) ( $attrs['sideCount'] ?? 2 ) );
	$args       = array(
		'post_type'           => 'post',
		'posts_per_page'      => 1 + $side_count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( 'sticky' === sm_get( 'hero_source' ) ) {
		$sticky = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts' ) ) ) );
		if ( $sticky ) {
			$lead = get_posts( array( 'post__in' => $sticky, 'posts_per_page' => 1, 'orderby' => 'date', 'post_type' => 'post' ) );
			if ( $lead ) {
				$args['post__not_in']   = array( $lead[0]->ID );
				$args['posts_per_page'] = $side_count;
			}
		}
	}
	$rest  = get_posts( $args );
	$posts = isset( $lead ) && $lead ? array_merge( $lead, $rest ) : $rest;
	if ( ! $posts ) {
		return '';
	}
	$main = array_shift( $posts );
	$html = '<section ' . sm_block_wrap( $attrs, 'sm-hero' ) . ' aria-label="' . esc_attr__( 'Featured stories', 'sm' ) . '">';
	$html .= '<article class="sm-hero__main">';
	$html .= '<a class="sm-hero__media" href="' . esc_url( get_permalink( $main ) ) . '">';
	$html .= has_post_thumbnail( $main ) ? get_the_post_thumbnail( $main, 'sm-hero', array( 'fetchpriority' => 'high' ) ) : '<span class="sm-card__placeholder"></span>';
	$html .= '</a><div class="sm-hero__overlay">' . sm_category_tag( $main );
	$html .= '<h2 class="sm-hero__title"><a href="' . esc_url( get_permalink( $main ) ) . '">' . esc_html( get_the_title( $main ) ) . '</a></h2>';
	$excerpt = has_excerpt( $main ) ? get_the_excerpt( $main ) : wp_trim_words( wp_strip_all_tags( $main->post_content ), 16 );
	if ( $excerpt ) {
		$html .= '<p class="sm-hero__excerpt">' . esc_html( $excerpt ) . '</p>';
	}
	$html .= '</div></article>';
	if ( $posts ) {
		$html .= '<div class="sm-hero__side">';
		foreach ( $posts as $p ) {
			$html .= sm_post_card( $p, array( 'show_tag' => true, 'show_image' => false, 'class' => 'sm-card--side' ) );
		}
		$html .= '</div>';
	}
	return $html . '</section>';
}

/**
 * Homepage sections grid.
 */
function sm_render_sections( $attrs ) {
	$terms = sm_home_section_terms();
	if ( ! $terms ) {
		return '';
	}
	$per     = (int) ( $attrs['perSection'] ?? 0 ) ?: max( 1, (int) sm_get( 'section_posts' ) );
	$columns = max( 1, min( 4, (int) ( $attrs['columns'] ?? 3 ) ) );
	$excerpt = (bool) sm_get( 'show_excerpt' );
	$html    = '<div ' . sm_block_wrap( $attrs, 'sm-sections' ) . ' style="--sm-columns:' . $columns . '">';
	foreach ( $terms as $term ) {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => $per,
				'cat'            => $term->term_id,
				'no_found_rows'  => true,
			)
		);
		if ( ! $posts ) {
			continue;
		}
		$html .= '<section class="sm-sections__item" aria-labelledby="sm-sec-' . (int) $term->term_id . '">';
		$html .= str_replace( '<h2 class="sm-section-heading__title">', '<h2 class="sm-section-heading__title" id="sm-sec-' . (int) $term->term_id . '">', sm_section_heading_html( $term->name, get_category_link( $term ) ) );
		foreach ( $posts as $p ) {
			$html .= sm_post_card( $p, array( 'show_excerpt' => $excerpt ) );
		}
		$html .= '</section>';
	}
	return $html . '</div>';
}

/**
 * Related posts on single.
 */
function sm_render_related( $attrs ) {
	$post = get_post();
	if ( ! $post || ! is_singular( 'post' ) && ! defined( 'REST_REQUEST' ) ) {
		return '';
	}
	$count = max( 1, (int) ( $attrs['count'] ?? 3 ) );
	$cats  = wp_get_post_categories( $post->ID );
	$args  = array(
		'post_type'      => 'post',
		'posts_per_page' => $count,
		'post__not_in'   => array( $post->ID ),
		'no_found_rows'  => true,
	);
	if ( $cats ) {
		$args['category__in'] = $cats;
	}
	$posts = get_posts( $args );
	if ( count( $posts ) < $count ) {
		$more = get_posts( array( 'post_type' => 'post', 'posts_per_page' => $count - count( $posts ), 'post__not_in' => array_merge( array( $post->ID ), wp_list_pluck( $posts, 'ID' ) ), 'no_found_rows' => true ) );
		$posts = array_merge( $posts, $more );
	}
	if ( ! $posts ) {
		return '';
	}
	$title = $attrs['title'] ?: __( 'You may be interested…', 'sm' );
	$html  = '<section ' . sm_block_wrap( $attrs, 'sm-related' ) . '>';
	$html .= str_replace( 'sm-section-heading__title', 'sm-section-heading__title sm-section-heading__title--small', sm_section_heading_html( $title ) );
	$html .= '<div class="sm-grid sm-grid--3">';
	foreach ( $posts as $p ) {
		$html .= sm_post_card( $p );
	}
	return $html . '</div></section>';
}

function sm_render_newsletter_block( $attrs ) {
	return sm_newsletter_form_html( array( 'heading' => $attrs['heading'] ?: sm_get( 'nl_heading' ), 'class' => trim( ( $attrs['className'] ?? '' ) . ' align' . ( $attrs['align'] ?? 'wide' ) ) ) );
}

function sm_render_contact_block( $attrs ) {
	return sm_contact_form_html( array( 'class' => $attrs['className'] ?? '' ) );
}

function sm_render_social_block( $attrs ) {
	return sm_social_icons_html(
		array(
			'style' => $attrs['style'] ?: sm_get( 'social_style' ),
			'size'  => $attrs['size'] ?: sm_get( 'social_size' ),
			'label' => $attrs['label'],
			'class' => $attrs['className'] ?? '',
		)
	);
}

/**
 * Donate form: type, amount, frequency → checkout URL with query args.
 */
function sm_render_donate_block( $attrs ) {
	$amounts  = array_filter( array_map( 'intval', explode( ',', (string) sm_get( 'donate_amounts' ) ) ) );
	$types    = array_filter( array_map( 'trim', explode( "\n", (string) sm_get( 'donate_types' ) ) ) );
	$currency = sm_get( 'donate_currency' );
	$url      = sm_get( 'donate_url' );
	$uid      = wp_unique_id( 'sm-donate-' );

	$html  = '<form class="sm-donate ' . esc_attr( $attrs['className'] ?? '' ) . '" method="get" action="' . esc_url( $url ?: home_url( '/' ) ) . '" data-sm-form="donate"' . ( $url ? '' : ' data-no-checkout="1"' ) . '>';
	if ( $types ) {
		$html .= '<div class="sm-donate__group"><label for="' . $uid . '-type">' . esc_html__( 'Choose a donation type', 'sm' ) . '</label><select id="' . $uid . '-type" name="type">';
		foreach ( $types as $t ) {
			$html .= '<option value="' . esc_attr( $t ) . '">' . esc_html( $t ) . '</option>';
		}
		$html .= '</select></div>';
	}
	$html .= '<fieldset class="sm-donate__group"><legend>' . esc_html__( 'Choose a donation amount', 'sm' ) . '</legend><div class="sm-donate__amounts">';
	$i = 0;
	foreach ( $amounts as $a ) {
		$html .= '<label class="sm-radio"><input type="radio" name="amount" value="' . (int) $a . '"' . ( 0 === $i ? ' checked' : '' ) . ' /><span>' . esc_html( $currency . $a ) . '</span></label>';
		$i++;
	}
	$html .= '<label class="sm-radio sm-radio--custom"><input type="radio" name="amount" value="custom" /><span>' . esc_html__( 'Enter a custom donation amount', 'sm' ) . '</span></label>';
	$html .= '<input class="sm-donate__custom" type="number" name="custom_amount" min="1" step="1" placeholder="' . esc_attr( $currency ) . '" aria-label="' . esc_attr__( 'Custom amount', 'sm' ) . '" hidden />';
	$html .= '</div></fieldset>';
	$html .= '<fieldset class="sm-donate__group"><legend>' . esc_html__( 'Choose a donation frequency', 'sm' ) . '</legend><div class="sm-donate__freq">';
	$html .= '<label class="sm-pill"><input type="radio" name="frequency" value="monthly" checked /><span>' . esc_html__( 'Monthly', 'sm' ) . '</span></label>';
	$html .= '<label class="sm-pill"><input type="radio" name="frequency" value="once" /><span>' . esc_html__( 'One time', 'sm' ) . '</span></label>';
	$html .= '</div></fieldset>';
	$html .= '<p class="sm-donate__note">' . esc_html__( 'Donations help us keep independent Malaysian journalism free for everyone. You will be taken to our secure payment partner to complete your gift.', 'sm' ) . '</p>';
	$html .= '<div class="sm-donate__actions"><a class="wp-element-button is-style-sm-outline" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Cancel', 'sm' ) . '</a>';
	$html .= '<button type="submit" class="wp-element-button">' . esc_html__( 'Go to checkout', 'sm' ) . '</button></div>';
	if ( ! $url ) {
		$html .= '<p class="sm-form__message is-error">' . esc_html__( 'No checkout URL has been configured yet (Appearance → SM Theme → Donate).', 'sm' ) . '</p>';
	}
	return $html . '</form>';
}

/* Editor registration script. */
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script(
			'sm-editor',
			SM_URI . 'assets/js/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-core-data' ),
			SM_VERSION,
			true
		);
		wp_localize_script(
			'sm-editor',
			'smEditor',
			array(
				'socialStyles' => array( 'plain' => __( 'Plain', 'sm' ), 'boxed' => __( 'Boxed', 'sm' ), 'round' => __( 'Round', 'sm' ) ),
				'unlistMeta'   => SM_UNLIST_META,
			)
		);
	}
);
