
<?php get_header();?>

<div id="content" class="<?php echo sidebar_position();?>">
	<div class="min-height-prop"></div>




	<div id="posts">

<div id="my-mega-menu-widget"> 
<?php /* Widgetized sidebar */
    if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('my_mega_menu') ) : ?><?php endif; ?>
</div>


<?php


if (have_posts()) {
		while (have_posts()) {
			the_post();?>
			<div class="post">
			<?php include( TEMPLATEPATH . '/thecontent.php');?>
			<?php comments_template('/comments.php', true); /* Call comments template which will check that if it is needed or not. */?>
			</div><?php
		}

	} else {?>
		<div class="post search">
		<h2>Sorry no pages matching your request were found.</h2>
		<p>Either hit the back button on your browser or use this search form to find what you where looking for.</p>
		<?php include (TEMPLATEPATH . "/searchform.php");?>
		</div><?php
	}



		global $wp_query; /* To avoid orphaned tags showing up when there is no need for post_nav_link() the following checks for the need of it. */
		if ( $wp_query->max_num_pages > 1 && !is_singular()) {?>
			<div id="page-navigation">
				<div class="previous_posts"><?php previous_posts_link("&laquo; Předchozí strana");?></div>
				<div class="next_posts"><?php next_posts_link("Další strana &raquo;");?></div>
			</div><?php
		}?>
	</div><?php
	if (sidebar_position() != "sidebar-off") get_sidebar(); // No point even calling the sidebar if its not wanted. ?>
	<span class="clear"></span>

	
</div>
<?php get_footer(); ?>