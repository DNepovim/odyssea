<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

	global $is_first, $show_post_date_on_top;
	$post_class = 'post ';
	if($is_first == false && !is_single()) { $post_class .= ' animated'; }
?>
		<div <?php post_class($post_class); ?> data-animation="fadeInUp" data-animation-delay="0">
			<?php get_template_part( 'content', 'common'); ?>
		</div><!-- End Post -->
	<?php
	$is_first = false;