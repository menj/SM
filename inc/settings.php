<?php
/**
 * Appearance → SM Theme: tabbed settings page built on the Settings API.
 *
 * All options are stored in one array option `sm_settings`.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tab registry. Each tab lists its fields; the renderer is generic.
 *
 * @return array
 */
function sm_settings_tabs() {
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	$pt_options = array();
	foreach ( $post_types as $pt ) {
		if ( 'attachment' === $pt->name ) {
			continue;
		}
		$pt_options[ $pt->name ] = $pt->labels->singular_name;
	}

	$scheme_options = array();
	foreach ( sm_color_schemes() as $k => $s ) {
		$scheme_options[ $k ] = $s['label'];
	}

	$social = array();
	foreach ( sm_social_networks() as $slug => $label ) {
		$social[] = array(
			'id'    => 'social_' . $slug,
			'label' => $label,
			'type'  => 'url',
			'icon'  => $slug,
		);
	}

	return array(
		'general'    => array(
			'label'  => __( 'General', 'sm' ),
			'icon'   => 'admin-generic',
			'fields' => array(
				array( 'id' => 'phone', 'label' => __( 'Phone number', 'sm' ), 'type' => 'text', 'help' => __( 'Shown on the About and Contact pages.', 'sm' ) ),
				array( 'id' => 'email', 'label' => __( 'Public email', 'sm' ), 'type' => 'email' ),
				array( 'id' => 'copyright', 'label' => __( 'Copyright line', 'sm' ), 'type' => 'text', 'help' => __( 'Use {year} to insert the current year.', 'sm' ) ),
				array( 'id' => 'back_to_top', 'label' => __( 'Back-to-top button', 'sm' ), 'type' => 'toggle', 'help' => __( 'Show the small red scroll-to-top button in the footer.', 'sm' ) ),
				array( 'id' => 'meta_description', 'label' => __( 'Homepage meta description', 'sm' ), 'type' => 'textarea', 'help' => __( 'Shown to search engines for the homepage. Aim for 150 to 160 characters.', 'sm' ) ),
				array( 'id' => 'seed_import', 'label' => __( 'Seed content', 'sm' ), 'type' => 'seed' ),
			),
		),
		'colours'    => array(
			'label'  => __( 'Colours', 'sm' ),
			'icon'   => 'art',
			'fields' => array(
				array( 'id' => 'scheme', 'label' => __( 'Colour scheme', 'sm' ), 'type' => 'scheme', 'options' => $scheme_options, 'help' => __( 'Pick a preset, or choose Custom and set each colour below.', 'sm' ) ),
				array( 'id' => 'color_primary', 'label' => __( 'Primary (category bar, buttons)', 'sm' ), 'type' => 'color' ),
				array( 'id' => 'color_tag', 'label' => __( 'Category tag', 'sm' ), 'type' => 'color' ),
				array( 'id' => 'color_newsletter', 'label' => __( 'Newsletter panel', 'sm' ), 'type' => 'color' ),
				array( 'id' => 'color_link', 'label' => __( 'Headline links', 'sm' ), 'type' => 'color' ),
				array( 'id' => 'color_text', 'label' => __( 'Body text', 'sm' ), 'type' => 'color' ),
				array( 'id' => 'color_surface', 'label' => __( 'Surface (quotes, placeholders)', 'sm' ), 'type' => 'color' ),
			),
		),
		'homepage'   => array(
			'label'  => __( 'Homepage', 'sm' ),
			'icon'   => 'layout',
			'fields' => array(
				array( 'id' => 'hero_source', 'label' => __( 'Hero story', 'sm' ), 'type' => 'select', 'options' => array( 'sticky' => __( 'Sticky post, otherwise latest', 'sm' ), 'latest' => __( 'Always the latest post', 'sm' ) ) ),
				array( 'id' => 'home_sections', 'label' => __( 'Sections and order', 'sm' ), 'type' => 'categories', 'help' => __( 'Tick the categories to show as homepage sections and drag to reorder. Leave all unticked to show every non-empty category.', 'sm' ) ),
				array( 'id' => 'section_posts', 'label' => __( 'Stories per section', 'sm' ), 'type' => 'number', 'min' => 1, 'max' => 6 ),
				array( 'id' => 'show_excerpt', 'label' => __( 'Show excerpts on cards', 'sm' ), 'type' => 'toggle' ),
			),
		),
		'newsletter' => array(
			'label'  => __( 'Newsletter', 'sm' ),
			'icon'   => 'email-alt',
			'fields' => array(
				array( 'id' => 'nl_heading', 'label' => __( 'Heading', 'sm' ), 'type' => 'textarea', 'help' => __( 'Line breaks are kept.', 'sm' ) ),
				array( 'id' => 'nl_success', 'label' => __( 'Success message', 'sm' ), 'type' => 'text' ),
				array( 'id' => 'nl_action', 'label' => __( 'External form action URL', 'sm' ), 'type' => 'url', 'help' => __( 'Optional. Paste a Mailchimp, Brevo or similar form action URL to post subscribers there instead of storing them in WordPress.', 'sm' ) ),
				array( 'id' => 'nl_notify', 'label' => __( 'Email the admin on each new subscriber', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'nl_list', 'label' => __( 'Subscribers', 'sm' ), 'type' => 'link', 'href' => 'edit.php?post_type=sm_subscriber', 'text' => __( 'View and export subscribers', 'sm' ) ),
			),
		),
		'forms'      => array(
			'label'  => __( 'Contact form', 'sm' ),
			'icon'   => 'feedback',
			'fields' => array(
				array( 'id' => 'contact_to', 'label' => __( 'Send messages to', 'sm' ), 'type' => 'email', 'help' => __( 'Leave empty to use the site admin email.', 'sm' ) ),
				array( 'id' => 'contact_intro', 'label' => __( 'Intro text beside the form', 'sm' ), 'type' => 'textarea' ),
				array( 'id' => 'contact_fields', 'label' => __( 'Fields', 'sm' ), 'type' => 'textarea', 'code' => true, 'help' => __( 'One field per line: type|name|label|required. Types: text, email, tel, textarea, select (options separated by commas after a colon, e.g. select|topic|Topic|yes:Tip,Correction,Other).', 'sm' ) ),
				array( 'id' => 'contact_success', 'label' => __( 'Success message', 'sm' ), 'type' => 'text' ),
				array( 'id' => 'contact_store', 'label' => __( 'Store submissions in the database', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'contact_list', 'label' => __( 'Submissions', 'sm' ), 'type' => 'link', 'href' => 'edit.php?post_type=sm_entry', 'text' => __( 'View and export submissions', 'sm' ) ),
			),
		),
		'donate'     => array(
			'label'  => __( 'Donate', 'sm' ),
			'icon'   => 'heart',
			'fields' => array(
				array( 'id' => 'donate_url', 'label' => __( 'Checkout URL', 'sm' ), 'type' => 'url', 'help' => __( 'Where "Go to checkout" sends the reader. The chosen amount, type and frequency are appended as query parameters.', 'sm' ) ),
				array( 'id' => 'donate_currency', 'label' => __( 'Currency symbol', 'sm' ), 'type' => 'text' ),
				array( 'id' => 'donate_amounts', 'label' => __( 'Preset amounts', 'sm' ), 'type' => 'text', 'help' => __( 'Comma separated, e.g. 25,50,100', 'sm' ) ),
				array( 'id' => 'donate_types', 'label' => __( 'Donation types', 'sm' ), 'type' => 'textarea', 'help' => __( 'One per line.', 'sm' ) ),
			),
		),
		'reading'    => array(
			'label'  => __( 'Reading', 'sm' ),
			'icon'   => 'book',
			'fields' => array(
				array( 'id' => 'rp_enabled', 'label' => __( 'Reading progress bar', 'sm' ), 'type' => 'toggle', 'help' => __( 'A thin bar that fills as the reader scrolls.', 'sm' ) ),
				array( 'id' => 'rp_position', 'label' => __( 'Position', 'sm' ), 'type' => 'select', 'options' => array( 'top' => __( 'Top of page', 'sm' ), 'bottom' => __( 'Bottom of page', 'sm' ) ) ),
				array( 'id' => 'rp_height', 'label' => __( 'Height (px)', 'sm' ), 'type' => 'number', 'min' => 1, 'max' => 20 ),
				array( 'id' => 'rp_color', 'label' => __( 'Bar colour', 'sm' ), 'type' => 'color', 'help' => __( 'Leave empty to use the primary colour.', 'sm' ) ),
				array( 'id' => 'rp_post_types', 'label' => __( 'Show on', 'sm' ), 'type' => 'checkboxes', 'options' => $pt_options ),
				array( 'id' => 'lb_enabled', 'label' => __( 'Image lightbox', 'sm' ), 'type' => 'toggle', 'help' => __( 'Open images that link to their full-size file in an overlay (Lightbox2). Galleries become slideshows.', 'sm' ) ),
				array( 'id' => 'lb_wrap', 'label' => __( 'Wrap around at the end of a gallery', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'lb_captions', 'label' => __( 'Show captions', 'sm' ), 'type' => 'toggle' ),
			),
		),
		'site'       => array(
			'label'  => __( 'Search & Login', 'sm' ),
			'icon'   => 'shield',
			'fields' => array(
				array( 'id' => 'search_base', 'label' => __( 'Search permalink base', 'sm' ), 'type' => 'text', 'help' => __( 'Search URLs become /base/your-terms/ instead of ?s=your-terms. Default: search.', 'sm' ) ),
				array( 'id' => 'login_enabled', 'label' => __( 'Hide the login page', 'sm' ), 'type' => 'toggle', 'help' => __( 'Blocks wp-login.php and wp-admin for visitors who are not logged in, and serves the login form at the slug below. Bookmark the new URL before enabling.', 'sm' ) ),
				array( 'id' => 'login_slug', 'label' => __( 'Login slug', 'sm' ), 'type' => 'text', 'help' => __( 'The login form will be at /slug/. Default: login.', 'sm' ) ),
				array( 'id' => 'login_redirect', 'label' => __( 'Redirect blocked requests to', 'sm' ), 'type' => 'text', 'help' => __( 'Path visitors are sent to when they hit the hidden pages. Default: 404.', 'sm' ) ),
			),
		),
		'social'     => array(
			'label'  => __( 'Social', 'sm' ),
			'icon'   => 'share',
			'fields' => array_merge(
				array(
					array( 'id' => 'social_style', 'label' => __( 'Icon style', 'sm' ), 'type' => 'select', 'options' => array( 'plain' => __( 'Plain glyph', 'sm' ), 'boxed' => __( 'Boxed (as in the design)', 'sm' ), 'round' => __( 'Round', 'sm' ) ) ),
					array( 'id' => 'social_size', 'label' => __( 'Icon size (px)', 'sm' ), 'type' => 'number', 'min' => 12, 'max' => 64 ),
					array( 'id' => 'social_heading', 'label' => __( 'Profiles', 'sm' ), 'type' => 'heading', 'text' => __( 'Enter a URL for each profile you want shown. Empty profiles are hidden. Use the "Social icons" block or [sm_social] shortcode to place them anywhere.', 'sm' ) ),
				),
				$social
			),
		),
	);
}

/**
 * Extra defaults that belong to the integrated features.
 */
add_filter(
	'sm_setting',
	function ( $value, $key ) {
		if ( null !== $value ) {
			return $value;
		}
		$extra = array(
			'contact_fields'  => "text|name|Name|yes\nemail|email|Email|yes\ntext|subject|Subject|no\ntextarea|message|Type your message|yes",
			'contact_success' => __( 'Thank you. Your message has been sent.', 'sm' ),
			'contact_store'   => 1,
			'rp_enabled'      => 1,
			'rp_position'     => 'top',
			'rp_height'       => 4,
			'rp_color'        => '',
			'rp_post_types'   => array( 'post' ),
			'lb_enabled'      => 1,
			'lb_wrap'         => 1,
			'lb_captions'     => 1,
			'search_base'     => 'search',
			'login_enabled'   => 0,
			'login_slug'      => 'login',
			'login_redirect'  => '404',
			'social_style'    => 'boxed',
			'social_size'     => 18,
		);
		return isset( $extra[ $key ] ) ? $extra[ $key ] : $value;
	},
	10,
	2
);

add_action(
	'admin_menu',
	function () {
		add_theme_page(
			__( 'SM Theme', 'sm' ),
			__( 'SM Theme', 'sm' ),
			'manage_options',
			'sm-settings',
			'sm_render_settings_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'sm_settings_group',
			'sm_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'sm_sanitize_settings',
			)
		);
	}
);

/**
 * Sanitise every field according to its declared type.
 *
 * @param array $input Raw input.
 * @return array
 */
function sm_sanitize_settings( $input ) {
	$input   = (array) $input;
	$current = (array) get_option( 'sm_settings', array() );
	$clean   = $current;

	foreach ( sm_settings_tabs() as $tab ) {
		foreach ( $tab['fields'] as $f ) {
			$id = $f['id'];
			if ( in_array( $f['type'], array( 'link', 'heading', 'seed' ), true ) ) {
				continue;
			}
			$v = isset( $input[ $id ] ) ? $input[ $id ] : null;
			switch ( $f['type'] ) {
				case 'toggle':
					$clean[ $id ] = empty( $v ) ? 0 : 1;
					break;
				case 'number':
					$n = (int) $v;
					if ( isset( $f['min'] ) ) {
						$n = max( (int) $f['min'], $n );
					}
					if ( isset( $f['max'] ) ) {
						$n = min( (int) $f['max'], $n );
					}
					$clean[ $id ] = $n;
					break;
				case 'color':
					$clean[ $id ] = $v ? ( sanitize_hex_color( $v ) ?: '' ) : '';
					break;
				case 'url':
					$clean[ $id ] = esc_url_raw( trim( (string) $v ) );
					break;
				case 'email':
					$clean[ $id ] = sanitize_email( (string) $v );
					break;
				case 'textarea':
					$clean[ $id ] = sanitize_textarea_field( (string) $v );
					break;
				case 'select':
				case 'scheme':
					$clean[ $id ] = ( is_string( $v ) && isset( $f['options'][ $v ] ) ) ? $v : array_key_first( $f['options'] );
					break;
				case 'checkboxes':
					$v            = array_map( 'sanitize_key', (array) $v );
					$clean[ $id ] = array_values( array_intersect( $v, array_keys( $f['options'] ) ) );
					break;
				case 'categories':
					$clean[ $id ] = array_values( array_filter( array_map( 'absint', (array) $v ) ) );
					break;
				default:
					$clean[ $id ] = sanitize_text_field( (string) $v );
			}
		}
	}

	// Slugs must be URL safe and must not collide with core paths.
	$clean['search_base'] = sanitize_title( $clean['search_base'] ?? 'search' ) ?: 'search';
	$clean['login_slug']  = sanitize_title( $clean['login_slug'] ?? 'login' ) ?: 'login';
	if ( in_array( $clean['login_slug'], array( 'wp-admin', 'wp-login', 'wp-login.php', 'admin', 'login.php', 'wp-content', 'wp-includes' ), true ) ) {
		$clean['login_slug'] = 'login';
		add_settings_error( 'sm_settings', 'login_slug', __( 'That login slug is reserved. Reverted to "login".', 'sm' ) );
	}
	$clean['login_redirect'] = ltrim( sanitize_text_field( $clean['login_redirect'] ?? '404' ), '/' ) ?: '404';

	// Apply the preset scheme colours unless the custom scheme is chosen.
	$schemes = sm_color_schemes();
	if ( ! empty( $clean['scheme'] ) && 'custom' !== $clean['scheme'] && isset( $schemes[ $clean['scheme'] ] ) ) {
		foreach ( $schemes[ $clean['scheme'] ] as $k => $val ) {
			if ( 'label' !== $k ) {
				$clean[ $k ] = $val;
			}
		}
	}

	// Rewrite rules depend on the search base and login slug.
	set_transient( 'sm_flush_rewrite', 1, 60 );

	return $clean;
}

add_action(
	'init',
	function () {
		if ( get_transient( 'sm_flush_rewrite' ) ) {
			delete_transient( 'sm_flush_rewrite' );
			flush_rewrite_rules( false );
		}
	},
	99
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'appearance_page_sm-settings' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'sm-admin', SM_URI . 'assets/css/admin.css', array( 'wp-color-picker' ), SM_VERSION );
		wp_enqueue_script( 'sm-admin', SM_URI . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), SM_VERSION, true );
		wp_localize_script(
			'sm-admin',
			'smAdmin',
			array(
				'schemes' => sm_color_schemes(),
			)
		);
	}
);

