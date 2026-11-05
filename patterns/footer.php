<?php
/**
 * Title: Suara Malaysia footer
 * Slug: sm/footer
 * Categories: sm, footer
 * Block Types: core/template-part/footer
 * Description: Logo, site map, legal links, copyright line and social icons.
 *
 * @package SM
 */

$sm_copyright = str_replace( '{year}', gmdate( 'Y' ), (string) sm_get( 'copyright' ) );
?>
<!-- wp:group {"tagName":"footer","align":"full","className":"sm-footer","layout":{"type":"constrained"}} -->
<footer class="wp-block-group alignfull sm-footer">
	<!-- wp:group {"align":"wide","className":"sm-footer__main","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide sm-footer__main">
		<!-- wp:group {"className":"sm-footer__brand","layout":{"type":"default"}} -->
		<div class="wp-block-group sm-footer__brand">
			<!-- wp:site-logo {"width":48,"shouldSyncIcon":true} /-->
			<!-- wp:site-title {"level":0} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"sm-footer__sitemap","layout":{"type":"default"}} -->
		<div class="wp-block-group sm-footer__sitemap">
			<!-- wp:heading {"level":3,"className":"sm-footer__heading"} -->
			<h3 class="wp-block-heading sm-footer__heading"><?php esc_html_e( 'Site Map', 'sm' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:navigation {"smMenu":"sm-sitemap","overlayMenu":"never","layout":{"type":"flex","flexWrap":"wrap"}} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"sm-footer__legal","layout":{"type":"default"}} -->
		<div class="wp-block-group sm-footer__legal">
			<!-- wp:heading {"level":3,"className":"sm-footer__heading"} -->
			<h3 class="wp-block-heading sm-footer__heading"><?php esc_html_e( 'Legal', 'sm' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:navigation {"smMenu":"sm-legal","overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"}} /-->
		</div>
		<!-- /wp:group -->

		<?php if ( sm_get( 'back_to_top' ) ) : ?>
		<!-- wp:html -->
		<button class="sm-totop" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'sm' ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg></button>
		<!-- /wp:html -->
		<?php endif; ?>
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"sm-footer__bottom","layout":{"type":"flex","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide sm-footer__bottom">
		<!-- wp:paragraph -->
		<p><?php echo esc_html( $sm_copyright ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:sm/social-icons {"align":""} /-->
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
