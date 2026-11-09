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
			'share'           => 'sm_render_share_block',
			'editors-picks'   => 'sm_render_editors_picks',
			'active-topics'   => 'sm_render_active_topics',
			'most-read'       => 'sm_render_most_read',
			'footer-sections' => 'sm_render_footer_sections',
			'reading-time'    => 'sm_render_reading_time_block',
		);
		// Blocks whose output only changes when content changes are cached briefly.
		$cached = array( 'hero', 'sections', 'editors-picks', 'active-topics', 'most-read', 'footer-sections' );
		foreach ( $blocks as $name => $cb ) {
			if ( in_array( $name, $cached, true ) ) {
				$cb = sm_cached_block( $name, $cb );
			}
			register_block_type(
				'sm/' . $name,
				array(
					'api_version'     => 3,
					'render_callback' => $cb,
					'attributes'      => sm_block_attributes( $name ),
					'supports'        => array(
						'html' => false,
						'align' => array( 'wide', 'full' ),
					),
				)
			);
		}
	}
);

/**
 * Wrap a block renderer in a short-lived cache.
 *
 * The homepage blocks de-duplicate stories through sm_shown_ids(), which is a side effect of
 * rendering. The cache therefore (a) keys on the IDs already shown, and (b) stores and replays
 * the IDs each block adds. Entries are dropped when posts, terms or settings change (see
 * sm_cache_bump) and expire after ten minutes so "Most read" stays fresh.
 *
 * @param string   $name Block short name.
 * @param callable $cb   Original render callback.
 * @return callable
 */
function sm_cached_block( $name, $cb ) {
	return function ( $attrs = array(), $content = '', $block = null ) use ( $name, $cb ) {
		if ( defined( 'REST_REQUEST' ) || is_preview() || is_customize_preview() || is_admin() || sm_cache_disabled() ) {
			return call_user_func( $cb, $attrs, $content, $block );
		}
		$before_all  = sm_shown_ids();
		$before_hero = sm_shown_ids( array(), 'hero' );
		$key         = 'sm_bc_' . md5( $name . '|' . wp_json_encode( $attrs ) . '|' . get_option( 'sm_cache_gen', '0' ) . '|' . implode( ',', $before_all ) . '|' . implode( ',', $before_hero ) . '|' . get_locale() . '|' . SM_VERSION );
		$hit         = get_transient( $key );
		if ( is_array( $hit ) && isset( $hit['html'] ) ) {
			sm_shown_ids( $hit['all'], 'all' );
			sm_shown_ids( $hit['hero'], 'hero' );
			return $hit['html'];
		}
		$html = call_user_func( $cb, $attrs, $content, $block );
		set_transient(
			$key,
			array(
				'html' => $html,
				'all'  => array_values( array_diff( sm_shown_ids(), $before_all ) ),
				'hero' => array_values( array_diff( sm_shown_ids( array(), 'hero' ), $before_hero ) ),
			),
			10 * MINUTE_IN_SECONDS
		);
		return $html;
	};
}

/**
 * Turn the block cache off with define( 'SM_NO_BLOCK_CACHE', true ) or the sm_block_cache filter.
 *
 * @return bool
 */
function sm_cache_disabled() {
	return ( defined( 'SM_NO_BLOCK_CACHE' ) && SM_NO_BLOCK_CACHE ) || ! apply_filters( 'sm_block_cache', true );
}

/**
 * Invalidate every cached block by changing the generation number in the cache key.
 */
function sm_cache_bump() {
	update_option( 'sm_cache_gen', (string) microtime( true ) );
}
foreach ( array( 'save_post', 'deleted_post', 'trashed_post', 'untrashed_post', 'set_object_terms', 'created_term', 'edited_term', 'delete_term', 'update_option_sm_settings', 'add_option_sm_settings', 'switch_theme', 'wp_update_nav_menu' ) as $sm_hook ) {
	add_action( $sm_hook, 'sm_cache_bump' );
}
unset( $sm_hook );

/**
 * Attribute schema per block.
 *
 * @param string $name Block short name.
 * @return array
 */
