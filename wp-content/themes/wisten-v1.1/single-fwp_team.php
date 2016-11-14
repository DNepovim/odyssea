<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
$is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')? true : false;
if($is_ajax == true){ 
	echo '<body>';
	// sleep(1);
} else {
	get_header();
} 
$value = get_post_meta( $post->ID, '_fastwp_meta', true );
$tags 	= wp_get_object_terms($post->ID, 'portfolio-category');
$Tags = array();
foreach($tags as $tag){
	$Tags[] = $tag->name;
}
global $fastwp_social_networks, $fastwp_share_networks;
$social_template = '<a href="#" onClick="share_on.%s()"><i class="fa fa-%s"></i></a>';						
			$social = '';
			
			foreach($fastwp_share_networks as $name=>$icon){
				$social .= sprintf($social_template, $name, $icon);
			}
?>

	<div id="primary-team" class="site-content">
		<div id="content" role="main">
			
			<?php while ( have_posts() ) : the_post();  ?>
			<?php
				if($is_ajax == false){
			?>
			<div class="project-title-nav bg parallax " style="<?php echo (isset($value['top_parallax']) && !empty($value['top_parallax']))?'background-image:url('.$value['top_parallax'].')':'';?>">
				<div class="inner clearfix">
					<div class="title">
						<h2><?php the_title(); ?></h2>
						
					</div>
					<div class="post-nav">
						
				<nav class="nav-single">
				
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav"></span>' ); ?></span>
					<span class="grid-separator"></span>
					<span class="nav-next"><?php next_post_link( '%link', '<span class="meta-nav"></span>' ); ?></span>
				</nav><!-- .nav-single -->
			
					</div>
				</div>
			</div>
				<?php } else { } ?>
			<div class="inner member-item">
				
				<div class="inside-content clearfix group">
					<div class="col-md-4">
					<?php echo do_shortcode('[team-member id="'.$post->ID.'"]');?>
					</div>
					<div class="col-md-8 member-bio">
					<h2 class="team-page-title"><?php the_title();?></h2>
					<?php
					the_content();
					?>
					</div>
				</div>
				
			</div>
			<?php
			endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->
<?php if($is_ajax == true){ 
	wp_footer();
	echo '</body>';
} else {
	get_sidebar();
	get_footer();
}