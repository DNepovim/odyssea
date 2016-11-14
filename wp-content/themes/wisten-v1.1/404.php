<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<article id="post-0" class="post error404 no-results not-found center-content">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'This is somewhat embarrassing, isn&rsquo;t it?', 'wisten' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php echo (isset($smof_data['custom_404']) && !empty($smof_data['custom_404']))? $smof_data['custom_404'] : __('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.','fastwp'); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>