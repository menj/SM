<?php
/**
 * Title: Homepage intro
 * Slug: sm/home-intro
 * Categories: sm
 * Description: Tagline and mission statement shown above the featured stories.
 *
 * @package SM
 */

?>
<!-- wp:group {"align":"wide","className":"sm-intro","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-intro">
	<!-- wp:group {"className":"sm-intro__lead","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-intro__lead">
		<!-- wp:paragraph {"className":"sm-intro__eyebrow"} -->
		<p class="sm-intro__eyebrow"><?php esc_html_e( 'About us', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"level":2,"className":"sm-intro__tagline"} -->
		<h2 class="wp-block-heading sm-intro__tagline"><?php esc_html_e( 'Social commentary webzine on Malaysian issues.', 'sm' ); ?></h2>
		<!-- /wp:heading -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"className":"sm-intro__body","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-intro__body">
		<!-- wp:paragraph {"className":"sm-intro__text"} -->
		<p class="sm-intro__text"><strong><?php esc_html_e( 'Suara Malaysia', 'sm' ); ?></strong> <?php esc_html_e( 'is a dedicated online platform where you can access a wealth of insightful and thought-provoking Malaysian social commentary. Our mission is to engage and empower individuals like you in meaningful discussions that contribute to creating a Better Malaysia for all.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"sm-intro__more"} -->
			<div class="wp-block-button sm-intro__more"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Learn more about us', 'sm' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
