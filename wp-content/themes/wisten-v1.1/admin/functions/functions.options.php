<?php

add_action('init','of_options');

if (!function_exists('of_options'))
{
	function of_options()
	{ 
		//Access the WordPress Categories via an Array
		$of_categories 		= array();  
		$of_categories_obj 	= get_categories('hide_empty=0');
		foreach ($of_categories_obj as $of_cat) {
		    $of_categories[$of_cat->cat_ID] = $of_cat->cat_name;}
		$categories_tmp 	= array_unshift($of_categories, "Select a category:");    
	       
		//Access the WordPress Pages via an Array
		$of_pages 			= array();
		$of_pages_obj 		= get_pages('sort_column=post_parent,menu_order');    
		foreach ($of_pages_obj as $of_page) {
		    $of_pages[$of_page->ID] = $of_page->post_name; }
		$of_pages_tmp 		= array_unshift($of_pages, "Select a page:");       
	
		//Testing 
		$of_options_select 	= array("one","two","three","four","five"); 
		$of_options_radio 	= array("one" => "One","two" => "Two","three" => "Three","four" => "Four","five" => "Five");
		
		//Sample Homepage blocks for the layout manager (sorter)
		$of_options_homepage_blocks = array
		( 
			"disabled" => array (
				"placebo" 		=> "placebo", //REQUIRED!
				"block_one"		=> "Block One",
				"block_two"		=> "Block Two",
				"block_three"	=> "Block Three",
			), 
			"enabled" => array (
				"placebo" 		=> "placebo", //REQUIRED!
				"block_four"	=> "Block Four",
			),
		);


		//Stylesheets Reader
		$alt_stylesheet_path = LAYOUT_PATH;
		$alt_stylesheets = array();
		
		if ( is_dir($alt_stylesheet_path) ) 
		{
		    if ($alt_stylesheet_dir = opendir($alt_stylesheet_path) ) 
		    { 
		        while ( ($alt_stylesheet_file = readdir($alt_stylesheet_dir)) !== false ) 
		        {
		            if(stristr($alt_stylesheet_file, ".css") !== false)
		            {
		                $alt_stylesheets[] = $alt_stylesheet_file;
		            }
		        }    
		    }
		}


		//Background Images Reader
		$bg_images_path = get_stylesheet_directory(). '/images/bg/'; // change this to where you store your bg images
		$bg_images_url = get_template_directory_uri().'/images/bg/'; // change this to where you store your bg images
		$bg_images = array();
		
		if ( is_dir($bg_images_path) ) {
		    if ($bg_images_dir = opendir($bg_images_path) ) { 
		        while ( ($bg_images_file = readdir($bg_images_dir)) !== false ) {
		            if(stristr($bg_images_file, ".png") !== false || stristr($bg_images_file, ".jpg") !== false) {
		                $bg_images[] = $bg_images_url . $bg_images_file;
		            }
		        }    
		    }
		}
		

		/*-----------------------------------------------------------------------------------*/
		/* TO DO: Add options/functions that use these */
		/*-----------------------------------------------------------------------------------*/
		
		//More Options
		$uploads_arr 		= wp_upload_dir();
		$all_uploads_path 	= $uploads_arr['path'];
		$all_uploads 		= get_option('of_uploads');
		$other_entries 		= array("Select a number:","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19");
		$body_repeat 		= array("no-repeat","repeat-x","repeat-y","repeat");
		$body_pos 			= array("top left","top center","top right","center left","center center","center right","bottom left","bottom center","bottom right");
		
		// Image Alignment radio box
		$of_options_thumb_align = array("alignleft" => "Left","alignright" => "Right","aligncenter" => "Center"); 
		
		// Image Links to Options
		$of_options_image_link_to = array("image" => "The Image","post" => "The Post"); 


/*-----------------------------------------------------------------------------------*/
/* The Options Array */
/*-----------------------------------------------------------------------------------*/

// Set the Options Array
global $of_options;
$of_options = array();



$of_options[] = array( 	"name" 		=> "Global settings",
					"type" 		=> "heading",
					"icon"		=> ADMIN_IMAGES . "icon-home.png"
			);	

$of_options[] = array( 	"name" 		=> "Welcome!",
						"desc" 		=> "",
						"id" 		=> "introduction",
						"std" 		=> "<h3 style=\"margin: 0 0 10px;\">Welcome to Wisten</h3>
						This panel enables you almost all website settings. If you need support don`t hiesitate to contact us on our support forum.",
						"icon" 		=> true,
						"type" 		=> "info"
				);	
				
				
$of_options[] = array( 	"name" 		=> "Top bar social icons",
						"desc" 		=> "Contact email/url",
						"id" 		=> "social_contact",
						"std" 		=> "#",
						"type" 		=> "text"
				);	
$of_options[] = array(  "desc" 		=> "Facebook url",
						"id" 		=> "social_facebook",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
$of_options[] = array(  "desc" 		=> "Bitbucket url",
						"id" 		=> "social_bitbucket",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
$of_options[] = array(  "desc" 		=> "Dribbble url",
						"id" 		=> "social_dribbble",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Github url",
						"id" 		=> "social_github",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Google+ url",
						"id" 		=> "social_google_plus",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Instagram  url",
						"id" 		=> "social_instagram",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
				
					
$of_options[] = array(  "desc" 		=> "LinkedIn url",
						"id" 		=> "social_linkedin",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
				
					
$of_options[] = array(  "desc" 		=> "Skype url",
						"id" 		=> "social_skype",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Stack Exchange url",
						"id" 		=> "social_stack_exchange",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Tumblr url",
						"id" 		=> "social_tumblr",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Weibo url",
						"id" 		=> "social_weibo",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Vimeo url",
						"id" 		=> "social_vimeo",
						"std" 		=> "#",
						"type" 		=> "text"
				);				

$of_options[] = array( 	"desc" 		=> "Twitter url",
						"id" 		=> "social_twitter",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
$of_options[] = array( 	"desc" 		=> "Pinterest url",
						"id" 		=> "social_pinterest",
						"std" 		=> "#",
						"type" 		=> "text"
				);				
$of_options[] = array( 	"desc" 		=> "Youtube url",
						"id" 		=> "social_youtube",
						"std" 		=> "#",
						"type" 		=> "text"
				);	
$of_options[] = array( 	"desc" 		=> "RSS url",
						"id" 		=> "social_rss",
						"std" 		=> "#",
						"type" 		=> "text"
				);	
		
					
$of_options[] = array( 	"name" 		=> "Slogans",
						"desc" 		=> "",
						"id" 		=> "info1",
						"std" 		=> "If only one slogan is set it will be present all time on site header. If you add more than one, at page refresh a random slogan from list will be used.",
						"icon" 		=> true,
						"type" 		=> "info"
				);	
$of_options[] = array( 	"name" 		=> "Top bar random slogan",
						"desc" 		=> "Slogan 1",
						"id" 		=> "top_slogan_1",
						"std" 		=> "Contrary to popular belief, is not simply random text.",
						"type" 		=> "text"
				);						
$of_options[] = array(  "desc" 		=> "Slogan 2",
						"id" 		=> "top_slogan_2",
						"std" 		=> "",
						"type" 		=> "text"
				);						
$of_options[] = array(  "desc" 		=> "Slogan 3",
						"id" 		=> "top_slogan_3",
						"std" 		=> "",
						"type" 		=> "text"
				);						
$of_options[] = array(  "desc" 		=> "Slogan 4",
						"id" 		=> "top_slogan_4",
						"std" 		=> "",
						"type" 		=> "text"
				);						
$of_options[] = array(  "desc" 		=> "Slogan 5",
						"id" 		=> "top_slogan_5",
						"std" 		=> "",
						"type" 		=> "text"
				);	
$of_options[] = array( 	"name" 		=> "Hide top bar",
						"desc" 		=> "Hide top bar from website (this includes top social icons and top slogans)",
						"id" 		=> "hide_top_bar",
						"std" 		=> 0,
						"type" 		=> "switch"
				);	
				
$of_options[] = array( 	"name" 		=> "Menu breakpoint",
						"desc" 		=> "Set width of the screen where menu will transform into icon",
						"id" 		=> "menu_breakpoint",
						"std" 		=> "720",
						"min" 		=> "320",
						"step"		=> "1",
						"max" 		=> "1280",
						"type" 		=> "sliderui" 
				);
				

				
				$of_options[] = array( 	"name" 		=> "Show \"Home\" in menu",
						"desc" 		=> "Display \"Home\" button in navigation menu by default",
						"id" 		=> "home_in_menu",
						"std" 		=> 0,
						"type" 		=> "switch"
				);		
				
$of_options[] = array( 	"name" 		=> "Show menu after first section",
						"desc" 		=> "Display menu at the bottom of first section. Default: show menu on top of website",
						"id" 		=> "menu_after_home",
						"std" 		=> 0,
						"type" 		=> "switch"
				);			
		
$of_options[] = array( 	"name" 		=> "Combine JavaScript",
						"desc" 		=> "Combine javascript in order to speed up website",
						"id" 		=> "combine_js",
						"std" 		=> 0,
						"type" 		=> "switch"
				);	
$of_options[] = array( 	"name" 		=> "Combine CSS",
						"desc" 		=> "Combine css in order to speed up website",
						"id" 		=> "combine_css",
						"std" 		=> 0,
						"type" 		=> "switch"
				);	
$of_options[] = array( 	"name" 		=> "Enable preloader",
						"desc" 		=> "Enable preloader on page load",
						"id" 		=> "use_preloader",
						"std" 		=> 0,
						"type" 		=> "switch"
				);					
$of_options[] = array(  "name" 		=> "Portfolio permalink",
						"desc" 		=> "Set custom permalink for portfolio items",
						"id" 		=> "portfolio_permalink",
						"std" 		=> "project",
						"type" 		=> "text"
				);
$of_options[] = array( "name" 		=> "Project description",	
						"desc" 		=> "Project description label",
						"id" 		=> "project_desc_label",
						"std" 		=> "PROJECT DESCRIPTION",
						"type" 		=> "text",
				);	

$of_options[] = array( "name" 		=> "Project details",	
						"desc" 		=> "Project details label",
						"id" 		=> "project_detail_label",
						"std" 		=> "PROJECT DETAILS",
						"type" 		=> "text",
				);	
				
$of_options[] = array( 	"name" 		=> "General Settings",
						"type" 		=> "heading"
				);
							
				
$of_options[] = array( 	"name" 		=> "Favicon",
						"desc" 		=> "Upload favicon using this option",
						"id" 		=> "media_favicon",
						// Use the shortcodes [site_url] or [site_url_secure] for setting default URLs
						"std" 		=> get_template_directory_uri().'/images/favicon.png',
						// "mod"		=> "min",
						"type" 		=> "upload"
				);

$of_options[] = array( 	"name" 		=> "Logo setup",
						"desc" 		=> "Upload site logo",
						"id" 		=> "media_logo",
						"std" 		=> get_template_directory_uri().'/images/logo.png',
						"type" 		=> "upload"
				);

$of_options[] = array( //	"name" 		=> "Hidden option 1",
						"desc" 		=> "px left margin",
						"id" 		=> "logo_left",
						"std" 		=> "0",
					//	"fold" 		=> "switch_ex4", /* the switch hook */
						"type" 		=> "text"
				);
$of_options[] = array( //	"name" 		=> "Hidden option 1",
						"desc" 		=> "px top margin",
						"id" 		=> "logo_top",
						"std" 		=> "0",
					//	"fold" 		=> "switch_ex4", /* the switch hook */
						"type" 		=> "text"
				);

/*
$of_options[] = array( 	"name" 		=> "Smooth scroll",
						"desc" 		=> "Enable nicescroll for scrolling page instead of browsers standard scrollbar",
						"id" 		=> "use_nicescroll",
						"std" 		=> 0,
						"type" 		=> "switch"
				);
*/
$of_options[] = array( 	"name" 		=> "Custom JavaScript code",
						"desc" 		=> "Paste your custom javascript code here.",
						"id" 		=> "custom_js",
						"std" 		=> "",
						"type" 		=> "textarea"
				);

$of_options[] = array( 	"name" 		=> "Custom CSS",
						"desc" 		=> "Paste your custom CSS code here",
						"id" 		=> "custom_css",
						"std" 		=> "",
						"type" 		=> "textarea"
				);

$of_options[] = array( 	"name" 		=> "Error page content",
						"desc" 		=> "Build your 404 error page content here. You can use shortcodes to build this area.",
						"id" 		=> "custom_404",
						"std" 		=> "It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.",
						"type" 		=> "textarea"
				);
				
$alt_stylesheets = array('light'=>'Light skin', 'dark'=>'Dark skin');

$fonts = apply_filters('fastwp_add_google_fonts', array("none" => "Select a font",));
$of_options[] = array( 	"name" 		=> "Skin options",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-paint.png"
				);
					
$of_options[] = array( 	"name" 		=> "Website colorize",
						"desc" 		=> "",
						"id" 		=> "info1",
						"std" 		=> "Wisten theme supports secondary color customization.<br>You can change it in order to customize your website look.",
						"icon" 		=> true,
						"type" 		=> "info"
				);	
				
$of_options[] = array( 	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color to be used as secondary color overall.",
						"id" 		=> "color_secondary",
						"std" 		=> "#e70000",
						"type" 		=> "color"
				);
								
$of_options[] = array( 	"name" 		=> "Secondary website color gradient",
						"desc" 		=> "Top color",
						"id" 		=> "color_secondary_top",
						"std" 		=> "#e02424",
						"type" 		=> "color"
				);
			$of_options[] = array( 	"name" 		=> "Secondary website color gradient",
						"desc" 		=> "Bottom color",
						"id" 		=> "color_secondary_bottom",
						"std" 		=> "#be1919",
						"type" 		=> "color"
				);
					
					
$of_options[] = array( 	"name" 		=> "Custom fonts",
						"desc" 		=> "",
						"id" 		=> "info2",
						"std" 		=> "You can use google fonts for website in order to customize.<br>Customize your paragraphs and headings fonts.",
						"icon" 		=> true,
						"type" 		=> "info"
				);					
$of_options[] = array( 	"name" 		=> "Paragraphs font",
						"desc" 		=> "Color for paragraph text.",
						"id" 		=> "font_p",
						"std" 		=> "Select a font",
						"type" 		=> "select_google_font",
						"preview" 	=> array(
										"text" => "This is my preview text!", //this is the text from preview box
										"size" => "30px" //this is the text size from preview box
						),
						"options" 	=> $fonts,
				);
				

$of_options[] = array( 	"name" 		=> "Heading fonts",
						"desc" 		=> "Font for H1 heading text.",
						"id" 		=> "font_h1",
						"std" 		=> "Select a font",
						"type" 		=> "select_google_font",
						"preview" 	=> array(
										"text" => "This is my preview text!", //this is the text from preview box
										"size" => "30px" //this is the text size from preview box
						),
						"options" 	=> $fonts,
				);				
for($i=2;$i<6;$i++){
$of_options[] = array(  "desc" 		=> "Font for H$i heading text.",
						"id" 		=> "font_h$i",
						"std" 		=> "Select a font",
						"type" 		=> "select_google_font",
						"preview" 	=> array(
										"text" => "This is my preview text!", //this is the text from preview box
										"size" => "30px" //this is the text size from preview box
						),
						"options" 	=> $fonts,
				);
}								
				

$of_options[] = array( 	"name" 		=> "Blog settings",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-edit.png"
				);
$sidebar_locations = array(
	'sidebar-1' => 'Sidebar 1',
	'sidebar-2' => 'Sidebar 2',
	'sidebar-3' => 'Sidebar 3',
	'sidebar-4' => 'Sidebar 4',
	'sidebar-5' => 'Sidebar 5',

);				
$url =  ADMIN_DIR . 'assets/images/';
$of_options[] = array( "name" => "Blog Layout",
					"desc" => "Select blog layout (with or without sidebar) and sidebar position.",
					"id" => "blog_layout",
					"std" => "no-sidebar",
					"type" => "images",
					"folds" => 1,
					"options" => array(
						'no-sidebar' => $url . '1col.png',
						'sidebar-left' => $url . '2cr.png',
						'sidebar-right' => $url . '2cl.png',
						)
					);	

$of_options[] = array(// 	"name" 		=> "Sidebar ",
						"desc" 		=> "Use custom sidebar over blog pages",
						"id" 		=> "custom_sidebar",
						"std" 		=> 0,
						"folds" 	=> 1,
						"type" 		=> "switch",
					
				);

					
$of_options[] = array( 	"name" 		=> "Custom sidebars ",
						"desc" 		=> "Front page (when set to show latest posts)",
						"id" 		=> "sidebar_index",
						"std" 		=> 0,
						"type" 		=> "switch",
						"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
						
				$of_options[] = array( 
					"desc" => "Select sidebar",
					"id" => "sidebar_index_custom",
					"std" => "sidebar-0",
					"type" => "select",
					"fold" => "sidebar_index",
				"options" => $sidebar_locations);
											
		
					
$of_options[] = array(
						"desc" 		=> "Archive, Category, Tag",
						"id" 		=> "sidebar_archive",
						"std" 		=> 0,
						"type" 		=> "switch",
						"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
						
				$of_options[] = array( 
					"desc" => "Select sidebar",
					"id" => "sidebar_archive_custom",
					"std" => "sidebar-0",
					"type" => "select",
					"fold" 		=> "sidebar_archive",
				"options" => $sidebar_locations);
											
		
				$of_options[] = array( //	"name" 		=> "Sidebar1 ",
						"desc" 		=> "Author page",
						"id" 		=> "sidebar_author",
						"std" 		=> 0,
						"type" 		=> "switch",
						"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
				
				$of_options[] = array( 
					"desc" => "Select sidebar",
					"id" => "sidebar_author_custom",
					"std" => "sidebar-0",
					"type" => "select",
					"fold" 		=> "sidebar_author",
				"options" => $sidebar_locations);
								
		
				$of_options[] = array( 	//"name" 		=> "Sidebar2 ",
						"desc" 		=> "Search page",
						"id" 		=> "sidebar_search",
						"std" 		=> 0,
						"type" 		=> "switch",
						"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
						
				$of_options[] = array( 
					"desc" => "Select sidebar",
					"id" => "sidebar_search_custom",
					"std" => "sidebar-0",
					"type" => "select",
					"fold" 		=> "sidebar_search",
				"options" => $sidebar_locations);
											
		
				$of_options[] = array(// 	"name" 		=> "Sidebar3 ",
						"desc" 		=> "Single post page",
						"id" 		=> "sidebar_single",
						"std" 		=> 0,
						"type" 		=> "switch",
						"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
					
				$of_options[] = array( 
					"desc" => "Select sidebar",
					"id" => "sidebar_single_custom",
					"std" => "sidebar-0",
					"type" => "select",
					"fold" 		=> "sidebar_single",
				"options" => $sidebar_locations);
												
		
				$of_options[] = array( 	"name" 		=> "Blog paging",
						"desc" 		=> "Use simple navigation (Prev/Next). Default: Numbered navigation",
						"id" 		=> "blog_navi_type",
						"std" 		=> 0,
						"type" 		=> "switch",
						//"fold" 		=> "custom_sidebar",
				);
							
				$of_options[] = array( 	
						"name" 		=> "Blog top date",
						"desc" 		=> "Show posted date above each post except default post type",
						"id" 		=> "blog_top_date",
						"std" 		=> 0,
						"type" 		=> "switch",
				);
								
		
				$of_options[] = array( 	"name" 		=> "Map settings",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-edit.png"
				);		
				$of_options[] = array( 	"name" 		=> "Map center",
						"desc" 		=> "Latitude",
						"id" 		=> "map_lat",
						"std" 		=> "0",
						"type" 		=> "text"
				);
				$of_options[] = array( 	"desc" 		=> "Longitude",
						"id" 		=> "map_lng",
						"std" 		=> "0",
						"type" 		=> "text"
				);
				
				$of_options[] = array( 	"name" 		=> "Map zoom",
						"desc" 		=> "Set default zoom for map",
						"id" 		=> "map_zoom",
						"std" 		=> "16",
						"min" 		=> "1",
						"step"		=> "1",
						"max" 		=> "25",
						"type" 		=> "sliderui" 
				);
				

				
				$of_options[] = array(
						"name" 		=> "Map marker #1",
						"desc" 		=> "Use map marker #1",
						"id" 		=> "map_marker_1",
						"std" 		=> 1,
						"type" 		=> "switch",
						"folds"		=> 1,
				);
				$of_options[] = array( 	
						"desc" 		=> "Latitude",
						"id" 		=> "map_lat_m1",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_1",
				);
				$of_options[] = array( 	"desc" 		=> "Longitude",
						"id" 		=> "map_lng_m1",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_1",
				);
				$of_options[] = array( 	"desc" 		=> "Title",
						"id" 		=> "map_ttl_m1",
						"std" 		=> "",
						"type" 		=> "text",
						"fold"		=> "map_marker_1",
				);
				$of_options[] = array( 	"desc" 		=> "Content",
						"id" 		=> "map_ct_m1",
						"std" 		=> "",
						"type" 		=> "textarea",
						"fold"		=> "map_marker_1",
				);
				 

				$of_options[] = array(
						"name" 		=> "Map marker #2",
						"desc" 		=> "Use map marker #2",
						"id" 		=> "map_marker_2",
						"std" 		=> 0,
						"type" 		=> "switch",
						"folds"		=> 1,
				);
				$of_options[] = array( 	
						"desc" 		=> "Latitude",
						"id" 		=> "map_lat_m2",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_2",
				);
				$of_options[] = array( 	"desc" 		=> "Longitude",
						"id" 		=> "map_lng_m2",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_2",
				);
				$of_options[] = array( 	"desc" 		=> "Title",
						"id" 		=> "map_ttl_m2",
						"std" 		=> "",
						"type" 		=> "text",
						"fold"		=> "map_marker_2",
				);
				$of_options[] = array( 	"desc" 		=> "Content",
						"id" 		=> "map_ct_m2",
						"std" 		=> "",
						"type" 		=> "textarea",
						"fold"		=> "map_marker_2",
				);
				 

				$of_options[] = array(
						"name" 		=> "Map marker #3",
						"desc" 		=> "Use map marker #3",
						"id" 		=> "map_marker_3",
						"std" 		=> 0,
						"type" 		=> "switch",
						"folds"		=> 1,
				);
				$of_options[] = array( 	
						"desc" 		=> "Latitude",
						"id" 		=> "map_lat_m3",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_3",
				);
				$of_options[] = array( 	"desc" 		=> "Longitude",
						"id" 		=> "map_lng_m3",
						"std" 		=> "0",
						"type" 		=> "text",
						"fold"		=> "map_marker_3",
				);
				$of_options[] = array( 	"desc" 		=> "Title",
						"id" 		=> "map_ttl_m3",
						"std" 		=> "",
						"type" 		=> "text",
						"fold"		=> "map_marker_3",
				);
				$of_options[] = array( 	"desc" 		=> "Content",
						"id" 		=> "map_ct_m3",
						"std" 		=> "",
						"type" 		=> "textarea",
						"fold"		=> "map_marker_3",
				);
				 

				$of_options[] = array(
						"name" 		=> "Custom map style",
						"desc" 		=> "Customize google map color to fit your needs",
						"id" 		=> "map_custom",
						"std" 		=> 0,
						"type" 		=> "switch",
					//	"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
				$of_options[] = array( //	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color for map roads",
						"id" 		=> "map_color_road",
						"std" 		=> "#000000",
						"type" 		=> "color",
						"fold"		=> "map_custom",
				);

				$of_options[] = array( //	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color for landscapes",
						"id" 		=> "map_color_landscape",
						"std" 		=> "#141414",
						"type" 		=> "color",
						"fold"		=> "map_custom",
				);

				$of_options[] = array( //	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color for labels",
						"id" 		=> "map_color_labels",
						"std" 		=> "#7f8080",
						"type" 		=> "color",
						"fold"		=> "map_custom",
				);

				
				$of_options[] = array( //	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color for filling labels",
						"id" 		=> "map_color_labels_fill",
						"std" 		=> "#808080",
						"type" 		=> "color",
						"fold"		=> "map_custom",
				);

				$of_options[] = array( //	"name" 		=> "Secondary website color",
						"desc" 		=> "Pick a color for POI",
						"id" 		=> "map_color_poi",
						"std" 		=> "#161616",
						"type" 		=> "color",
						"fold"		=> "map_custom",
				);

				
				$of_options[] = array( 	"name" 		=> "Twitter",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-twitter.png"
				);		
				$of_options[] = array( 	"name" 		=> "Twitter API",
						"desc" 		=> "",
						"id" 		=> "info_twitter",
						"std" 		=> "If you don`t like to use our API keys or twitter feed is not visible, here you can setup new keys to be used for twitter feed calls.",
						"icon" 		=> true,
						"type" 		=> "info"
				);			
				$of_options[] = array( "name" 		=> "Twitter API",	
						"desc" 		=> "API Token",
						"id" 		=> "twt_api_token",
						"std" 		=> "",
						"type" 		=> "text",
				);	
				$of_options[] = array(
						"desc" 		=> "API Token Secret",
						"id" 		=> "twt_api_token_secret",
						"std" 		=> "",
						"type" 		=> "text",
				);	
				$of_options[] = array(
						"desc" 		=> "API Consumer Key",
						"id" 		=> "twt_api_consumer_key",
						"std" 		=> "",
						"type" 		=> "text",
				);	
				$of_options[] = array(
						"desc" 		=> "API Consumer Key Secret",
						"id" 		=> "twt_api_consumer_secret",
						"std" 		=> "",
						"type" 		=> "text",
				);	
	$of_options[] = array( 	"name" 		=> "Footer settings",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-edit.png"
				);		


				$of_options[] = array(
					"name" 		=> "Footer fields",	
					"desc" 		=> "Phone",
					"id" 		=> "footer_phone",
					"std" 		=> "",
					"type" 		=> "text",
				);	


				$of_options[] = array(
					// "name" 		=> "",	
					"desc" 		=> "Email",
					"id" 		=> "footer_email",
					"std" 		=> "themes@fastwp.net",
					"type" 		=> "text",
				);	


				$of_options[] = array(
					// "name" 		=> "",	
					"desc" 		=> "Address",
					"id" 		=> "footer_address",
					"std" 		=> "",
					"type" 		=> "text",
				);	


				$of_options[] = array(
					// "name" 		=> "",	
					"desc" 		=> "Copyright",
					"id" 		=> "footer_copy",
					"std" 		=> "&copy;2014 FastWP. ALL RIGHTS RESERVED.",
					"type" 		=> "text",
				);	

	
$of_options[] = array(  "name" 		=> "Footer social icons",
						"desc" 		=> "Facebook url",
						"id" 		=> "footer_social_facebook",
						"std" 		=> "",
						"type" 		=> "text"
				);				
$of_options[] = array(  "desc" 		=> "Bitbucket url",
						"id" 		=> "footer_social_bitbucket",
						"std" 		=> "",
						"type" 		=> "text"
				);				
$of_options[] = array(  "desc" 		=> "Dribbble url",
						"id" 		=> "footer_social_dribbble",
						"std" 		=> "",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Github url",
						"id" 		=> "footer_social_github",
						"std" 		=> "",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Google+ url",
						"id" 		=> "footer_social_google_plus",
						"std" 		=> "",
						"type" 		=> "text"
				);				
				
$of_options[] = array(  "desc" 		=> "Instagram  url",
						"id" 		=> "footer_social_instagram",
						"std" 		=> "",
						"type" 		=> "text"
				);				
				
					
$of_options[] = array(  "desc" 		=> "LinkedIn url",
						"id" 		=> "footer_social_linkedin",
						"std" 		=> "",
						"type" 		=> "text"
				);				
				
					
$of_options[] = array(  "desc" 		=> "Skype url",
						"id" 		=> "footer_social_skype",
						"std" 		=> "",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Stack Exchange url",
						"id" 		=> "footer_social_stack_exchange",
						"std" 		=> "",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Tumblr url",
						"id" 		=> "footer_social_tumblr",
						"std" 		=> "",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Weibo url",
						"id" 		=> "footer_social_weibo",
						"std" 		=> "",
						"type" 		=> "text"
				);				
					
$of_options[] = array(  "desc" 		=> "Vimeo url",
						"id" 		=> "footer_social_vimeo",
						"std" 		=> "",
						"type" 		=> "text"
				);				

$of_options[] = array( 	"desc" 		=> "Twitter url",
						"id" 		=> "footer_social_twitter",
						"std" 		=> "",
						"type" 		=> "text"
				);				
$of_options[] = array( 	"desc" 		=> "Pinterest url",
						"id" 		=> "footer_social_pinterest",
						"std" 		=> "",
						"type" 		=> "text"
				);	
				
				$of_options[] = array( 	"desc" 		=> "Youtube url",
						"id" 		=> "footer_social_youtube",
						"std" 		=> "",
						"type" 		=> "text"
				);	
				
				$of_options[] = array(
						"name" 		=> "Custom footer override",
						"desc" 		=> "Override default footer style with custom page",
						"id" 		=> "footer_override",
						"std" 		=> 0,
						"type" 		=> "switch",
					//	"fold" 		=> "custom_sidebar",
						"folds"		=> 1,
				);
			//	return;
			
			// $pages = (function_exists('fastwp_page_list'))? fastwp_page_list() : array();
			$of_options[] = array( 
					"desc" => "Select page",
					"id" => "footer_page",
					"std" => "",
					"type" => "select",
					"fold"		=> "footer_override",
					"options" => $of_pages);
			/*
				$of_options[] = array( // 	"name"		=> '',
						"desc" 		=> "",
						"id" 		=> "footer_mce",
						"std" 		=> "This is a test",
						"type" 		=> "mce_textarea",
						"fold"		=> "footer_override"
				);	
*/
				
// Backup Options
$of_options[] = array( 	"name" 		=> "Backup Options",
						"type" 		=> "heading",
						"icon"		=> ADMIN_IMAGES . "icon-backup.png"
				);
				
$of_options[] = array( 	"name" 		=> "Backup and Restore Options",
						"id" 		=> "of_backup",
						"std" 		=> "",
						"type" 		=> "backup",
						"desc" 		=> 'You can use the two buttons below to backup your current options, and then restore it back at a later time. This is useful if you want to experiment on the options but would like to keep the old settings in case you need it back.',
				);
				
$of_options[] = array( 	"name" 		=> "Transfer Theme Options Data",
						"id" 		=> "of_transfer",
						"std" 		=> "",
						"type" 		=> "transfer",
						"desc" 		=> 'You can tranfer the saved options data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Import Options".',
				);
				
	}//End function: of_options()
}//End chack if function exists: of_options()
?>
