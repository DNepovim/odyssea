<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo("html_type"); ?>; charset=<?php bloginfo("charset"); ?>" />
	<meta name="MSSmartTagsPreventParsing" content="TRUE" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="X-UA-Compatible" content="IE=7" /> <?php /* Due to some problems with jQuery and ie8 I force into ie7 mode. */ ?>

	<title><?php wp_title('&raquo;',true,'right');?><?php echo $page > 1 ? "Page $page &raquo; " : '';?><?php bloginfo('name');?></title>
	<base href="<?php bloginfo('url'); ?>"></base>

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name');?> &raquo; <?php _e('global feed')?>" href="<?php bloginfo('rss2_url');?>" />

	<?php $style = get_theme_option('theme_style');?>

	<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/dist/styles.min.css" type="text/css" media="screen" />

	<!--[if lt IE 7]>
	<link rel="stylesheet" href="<?php bloginfo("template_directory");?>/ie.css" type="text/css" media="screen"/>
<?php if (preg_match("/\.png$/i",get_theme_option('header_image'))) { // If the header image is a PNG we need to fix things for IE6 and IE5.5 using IEs proprietary transparent image loader. ?>

	<style>
		a#header_image img { visibility:hidden}
		a#header_image { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo get_theme_option('header_image'); ?>', sizingMethod='image'; cursor:pointer}
	</style>

<?php }?>
	<![endif]-->

	<?php wp_head(); ?>

</head>
<body id="<?php echo strtolower(date("M"));?>"<?php body_class();?>>

<div id="background-layer-1">
<div id="background-layer-2">
<div id="background-layer-3">
<div id="container"><?php
// Find out what is needed in the header links space, if the selected option has no links or pages in it then set to off.
switch (get_theme_option("header_links")) {
	case 0:
		unset($links);
		unset($header_class);
	break;
	case 2:
		/* Annoyingly I have to strip out uls from this list to get the equivalent of hierarchical=0 as that seems to only half work. */
		$links=strip_tags(wp_list_pages("meta_key=header&meta_value=1&title_li=&hierarchical=0&echo=0"),"<a>,<li>");
		if ($links) $header_class=" class=\"with-links\"";
	break;
	default:
	case 1:
		$links=wp_list_bookmarks("limit=10&categorize=0&title_li=&show_images=0&updated=updated&categorize=0&echo=0&title_before=&title_after=");
		if (!function_exists('have_comments'))
			$links = strip_tags($links,'<a><li>'); // Remove all the ULs from wp2.1's return.
		if ($links) $header_class=" class=\"with-links\"";
	break;
}?>
<div id="header"<?php echo $header_class;?>>
<div id="titles"<?php echo (get_theme_option("header_image") != "" ? " class=\"with-image\"" : "")?>><?php
if (get_theme_option("header_image") != "") { ?>
	<a href="<?php echo get_settings("home");?>" id="header_image">
		<img src="<?php echo get_theme_option("header_image");?>" alt="<?php bloginfo("name");?>" />
	</a><?php
}?>
<div id="title-text">
	<h1 id="main-page-title"><?php bloginfo("name"); ?></h1><?php
if (get_bloginfo("description")) { // If there's a tagline we'll show it if not lets move along and not clutter this place up with unneeded tags.?>
	<h2 id="tag-line"><?php bloginfo("description");?></h2><?php
}?>
</div>
</div><?php
if ($links) {?>
	<div id="links">
		<ul><?php echo $links; ?></ul>
	</div><?php
}?>
	<div class="clear"></div>
</div>

<?php do_action('NAVbar');?>
<div id="content-section">
<div id="content-outer-1">
<div id="content-outer-2">
<div id="content-vertical-edge">
<div id="content-top-edge">
<div id="content-bottom-edge">

