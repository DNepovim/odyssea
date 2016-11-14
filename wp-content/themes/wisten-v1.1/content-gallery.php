<?php
/**
 * The template for displaying posts in the Status post format
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
	global $is_first, $show_post_date_on_top;
	$post_class = 'post with-gallery';
	if($is_first == false && !is_single()) { $post_class .= ' animated'; }
?>
		<div <?php post_class($post_class); ?> data-animation="fadeInUp" data-animation-delay="0">
			<?php if($show_post_date_on_top == true) {
			$posted_month = get_the_date('M');
			$posted_day = get_the_date('d');
			printf('<div class="contain-logo post-icon"><p class="day">%s</p><p class="month">%s</p></div>',$posted_day, $posted_month);
			?>
			<?php } ?>
			
			<!-- Post Slider -->
			<div class="post-slide flexslider">
			<div class="post-slides">
			<?php
				$_meta = get_post_meta( $post->ID, '_fastwp_meta', true );
				if(isset($_meta['gallery']) && is_array($_meta['gallery'])){
					foreach($_meta['gallery'] as $img){
						echo '<div class="item"> <img src="'.$img.'" alt=""/></div>';
					}
				}
			?>
			
					</div>
					<div class="clear"></div>
				</div>
			<?php get_template_part( 'content', 'common'); ?>
		</div><!-- End Post -->
	<?php
	$is_first = false;