/**
 * Render the settings screen.
 */
function sm_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$tabs   = sm_settings_tabs();
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $active ] ) ) {
		$active = 'general';
	}
	?>
	<div class="wrap sm-settings">
		<h1 class="sm-settings__title"><?php esc_html_e( 'SM Theme', 'sm' ); ?> <span><?php echo esc_html( SM_VERSION ); ?></span></h1>
		<?php settings_errors( 'sm_settings' ); ?>
		<form method="post" action="options.php" class="sm-settings__form">
			<?php settings_fields( 'sm_settings_group' ); ?>
			<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( admin_url( 'themes.php?page=sm-settings&tab=' . $active ) ); ?>" />
			<div class="sm-settings__layout">
				<nav class="sm-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'sm' ); ?>">
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<button type="button" role="tab" class="sm-tabs__tab<?php echo $key === $active ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo $key === $active ? 'true' : 'false'; ?>" aria-controls="sm-panel-<?php echo esc_attr( $key ); ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</nav>
				<div class="sm-panels">
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<section id="sm-panel-<?php echo esc_attr( $key ); ?>" role="tabpanel" class="sm-panel<?php echo $key === $active ? ' is-active' : ''; ?>" <?php echo $key === $active ? '' : 'hidden'; ?>>
							<h2 class="sm-panel__title"><?php echo esc_html( $tab['label'] ); ?></h2>
							<?php foreach ( $tab['fields'] as $f ) { sm_render_field( $f ); } ?>
						</section>
					<?php endforeach; ?>
					<div class="sm-settings__actions">
						<?php submit_button( __( 'Save changes', 'sm' ), 'primary', 'submit', false ); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Render one field row.
 *
 * @param array $f Field definition.
 */
