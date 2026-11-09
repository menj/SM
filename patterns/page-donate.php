<?php
/**
 * Title: Donate page
 * Slug: sm/page-donate
 * Categories: sm_pages
 * Description: Hashtag visual beside the donation chooser.
 *
 * @package SM
 */

?>
<!-- wp:group {"align":"wide","className":"sm-donate-page","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide sm-donate-page">
	<!-- wp:group {"className":"sm-donate-page__visual","layout":{"type":"default"}} -->
	<div class="wp-block-group sm-donate-page__visual">
		<!-- wp:paragraph {"className":"sm-donate-page__visual-text"} -->
		<p class="sm-donate-page__visual-text">#suara<br>malaysia</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"layout":{"type":"default"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"level":1,"className":"sm-donate-page__title"} -->
		<h1 class="wp-block-heading sm-donate-page__title"><?php esc_html_e( 'Donate', 'sm' ); ?></h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"sm-donate-page__intro"} -->
		<p class="sm-donate-page__intro"><?php esc_html_e( 'Support Suara Malaysia. Your gift helps keep the magazine online and publishing.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:sm/donate-form {"align":""} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:sm/newsletter {"align":"wide"} /-->
