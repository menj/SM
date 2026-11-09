<?php
/**
 * Seed content importer.
 *
 * Reads seed/content.json and creates categories, posts (with original dates,
 * featured images and SEO fields), pages, navigation menus and site options.
 * Runs once on activation; can be re-run from Appearance → Theme Options.
 * Items are matched by slug, so re-running never duplicates content.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load and decode the seed file.
 *
 * @return array|null
 */
function sm_seed_data() {
	$file = SM_DIR . 'seed/content.json';
	if ( ! is_readable( $file ) ) {
		return null;
	}
	$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return is_array( $data ) ? $data : null;
}

add_action(
	'after_switch_theme',
	function () {
		if ( get_option( 'sm_seed_imported' ) ) {
			return;
		}
		sm_seed_import();
	},
	20
);

/**
 * Run the import. Returns a log of what happened.
 *
 * @param bool $overwrite Update existing posts/pages that match by slug.
 * @return string[]
 */
function sm_seed_import( $overwrite = false ) {
	$data = sm_seed_data();
	$log  = array();
	if ( ! $data ) {
		return array( __( 'seed/content.json is missing or invalid.', 'sm' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	/* Site options. */
	$site = $data['site'] ?? array();
	foreach ( array( 'blogname', 'blogdescription', 'timezone_string' ) as $opt ) {
		if ( ! empty( $site[ $opt ] ) && ( $overwrite || ! get_option( 'sm_seed_imported' ) ) ) {
			update_option( $opt, $site[ $opt ] );
		}
	}
	if ( ! empty( $site['permalink_structure'] ) && ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', $site['permalink_structure'] );
	}
	if ( ! empty( $site['meta_description'] ) ) {
		$settings                     = (array) get_option( 'sm_settings', array() );
		$settings['meta_description'] = $site['meta_description'];
		update_option( 'sm_settings', $settings );
	}
	$current_logo = (int) get_theme_mod( 'custom_logo' );
	if ( ! empty( $site['logo'] ) && ( ! $current_logo || 'attachment' !== get_post_type( $current_logo ) ) ) {
		$logo_id = sm_seed_sideload( $site['logo'], $site['logo_alt'] ?? '' );
		if ( $logo_id ) {
			set_theme_mod( 'custom_logo', $logo_id );
		}
	}

	/* Categories. */
	$cat_ids = array();
	foreach ( (array) ( $data['categories'] ?? array() ) as $c ) {
		$term      = get_term_by( 'slug', $c['slug'], 'category' );
		$parent_id = ! empty( $c['parent'] ) ? (int) ( $cat_ids[ $c['parent'] ] ?? 0 ) : 0;
		if ( ! $term ) {
			$r = wp_insert_term(
				$c['name'],
				'category',
				array(
					'slug' => $c['slug'],
					'description' => $c['description'] ?? '',
					'parent' => $parent_id,
				)
			);
			if ( ! is_wp_error( $r ) ) {
				$cat_ids[ $c['slug'] ] = (int) $r['term_id'];
				sm_seed_track_term( (int) $r['term_id'], 'category' );
			}
		} else {
			$cat_ids[ $c['slug'] ] = (int) $term->term_id;
			if ( $overwrite ) {
				wp_update_term(
					$term->term_id,
					'category',
					array(
						'name' => $c['name'],
						'description' => $c['description'] ?? '',
						'parent' => $parent_id,
					)
				);
			}
		}
	}
	$log[] = sprintf( /* translators: count */ __( '%d categories ready.', 'sm' ), count( $cat_ids ) );

	/* Posts. */
	$count = 0;
	foreach ( (array) ( $data['posts'] ?? array() ) as $p ) {
		$existing = get_page_by_path( $p['slug'], OBJECT, 'post' );
		if ( $existing && ! $overwrite ) {
			continue;
		}
		// Inline seed images: {{seed:file.jpg}} becomes the media library URL.
		$p['content'] = preg_replace_callback(
			'/\{\{seed:([^}]+)\}\}/',
			function ( $m ) use ( $p ) {
				$att = sm_seed_sideload( $m[1], $p['title'] );
				return $att ? wp_get_attachment_url( $att ) : '';
			},
			(string) ( $p['content'] ?? '' )
		);
		// Mark each inline image with its attachment ID so WordPress adds responsive sizes (srcset).
		$p['content'] = preg_replace_callback(
			'#<img src="([^"]+)" alt="([^"]*)"/>#',
			function ( $m ) {
				$aid = attachment_url_to_postid( html_entity_decode( $m[1] ) );
				return $aid ? '<img src="' . $m[1] . '" alt="' . $m[2] . '" class="wp-image-' . (int) $aid . '"/>' : $m[0];
			},
			$p['content']
		);
		$args = array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_name'     => $p['slug'],
			'post_title'    => $p['title'],
			'post_content'  => $p['content'] ?? '',
			'post_excerpt'  => $p['excerpt'] ?? '',
			'post_date'     => $p['date'],
			'post_date_gmt' => get_gmt_from_date( $p['date'] ),
			'post_category' => array_values(
				array_filter(
					array_map(
						function ( $s ) use ( $cat_ids ) {
							return $cat_ids[ $s ] ?? 0; },
						(array) ( $p['categories'] ?? array() )
					)
				)
			),
			'tags_input'    => (array) ( $p['tags'] ?? array() ),
		);
		$new_tags = array();
		foreach ( (array) ( $p['tags'] ?? array() ) as $tag_name ) {
			if ( ! term_exists( $tag_name, 'post_tag' ) ) {
				$new_tags[] = $tag_name;
			}
		}
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args, true );
		} else {
			$id = wp_insert_post( $args, true );
		}
		if ( is_wp_error( $id ) ) {
			$log[] = $p['slug'] . ': ' . $id->get_error_message();
			continue;
		}
		if ( ! $existing ) {
			sm_seed_track( $id );
		}
		foreach ( $new_tags as $tag_name ) {
			$tt = get_term_by( 'name', $tag_name, 'post_tag' );
			if ( $tt ) {
				sm_seed_track_term( (int) $tt->term_id, 'post_tag' );
			}
		}
		if ( ! empty( $p['seo_title'] ) ) {
			update_post_meta( $id, '_sm_seo_title', $p['seo_title'] );
		}
		if ( ! empty( $p['featured_image'] ) && ( $overwrite || ! has_post_thumbnail( $id ) ) ) {
			$att = sm_seed_sideload( $p['featured_image'], $p['featured_image_alt'] ?? $p['title'], $id );
			if ( $att ) {
				set_post_thumbnail( $id, $att );
			}
		}
		if ( ! empty( $p['sticky'] ) ) {
			stick_post( $id );
		}
		++$count;
	}
	$log[] = sprintf( /* translators: count */ __( '%d posts imported.', 'sm' ), $count );

	/* Pages. */
	$count    = 0;
	$page_ids = array();
	foreach ( (array) ( $data['pages'] ?? array() ) as $pg ) {
		$existing = get_page_by_path( $pg['slug'], OBJECT, 'page' );
		$content  = '';
		if ( ! empty( $pg['pattern'] ) ) {
			$content = '<!-- wp:pattern {"slug":"' . esc_attr( $pg['pattern'] ) . '"} /-->';
		} elseif ( ! empty( $pg['content'] ) ) {
			$content = $pg['content'];
		}
		// WordPress ships a draft Privacy Policy page. Treat any unpublished page as a placeholder to replace.
		$is_placeholder = $existing && 'publish' !== $existing->post_status;
		if ( $existing && ! $overwrite && ! $is_placeholder ) {
			$page_ids[ $pg['slug'] ] = $existing->ID;
			continue;
		}
		$args = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $pg['slug'],
			'post_title'   => $pg['title'],
			'post_content' => $content,
			'post_excerpt' => $pg['excerpt'] ?? '',
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args, true );
		} else {
			$id = wp_insert_post( $args, true );
		}
		if ( is_wp_error( $id ) ) {
			$log[] = $pg['slug'] . ': ' . $id->get_error_message();
			continue;
		}
		if ( ! $existing || $is_placeholder ) {
			sm_seed_track( $id );
		}
		$page_ids[ $pg['slug'] ] = $id;
		if ( ! empty( $pg['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $pg['template'] );
		}
		if ( ! empty( $pg['seo_title'] ) ) {
			update_post_meta( $id, '_sm_seo_title', $pg['seo_title'] );
		}
		if ( ! empty( $pg['privacy_page'] ) ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		}
		++$count;
	}
	$log[] = sprintf( /* translators: count */ __( '%d pages imported.', 'sm' ), $count );

	/* Remove WordPress's untouched demo content so it does not appear in lists. */
	$demo_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $demo_post && 'Hello world!' === $demo_post->post_title && false !== strpos( $demo_post->post_content, 'Welcome to WordPress' ) ) {
		wp_trash_post( $demo_post->ID );
	}
	$demo_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
	if ( $demo_page && 'Sample Page' === $demo_page->post_title && false !== strpos( $demo_page->post_content, 'This is an example page' ) ) {
		wp_trash_post( $demo_page->ID );
	}

	/* Navigation menus (block navigation posts referenced by slug in the parts). */
	foreach ( (array) ( $data['menus'] ?? array() ) as $slug => $menu ) {
		$blocks = '';
		foreach ( (array) $menu['items'] as $item ) {
			$blocks .= sm_seed_nav_item( $item, $page_ids );
		}
		$existing = get_page_by_path( $slug, OBJECT, 'wp_navigation' );
		$args     = array(
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $menu['title'],
			'post_content' => $blocks,
		);
		if ( $existing ) {
			if ( $overwrite ) {
				$args['ID'] = $existing->ID;
				wp_update_post( $args );
			}
		} else {
			$nav_id = wp_insert_post( $args );
			if ( $nav_id && ! is_wp_error( $nav_id ) ) {
				sm_seed_track( $nav_id );
			}
		}
	}
	$log[] = __( 'Navigation menus ready.', 'sm' );

	/* Homepage sections follow the seeded category order. */
	$settings                  = (array) get_option( 'sm_settings', array() );
	$top_level                 = array();
	foreach ( $cat_ids as $cslug => $cid ) {
		$t = get_term( $cid, 'category' );
		if ( $t && ! is_wp_error( $t ) && 0 === (int) $t->parent ) {
			$top_level[] = $cid;
		}
	}
	$settings['home_sections'] = $top_level;
	update_option( 'sm_settings', $settings );

	update_option( 'sm_seed_imported', time() );
	set_transient( 'sm_flush_rewrite', 1, 60 );
	return $log;
}

