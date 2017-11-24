<?php
global $post;
$exclude = get_option("page_on_front");
if ((is_home() and get_option("show_on_front") != "page") or ((get_option("page_on_front") == "{$post->ID}") and get_option("show_on_front") == "page")) {
	$highlight = " current_page_item";
}?>
<div id="sidebar">
	<?php 	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("Boční panel") ) {?>
	<!-- No widgets included -->
	<div class="widget sidebar-widget widget_search">
		<form action="<?php bloginfo('url'); ?>/" method="get">
			<div>
			<input type="text" size="15" class="s" name="s" value="<?php the_search_query(); ?>" /><br/>
			<input type="submit" value="Search"/>
			</div>
		</form>
	</div>
	<div class="widget sidebar-widget widget_pages">
		<div class="widgettitle">
			<h3>Stránky</h3>
		</div>
		<ul>
			<li class="page_item <?php echo $highlight; ?>"><a href="<?php echo get_settings('home');?>">Home</a></li>
			<?php wp_list_pages("sort_column=menu_order&title_li");?>
		</ul>
	</div>
	<div class="widget sidebar-widget widget_catgories">
		<div class="widgettitle">
			<h3>Kategorie</h3>
		</div>
		<ul>
			<?php wp_list_categories("children=1&hierarchical=1&use_desc_for_title=0&title_li=");?>
		</ul>
	</div>
	<div class="widget sidebar-widget widget_archives">
		<div class="widgettitle">
			<h3>Archivy</h3>
		</div>
		<ul>
			<?php  wp_get_archives("type=monthly&limit=12&format=html");?>
		</ul>
	</div>
	<div class="widget sidebar-widget">
		<div class="widgettitle">
			<h3>Hlavička</h3>
		</div>
		<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
			<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
			<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
			<?php wp_meta(); ?>
		</ul>
	</div>
	<?php }?>
	<?php wp_meta(); ?>
</div>