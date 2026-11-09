<?php
/**
 * Title: Editorial policy page
 * Slug: sm/page-editorial
 * Categories: sm_pages
 * Description: Editorial standards page with a sticky section navigation.
 *
 * @package SM
 */

?>
<!-- wp:heading {"level":1,"align":"wide","className":"sm-page-title"} -->
<h1 class="wp-block-heading alignwide sm-page-title"><?php esc_html_e( 'Editorial Policy', 'sm' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"sm-legal","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-legal">
	<!-- wp:html -->
	<nav class="sm-legal__nav" aria-label="<?php esc_attr_e( 'Sections', 'sm' ); ?>">
		<a href="#what-we-publish"><?php esc_html_e( 'What we publish', 'sm' ); ?></a>
		<a href="#opinion"><?php esc_html_e( 'Opinion', 'sm' ); ?></a>
		<a href="#contributions"><?php esc_html_e( 'Contributions', 'sm' ); ?></a>
		<a href="#corrections"><?php esc_html_e( 'Corrections', 'sm' ); ?></a>
	</nav>
	<!-- /wp:html -->

	<!-- wp:group {"className":"sm-legal__content","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-legal__content">
		<!-- wp:heading {"level":2,"anchor":"what-we-publish"} -->
		<h2 class="wp-block-heading" id="what-we-publish"><?php esc_html_e( 'What we publish', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Suara Malaysia is an online magazine of social commentary about Malaysia and Malaysian issues, owned and edited by MENJ.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"opinion"} -->
		<h2 class="wp-block-heading" id="opinion"><?php esc_html_e( 'Opinion', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Articles express the views of their authors. Guest pieces appear under the author’s name and do not necessarily reflect the views of the editor.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"contributions"} -->
		<h2 class="wp-block-heading" id="contributions"><?php esc_html_e( 'Contributions', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'We publish guest articles that support our objectives for a better Malaysia, and contributions are welcome. Use the contact page for details.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"corrections"} -->
		<h2 class="wp-block-heading" id="corrections"><?php esc_html_e( 'Corrections', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'If you believe something we published is inaccurate, please tell us through the contact page.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
