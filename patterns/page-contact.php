<?php
/**
 * Title: Contact page
 * Slug: sm/page-contact
 * Categories: sm_pages
 * Description: Contact form beside the contact details and social links.
 *
 * @package SM
 */

$sm_email = (string) sm_get( 'email' );
$sm_phone = (string) sm_get( 'phone' );
?>
<!-- wp:heading {"level":1,"align":"wide","className":"sm-page-title"} -->
<h1 class="wp-block-heading alignwide sm-page-title"><?php esc_html_e( 'Contact Us', 'sm' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"sm-contact","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-contact">
	<!-- wp:sm/contact-form {"align":""} /-->

	<!-- wp:group {"className":"sm-contact__aside","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-contact__aside">
		<!-- wp:paragraph -->
		<p><?php echo esc_html( sm_get( 'contact_intro' ) ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph -->
		<p><a href="mailto:<?php echo esc_attr( $sm_email ); ?>"><?php echo esc_html( $sm_email ); ?></a></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph -->
		<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $sm_phone ) ); ?>"><?php echo esc_html( $sm_phone ); ?></a></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"sm-contact__follow"} -->
		<p class="sm-contact__follow"><?php esc_html_e( 'Follow on:', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:sm/social-icons {"align":""} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
