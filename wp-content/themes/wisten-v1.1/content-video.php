<?php
/**
 * The template for displaying posts in the Image post format
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
	global $is_first, $show_post_date_on_top;
	$post_class = 'post type-image';
	if($is_first == false && !is_single()) { $post_class .= ' animated'; }
	
?>		
		<div <?php post_class($post_class); ?> data-animation="fadeInUp" data-animation-delay="0">
			<?php if($show_post_date_on_top == true) {
			$posted_month = get_the_date('M');
			$posted_day = get_the_date('d');
			printf('<div class="contain-logo post-icon"><p class="day">%s</p><p class="month">%s</p></div>',$posted_day, $posted_month);
			?>
			<?php } 
			$_meta = get_post_meta( $post->ID, '_fastwp_meta', true );
			if(!empty($_meta['video'])){ 
			$video = $_meta['video'];
			if(substr_count($video, 'vimeo.com') == 1){
				$videoURL = str_replace('http://vimeo.com/','http://player.vimeo.com/video/', $video);

			}else if(substr_count($video, 'youtu.be') == 1 ||substr_count($video, 'youtube.com') == 1){
				$videoURL = str_replace('/watch?v=','/embed/', $video);
			}
			?>
			<div class="post-video video-wrapper <?php echo (isset($_meta['video_wide']) && $_meta['video_wide'] == '1')?'wide':''; ?>">
				<div>
				<iframe style="width:100%; height:100%; border:none; overflow:hidden;" src="<?php echo $videoURL; ?>?wmode=opaque" allowfullscreen></iframe>
				</div>
			</div>
			<?php 
			}
			get_template_part( 'content', 'common'); ?>
		</div><!-- End Post -->
	<?php
	$is_first = false;