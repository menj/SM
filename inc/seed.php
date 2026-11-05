<?php
/**
 * Seed content importer.
 *
 * Reads seed/content.json and creates categories, posts (with original dates,
 * featured images and SEO fields), pages, navigation menus and site options.
 * Runs once on activation; can be re-run from Appearance → SM Theme.
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
	if ( ! empty( $site['logo'] ) && ! get_theme_mod( 'custom_logo' ) ) {
		$logo_id = sm_seed_sideload( $site['logo'], $site['logo_alt'] ?? '' );
		if ( $logo_id ) {
			set_theme_mod( 'custom_logo', $logo_id );
			update_option( 'site_icon', $logo_id );
		}
	}

	/* Categories. */
	$cat_ids = array();
	foreach ( (array) ( $data['categories'] ?? array() ) as $c ) {
		$term = get_term_by( 'slug', $c['slug'], 'category' );
		if ( ! $term ) {
			$r = wp_insert_term( $c['name'], 'category', array( 'slug' => $c['slug'], 'description' => $c['description'] ?? '' ) );
			if ( ! is_wp_error( $r ) ) {
				$cat_ids[ $c['slug'] ] = (int) $r['term_id'];
			}
		} else {
			$cat_ids[ $c['slug'] ] = (int) $term->term_id;
			if ( $overwrite ) {
				wp_update_term( $term->term_id, 'category', array( 'name' => $c['name'], 'description' => $c['description'] ?? '' ) );
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
		$args = array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_name'     => $p['slug'],
			'post_title'    => $p['title'],
			'post_content'  => $p['content'] ?? '',
			'post_excerpt'  => $p['excerpt'] ?? '',
			'post_date'     => $p['date'],
			'post_date_gmt' => get_gmt_from_date( $p['date'] ),
			'post_category' => array_values( array_filter( array_map( function ( $s ) use ( $cat_ids ) { return $cat_ids[ $s ] ?? 0; }, (array) ( $p['categories'] ?? array() ) ) ) ),
			'tags_input'    => (array) ( $p['tags'] ?? array() ),
		);
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
		if ( ! empty( $p['seo_title'] ) ) {
			update_post_meta( $id, '_sm_seo_title', $p['seo_title'] );
		}
		if ( ! empty( $p['featured_image'] ) && ! has_post_thumbnail( $id ) ) {
			$att = sm_seed_sideload( $p['featured_image'], $p['featured_image_alt'] ?? $p['title'], $id );
			if ( $att ) {
				set_post_thumbnail( $id, $att );
			}
		}
		if ( ! empty( $p['sticky'] ) ) {
			stick_post( $id );
		}
		$count++;
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
		if ( $existing && ! $overwrite ) {
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
		$count++;
	}
	$log[] = sprintf( /* translators: count */ __( '%d pages imported.', 'sm' ), $count );

	/* Navigation menus (block navigation posts referenced by slug in the parts). */
	foreach ( (array) ( $data['menus'] ?? array() ) as $slug => $menu ) {
		$blocks = '';
		foreach ( (array) $menu['items'] as $item ) {
			if ( 'category' === $item['type'] ) {
				$term = get_term_by( 'slug', $item['slug'], 'category' );
				if ( ! $term ) {
					continue;
				}
				$blocks .= sprintf(
					'<!-- wp:navigation-link {"label":"%1$s","type":"category","id":%2$d,"url":"%3$s","kind":"taxonomy"} /-->',
					esc_attr( $item['label'] ?? $term->name ),
					(int) $term->term_id,
					esc_url( get_category_link( $term ) )
				) . "\n";
			} elseif ( 'page' === $item['type'] ) {
				$pid = $page_ids[ $item['slug'] ] ?? 0;
				if ( ! $pid ) {
					continue;
				}
				$blocks .= sprintf(
					'<!-- wp:navigation-link {"label":"%1$s","type":"page","id":%2$d,"url":"%3$s","kind":"post-type"} /-->',
					esc_attr( $item['label'] ?? get_the_title( $pid ) ),
					(int) $pid,
					esc_url( get_permalink( $pid ) )
				) . "\n";
			} elseif ( 'custom' === $item['type'] ) {
				$blocks .= sprintf( '<!-- wp:navigation-link {"label":"%1$s","url":"%2$s","kind":"custom"} /-->', esc_attr( $item['label'] ), esc_url( $item['url'] ) ) . "\n";
			}
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
			wp_insert_post( $args );
		}
	}
	$log[] = __( 'Navigation menus ready.', 'sm' );

	/* Homepage sections follow the seeded category order. */
	$settings                  = (array) get_option( 'sm_settings', array() );
	$settings['home_sections'] = array_values( $cat_ids );
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
 * @param int    $parent Parent post.
 * @return int Attachment ID or 0.
 */
function sm_seed_sideload( $file, $alt = '', $parent = 0 ) {
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
	$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), $parent, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}
	update_post_meta( $id, '_sm_seed_file', basename( $file ) );
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