/**
 * Copy a file from seed/images into the media library (once per file).
 *
 * @param string $file   File name inside seed/images.
 * @param string $alt    Alt text.
 * @param int    $post_id Parent post.
 * @return int Attachment ID or 0.
 */
function sm_seed_sideload( $file, $alt = '', $post_id = 0 ) {
	$path = SM_DIR . 'seed/images/' . basename( $file );
	if ( ! is_readable( $path ) ) {
		return 0;
	}
	$found = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_sm_seed_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => basename( $file ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}
	$tmp = wp_tempnam( basename( $file ) );
	copy( $path, $tmp );
	$id = media_handle_sideload(
		array(
			'name' => basename( $path ),
			'tmp_name' => $tmp,
		),
		$post_id,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_sm_seed_file', basename( $file ) );
	sm_seed_track( $id );
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	return (int) $id;
}

/* Admin action: re-run the import. */
add_action(
	'admin_post_sm_seed_import',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'sm' ) );
		}
		check_admin_referer( 'sm_seed_import' );
		$overwrite = ! empty( $_GET['overwrite'] );
		$log       = sm_seed_import( $overwrite );
		set_transient( 'sm_seed_log', $log, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=sm-settings&tab=general&seeded=1' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		$log = get_transient( 'sm_seed_log' );
		if ( ! $log ) {
			return;
		}
		delete_transient( 'sm_seed_log' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Seed content imported.', 'sm' ) . '</strong> ' . esc_html( implode( ' ', (array) $log ) ) . '</p></div>';
	}
);

