<?php
/**
 * SM child theme bootstrap.
 *
 * Everything is split by concern inside /inc. Front-end CSS lives in
 * /assets/css and JavaScript in /assets/js; nothing is inlined here.
 *
 * @package SM
 */

defined( 'ABSPATH' ) || exit;

define( 'SM_VERSION', '1.0.0' );
define( 'SM_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'SM_URI', trailingslashit( get_stylesheet_directory_uri() ) );

require_once SM_DIR . 'inc/helpers.php';
require_once SM_DIR . 'inc/settings.php';
require_once SM_DIR . 'inc/assets.php';
require_once SM_DIR . 'inc/setup.php';
require_once SM_DIR . 'inc/blocks.php';
require_once SM_DIR . 'inc/newsletter.php';
require_once SM_DIR . 'inc/forms.php';
require_once SM_DIR . 'inc/features/social.php';
require_once SM_DIR . 'inc/features/unlist.php';
require_once SM_DIR . 'inc/features/progress-bar.php';
require_once SM_DIR . 'inc/features/lightbox.php';
require_once SM_DIR . 'inc/features/dropcaps.php';
require_once SM_DIR . 'inc/features/views.php';
require_once SM_DIR . 'inc/features/images.php';
require_once SM_DIR . 'inc/features/search-permalinks.php';
require_once SM_DIR . 'inc/features/hide-login.php';
require_once SM_DIR . 'inc/favicon.php';
require_once SM_DIR . 'inc/seo.php';
require_once SM_DIR . 'inc/seed.php';
