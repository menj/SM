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
		<p class="sm-about__text"><strong>SuaraMalaysia.com</strong> is an online magazine initiative developed to provide social commentary about Malaysia and Malaysian issues. Our goal is to not only share our vision for a better Malaysia with Islam (as the religion of the Federation) at its core principle and guidance, but to also educate the public as to why we believe that <strong>our</strong> <em>Weltanshaaung</em> is the best alternative, compared to the various streams of political thought struggling for a <em>gay</em> Malaysia, a <em>feminist</em> Malaysia, a <em>secular</em> Malaysia, or even worse….a <em>godless</em> Malaysia.</p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"sm-about__text"} -->
		<p class="sm-about__text">The owner and executive editor of <strong>SuaraMalaysia.com</strong> is MENJ. We will also publish guest articles that strengthen our objectives for a better Malaysia from time to time….and we do welcome contributions as well.</p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"sm-about__text"} -->
		<p class="sm-about__text"><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a> for the details.</p>
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
	<div class="wp-block-group sm-about__collage" aria-hidden="true">
		<!-- wp:group {"className":"sm-about__tile sm-about__tile--navy"} -->
		<div class="wp-block-group sm-about__tile sm-about__tile--navy"></div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"sm-about__tile sm-about__tile--yellow"} -->
		<div class="wp-block-group sm-about__tile sm-about__tile--yellow"></div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"sm-about__tile sm-about__tile--red"} -->
		<div class="wp-block-group sm-about__tile sm-about__tile--red"></div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"sm-about__tile sm-about__tile--blue"} -->
		<div class="wp-block-group sm-about__tile sm-about__tile--blue"></div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
