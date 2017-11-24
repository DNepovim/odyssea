<?php
/**
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
?>

<?php 
	ob_start();
		the_author_posts_link();
	$author_ori = ob_get_clean();
	$author_ori = str_replace('rel="author"','class="tag" rel="author"',$author_ori);
	$author_ori = preg_replace('|<a(.*?)>(.*?)<\/a>|ims','<a$1><i class="fa fa-smile-o"></i> '.__('Posted by','fastwp').' $2</a>',$author_ori);
	
	$posted_year 	= get_the_date('Y');
	$posted_month 	= get_the_date('n');
	$posted_day 	= get_the_date('d');
	
	$archive_link 	= get_day_link( $posted_year, $posted_month, $posted_day );

	$more_tag		= ($pos=strpos($post->post_content, '<!--more-->'))? '#more-'.$post->ID : '';
?>
				<!-- Post Texts -->
				<div class="post-texts">
				
					<!-- Post Header -->
					<h1 class="post-head">
						<?php if(is_single()) { 
							the_title(); 
							}else {
							?>
						<a href="<?php the_permalink(); echo $more_tag; ?>" class="post-title">
							<?php the_title(); ?>
						</a>
						<?php } ?>
					</h1>
					
					<!-- Post Tags -->
					<div class="post-tags">
						<a class="tag" href="<?php echo $archive_link; ?>">
						<i class="fa fa-clock-o"></i>
						<?php echo get_the_date(); ?>
						</a>
						<?php echo $author_ori; ?>
						<?php if ( comments_open() && ! post_password_required() ) : ?>
							<?php comments_popup_link( '<i class="fa fa-comment-o"></i> ' . __( '0 Comments', 'fastwp' ), _x( '<i class="fa fa-comment-o"></i> 1 Comment', 'fastwp' ), _x( '<i class="fa fa-comment-o"></i> % Comments', 'comments number', 'fastwp' ), 'tag' ); ?>
						<?php endif; ?>
						<?php
						the_tags( '<span class="tag"><i class="fa fa-tags"></i><span class="current-tags">', '', '</span></span>' ); 
						?>
						<span class="tag"><i class="fa fa-folder-o"></i><span class="current-tags">
						<?php
						 echo get_the_category_list(' '); 
						?>
						</span></span>
						
						
					</div>
					
					<!-- Post Message -->
					<div class="p-content">
						<?php 
						if(has_post_thumbnail()){
							echo '<div class="wisten-post-thumbnail">';
							the_post_thumbnail();
							echo "</div>";
						}
						if(is_single()){
							the_content();
						}else {
							the_excerpt(); 
						}
						?>
					</div>
					<div class="clear"></div>
					<?php if(!is_single()) { ?>
					<!-- More Button -->
					<a class="btn btn-large btn-post" href="<?php the_permalink(); echo $more_tag; ?>">
						<?php _e('Continue Reading','fastwp'); ?>
						<i class="fa fa-angle-double-right"></i>
					</a>
					<?php } ?>
				</div><!-- End Post Texts -->