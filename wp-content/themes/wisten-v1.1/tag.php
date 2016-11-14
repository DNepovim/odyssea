<?php
/**
 * The template for displaying Tag pages
 *
 * Used to display archive-type pages for posts in a tag.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
 
get_header(); 
$blog_main_class = 'blog';
$has_sidebar = false;
if(isset($smof_data['blog_layout']) && $smof_data['blog_layout'] != 'no-sidebar'){
	$blog_main_class 	= 	'blog1 ';
	$blog_main_class 	.= 	( $smof_data['blog_layout'] == 'sidebar-right')? 'inner right':'inner left';
	$sidebar_position 	= 	( $smof_data['blog_layout'] == 'sidebar-right')? 'left':'right';
	$has_sidebar 		= true;
}
?>

	<section id="blog" class="contain">
		<div class="inner">
			<div class="header">
				<?php echo single_tag_title( '', false ); ?>
			</div>
			<?php
				$term_description = term_description();
			if ( ! empty( $term_description ) ){ ?>
			<div class="page-desc">
				<?php echo $term_description; ?>
			</div>
			<?php } ?>
			<div class="<?php echo $blog_main_class; ?>">
<?php
 if ( have_posts() ){
 $is_first = true;
 $show_post_date_on_top = true;
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
</div>
<?php if($has_sidebar == true){
global $theme_sidebar; 
$theme_sidebar = 'sidebar-0';
if(isset($smof_data['custom_sidebar']) && $smof_data['sidebar_archive'] == 1 && $smof_data['sidebar_archive_custom'] != 'sidebar-0'){
	$theme_sidebar = $smof_data['sidebar_archive_custom'];
}
?>
	<div class="sidebar inner <?php echo $sidebar_position; ?>">
		<?php get_sidebar(); ?>		
	</div>
<?php } ?>
<div class="clear"></div></div></section>

<?php get_footer(); 				