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
	<!-- wp:heading {"level":1,"className":"sm-intro__tagline"} -->
	<h1 class="wp-block-heading sm-intro__tagline"><?php esc_html_e( 'Social commentary webzine on Malaysian issues.', 'sm' ); ?></h1>
	<!-- /wp:heading -->
	<!-- wp:group {"className":"sm-intro__body","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-intro__body">
		<!-- wp:paragraph {"className":"sm-intro__text"} -->
		<p class="sm-intro__text"><strong><?php esc_html_e( 'Suara Malaysia', 'sm' ); ?></strong> <?php esc_html_e( 'is a dedicated online platform where you can access a wealth of insightful and thought-provoking Malaysian social commentary. Our mission is to engage and empower individuals like you in meaningful discussions that contribute to creating a Better Malaysia for all.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"sm-intro__more"} -->
		<p class="sm-intro__more"><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Learn more about us', 'sm' ); ?> <span aria-hidden="true">&#8599;</span></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:image {"className":"sm-intro__visual","sizeSlug":"full","linkDestination":"none"} -->
	<figure class="wp-block-image sm-intro__visual"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/malaysia/sultan-abdul-samad.avif' ) ); ?>" alt="<?php esc_attr_e( 'Bangunan Sultan Abdul Samad in Kuala Lumpur, lit up at night', 'sm' ); ?>" width="960" height="541" fetchpriority="low" decoding="async"/><figcaption class="wp-element-caption"><?php esc_html_e( 'Bangunan Sultan Abdul Samad, Kuala Lumpur', 'sm' ); ?></figcaption></figure>
	<!-- /wp:image -->
</div>
<!-- /wp:group -->
