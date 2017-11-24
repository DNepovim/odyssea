<?php get_header();?>
<div id="content" class="<?php echo sidebar_position();?>">
	<div class="min-height-prop"></div>
	<div id="posts">
		<h2>404: Stránka nebyla nalezena</h2>
		<p><strong>Pardon.</strong>	Použijte tlačítka zpět vašeho prohlížeče, vyhledávací formulář a nebo zaberte pádlem kontra... tahle stránka tady prostě není nebo na ní nemáte dostatečná práva.</p>
		<?php include (TEMPLATEPATH . "/searchform.php");?>
	</div><?php
	if (sidebar_position() != "sidebar-off") get_sidebar(); // No point even calling the sidebar if its not wanted. ?>
	<span class="clear"></span>
</div>
<?php get_footer(); ?>