/**
 * Build the block markup for one navigation item, with nested children as a submenu.
 *
 * @param array $item     Menu item from the seed file.
 * @param array $page_ids Map of page slug to ID.
 * @return string
 */
function sm_seed_nav_item( $item, $page_ids ) {
	$children = '';
	foreach ( (array) ( $item['children'] ?? array() ) as $child ) {
		$children .= sm_seed_nav_item( $child, $page_ids );
	}
	$attrs = null;
	if ( 'category' === $item['type'] ) {
		$term = get_term_by( 'slug', $item['slug'], 'category' );
		if ( $term ) {
			$attrs = array(
				'label' => $item['label'] ?? $term->name,
				'type' => 'category',
				'id' => (int) $term->term_id,
				'url' => get_category_link( $term ),
				'kind' => 'taxonomy',
			);
		}
	} elseif ( 'page' === $item['type'] ) {
		$pid = $page_ids[ $item['slug'] ] ?? 0;
		if ( $pid ) {
			$attrs = array(
				'label' => $item['label'] ?? get_the_title( $pid ),
				'type' => 'page',
				'id' => (int) $pid,
				'url' => get_permalink( $pid ),
				'kind' => 'post-type',
			);
		}
	} elseif ( 'custom' === $item['type'] ) {
		$attrs = array(
			'label' => $item['label'],
			'url' => $item['url'],
			'kind' => 'custom',
		);
	}
	if ( ! $attrs ) {
		return '';
	}
	$json = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( $children ) {
		return '<!-- wp:navigation-submenu ' . $json . " -->\n" . $children . "<!-- /wp:navigation-submenu -->\n";
	}
	return '<!-- wp:navigation-link ' . $json . " /-->\n";
}

