<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

get_header(); 
$blog_main_class 		= 'blog single post';
$has_sidebar 			= false;
if(isset($smof_data['blog_layout']) && $smof_data['blog_layout'] != 'no-sidebar'){
	$blog_main_class 	= 	'blog1 single post ';
	$blog_main_class 	.= 	( $smof_data['blog_layout'] == 'sidebar-right')? 'inner right':'inner left';
	$sidebar_position 	= 	( $smof_data['blog_layout'] == 'sidebar-right')? 'left':'right';
	$has_sidebar 		= true;
}
	$_meta 				= get_post_meta( $post->ID, '_fastwp_meta', true );
?>
	<section id="blog" class="contain">		<div class="post-title bg parallax" data-speed="50" style="<?php echo (isset($_meta['top_parallax']) && !empty($_meta['top_parallax']))?'background-image:url('.$_meta['top_parallax'].')':'';?>">
				<div class="inner clearfix">
					<div class="title header">
						<h2><?php the_title();  ?></h2>
					</div>
				</div>
			</div>
		<div class="inner">

			<div class="<?php echo $blog_main_class; ?>">
<?php
 if ( have_posts() ){
 $is_first = true;
 // $show_post_date_on_top = true;
 global $is_first, $show_post_date_on_top;

 while ( have_posts() ) { 
	the_post(); 
	get_template_part( 'content', get_post_format() );
 } 

echo FastWP_UI::build_post_navigation(); 
?>
	<div class="clear"></div>
<?php } else { 
	get_template_part( 'content', '404');
 } ?>
 <?php
 wp_link_pages();
 ?>
		<ul class="pagination left">
			<li><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '<i class="fa fa-chevron-left"></i>', 'Previous post link', 'wisten' ) . '</span> %title' ); ?></li>
			<li><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '<i class="fa fa-chevron-right"></i>', 'Next post link', 'wisten' ) . '</span>' ); ?></li>
		</ul>
<div class="clear"></div>
<?php 

if ( comments_open() || get_comments_number() ) {
	comments_template(); 
}
?>
</div>
<?php if($has_sidebar == true){
global $theme_sidebar; 
$theme_sidebar = 'sidebar-1';
if(isset($smof_data['custom_sidebar']) && @$smof_data['sidebar_single'] == 1 && @$smof_data['sidebar_single_custom'] != 'sidebar-0'){
	$theme_sidebar = $smof_data['sidebar_single_custom'];
}
?>
	<div class="sidebar inner <?php echo $sidebar_position; ?>">
		<?php get_sidebar(); ?>		
	</div>
<?php } ?>
<div class="clear"></div></div></section>
<?php
$post_meta = get_post_meta( $post->ID, '_attach_section', true );
if(	isset($post_meta['fastwp_attach_separator']) && 
	!empty($post_meta['fastwp_attach_separator']) && 
	strlen($post_meta['fastwp_section_ct']) > 1
){
	echo '<div class="bottom-content">'.apply_filters('the_content', $post_meta['fastwp_section_ct']).'</div>';
}
?>

<?php get_footer(); 