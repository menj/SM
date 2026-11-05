<?php
/**
 * Title: About page
 * Slug: sm/page-about
 * Categories: sm_pages
 * Description: Two-column About layout with phone contact and image collage.
 *
 * @package SM
 */

$sm_phone = (string) sm_get( 'phone' );
?>
<!-- wp:group {"align":"wide","className":"sm-about","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-about">
	<!-- wp:group {"layout":{"type":"default"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"level":1,"className":"sm-page-title"} -->
		<h1 class="wp-block-heading sm-page-title"><?php esc_html_e( 'About Us', 'sm' ); ?></h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"sm-about__text"} -->
		<p class="sm-about__text"><?php esc_html_e( 'Suara Malaysia is an independent online magazine of social commentary on Malaysia. Since 2012 we have published analysis and opinion on politics, elections, society, the economy and Malaysia’s place in the world, written for readers who want more than the headlines.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"sm-about__text"} -->
		<p class="sm-about__text"><?php esc_html_e( 'We are not aligned with any party or coalition. Our writers argue from evidence, name their sources and welcome rebuttal. If you think we got something wrong, tell us and we will look again.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:group {"className":"sm-about__contact","layout":{"type":"default"}} -->
		<div class="wp-block-group sm-about__contact">
			<!-- wp:paragraph {"className":"sm-about__contact-label"} -->
			<p class="sm-about__contact-label"><?php esc_html_e( 'Have any questions? Contact us!', 'sm' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"sm-about__phone"} -->
			<p class="sm-about__phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $sm_phone ) ); ?>"><?php echo esc_html( $sm_phone ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"sm-about__collage","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-about__collage">
		<!-- wp:image {"className":"is-style-sm-rounded"} -->
		<figure class="wp-block-image is-style-sm-rounded"><img alt="" /></figure>
		<!-- /wp:image -->
		<!-- wp:image {"className":"is-style-sm-rounded"} -->
		<figure class="wp-block-image is-style-sm-rounded"><img alt="" /></figure>
		<!-- /wp:image -->
		<!-- wp:image {"className":"is-style-sm-rounded"} -->
		<figure class="wp-block-image is-style-sm-rounded"><img alt="" /></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
