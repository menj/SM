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
	<!-- wp:paragraph {"className":"sm-intro__tagline"} -->
	<p class="sm-intro__tagline"><?php esc_html_e( 'Social commentary webzine on Malaysian issues.', 'sm' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"className":"sm-intro__text"} -->
	<p class="sm-intro__text"><strong><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Suara Malaysia', 'sm' ); ?></a></strong> <?php esc_html_e( 'is a dedicated online platform where you can access a wealth of insightful and thought-provoking Malaysian social commentary. Our mission is to engage and empower individuals like you in meaningful discussions that contribute to creating a Better Malaysia for all.', 'sm' ); ?> <a class="sm-intro__more" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Learn more about us', 'sm' ); ?></a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
