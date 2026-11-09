<?php
/**
 * Appearance → Theme Options: tabbed settings page built on the Settings API.
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
			'desc'   => __( '1. Fill in the contact details. 2. Set the copyright line (use {year}). 3. Write a 150 to 160 character description for search engines. 4. Decide whether demo content is removed when you switch themes.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_site_details', 'type' => 'card', 'label' => __( 'Site details', 'sm' ), 'text' => __( 'Contact details and the footer line shown across the site.', 'sm' ) ),
				array( 'id' => 'phone', 'label' => __( 'Phone number', 'sm' ), 'type' => 'text', 'help' => __( 'Shown on the About and Contact pages.', 'sm' ) ),
				array( 'id' => 'email', 'label' => __( 'Public email', 'sm' ), 'type' => 'email' ),
				array( 'id' => 'copyright', 'label' => __( 'Copyright line', 'sm' ), 'type' => 'text', 'help' => __( 'Use {year} to insert the current year.', 'sm' ) ),
				array( 'id' => 'back_to_top', 'label' => __( 'Back-to-top button', 'sm' ), 'type' => 'toggle', 'help' => __( 'Show the small red scroll-to-top button in the footer.', 'sm' ) ),
				array( 'id' => 'card_search_engines', 'type' => 'card', 'label' => __( 'Search engines', 'sm' ), 'text' => __( 'How the homepage appears in search results.', 'sm' ) ),
				array( 'id' => 'meta_description', 'label' => __( 'Homepage meta description', 'sm' ), 'type' => 'textarea', 'help' => __( 'Shown to search engines for the homepage. Aim for 150 to 160 characters.', 'sm' ) ),
				array( 'id' => 'card_demo_content', 'type' => 'card', 'label' => __( 'Demo content', 'sm' ), 'text' => __( 'Control the sample stories, pages and menus that come with the theme.', 'sm' ) ),
				array( 'id' => 'autodelete_demo', 'label' => __( 'Delete demo content on deactivation', 'sm' ), 'type' => 'toggle', 'help' => __( 'When another theme is activated, the demo posts, pages, categories, menus and images this theme imported are deleted permanently. Content you added yourself is never touched.', 'sm' ) ),
				array( 'id' => 'seed_import', 'label' => __( 'Seed content', 'sm' ), 'type' => 'seed' ),
			),
		),
		'colours'    => array(
			'label'  => __( 'Colours', 'sm' ),
			'icon'   => 'art',
			'desc'   => __( '1. Click a preset to try a colour scheme. 2. Or adjust any colour below; the scheme switches to Custom. 3. Save, then check the homepage. Text colours are checked against their background for readability.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_preset_scheme', 'type' => 'card', 'label' => __( 'Preset scheme', 'sm' ), 'text' => __( 'Pick a preset. Every colour below updates to match.', 'sm' ) ),
				array( 'id' => 'scheme', 'label' => __( 'Colour scheme', 'sm' ), 'type' => 'scheme', 'options' => $scheme_options, 'help' => __( 'Pick a preset, or choose Custom and set each colour below.', 'sm' ) ),
				array( 'id' => 'card_custom_colours', 'type' => 'card', 'label' => __( 'Custom colours', 'sm' ), 'text' => __( 'Fine-tune individual colours. Changing one switches the scheme to Custom.', 'sm' ), 'grid' => true ),
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
			'desc'   => __( '1. Choose how the hero story is picked. 2. Tick the categories to feature and drag to reorder (leave all unticked to show every category). 3. Set how many stories each section shows.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_top_of_the_page', 'type' => 'card', 'label' => __( 'Top of the page', 'sm' ), 'text' => __( 'Which story leads the homepage.', 'sm' ) ),
				array( 'id' => 'hero_source', 'label' => __( 'Hero story', 'sm' ), 'type' => 'choice', 'options' => array( 'sticky' => __( 'Sticky post, otherwise latest', 'sm' ), 'latest' => __( 'Always the latest post', 'sm' ) ) ),
				array( 'id' => 'card_category_sections', 'type' => 'card', 'label' => __( 'Category sections', 'sm' ), 'text' => __( 'Choose which categories appear below the hero, and in what order.', 'sm' ) ),
				array( 'id' => 'home_sections', 'label' => __( 'Sections and order', 'sm' ), 'type' => 'categories', 'help' => __( 'Tick the categories to show as homepage sections and drag to reorder. Leave all unticked to show every non-empty category.', 'sm' ) ),
				array( 'id' => 'section_posts', 'label' => __( 'Stories per section', 'sm' ), 'type' => 'range', 'min' => 1, 'max' => 6 ),
				array( 'id' => 'show_excerpt', 'label' => __( 'Show excerpts on cards', 'sm' ), 'type' => 'toggle' ),
			),
		),
		'newsletter' => array(
			'label'  => __( 'Newsletter', 'sm' ),
			'icon'   => 'email-alt',
			'desc'   => __( '1. Edit the heading and success message. 2. Optionally paste an external form URL (Mailchimp, Brevo). Without one, subscribers are stored in WordPress and can be exported as CSV.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_panel_text', 'type' => 'card', 'label' => __( 'Panel text', 'sm' ), 'text' => __( 'What readers see in the subscribe panel.', 'sm' ) ),
				array( 'id' => 'nl_heading', 'label' => __( 'Heading', 'sm' ), 'type' => 'textarea', 'help' => __( 'Line breaks are kept.', 'sm' ) ),
				array( 'id' => 'nl_success', 'label' => __( 'Success message', 'sm' ), 'type' => 'text' ),
				array( 'id' => 'card_where_subscribers_go', 'type' => 'card', 'label' => __( 'Where subscribers go', 'sm' ), 'text' => __( 'Store subscribers in WordPress, or send them to an email service.', 'sm' ) ),
				array( 'id' => 'nl_action', 'label' => __( 'External form action URL', 'sm' ), 'type' => 'url', 'help' => __( 'Optional. Paste a Mailchimp, Brevo or similar form action URL to post subscribers there instead of storing them in WordPress.', 'sm' ) ),
				array( 'id' => 'nl_notify', 'label' => __( 'Email the admin on each new subscriber', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'nl_list', 'label' => __( 'Subscribers', 'sm' ), 'type' => 'link', 'href' => 'edit.php?post_type=sm_subscriber', 'text' => __( 'View and export subscribers', 'sm' ) ),
			),
		),
		'forms'      => array(
			'label'  => __( 'Contact form', 'sm' ),
			'icon'   => 'feedback',
			'desc'   => __( '1. Set who receives messages. 2. Edit the form fields, one per line as type|name|label|required. 3. Choose whether submissions are also stored; view them under Form entries.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_delivery', 'type' => 'card', 'label' => __( 'Delivery', 'sm' ), 'text' => __( 'Where contact messages are sent and kept.', 'sm' ) ),
				array( 'id' => 'contact_to', 'label' => __( 'Send messages to', 'sm' ), 'type' => 'email', 'help' => __( 'Leave empty to use the site admin email.', 'sm' ) ),
				array( 'id' => 'card_form_layout', 'type' => 'card', 'label' => __( 'Form layout', 'sm' ), 'text' => __( 'The text beside the form and the fields it asks for.', 'sm' ) ),
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
			'desc'   => __( '1. Paste your PayPal.Me link (or another checkout URL). 2. Set the currency symbol shown on the buttons and the three-letter PayPal currency code (MYR). 3. Set the preset amounts. Donation types apply to non-PayPal.Me checkouts only; add a monthly gift link to offer monthly giving.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_checkout', 'type' => 'card', 'label' => __( 'Checkout', 'sm' ), 'text' => __( 'Where the Donate button sends readers.', 'sm' ) ),
				array( 'id' => 'donate_url', 'label' => __( 'Checkout URL', 'sm' ), 'type' => 'url', 'help' => __( 'A PayPal.Me link (for example https://www.paypal.com/paypalme/yourname) sends the reader to PayPal with the chosen amount filled in; PayPal.Me gifts are one-time, so the type and frequency choices are hidden. Any other URL gets the amount, type and frequency as query parameters.', 'sm' ) ),
				array( 'id' => 'donate_monthly_url', 'label' => __( 'Monthly gift link (optional)', 'sm' ), 'type' => 'url', 'help' => __( 'A PayPal subscription or hosted-button link. When set, readers can choose One time (PayPal.Me, amount filled in) or Monthly (this link). Create it in PayPal: Pay & Get Paid, Subscriptions, Create plan.', 'sm' ) ),
				array( 'id' => 'donate_currency_code', 'label' => __( 'PayPal currency code', 'sm' ), 'type' => 'text', 'help' => __( 'Three letters, for example MYR. Used with PayPal.Me links.', 'sm' ) ),
				array( 'id' => 'donate_currency', 'label' => __( 'Currency symbol', 'sm' ), 'type' => 'text' ),
				array( 'id' => 'card_choices_on_offer', 'type' => 'card', 'label' => __( 'Choices on offer', 'sm' ), 'text' => __( 'The amounts and types readers can pick from.', 'sm' ) ),
				array( 'id' => 'donate_amounts', 'label' => __( 'Preset amounts', 'sm' ), 'type' => 'text', 'help' => __( 'Comma separated, e.g. 25,50,100', 'sm' ) ),
				array( 'id' => 'donate_types', 'label' => __( 'Donation types', 'sm' ), 'type' => 'textarea', 'help' => __( 'One per line.', 'sm' ) ),
			),
		),
		'reading'    => array(
			'label'  => __( 'Reading', 'sm' ),
			'icon'   => 'book',
			'desc'   => __( '1. Switch features on or off with the toggles; their settings appear when enabled. 2. Pick a bar position, lightbox behaviour and drop cap style. 3. Turn the Save as PDF button on or off. 4. Image format controls how new uploads are saved.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_reading_progress_bar', 'type' => 'card', 'label' => __( 'Reading progress bar', 'sm' ), 'text' => __( 'A thin bar that fills as readers scroll through an article.', 'sm' ) ),
				array( 'id' => 'rp_enabled', 'label' => __( 'Reading progress bar', 'sm' ), 'type' => 'toggle', 'help' => __( 'A thin bar that fills as the reader scrolls.', 'sm' ) ),
				array( 'id' => 'rp_position', 'label' => __( 'Position', 'sm' ), 'type' => 'choice', 'options' => array( 'top' => __( 'Top of page', 'sm' ), 'bottom' => __( 'Bottom of page', 'sm' ) ) ),
				array( 'id' => 'rp_height', 'label' => __( 'Height (px)', 'sm' ), 'type' => 'range', 'min' => 1, 'max' => 20 ),
				array( 'id' => 'rp_color', 'label' => __( 'Bar colour', 'sm' ), 'type' => 'color', 'help' => __( 'Leave empty to use the primary colour.', 'sm' ) ),
				array( 'id' => 'rp_post_types', 'label' => __( 'Show on', 'sm' ), 'type' => 'checkboxes', 'options' => $pt_options ),
				array( 'id' => 'card_image_lightbox', 'type' => 'card', 'label' => __( 'Image lightbox', 'sm' ), 'text' => __( 'Open linked images in an overlay, with gallery navigation.', 'sm' ) ),
				array( 'id' => 'lb_enabled', 'label' => __( 'Image lightbox', 'sm' ), 'type' => 'toggle', 'help' => __( 'Open images that link to their full-size file in an overlay. Galleries become slideshows.', 'sm' ) ),
				array( 'id' => 'lb_wrap', 'label' => __( 'Wrap around at the end of a gallery', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'lb_captions', 'label' => __( 'Show captions', 'sm' ), 'type' => 'toggle' ),
				array( 'id' => 'card_images', 'type' => 'card', 'label' => __( 'Images', 'sm' ), 'text' => __( 'The format used for new uploads.', 'sm' ) ),
				array( 'id' => 'card_pdf', 'type' => 'card', 'label' => __( 'Save as PDF', 'sm' ), 'text' => __( 'A button under each article that saves it as a PDF in the reader\'s browser (html2pdf.js, loaded only when clicked).', 'sm' ) ),
				array( 'id' => 'pdf_enabled', 'label' => __( 'Save as PDF button', 'sm' ), 'type' => 'toggle', 'help' => __( 'Appears next to the share icons on articles. Nothing is loaded until a reader clicks it.', 'sm' ) ),
				array( 'id' => 'image_format', 'label' => __( 'Image format', 'sm' ), 'type' => 'choice', 'options' => array( 'avif' => __( 'AVIF', 'sm' ), 'original' => __( 'Keep original format', 'sm' ) ), 'help' => __( 'New uploads and their sizes are written as AVIF for much smaller files. Originals are kept.', 'sm' ) ),
				array( 'id' => 'card_drop_caps', 'type' => 'card', 'label' => __( 'Drop caps', 'sm' ), 'text' => __( 'An illuminated first letter at the start of each article.', 'sm' ) ),
				array( 'id' => 'dc_enabled', 'label' => __( 'Drop cap on article openings', 'sm' ), 'type' => 'toggle', 'help' => __( 'Sets the first letter of an article in the Dropcaps illuminated-initial font. You can also apply the "Drop cap" style to any paragraph.', 'sm' ) ),
				array( 'id' => 'dc_variant', 'label' => __( 'Drop cap style', 'sm' ), 'type' => 'choice', 'options' => array( 'framed' => __( 'Framed initial', 'sm' ), 'open' => __( 'Open initial', 'sm' ) ) ),
				array( 'id' => 'dc_lines', 'label' => __( 'Drop cap height (lines)', 'sm' ), 'type' => 'range', 'min' => 2, 'max' => 5 ),
			),
		),
		'site'       => array(
			'label'  => __( 'Search & Login', 'sm' ),
			'icon'   => 'shield',
			'desc'   => __( '1. Optionally change the search address. 2. Leave security headers on unless your host already sends them. 3. To hide the login page, bookmark the new login address first, then switch it on. Blocked visitors are redirected to the path you set.', 'sm' ),
			'fields' => array(
				array( 'id' => 'card_search', 'type' => 'card', 'label' => __( 'Search', 'sm' ), 'text' => __( 'Cleaner search URLs.', 'sm' ) ),
				array( 'id' => 'search_base', 'label' => __( 'Search permalink base', 'sm' ), 'type' => 'text', 'help' => __( 'Search URLs become /base/your-terms/ instead of ?s=your-terms. Default: search.', 'sm' ) ),
				array( 'id' => 'card_login_page', 'type' => 'card', 'label' => __( 'Login page', 'sm' ), 'text' => __( 'Move the login form away from the default address.', 'sm' ) ),
				array( 'id' => 'card_security', 'type' => 'card', 'label' => __( 'Security headers', 'sm' ), 'text' => __( 'Extra protection sent with every public page.', 'sm' ) ),
				array( 'id' => 'security_headers', 'label' => __( 'Send security headers', 'sm' ), 'type' => 'toggle', 'help' => __( 'Stops other sites framing yours, stops browsers guessing file types and limits what referrer information is shared. Turn off if your host already sets these.', 'sm' ) ),
				array( 'id' => 'login_enabled', 'label' => __( 'Hide the login page', 'sm' ), 'type' => 'toggle', 'help' => __( 'Blocks wp-login.php and wp-admin for visitors who are not logged in, and serves the login form at the slug below. Bookmark the new URL before enabling.', 'sm' ) ),
				array( 'id' => 'login_slug', 'label' => __( 'Login slug', 'sm' ), 'type' => 'text', 'help' => __( 'The login form will be at /slug/. Default: login.', 'sm' ) ),
				array( 'id' => 'login_redirect', 'label' => __( 'Redirect blocked requests to', 'sm' ), 'type' => 'text', 'help' => __( 'Path visitors are sent to when they hit the hidden pages. Default: 404.', 'sm' ) ),
			),
		),
		'social'     => array(
			'label'  => __( 'Social', 'sm' ),
			'icon'   => 'share',
			'desc'   => __( '1. Pick an icon style and size. 2. Optionally add your Mastodon handle. 3. Enter a URL for each profile you use; empty ones are hidden. 3. Place icons with the Social icons block or the [sm_social] shortcode.', 'sm' ),
			'fields' => array_merge(
				array(
					array( 'id' => 'card_appearance', 'type' => 'card', 'label' => __( 'Appearance', 'sm' ), 'text' => __( 'How the icons look wherever they are shown.', 'sm' ) ),
				array( 'id' => 'social_style', 'label' => __( 'Icon style', 'sm' ), 'type' => 'choice', 'options' => array( 'plain' => __( 'Plain glyph', 'sm' ), 'boxed' => __( 'Boxed (as in the design)', 'sm' ), 'round' => __( 'Round', 'sm' ) ) ),
					array( 'id' => 'social_size', 'label' => __( 'Icon size (px)', 'sm' ), 'type' => 'range', 'min' => 12, 'max' => 64 ),
					array( 'id' => 'fediverse_handle', 'label' => __( 'Mastodon handle', 'sm' ), 'type' => 'text', 'help' => __( 'Optional, for example @name@mastodon.social. Adds the fediverse:creator tag so Mastodon can credit your articles to this account.', 'sm' ) ),
					array( 'id' => 'social_heading', 'label' => __( 'Profiles', 'sm' ), 'type' => 'card', 'text' => __( 'Enter a URL for each profile you want shown. Empty profiles are hidden. Use the "Social icons" block or [sm_social] shortcode to place them anywhere.', 'sm' ) ),
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
			'image_format'    => 'avif',
			'autodelete_demo' => 1,
			'pdf_enabled'     => 1,
			'security_headers' => 1,
			'fediverse_handle' => '',
			'dc_enabled'      => 1,
			'dc_variant'      => 'framed',
			'dc_lines'        => 3,
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
			__( 'SM Theme Options', 'sm' ),
			__( 'Theme Options', 'sm' ),
			'edit_theme_options',
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

	// Programmatic saves (importer, cleanup, other code) only send the keys they change.
	// Merge them over what is stored and never treat a missing field as cleared.
	if ( empty( $input['__form'] ) ) {
		return array_merge( $current, $input );
	}
	unset( $input['__form'] );
	$clean = $current;

	foreach ( sm_settings_tabs() as $tab ) {
		foreach ( $tab['fields'] as $f ) {
			$id = $f['id'];
			if ( in_array( $f['type'], array( 'link', 'heading', 'seed', 'card' ), true ) ) {
				continue;
			}
			$v = isset( $input[ $id ] ) ? $input[ $id ] : null;
			switch ( $f['type'] ) {
				case 'toggle':
					$clean[ $id ] = empty( $v ) ? 0 : 1;
					break;
				case 'number':
				case 'range':
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
				case 'choice':
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
	$code                         = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) ( $clean['donate_currency_code'] ?? '' ) ) );
	$clean['donate_currency_code'] = 3 === strlen( $code ) ? $code : 'MYR';
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
				'on'      => __( 'On', 'sm' ),
				'off'     => __( 'Off', 'sm' ),
			)
		);
	}
);

/**
 * Render the settings screen.
 */
