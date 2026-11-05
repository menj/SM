<?php
/**
 * Title: Suara Malaysia header
 * Slug: sm/header
 * Categories: sm, header
 * Block Types: core/template-part/header
 * Description: Serif masthead, utility links, red category bar with search.
 *
 * @package SM
 */

?>
<!-- wp:group {"tagName":"header","align":"full","className":"sm-header","layout":{"type":"default"}} -->
<header class="wp-block-group alignfull sm-header">
	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"align":"wide","className":"sm-header__top","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
		<div class="wp-block-group alignwide sm-header__top">
			<!-- wp:group {"className":"sm-header__brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group sm-header__brand">
				<!-- wp:site-logo {"width":64,"shouldSyncIcon":true} /-->
				<!-- wp:site-title {"level":0} /-->
			</div>
			<!-- /wp:group -->
			<!-- wp:group {"className":"sm-header__utility","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
			<div class="wp-block-group sm-header__utility">
				<!-- wp:navigation {"smMenu":"sm-utility","overlayMenu":"never","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"full","className":"sm-catbar","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull sm-catbar">
		<!-- wp:group {"align":"wide","className":"sm-catbar__inner","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
		<div class="wp-block-group alignwide sm-catbar__inner">
			<!-- wp:navigation {"smMenu":"sm-primary","overlayMenu":"mobile","overlayBackgroundColor":"accent-1","overlayTextColor":"base","layout":{"type":"flex","justifyContent":"left","flexWrap":"wrap"}} /-->
			<!-- wp:html -->
			<div class="sm-catbar__tools">
				<button class="sm-search-toggle" type="button" aria-expanded="false" aria-controls="sm-search-panel" aria-label="<?php esc_attr_e( 'Open search', 'sm' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="M21 21l-5.2-5.2"/></svg>
				</button>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"full","className":"sm-search-panel","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull sm-search-panel" id="sm-search-panel" hidden>
		<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'sm' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search Suara Malaysia…', 'sm' ); ?>","buttonText":"<?php esc_attr_e( 'Search', 'sm' ); ?>","buttonPosition":"button-inside"} /-->
	</div>
	<!-- /wp:group -->
</header>
<!-- /wp:group -->