function sm_block_attributes( $name ) {
	$common = array(
		'align'     => array(
			'type' => 'string',
			'default' => 'wide',
		),
		'className' => array(
			'type' => 'string',
			'default' => '',
		),
	);
	switch ( $name ) {
		case 'hero':
			return $common + array(
				'sideCount' => array(
					'type' => 'number',
					'default' => 5,
				),
			);
		case 'sections':
			return $common + array(
				'columns' => array(
					'type' => 'number',
					'default' => 3,
				),
				'perSection' => array(
					'type' => 'number',
					'default' => 0,
				),
			);
		case 'related':
			return $common + array(
				'count' => array(
					'type' => 'number',
					'default' => 3,
				),
				'title' => array(
					'type' => 'string',
					'default' => '',
				),
			);
		case 'newsletter':
			return $common + array(
				'heading' => array(
					'type' => 'string',
					'default' => '',
				),
			);
		case 'social-icons':
			return $common + array(
				'style' => array(
					'type' => 'string',
					'default' => '',
				),
				'size'  => array(
					'type' => 'number',
					'default' => 0,
				),
				'label' => array(
					'type' => 'string',
					'default' => '',
				),
			);
		case 'editors-picks':
			return $common + array(
				'title' => array(
					'type' => 'string',
					'default' => '',
				),
				'count' => array(
					'type' => 'number',
					'default' => 5,
				),
			);
		case 'most-read':
		case 'active-topics':
			return $common + array(
				'title' => array(
					'type' => 'string',
					'default' => '',
				),
				'count' => array(
					'type' => 'number',
					'default' => 5,
				),
			);
		case 'section-heading':
			return $common + array(
				'title' => array(
					'type' => 'string',
					'default' => '',
				),
				'link'  => array(
					'type' => 'string',
					'default' => '',
				),
				'level' => array(
					'type' => 'number',
					'default' => 2,
				),
			);
		default:
			return $common;
	}
}

/**
 * Wrapper attributes helper.
 *
 * @param array  $attrs Block attributes.
 * @param string $base_class Base class.
 * @return string
 */
