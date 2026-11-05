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
		<a href="#independence"><?php esc_html_e( 'Independence', 'sm' ); ?></a>
		<a href="#accuracy"><?php esc_html_e( 'Accuracy', 'sm' ); ?></a>
		<a href="#opinion"><?php esc_html_e( 'Opinion', 'sm' ); ?></a>
		<a href="#corrections"><?php esc_html_e( 'Corrections', 'sm' ); ?></a>
	</nav>
	<!-- /wp:html -->

	<!-- wp:group {"className":"sm-legal__content","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-legal__content">
		<!-- wp:heading {"level":2,"anchor":"independence"} -->
		<h2 class="wp-block-heading" id="independence"><?php esc_html_e( 'Independence', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Suara Malaysia accepts no funding from political parties, candidates or government agencies. Reader donations never buy influence over what we publish.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"accuracy"} -->
		<h2 class="wp-block-heading" id="accuracy"><?php esc_html_e( 'Accuracy', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Claims of fact are checked against primary sources before publication. Where a source cannot be named we say so and explain why.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"opinion"} -->
		<h2 class="wp-block-heading" id="opinion"><?php esc_html_e( 'Opinion', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Pieces in the Op-Ed section represent the views of their authors. We publish a range of perspectives and welcome considered responses.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"anchor":"corrections"} -->
		<h2 class="wp-block-heading" id="corrections"><?php esc_html_e( 'Corrections', 'sm' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Errors are corrected promptly and noted at the foot of the article. Use the contact page to report one.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
