<?php
/**
 * Title: Suara Malaysia footer
 * Slug: sm/footer
 * Categories: sm, footer
 * Block Types: core/template-part/footer
 * Description: Search, sitemap of top sections with recent stories, then a brand band with social links and a contact prompt.
 *
 * @package SM
 */

$sm_copyright = str_replace( '{year}', gmdate( 'Y' ), (string) sm_get( 'copyright' ) );
?>
<!-- wp:group {"tagName":"footer","align":"full","className":"sm-footer","layout":{"type":"default"}} -->
<footer class="wp-block-group alignfull sm-footer">
	<!-- wp:group {"align":"full","className":"sm-footer__top","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull sm-footer__top">
		<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
		<div class="wp-block-group alignwide">
			<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search Suara Malaysia","buttonText":"Go","buttonPosition":"button-inside","className":"sm-footer__search"} /-->
			<!-- wp:sm/footer-sections {"align":""} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"full","className":"sm-footer__base","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull sm-footer__base">
		<!-- wp:group {"align":"wide","className":"sm-footer__base-inner","layout":{"type":"default"}} -->
		<div class="wp-block-group alignwide sm-footer__base-inner">
			<!-- wp:group {"className":"sm-footer__brand","layout":{"type":"default"}} -->
			<div class="wp-block-group sm-footer__brand">
				<!-- wp:group {"className":"sm-footer__lockup","layout":{"type":"flex","flexWrap":"nowrap"}} -->
				<div class="wp-block-group sm-footer__lockup">
					<!-- wp:group {"className":"sm-footer__logo","layout":{"type":"default"}} -->
					<div class="wp-block-group sm-footer__logo">
						<!-- wp:site-logo {"width":48,"shouldSyncIcon":true} /-->
					</div>
					<!-- /wp:group -->
					<!-- wp:site-title {"level":0} /-->
				</div>
				<!-- /wp:group -->
				<!-- wp:site-tagline {"className":"sm-footer__tagline"} /-->
				<!-- wp:navigation {"smMenu":"sm-footer","overlayMenu":"never","className":"sm-footer__links","layout":{"type":"flex","flexWrap":"wrap"}} /-->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"sm-footer__contact","layout":{"type":"default"}} -->
			<div class="wp-block-group sm-footer__contact">
				<!-- wp:sm/social-icons {"align":"","style":"round","size":18} /-->
				<!-- wp:heading {"level":3,"className":"sm-footer__ask"} -->
				<h3 class="wp-block-heading sm-footer__ask"><?php esc_html_e( 'Got a story or a correction?', 'sm' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"sm-footer__ask-text"} -->
				<p class="sm-footer__ask-text"><?php esc_html_e( 'Tell the editors. We read every message.', 'sm' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:buttons -->
				<div class="wp-block-buttons">
					<!-- wp:button {"className":"sm-footer__btn"} -->
					<div class="wp-block-button sm-footer__btn"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact the editors', 'sm' ); ?></a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"align":"wide","className":"sm-footer__copy","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<div class="wp-block-group alignwide sm-footer__copy">
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $sm_copyright ); ?> &middot; <?php esc_html_e( 'Made in Malaysia', 'sm' ); ?> &#127474;&#127486;</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<?php if ( sm_get( 'back_to_top' ) ) : ?>
	<!-- wp:html -->
	<button class="sm-totop" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'sm' ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg></button>
	<!-- /wp:html -->
	<?php endif; ?>
</footer>
<!-- /wp:group -->