function sm_block_wrap( $attrs, $base_class ) {
	$classes = array( $base_class );
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
function sm_section_heading_html( $title, $link = '', $level = 2, $color = '' ) {
	$tag  = 'h' . max( 1, min( 6, (int) $level ) );
	$dot  = $color ? ' style="--sm-cat:' . esc_attr( $color ) . '"' : '';
	$html = '<div class="sm-section-heading' . ( $color ? ' has-dot' : '' ) . '"' . $dot . '><' . $tag . ' class="sm-section-heading__title">';
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
	$side_count = max( 0, (int) ( $attrs['sideCount'] ?? 4 ) );
	$args       = array(
		'post_type'           => 'post',
		'posts_per_page'      => 1 + $side_count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( 'sticky' === sm_get( 'hero_source' ) ) {
		$sticky = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts' ) ) ) );
		if ( $sticky ) {
			$lead = get_posts(
				array(
					'post__in' => $sticky,
					'posts_per_page' => 1,
					'orderby' => 'date',
					'post_type' => 'post',
				)
			);
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
	sm_shown_ids( wp_list_pluck( $posts, 'ID' ), 'hero' );
	$main = array_shift( $posts );
	$html = '<section ' . sm_block_wrap( $attrs, 'sm-hero' ) . ' aria-label="' . esc_attr__( 'Featured stories', 'sm' ) . '">';
	$html .= '<div class="sm-hero__lead"><article class="sm-hero__main">';
	$html .= '<a class="sm-hero__media" href="' . esc_url( get_permalink( $main ) ) . '" tabindex="-1" aria-hidden="true">';
	$html .= has_post_thumbnail( $main ) ? get_the_post_thumbnail( $main, 'sm-hero', array( 'fetchpriority' => 'high' ) ) : sm_placeholder_html( $main );
	$html .= '</a><div class="sm-hero__overlay">' . sm_category_tag( $main );
	$html .= '<h2 class="sm-hero__title"><a href="' . esc_url( get_permalink( $main ) ) . '">' . esc_html( get_the_title( $main ) ) . '</a></h2>';
	$html .= '</div></article>';
	$credit = has_post_thumbnail( $main ) ? wp_strip_all_tags( (string) get_the_post_thumbnail_caption( $main ) ) : '';
	if ( $credit ) {
		$html .= '<p class="sm-hero__credit">' . esc_html( $credit ) . '</p>';
	}
	$html .= '</div>';
	if ( $posts ) {
		$html .= '<ol class="sm-hero__list" aria-label="' . esc_attr__( 'More top stories', 'sm' ) . '">';
		foreach ( $posts as $p ) {
			$cats  = get_the_category( $p );
			$color = $cats ? sm_cat_color( $cats[0] ) : 'var(--sm-primary)';
			$label = $cats ? '<span class="sm-hero__cat">' . esc_html( $cats[0]->name ) . '</span>' : '';
			$thumb = has_post_thumbnail( $p ) ? get_the_post_thumbnail( $p, 'sm-thumb', array( 'loading' => 'lazy' ) ) : sm_placeholder_html( $p );
			$html .= '<li class="sm-hero__item" style="--sm-cat:' . esc_attr( $color ) . '"><a class="sm-hero__thumb" href="' . esc_url( get_permalink( $p ) ) . '" tabindex="-1" aria-hidden="true">' . $thumb . '</a><div class="sm-hero__item-body">' . $label . '<a class="sm-hero__item-title" href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></div></li>';
		}
		$html .= '</ol>';
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
				'post__not_in'   => sm_shown_ids(),
				'no_found_rows'  => true,
			)
		);
		if ( ! $posts ) {
			continue;
		}
		sm_shown_ids( wp_list_pluck( $posts, 'ID' ) );
		$html .= '<section class="sm-sections__item" aria-labelledby="sm-sec-' . (int) $term->term_id . '">';
		$html .= str_replace( '<h2 class="sm-section-heading__title">', '<h2 class="sm-section-heading__title" id="sm-sec-' . (int) $term->term_id . '">', sm_section_heading_html( $term->name, get_category_link( $term ), 2, sm_cat_color( $term ) ) );
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
	if ( ! $post || ( ! is_singular( 'post' ) && ! defined( 'REST_REQUEST' ) ) ) {
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
		$more = get_posts(
			array(
				'post_type' => 'post',
				'posts_per_page' => $count - count( $posts ),
				'post__not_in' => array_merge( array( $post->ID ), wp_list_pluck( $posts, 'ID' ) ),
				'no_found_rows' => true,
			)
		);
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
	return sm_newsletter_form_html(
		array(
			'heading' => $attrs['heading'] ?: sm_get( 'nl_heading' ),
			'class' => trim( ( $attrs['className'] ?? '' ) . ' align' . ( $attrs['align'] ?? 'wide' ) ),
		)
	);
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
/**
 * Is this a PayPal.Me link? Returns the normalised base URL, or '' when it is something else.
 *
 * @param string $url Checkout URL from the settings.
 * @return string
 */
function sm_paypalme_base( $url ) {
	if ( preg_match( '~^https?://(?:www\.)?paypal\.(?:com/paypalme|me)/([A-Za-z0-9]{1,20})/?$~i', trim( (string) $url ), $m ) ) {
		return 'https://www.paypal.com/paypalme/' . $m[1];
	}
	return '';
}

function sm_render_donate_block( $attrs ) {
	$amounts  = array_filter( array_map( 'intval', explode( ',', (string) sm_get( 'donate_amounts' ) ) ) );
	$types    = array_filter( array_map( 'trim', explode( "\n", (string) sm_get( 'donate_types' ) ) ) );
	$currency = sm_get( 'donate_currency' );
	$url      = sm_get( 'donate_url' );
	$paypal   = sm_paypalme_base( $url );
	$code     = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) sm_get( 'donate_currency_code' ) ) );
	$code     = 3 === strlen( $code ) ? $code : 'MYR';
	$url      = $paypal ?: $url;
	$monthly  = $paypal ? esc_url_raw( (string) sm_get( 'donate_monthly_url' ), array( 'https' ) ) : '';
	$uid      = wp_unique_id( 'sm-donate-' );

	$html  = '<form class="sm-donate ' . esc_attr( $attrs['className'] ?? '' ) . '" method="get" action="' . esc_url( $url ?: home_url( '/' ) ) . '" data-sm-form="donate"' . ( $url ? '' : ' data-no-checkout="1"' ) . ( $paypal ? ' data-paypalme="' . esc_attr( $code ) . '"' : '' ) . ( $monthly ? ' data-monthly="' . esc_url( $monthly ) . '"' : '' ) . '>';
	// PayPal.Me takes an amount only: it is one-time and cannot carry a donation type.
	if ( $types && ! $paypal ) {
		$html .= '<div class="sm-donate__group"><label for="' . $uid . '-type">' . esc_html__( 'Choose a donation type', 'sm' ) . '</label><select id="' . $uid . '-type" name="type">';
		foreach ( $types as $t ) {
			$html .= '<option value="' . esc_attr( $t ) . '">' . esc_html( $t ) . '</option>';
		}
		$html .= '</select></div>';
	}
	$html .= '<fieldset class="sm-donate__group sm-donate__group--amount"><legend>' . esc_html__( 'Choose a donation amount', 'sm' ) . '</legend><div class="sm-donate__amounts">';
	$i = 0;
	foreach ( $amounts as $a ) {
		$html .= '<label class="sm-radio"><input type="radio" name="amount" value="' . (int) $a . '"' . ( 0 === $i ? ' checked' : '' ) . ' /><span>' . esc_html( $currency . $a ) . '</span></label>';
		++$i;
	}
	$html .= '<label class="sm-radio sm-radio--custom"><input type="radio" name="amount" value="custom" /><span>' . esc_html__( 'Enter a custom donation amount', 'sm' ) . '</span></label>';
	$html .= '<input class="sm-donate__custom" type="number" name="custom_amount" min="1" step="1" placeholder="' . esc_attr( $currency ) . '" aria-label="' . esc_attr__( 'Custom amount', 'sm' ) . '" hidden />';
	$html .= '</div></fieldset>';
	if ( ! $paypal || $monthly ) {
		$html .= '<fieldset class="sm-donate__group"><legend>' . esc_html__( 'Choose a donation frequency', 'sm' ) . '</legend><div class="sm-donate__freq">';
		$html .= '<label class="sm-pill"><input type="radio" name="frequency" value="monthly"' . ( $monthly ? '' : ' checked' ) . ' /><span>' . esc_html__( 'Monthly', 'sm' ) . '</span></label>';
		$html .= '<label class="sm-pill"><input type="radio" name="frequency" value="once"' . ( $monthly ? ' checked' : '' ) . ' /><span>' . esc_html__( 'One time', 'sm' ) . '</span></label>';
		$html .= '</div></fieldset>';
	}
	$html .= '<p class="sm-donate__note">' . esc_html( $paypal ? __( 'Donations help us keep independent Malaysian journalism free for everyone. You will be taken to PayPal to complete your gift. No PayPal account is needed to pay one-time gifts by card.', 'sm' ) : __( 'Donations help us keep independent Malaysian journalism free for everyone. You will be taken to our secure payment partner to complete your gift.', 'sm' ) ) . '</p>';
	$html .= '<div class="sm-donate__actions"><a class="wp-element-button is-style-sm-outline" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Cancel', 'sm' ) . '</a>';
	$html .= '<button type="submit" class="wp-element-button">' . esc_html( $paypal ? __( 'Donate with PayPal', 'sm' ) : __( 'Go to checkout', 'sm' ) ) . '</button></div>';
	if ( ! $url ) {
		$html .= current_user_can( 'manage_options' )
			? '<p class="sm-form__message is-error">' . esc_html__( 'Admins only: no checkout URL is set yet (Appearance → Theme Options → Donate).', 'sm' ) . '</p>'
			: '<p class="sm-form__message">' . esc_html__( 'Online donations are opening soon. To support us in the meantime, please contact the editors.', 'sm' ) . '</p>';
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
				'socialStyles' => array(
					'plain' => __( 'Plain', 'sm' ),
					'boxed' => __( 'Boxed', 'sm' ),
					'round' => __( 'Round', 'sm' ),
				),
				'unlistMeta'   => SM_UNLIST_META,
			)
		);
	}
);

