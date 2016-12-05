</div>
</div>
</div>
</div>
</div><?php
if (function_exists('dynamic_sidebar')) {
	$sidebars_widgets = get_option('sidebars_widgets');
	$widget_count = count($sidebars_widgets["sidebar-2"]); /* Count the number of widgets in the second sidebar */
	if ($widget_count > 0 && $widget_count <= 3) { /* If there are no widgets in the bar then just skip. */?>
		<div id="content-footer">
			<?php 	if (!dynamic_sidebar("Footer Bar") ) {}?>
			<span class="clear"></span>
		</div><?php
	} elseif ($widget_count > 3){?>
		<div id="content-footer">
		<span class="error"><?php _e("Sem se vejdou maximálně 3 widgety. Zmenšete jejich počet ve \"Footer Bar\"."); ?><span>
		</div><?php
	}
}?>
</div>
<div id="footer"><?php
	global $spectaculaTheme;
	$spectaculaTheme->turn_off_page_exclusions(); // Remove the global excludes before calling wp-list-pages.
	// Need to add a devider between each </a><a>
	echo preg_replace("/(\/a>)[ |\t|\n|\r|\w]*(<a )/","$1, $2",strip_tags(wp_list_pages("meta_key=footer&meta_value=1&title_li=&hierarchical=0&echo=0"),"<a>"))?>
<?php wp_footer(); ?><!-- <?php echo get_num_queries(); ?> požadavků. <?php timer_stop(1); ?> vteřin. -->

	(c) Čekatelský lesní kurz Odyssea 2005 - 2016, Junák – český skaut, z. s. 
</div>
</div>
</div>
</div>
</div>
</body>
</html>