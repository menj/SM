<?php
/**
 * Title: Three-column story grid (query loop)
 * Slug: sm/query-grid
 * Inserter: no
 *
 * @package SM
 */

?>
<!-- wp:query {"queryId":1,"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide">
	<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
		<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10"} /-->
		<!-- wp:post-terms {"term":"category","separator":" ","className":"sm-card-tab"} /-->
		<!-- wp:post-title {"isLink":true,"level":2} /-->
		<!-- wp:post-excerpt {"moreText":"","excerptLength":20} /-->
	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Nothing found. Try a different search.', 'sm' ); ?></p>
		<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->

	<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:query-pagination-previous {"label":"<?php esc_attr_e( 'Newer', 'sm' ); ?>"} /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next {"label":"<?php esc_attr_e( 'Older', 'sm' ); ?>"} /-->
	<!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