/**
 * Reading time label.
 */
function sm_render_reading_time_block( $attrs ) {
	$post = get_post();
	if ( ! $post ) {
		return '';
	}
	return '<span ' . sm_block_wrap( array_merge( $attrs, array( 'align' => '' ) ), 'sm-reading-time' ) . '>' . esc_html( sprintf( /* translators: minutes */ _n( '%d min read', '%d min read', sm_reading_minutes( $post ), 'sm' ), sm_reading_minutes( $post ) ) ) . '</span>';
}

/**
 * Share buttons: WhatsApp, Facebook, X and copy link.
 */
function sm_render_share_block( $attrs ) {
	$post = get_post();
	if ( ! $post ) {
		return '';
	}
	$url   = rawurlencode( get_permalink( $post ) );
	$title = rawurlencode( get_the_title( $post ) );
	$items = array(
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . $title . '%20' . $url ),
		'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'x'        => array( 'X', 'https://twitter.com/intent/tweet?text=' . $title . '&url=' . $url ),
	);
	$html = '<div ' . sm_block_wrap( array_merge( $attrs, array( 'align' => '' ) ), 'sm-share' ) . '><span class="sm-share__label">' . esc_html__( 'Share', 'sm' ) . '</span><ul class="sm-share__list">';
	foreach ( $items as $slug => $it ) {
		$html .= '<li><a href="' . esc_url( $it[1] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( sprintf( /* translators: network */ __( 'Share on %s', 'sm' ), $it[0] ) ) . '">' . sm_social_icon_svg( $slug ) . '</a></li>';
	}
	$html .= '<li><button type="button" class="sm-share__copy" data-url="' . esc_url( get_permalink( $post ) ) . '" data-done="' . esc_attr__( 'Link copied', 'sm' ) . '" aria-label="' . esc_attr__( 'Copy link', 'sm' ) . '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg></button></li>';
	if ( sm_get( 'pdf_enabled' ) && is_singular( 'post' ) ) {
		$html .= '<li><button type="button" class="sm-share__pdf" aria-label="' . esc_attr__( 'Save this article as PDF', 'sm' ) . '" data-busy="' . esc_attr__( 'Preparing PDF…', 'sm' ) . '" data-done="' . esc_attr__( 'PDF saved', 'sm' ) . '" data-fail="' . esc_attr__( 'Could not make the PDF. Try printing the page instead.', 'sm' ) . '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4"/><path d="M5 19h14"/></svg></button></li>';
	}
	return $html . '</ul><span class="sm-share__status" role="status" aria-live="polite"></span></div>';
}

