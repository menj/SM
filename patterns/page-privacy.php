<?php
/**
 * Title: Privacy policy page
 * Slug: sm/page-privacy
 * Categories: sm_pages
 * Description: Legal page with a sticky section navigation. Edit the text to match your practices.
 *
 * @package SM
 */

?>
<!-- wp:heading {"level":1,"className":"sm-page-title"} -->
<h1 class="wp-block-heading sm-page-title"><?php esc_html_e( 'Privacy Policy', 'sm' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"sm-legal","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-legal">
	<!-- wp:html -->
	<nav class="sm-legal__nav" aria-label="<?php esc_attr_e( 'Sections', 'sm' ); ?>">
		<a href="#what-we-collect"><?php esc_html_e( 'What we collect', 'sm' ); ?></a>
		<a href="#how-we-use-it"><?php esc_html_e( 'How we use it', 'sm' ); ?></a>
		<a href="#cookies"><?php esc_html_e( 'Cookies', 'sm' ); ?></a>
		<a href="#your-rights"><?php esc_html_e( 'Your rights', 'sm' ); ?></a>
		<a href="#contact-privacy"><?php esc_html_e( 'Contact', 'sm' ); ?></a>
	</nav>
	<!-- /wp:html -->

	<!-- wp:group {"className":"sm-legal__content","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-legal__content">
		<!-- wp:heading {"level":2,"anchor":"what-we-collect"} -->
		<h2 class="wp-block-heading" id="what-we-collect"><?php esc_html_e( 'What we collect', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'When you subscribe to our newsletter we store the name and email address you give us. When you send a message through the contact form we store the message, your name and email address, and the IP address it was sent from. Our web server keeps standard access logs.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"how-we-use-it"} -->
		<h2 class="wp-block-heading" id="how-we-use-it"><?php esc_html_e( 'How we use it', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'We use your email address only to send the newsletter you asked for and to reply to messages you send us. We do not sell, rent or share personal data with advertisers.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"cookies"} -->
		<h2 class="wp-block-heading" id="cookies"><?php esc_html_e( 'Cookies', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'This site sets only the cookies WordPress needs to function, such as a session cookie when you log in or leave a comment. Embedded media from third parties may set their own cookies.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"your-rights"} -->
		<h2 class="wp-block-heading" id="your-rights"><?php esc_html_e( 'Your rights', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Under the Personal Data Protection Act 2010 you may ask what personal data we hold about you, ask us to correct it, or ask us to delete it. Unsubscribe links are included in every newsletter.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"contact-privacy"} -->
		<h2 class="wp-block-heading" id="contact-privacy"><?php esc_html_e( 'Contact', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php echo esc_html( sprintf( /* translators: email */ __( 'Privacy questions can be sent to %s.', 'sm' ), sm_get( 'email' ) ) ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