function sm_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$tabs   = sm_settings_tabs();
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $active ] ) ) {
		$active = 'general';
	}
	?>
	<div class="wrap sm-settings">
		<header class="sm-header-bar">
			<span class="sm-header-bar__icon dashicons dashicons-admin-appearance" aria-hidden="true"></span>
			<div class="sm-header-bar__text">
				<h1 class="sm-settings__title"><?php esc_html_e( 'SM Theme Options', 'sm' ); ?></h1>
				<p><?php esc_html_e( 'Colours, homepage, forms and reading features for Suara Malaysia. Changes apply after you save.', 'sm' ); ?></p>
			</div>
			<span class="sm-badge"><?php echo esc_html( 'v' . SM_VERSION ); ?></span>
		</header>
		<?php settings_errors( 'sm_settings' ); ?>
		<form method="post" action="options.php" class="sm-settings__form">
			<?php settings_fields( 'sm_settings_group' ); ?>
			<input type="hidden" name="sm_settings[__form]" value="1" />
			<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( admin_url( 'themes.php?page=sm-settings&tab=' . $active ) ); ?>" />
			<div class="sm-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'sm' ); ?>">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<button type="button" role="tab" id="sm-tab-<?php echo esc_attr( $key ); ?>" class="sm-tabs__tab<?php echo $key === $active ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo $key === $active ? 'true' : 'false'; ?>" aria-controls="sm-panel-<?php echo esc_attr( $key ); ?>" tabindex="<?php echo $key === $active ? '0' : '-1'; ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
						<?php echo esc_html( $tab['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="sm-panels">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<section id="sm-panel-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="sm-tab-<?php echo esc_attr( $key ); ?>" class="sm-panel<?php echo $key === $active ? ' is-active' : ''; ?>" <?php echo $key === $active ? '' : 'hidden'; ?>>
						<h2 class="sm-panel__title"><?php echo esc_html( $tab['label'] ); ?></h2>
						<?php if ( ! empty( $tab['desc'] ) ) : ?>
							<p class="sm-panel__desc"><?php echo esc_html( $tab['desc'] ); ?></p>
						<?php endif; ?>
						<?php
						$open = false;
						foreach ( $tab['fields'] as $f ) {
							if ( 'card' === $f['type'] ) {
								if ( $open ) {
									echo '</div></div>';
								}
								echo '<div class="sm-card"><div class="sm-card__head"><h3>' . esc_html( $f['label'] ) . '</h3>' . ( ! empty( $f['text'] ) ? '<p>' . esc_html( $f['text'] ) . '</p>' : '' ) . '</div><div class="sm-card__body' . ( ! empty( $f['grid'] ) ? ' sm-card__body--grid' : '' ) . '">';
								$open = true;
								continue;
							}
							if ( ! $open ) {
								echo '<div class="sm-card"><div class="sm-card__body">';
								$open = true;
							}
							sm_render_field( $f );
						}
						if ( $open ) {
							echo '</div></div>';
						}
						?>
					</section>
				<?php endforeach; ?>
				<div class="sm-settings__actions">
					<?php submit_button( __( 'Save changes', 'sm' ), 'primary', 'submit', false ); ?>
				</div>
			</div>
		</form>
		<footer class="sm-settings__footer">
			<a href="https://github.com/menj/SM#readme" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'sm' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sm_entry' ) ); ?>"><?php esc_html_e( 'Contact form entries', 'sm' ); ?></a>
			<span><?php esc_html_e( 'Made for Suara Malaysia', 'sm' ); ?></span>
		</footer>
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
		$rm = wp_nonce_url( admin_url( 'admin-post.php?action=sm_seed_remove' ), 'sm_seed_remove' );
		echo '<a class="button" href="' . esc_url( $rm ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete all demo content imported by this theme? This cannot be undone.', 'sm' ) ) . '\')">' . esc_html__( 'Delete demo content now', 'sm' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $over ) . '" onclick="return confirm(\'' . esc_js( __( 'This overwrites the seeded posts, pages and menus with the versions in seed/content.json. Continue?', 'sm' ) ) . '\')">' . esc_html__( 'Re-import and overwrite', 'sm' ) . '</a>';
		echo '<p class="sm-field__help">' . esc_html__( 'Seed content lives in seed/content.json inside the theme. Posts, pages and menus are matched by slug, so importing never creates duplicates.', 'sm' ) . '</p>';
		echo '</div></div>';
		return;
	}
	if ( 'link' === $f['type'] ) {
		echo '<div class="sm-field"><div class="sm-field__label">' . esc_html( $f['label'] ) . '</div><div class="sm-field__control"><a class="button" href="' . esc_url( admin_url( $f['href'] ) ) . '">' . esc_html( $f['text'] ) . '</a></div></div>';
		return;
	}

	$grouped = in_array( $f['type'], array( 'choice', 'scheme', 'checkboxes', 'categories' ), true );
	echo '<div class="sm-field sm-field--' . esc_attr( $f['type'] ) . '" data-field="' . esc_attr( $id ) . '">';
	if ( $grouped ) {
		echo '<div class="sm-field__label" id="sm-lbl-' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</div>';
		echo '<div class="sm-field__control" role="group" aria-labelledby="sm-lbl-' . esc_attr( $id ) . '">';
	} else {
		echo '<label class="sm-field__label" for="sm-' . esc_attr( $id ) . '">';
		if ( ! empty( $f['icon'] ) ) {
			echo '<span class="sm-field__icon">' . sm_social_icon_svg( $f['icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo esc_html( $f['label'] ) . '</label>';
		echo '<div class="sm-field__control">';
	}

	switch ( $f['type'] ) {
		case 'toggle':
			printf(
				'<label class="sm-toggle"><input type="checkbox" id="sm-%1$s" name="%2$s" value="1" %3$s /><span class="sm-toggle__track" aria-hidden="true"></span><span class="sm-toggle__text">%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( ! empty( $value ), true, false ),
				! empty( $value ) ? esc_html__( 'On', 'sm' ) : esc_html__( 'Off', 'sm' )
			);
			break;
		case 'range':
			printf(
				'<div class="sm-range"><input type="range" id="sm-%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="1" /><output class="sm-range__value" for="sm-%1$s">%3$s</output></div>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $f['min'] ?? 0 ),
				esc_attr( $f['max'] ?? 100 )
			);
			break;
		case 'choice':
			echo '<div class="sm-choices">';
			foreach ( $f['options'] as $k => $label ) {
				printf(
					'<label class="sm-choice"><input type="radio" name="%1$s" value="%2$s" %3$s /><span class="sm-choice__body"><span class="sm-choice__preview sm-pv sm-pv--%4$s" aria-hidden="true"></span><span class="sm-choice__title">%5$s</span></span></label>',
					esc_attr( $name ),
					esc_attr( $k ),
					checked( (string) $value, (string) $k, false ),
					esc_attr( sanitize_html_class( $id . '-' . $k ) ),
					esc_html( $label )
				);
			}
			echo '</div>';
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
		case 'scheme':
			echo '<div class="sm-choices sm-choices--schemes">';
			foreach ( sm_color_schemes() as $k => $sch ) {
				printf(
					'<label class="sm-choice"><input type="radio" name="%1$s" value="%2$s" %3$s /><span class="sm-choice__body"><span class="sm-choice__preview sm-swatchrow" aria-hidden="true">',
					esc_attr( $name ),
					esc_attr( $k ),
					checked( (string) $value, (string) $k, false )
				);
				if ( 'custom' === $k ) {
					echo '<span class="sm-swatchrow__custom dashicons dashicons-admin-customizer"></span>';
				} else {
					foreach ( array( 'color_primary', 'color_tag', 'color_newsletter', 'color_link' ) as $ck ) {
						echo '<span style="background:' . esc_attr( $sch[ $ck ] ) . '"></span>';
					}
				}
				echo '</span><span class="sm-choice__title">' . esc_html( $sch['label'] ) . '</span></span></label>';
			}
			echo '</div>';
			break;
		case 'select':
			echo '<select id="sm-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $f['options'] as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '" ' . selected( $value, $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
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

/**
 * Shortcut to the options screen from the admin bar (front end and admin).
 */
add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'     => 'sm-theme-options',
				'parent' => 'site-name',
				'title'  => __( 'Theme Options', 'sm' ),
				'href'   => admin_url( 'themes.php?page=sm-settings' ),
			)
		);
	},
	80
);