/**
 * Aside module wrapper with a red-ruled heading.
 */
function sm_aside_module( $title, $inner, $attrs ) {
	return '<section ' . sm_block_wrap( array_merge( (array) $attrs, array( 'align' => '' ) ), 'sm-module' ) . '><h2 class="sm-module__title">' . esc_html( $title ) . '</h2>' . $inner . '</section>';
}

/**
 * Editor's picks: posts tagged "editors-pick", topped up with the latest stories.
 */
function sm_render_editors_picks( $attrs ) {
	$count = max( 1, (int) ( $attrs['count'] ?? 5 ) );
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'tag'                 => 'editors-pick',
			'post__not_in'        => sm_shown_ids( array(), 'hero' ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( count( $posts ) < $count ) {
		$more  = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => $count - count( $posts ),
				'post__not_in'   => array_merge( wp_list_pluck( $posts, 'ID' ), sm_shown_ids( array(), 'hero' ) ),
				'no_found_rows'  => true,
			)
		);
		$posts = array_merge( $posts, $more );
	}
	if ( ! $posts ) {
		return '';
	}
	$html = '<ol class="sm-pick-list">';
	foreach ( $posts as $p ) {
		$cats  = get_the_category( $p );
		$label = $cats ? '<a class="sm-pick__cat" href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>' : '';
		$html .= '<li class="sm-pick" style="--sm-cat:' . esc_attr( $cats ? sm_cat_color( $cats[0] ) : 'var(--sm-primary)' ) . '">' . $label . '<a class="sm-pick__title" href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a><time class="sm-pick__date" datetime="' . esc_attr( get_the_date( 'c', $p ) ) . '">' . esc_html( get_the_date( '', $p ) ) . '</time></li>';
	}
	return sm_aside_module( $attrs['title'] ?: __( 'Editor’s Picks', 'sm' ), $html . '</ol>', $attrs );
}