/* ---------- Demo content tracking and automatic removal ---------- */

/**
 * Remember a post, page, menu or attachment that the importer created.
 *
 * @param int $id Post ID.
 */
function sm_seed_track( $id ) {
	$m          = (array) get_option( 'sm_seed_manifest', array() );
	$m['posts'] = array_values( array_unique( array_merge( (array) ( $m['posts'] ?? array() ), array( (int) $id ) ) ) );
	update_option( 'sm_seed_manifest', $m, false );
}

/**
 * Remember a term that the importer created.
 *
 * @param int    $id       Term ID.
 * @param string $taxonomy Taxonomy.
 */
function sm_seed_track_term( $id, $taxonomy ) {
	$m          = (array) get_option( 'sm_seed_manifest', array() );
	$m['terms'] = (array) ( $m['terms'] ?? array() );
	$key        = (int) $id . ':' . $taxonomy;
	if ( ! in_array( $key, $m['terms'], true ) ) {
		$m['terms'][] = $key;
	}
	update_option( 'sm_seed_manifest', $m, false );
}

/**
 * Permanently delete everything the importer created, and nothing else.
 *
 * @return string[] Log lines.
 */
function sm_seed_remove() {
	$m       = (array) get_option( 'sm_seed_manifest', array() );
	$posts   = array_map( 'intval', (array) ( $m['posts'] ?? array() ) );
	$deleted = 0;
	foreach ( $posts as $id ) {
		if ( get_post( $id ) && wp_delete_post( $id, true ) ) {
			++$deleted;
		}
	}
	$terms_deleted = 0;
	foreach ( (array) ( $m['terms'] ?? array() ) as $key ) {
		list( $tid, $tax ) = array_pad( explode( ':', $key, 2 ), 2, 'category' );
		if ( term_exists( (int) $tid, $tax ) && (int) get_option( 'default_category' ) !== (int) $tid ) {
			wp_delete_term( (int) $tid, $tax );
			++$terms_deleted;
		}
	}
	// Options and theme mods that pointed at deleted content.
	if ( in_array( (int) get_theme_mod( 'custom_logo' ), $posts, true ) ) {
		remove_theme_mod( 'custom_logo' );
	}
	if ( in_array( (int) get_option( 'site_icon' ), $posts, true ) ) {
		delete_option( 'site_icon' );
	}
	if ( in_array( (int) get_option( 'wp_page_for_privacy_policy' ), $posts, true ) ) {
		delete_option( 'wp_page_for_privacy_policy' );
	}
	update_option( 'sticky_posts', array_values( array_diff( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ), $posts ) ) );
	$settings = (array) get_option( 'sm_settings', array() );
	unset( $settings['home_sections'] );
	update_option( 'sm_settings', $settings );
	delete_option( 'sm_unlisted_ids' );
	delete_option( 'sm_seed_manifest' );
	delete_option( 'sm_seed_imported' );
	set_transient( 'sm_flush_rewrite', 1, 60 );
	return array( sprintf( /* translators: 1: items, 2: terms */ __( 'Deleted %1$d demo items and %2$d categories and tags.', 'sm' ), $deleted, $terms_deleted ) );
}

// Switching to another theme removes the demo content (if the setting is on).
add_action(
	'switch_theme',
	function ( $new_name, $new_theme, $old_theme ) {
		if ( $old_theme instanceof WP_Theme && 'SM' === $old_theme->get_stylesheet() && sm_get( 'autodelete_demo' ) ) {
			sm_seed_remove();
		}
	},
	10,
	3
);

add_action(
	'admin_post_sm_seed_remove',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'sm' ) );
		}
		check_admin_referer( 'sm_seed_remove' );
		set_transient( 'sm_seed_log', sm_seed_remove(), 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=sm-settings&tab=general' ) );
		exit;
	}
);