function sm_render_field( $f ) {
	$id    = $f['id'];
	$name  = 'sm_settings[' . $id . ']';
	$value = sm_get( $id );
	$help  = isset( $f['help'] ) ? '<p class="sm-field__help">' . esc_html( $f['help'] ) . '</p>' : '';

	if ( 'heading' === $f['type'] ) {
		echo '<div class="sm-field sm-field--heading"><h3>' . esc_html( $f['label'] ) . '</h3><p>' . esc_html( $f['text'] ) . '</p></div>';
		return;
	}
	if ( 'seed' === $f['type'] ) {
		$when = (int) get_option( 'sm_seed_imported' );
		$run  = wp_nonce_url( admin_url( 'admin-post.php?action=sm_seed_import' ), 'sm_seed_import' );
		$over = wp_nonce_url( admin_url( 'admin-post.php?action=sm_seed_import&overwrite=1' ), 'sm_seed_import' );
		echo '<div class="sm-field"><div class="sm-field__label">' . esc_html( $f['label'] ) . '</div><div class="sm-field__control">';
		echo '<p>' . ( $when ? sprintf( /* translators: date */ esc_html__( 'Last imported %s.', 'sm' ), esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $when ) ) ) : esc_html__( 'Not imported yet.', 'sm' ) ) . '</p>';
		echo '<a class="button" href="' . esc_url( $run ) . '">' . esc_html__( 'Import missing items', 'sm' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $over ) . '" onclick="return confirm(\'' . esc_js( __( 'This overwrites the seeded posts, pages and menus with the versions in seed/content.json. Continue?', 'sm' ) ) . '\')">' . esc_html__( 'Re-import and overwrite', 'sm' ) . '</a>';
		echo '<p class="sm-field__help">' . esc_html__( 'Seed content lives in seed/content.json inside the theme. Posts, pages and menus are matched by slug, so importing never creates duplicates.', 'sm' ) . '</p>';
		echo '</div></div>';
		return;
	}
	if ( 'link' === $f['type'] ) {
		echo '<div class="sm-field"><div class="sm-field__label">' . esc_html( $f['label'] ) . '</div><div class="sm-field__control"><a class="button" href="' . esc_url( admin_url( $f['href'] ) ) . '">' . esc_html( $f['text'] ) . '</a></div></div>';
		return;
	}

	echo '<div class="sm-field sm-field--' . esc_attr( $f['type'] ) . '" data-field="' . esc_attr( $id ) . '">';
	echo '<label class="sm-field__label" for="sm-' . esc_attr( $id ) . '">';
	if ( ! empty( $f['icon'] ) ) {
		echo '<span class="sm-field__icon">' . sm_social_icon_svg( $f['icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo esc_html( $f['label'] ) . '</label>';
	echo '<div class="sm-field__control">';

	switch ( $f['type'] ) {
		case 'toggle':
			printf(
				'<label class="sm-toggle"><input type="checkbox" id="sm-%1$s" name="%2$s" value="1" %3$s /><span class="sm-toggle__track" aria-hidden="true"></span><span class="sm-toggle__text">%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( ! empty( $value ), true, false ),
				esc_html__( 'Enabled', 'sm' )
			);
			break;
		case 'number':
			printf(
				'<input type="number" class="small-text" id="sm-%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $f['min'] ?? 0 ),
				esc_attr( $f['max'] ?? 999 )
			);
			break;
		case 'color':
			printf(
				'<input type="text" class="sm-color" id="sm-%1$s" name="%2$s" value="%3$s" data-default-color="%4$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( sm_default_settings()[ $id ] ?? '' )
			);
			break;
		case 'textarea':
			printf(
				'<textarea id="sm-%1$s" name="%2$s" rows="5" class="large-text%4$s">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( $value ),
				! empty( $f['code'] ) ? ' code' : ''
			);
			break;
		case 'select':
		case 'scheme':
			echo '<select id="sm-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( 'scheme' === $f['type'] ? ' class="sm-scheme-select"' : '' ) . '>';
			foreach ( $f['options'] as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '" ' . selected( $value, $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
			if ( 'scheme' === $f['type'] ) {
				echo '<div class="sm-swatches">';
				foreach ( sm_color_schemes() as $k => $s ) {
					if ( 'custom' === $k ) {
						continue;
					}
					echo '<button type="button" class="sm-swatch" data-scheme="' . esc_attr( $k ) . '" title="' . esc_attr( $s['label'] ) . '">';
					foreach ( array( 'color_primary', 'color_tag', 'color_newsletter', 'color_link' ) as $ck ) {
						echo '<span style="background:' . esc_attr( $s[ $ck ] ) . '"></span>';
					}
					echo '</button>';
				}
				echo '</div>';
			}
			break;
		case 'checkboxes':
			$value = (array) $value;
			echo '<div class="sm-checklist">';
			foreach ( $f['options'] as $k => $label ) {
				printf(
					'<label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s /> %4$s</label>',
					esc_attr( $name ),
					esc_attr( $k ),
					checked( in_array( $k, $value, true ), true, false ),
					esc_html( $label )
				);
			}
			echo '</div>';
			break;
		case 'categories':
			$chosen = array_map( 'absint', (array) $value );
			$terms  = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
			$terms  = is_wp_error( $terms ) ? array() : $terms;
			// Chosen first, in saved order; then the rest alphabetically.
			usort(
				$terms,
				function ( $a, $b ) use ( $chosen ) {
					$ia = array_search( (int) $a->term_id, $chosen, true );
					$ib = array_search( (int) $b->term_id, $chosen, true );
					$ia = false === $ia ? PHP_INT_MAX : $ia;
					$ib = false === $ib ? PHP_INT_MAX : $ib;
					return $ia === $ib ? strcasecmp( $a->name, $b->name ) : $ia <=> $ib;
				}
			);
			echo '<ul class="sm-sortable">';
			foreach ( $terms as $t ) {
				printf(
					'<li><span class="sm-sortable__handle dashicons dashicons-menu" aria-hidden="true"></span><label><input type="checkbox" name="%1$s[]" value="%2$d" %3$s /> %4$s</label><span class="sm-sortable__count">%5$d</span></li>',
					esc_attr( $name ),
					(int) $t->term_id,
					checked( in_array( (int) $t->term_id, $chosen, true ), true, false ),
					esc_html( $t->name ),
					(int) $t->count
				);
			}
			echo '</ul>';
			break;
		default:
			printf(
				'<input type="%4$s" class="regular-text" id="sm-%1$s" name="%2$s" value="%3$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( in_array( $f['type'], array( 'url', 'email' ), true ) ? $f['type'] : 'text' )
			);
	}
	echo $help; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div></div>';
}