/**
 * Active topics: categories with the most stories.
 */
function sm_render_active_topics( $attrs ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => 0,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => max( 1, (int) ( $attrs['count'] ?? 5 ) ),
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return '';
	}
	$html = '<ul class="sm-topic-list">';
	foreach ( $terms as $t ) {
		$html .= '<li><a href="' . esc_url( get_category_link( $t ) ) . '" style="--sm-cat:' . esc_attr( sm_cat_color( $t ) ) . '"><span class="sm-topic__name">' . esc_html( $t->name ) . '</span><span class="sm-topic__count">' . (int) $t->count . '</span></a></li>';
	}
	return sm_aside_module( $attrs['title'] ?: __( 'Active Topics', 'sm' ), $html . '</ul>', $attrs );
}

/**
 * Most read: ranked list with large numerals.
 */
function sm_render_most_read( $attrs ) {
	$posts = sm_most_read_posts( max( 1, (int) ( $attrs['count'] ?? 5 ) ) );
	if ( ! $posts ) {
		return '';
	}
	$html = '<ol class="sm-rank-list">';
	foreach ( $posts as $i => $p ) {
		$cats  = get_the_category( $p );
		$label = $cats ? '<span class="sm-rank__cat">' . esc_html( $cats[0]->name ) . '</span>' : '';
		$html .= '<li class="sm-rank" style="--sm-cat:' . esc_attr( $cats ? sm_cat_color( $cats[0] ) : 'var(--sm-primary)' ) . '"><span class="sm-rank__num" aria-hidden="true">' . (int) ( $i + 1 ) . '</span><div class="sm-rank__body">' . $label . '<a class="sm-rank__title" href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></div></li>';
	}
	return sm_aside_module( $attrs['title'] ?: __( 'Most Read', 'sm' ), $html . '</ol>', $attrs );
}

/**
 * Footer sitemap: top sections with their latest stories, plus site links.
 */
function sm_render_footer_sections() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => 0,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 4,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		)
	);
	$html = '<nav class="sm-fcols" aria-label="' . esc_attr__( 'Sitemap', 'sm' ) . '">';
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$posts = get_posts(
				array(
					'post_type' => 'post',
					'posts_per_page' => 3,
					'cat' => $t->term_id,
					'no_found_rows' => true,
				)
			);
			$html .= '<div class="sm-fcol" style="--sm-cat:' . esc_attr( sm_cat_color( $t ) ) . '"><h3 class="sm-fcol__title"><a href="' . esc_url( get_category_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></h3><ul>';
			foreach ( $posts as $p ) {
				$html .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
			}
			$html .= '</ul></div>';
		}
	}
	$links = array();
	foreach ( array(
		'about' => __( 'About', 'sm' ),
		'contact' => __( 'Contact', 'sm' ),
		'donate' => __( 'Donate', 'sm' ),
		'privacy-policy' => __( 'Privacy Policy', 'sm' ),
		'editorial-policy' => __( 'Editorial Policy', 'sm' ),
	) as $slug => $label ) {
		$pg = get_page_by_path( $slug );
		if ( $pg ) {
			$links[] = '<li><a href="' . esc_url( get_permalink( $pg ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}
	$html .= '<div class="sm-fcol"><h3 class="sm-fcol__title">' . esc_html( get_bloginfo( 'name' ) ) . '</h3><ul>' . implode( '', $links ) . '<li><a href="' . esc_url( get_feed_link() ) . '">RSS</a></li></ul>';
	$donate = get_page_by_path( 'donate' );
	if ( $donate ) {
		$html .= '<a class="sm-fcta" href="' . esc_url( get_permalink( $donate ) ) . '">' . esc_html__( 'Support us', 'sm' ) . ' <span aria-hidden="true">&rarr;</span></a>';
	}
	return $html . '</div></nav>';
}
