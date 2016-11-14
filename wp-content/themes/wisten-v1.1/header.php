<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till dynamic content begin
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
 global $smof_data;
 global $show_post_date_on_top;
 $show_post_date_on_top = (isset($smof_data['blog_top_date']) && $smof_data['blog_top_date'] == 1)? true : false;
 $siteFavIcon 			= (isset($smof_data['media_favicon']))? $smof_data['media_favicon'] : get_template_directory_uri().'/images/favicon.png';
 $preloader_markup 		= '<div id="pageloader">   <div class="loader-item"><img src="%s"/></div></div>';
 $preloader_image 		= get_template_directory_uri().'/images/loading.gif';
 $preloader 			= (isset($smof_data['use_preloader']) && $smof_data['use_preloader'] == '1')? sprintf($preloader_markup, $preloader_image):'';
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php echo $siteFavIcon; ?>" type="image/vnd.microsoft.icon"/>
<link rel="icon" href="<?php echo $siteFavIcon; ?>" type="image/x-ico"/>
<?php wp_head(); ?>
</head>

<body data-spy="scroll" data-target=".nav-menu" data-offset="50" <?php body_class(); ?>>
<?php
echo $preloader;
do_action('demo_before_content');
if(!isset($smof_data['hide_top_bar']) || $smof_data['hide_top_bar'] != 1){
?>
	<div id="pagetop" class="contain">
		<div class="inner pagetop">
			<div class="col-xs-6 left">
				<?php echo FastWP_UI::get_slogan(); ?>
			</div>
			<div class="col-xs-6 right">
				<?php echo FastWP_UI::get_social_icons(); ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
		
<?php 
}
// fastwp_is_multipage();
if(!isset($smof_data['menu_after_home']) || $smof_data['menu_after_home'] == '0' || !is_page_template('wisten-multi.php') /* || !isset($_GET['styleId']) || @$_GET['styleId'] == '1' */){ fwpHelper::logo_menu_markup(); }


?>