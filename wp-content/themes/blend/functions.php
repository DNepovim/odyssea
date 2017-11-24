<?php

if ( function_exists('register_sidebar') ){
    register_sidebar(array(
        'name' => 'my_mega_menu',
        'before_widget' => '<div id="my-mega-menu-widget">',
        'after_widget' => '</div>',
        'before_title' => '',
        'after_title' => '',
));
}
add_action('init',create_function('','global $spectaculaTheme; $spectaculaTheme = new spectaculaTheme();')); // Create the theme object when WP kicks in.

class spectaculaTheme {
	// An array of font pt sizes to approximate % size. Allows me to present people with a familiar list but still have the font scalable in IE.
	var $font_size_array = array('10' => 84,'11' => 91,'12' => 98,'13' => 109.5,'14' => 117.5,'15' => 125,'16' => 133,'17' => 142,'18' => 151,'20' => 168.5,'22' => 181,'24' => 200,'26' => 218,'28' => 233,'32' => 270,'34' => 280,'36' => 300,'40' => 330,'44' => 370,'48' => 400,'56' => 470,'64' => 530,'72' => 600,);
	var $page_widget_exclusions = array();
	var	$list_page_options = 'sort_column=menu_order&title_li=';
	var	$list_cats_options = 'children=1&hierarchical=1&use_desc_for_title=0&title_li=&orderby=ID';

	// Some theme defaults, All these can be set from the admin page.
	var $theme_name = 'blend'; // The name of the theme. No real need to change this. I just use it to prefix the settings.
	var $theme_style = 'coffee'; // Default style, should be set to the folder name of the style you wish to use.
	var $title_size = 300; // Size of the tile font in the header.
	var $tag_size = 125; // Size of the site description tag. I use this mostly as a tag line so the default size is quite large.
	var $header_links = 1; // What should be shown in the link space in the header, 0 = off; 1 = blogroll; 2 = Pages with the meta value of header set to 1.
	var $enableNAV = true; // Should the nav bar be shown.
	var $navcontent = 1; // What should be shown on the nav bar, 1: Pages, 2: Categories, 3: Pages & Categories, 4: Categories & Pages.
	var $hometext = 'Domů'; // Text for the home link on the nav bar.
	var $homelink = true; // Should the home link be shown.
	var $sidebarposition = 2; // Where should the sidebar be, 0 = off; 1 = left; 2 = right
	var $sidebarpostoff = false; // Should we show the sidebar when in posts/pages.
	var $header_hide = false; // Hide the header text, the text is still there just moved 2 miles above the top border
	var $header_image = 'images/logo.png'; // Header logo image, path can be either a full URI to an image or relative to the theme root.

	var $sIFR = false; // Always keep the default at false. Strange things happen when it's set to true.
	var $sIFRtitle = '';
	var $sIFRtag = '';
	var $sIFRwidgettitle = '';
	var $sIFRposttitle = '';
	var $sIFRFooter = '';

	function spectaculaTheme() {

		if (!preg_match('/^https:\/\/.*\.(png|jpg|jpeg|gif)$/i',$this->header_image) && $this->header_image != '') // Prefix the theme path if the default doesn't reference http://.
			$this->header_image = get_bloginfo('template_directory').'/'.$this->header_image;
		//delete_option($this->theme_name.'_options'); // Uncomment to delete settings.

		$settings_temp = get_option($this->theme_name.'_options'); // Get the settings from the db.
		if (is_array($settings_temp))
			foreach (array_keys($settings_temp) as $key)
				$this->$key = $settings_temp[$key]; // populate this class with them.

		add_filter('body_class',array($this,'get_agent_body_class'));
		add_action('wp',array($this,'prepare_header_stuff'));
		add_action('NAVbar',array($this,'NAVbar'));
		add_action('admin_menu', array($this,'add_theme_page')); // Add the admin page
		add_action('admin_menu',array($this,'add_sidebar_options')); //Call a function that will add my custom page interface to pages.
		add_action('wp_insert_post',array($this,'process_single_page_options')); // Process any options when a page is saved
		add_filter('loginout',array($this,'no_follow_login')); // Adds a nofollow to the login logout.
		add_filter('wp_list_pages_excludes',array($this,'pages_excluded_from_widgets')); // Pages to be excluded from widgets. They will still show up in the navigation bar.
		add_action('wp_head',array($this,'header_CSS')); // Quick and dirty header addition to override the title font size with the ones chosen in the backend.
		add_filter('wp_list_categories',array($this,'CategoryCountFix'));

		/* Register sidebars */
		if ( function_exists('register_sidebar') ) {
			register_sidebar(array(
				'name' => 'Boční panel',
				'before_widget' => '<div class="widget sidebar-widget %2$s" id="%1$s">',
				'after_widget' => '</div>',
				'before_title' =>'<div class="widgettitle"><h3>',
				'after_title' => '</h3></div>'
				));
			register_sidebar(array(
				'name' => 'Footer Bar',
				'before_widget' => '<div class="widget footer-widget %2$s" id="footer-%1$s">',
				'after_widget' => '</div>',
				'before_title' => '<div class="widgettitle"><h3>',
				'after_title' => '</h3></div>'
				));
		}
	}

	// Stuff to execute before headers are sent but after WP has finished doing its thing.
	function prepare_header_stuff(){
		global $wp_db_version;

		if(function_exists('wp_list_comments') && is_singular())
			wp_enqueue_script('comment-reply');

		if (!is_admin() && $wp_db_version > 7000) { // Only add jQuery to wp 2.5 an above.
			wp_enqueue_script ('behaviour',get_bloginfo('template_url').'/js/behaviour.min.js',array('jquery'),1.0);
			wp_localize_script('behaviour','behaviourL10n',array(
				'searchError'	=> __('Ups! Rozběhni se a zkus to ještě jednou.'),
				'searchPrompt'	=> __('Hledat'),
				'trackbackShowText' => __('Ukázat trackbacky'),
				'trackbackHideText' => __('Skrýt trackbacks'),
				'replyHideMany' => __("Skrýt %count% odpovědí k %name% comment"),
				'replyShowMany' => __("Ukázat %count% odpovědí k %name% comment"),
				'replyHideOne' => __("Skrýt odpovědi k %name% comment"),
				'replyShowOne' => __("Ukázat odpovědi k %name% comment"),
				'nestDepth' => 1 // The depth at which to start collapsing comments.
			));


			/* Simply check that sIFR is wanted, if not lets not bother adding the actions. */
			wp_register_script('sifr',get_bloginfo('template_directory').'/js/sifr.js',array(),'2.0.7');
			wp_register_script('blendsifr',get_bloginfo('template_directory').'/js/blend-sifr.js',array('sifr'),'1.0.0');
			if ($this->sIFR) {
				$colours = get_theme_option('colours');
				$folder = get_bloginfo('template_directory').'/fonts/';

				wp_enqueue_script('sifr'); // This should dump sifr.js into the header, the next bit should output the calls in the footer.
				add_action('wp_footer',create_function('','wp_print_scripts("blendsifr"); return;'));

				wp_localize_script('blendsifr','sifrSettings',array(
					'header_text' =>		$colours['header_text'],
					'header_back' =>		$colours['header_back'],
					'content_text' =>		$colours['content_text'],
					'content_link1' =>		$colours['content_link1'],
					'content_back' =>		$colours['content_back'],
					'content_highlight' => 	$colours['content_highlight'],
					'content_link1_hover'=> $colours['content_link1_hover'],
					'footer1_text' =>		$colours['footer1_text'],
					'footer1_back' =>		$colours['footer1_back'],

					'sIFRtitle' => 			get_theme_option('sIFRtitle') ? $folder.get_theme_option('sIFRtitle') : false,
					'sIFRtag' => 			get_theme_option('sIFRtag') ? $folder.get_theme_option('sIFRtag') : false,
					'sIFRwidgettitle' =>	get_theme_option('sIFRwidgettitle') ? $folder.get_theme_option('sIFRwidgettitle') : false,
					'sIFRposttitle' => 		get_theme_option('sIFRposttitle') ? $folder.get_theme_option('sIFRposttitle') : false,
					'sIFRFooter' => 		get_theme_option('sIFRFooter') ? $folder.get_theme_option('sIFRFooter') : false,
				));
			}
		}
	}

	// Move the category count to inside the anchor. Otherwise formatting done on the anchor can be messed up when the option is turned on.
	function CategoryCountFix($content) {
		$content = preg_replace("/<\/a>[^\(<]*(\([0-9]*\))/i","&nbsp;&nbsp;<span class=\"category_count\">$1</span></a>",$content);
		return $content;
	}

	// Simple function to test if we are on WP25 yet or still on 2.3. If on 2.5 we'll use the new admin interface.
	function add_sidebar_options(){
		if( function_exists( "add_meta_box" )) {
			add_meta_box("ICIT_Page_Options","Theme specific options for this post",array($this,"single_post_options"),"post");
			add_meta_box("ICIT_Page_Options","Theme specific options for this page",array($this,"single_page_options"),"page");
		} else {
			add_action("dbx_post_sidebar",array($this,"single_post_options"),10);
			add_action("dbx_page_sidebar",array($this,"single_page_options"),10);
		}
	}

	//Change the [...] item at the bottom of the excerpt to a more useful link.
	function excerpt_more_link($content = "") {
		$content = str_replace(array(" [&#8230;]","[&#8230;]"),"&#8230;",$content);
		return ($content."<div class=\"excerpt-more\"><a href=\"".get_permalink()."\" >".get_theme_option(more_text)."</a></div>");
	}

	/* Navigation bar */
	function NAVbar(){
		if (get_theme_option("enableNAV")) {?>
			<div id="nav"><?php
				if (get_theme_option("homelink")) {
					global $post;
					// Setup the highlight of the home link if you have a page set as the front page and you are at that page.
					if ((is_home() and get_option("show_on_front") != "page") or ((get_option("page_on_front") == "{$post->ID}") and get_option("show_on_front") == "page")) {
						$highlight = " current_page_item";
					}
				}?>
				<ul id="pages_list"><?php
					if (get_theme_option("homelink")) {?>
						<li class="page_item <?php echo $highlight; ?>">
							<a href="<?php echo get_settings("home");?>"><?php echo get_theme_option("hometext"); ?></a>
						</li><?php
					}
					$this->turn_off_page_exclusions();
					// Remove the title attribute from the anchor as it can get in the way of the roll over.
					add_filter("wp_list_pages",array($this,"remove_title_attrib"),1);
					add_filter("wp_list_pages_excludes",array($this,"pages_excluded_from_navigation"),1);
					add_filter("wp_list_categories",array($this,"remove_title_attrib"),1);

					switch (get_theme_option("navcontent")) {
						case 1: wp_list_pages(get_theme_option("list_page_options"));break;
						case 2: wp_list_categories(get_theme_option("list_cats_options"));break;
						case 3: wp_list_pages(get_theme_option("list_page_options"));wp_list_categories(get_theme_option("list_cats_options"));break;
						case 4: wp_list_categories(get_theme_option("list_cats_options"));wp_list_pages(get_theme_option("list_page_options"));break;
					}
					// Remove the filter so any other calls to wp_list_pages and wp_list_categories will have title attributes on the anchors again.
					remove_filter("wp_list_pages",array($this,"remove_title_attrib"),1);
					remove_filter("wp_list_pages_excludes",array($this,"pages_excluded_from_navigation"),1);
					remove_filter("wp_list_categories",array($this,"remove_title_attrib"),1);
					$this->turn_on_page_exclusions();?>
				</ul>
				<span class="clear"></span>
			</div><?php
		}
	}
	/* This is a filter for pages that will be hidden from wp_list_pages. */
	function pages_excluded_from_widgets() {
		if (get_theme_option("page_widget_exclusions_executed"))
			return get_theme_option("page_widget_exclusions"); // No point searching twice so we'll just send back what we already know.

		$page_list = $this->page_widget_exclusions = array();
		$page_list = get_pages("meta_key=exclude&meta_value=1&hierarchical=0");
		foreach ($page_list as $excluded_pages) {
			array_push($this->page_widget_exclusions,$excluded_pages->ID);
		}
		$this->page_widget_exclusions_executed = true;
		return get_theme_option("page_widget_exclusions");
	}

	/* This is a filter for pages that will be hidden from The navigation bar. */
	function pages_excluded_from_navigation() {
		if ($this->pages_excluded_from_navigation_executed)
			return $this->page_navigation_exclusions; // No point searching twice so we'll just send back what we already know.

		$page_list = $this->page_navigation_exclusions = array();
		$page_list = get_pages("meta_key=nav-exclude&meta_value=1&hierarchical=0");
		foreach ($page_list as $excluded_pages) {
			array_push($this->page_navigation_exclusions,$excluded_pages->ID);
		}
		$this->pages_excluded_from_navigation_executed = true;
		return $this->page_navigation_exclusions;
	}

	/* A simple bit of regEX to find and remove a title from an anchor.
	 I use this as a filter on wp_list_pages and wp_list_categories to remove the title attribute when I have them arranged in a drop-down menu.
	The title tag can get in the way causing you to lose focus on the menu resulting in the menu folding away.*/
	function remove_title_attrib($content = "") {
		$content = preg_replace("/(<a [^<>]*)(title=\"[^\"]*\")([^<>]*>)/iUs","$1$3",$content);
		return($content);
	}

	/* Add a nofollow attribute to the wp_login as there is no point google indexing that and helps me hide from certain searches that are looking just for wp installs. */
	function no_follow_login($content="") {
		return ereg_replace( "(<a [^<>]*)(>.*)","\\1 rel=\"nofollow\"\\2",$content);
	}

	// Title should be sort of self explanatory. It removes the above filter.
	function turn_off_page_exclusions () {
		remove_filter("wp_list_pages_excludes",array($this,"pages_excluded_from_widgets"));
		return true;
	}

	// Title should be sort of self explanatory. It adds the above filter.
	function turn_on_page_exclusions () {
		add_filter("wp_list_pages_excludes",array($this,"pages_excluded_from_widgets"));
		return true;
	}

	/* Search the styles sub folder for valid colour schemes. */
	function find_schemes () {
		$this->colour_schemes; /* Define as a global so that if I need it twice in a single execution I shouldn't have to search the files again. */
		if (isset($this->colour_schemes)) return $this->colour_schemes;
		/* Most of this is a straight copy of the theme collection code in themes.php. Why reinvent the wheel. */
		$styles_root = TEMPLATEPATH."/styles";
		$styles_folder = @opendir($styles_root);
		if (!$styles_folder) return false;
		$this->colour_schemes = array();

		/* Read the content of the styles folder finding any folders with a style.cfg file in it and dropping that name into an array. */
		while (($scheme_folder = readdir($styles_folder)) !== false ) {
			if (is_dir($styles_root."/".$scheme_folder) && is_readable($styles_root."/".$scheme_folder) ) {
				if ( $scheme_folder{0} == "." || $scheme_folder == ".." || $scheme_folder == "CSV"  )
					continue; /* Drop out of the while if the file is very unlikely to be needed.*/

				$scheme_folder_content = @opendir($styles_root."/".$scheme_folder);
				while (($scheme_file = readdir($scheme_folder_content)) !== false) {
					if($scheme_file == "style.cfg")
						array_push($this->colour_schemes,array("folder" => $scheme_folder));
				}
				@closedir($scheme_folder_content);
			}
		}
		@closedir($styles_root);
		/*Lets open all previously found style.cfg files and extract a few needed elements. */
		for ($i = 0;$i < count($this->colour_schemes);$i++) {
			/* Open the file and dump it to a variable. */
			$file = TEMPLATEPATH."/styles/".$this->colour_schemes[$i]["folder"]."/style.cfg";
			if (file_exists($file))	{
				$stylecfg = fopen($file,"r");
				$file_content = fread($stylecfg, filesize($file));
				fclose($stylecfg);
			}
			/* Find all objects matching the patern of $word = vale; */
			preg_match_all("/\\$([a-z0-9A-Z_\-]+)[ |\t]*=[ |\t]*([^;$\n]*)/s",$file_content,$returns);
			for ($count=0;$count < count($returns[1]);$count++) {
				if (strtolower($returns[1][$count] == "title"))
					$this->colour_schemes[$i]["title"] = preg_replace("/[^a-zA-Z0-9 ]*/","",$returns[2][$count]); //strip anything that isn't meant to be in the title.
				/*Find the colours I need for the sIFR forground and background so I can store them in the DB.  I then don't need to  read the files each time, although I do read them when processing the CSS I don't want to over do it. */
				if (in_array(strtolower($returns[1][$count]),array("header_text","header_back","content_text","content_back","content_highlight","content_link1","content_link1_hover","footer1_back","footer1_text"))) {
					//I'll strip anything that dosn't look like a hex colour code and replace it with something ugly. :D Should be spotted that way.
					if (preg_match("/^#([0-9A-F]{3}|[0-9A-F]{6})$/i",$returns[2][$count])) {
						$this->colour_schemes[$i]["colours"][strtolower($returns[1][$count])] = $returns[2][$count];
					} else {
						$this->colour_schemes[$i]["colours"][strtolower($returns[1][$count])] = "#FF99CC";
					}
				}
			}
			if (!isset($this->colour_schemes[$i]["title"])) $this->colour_schemes[$i]["title"] = $this->colour_schemes[$i]["folder"];
		}
		return($this->colour_schemes);
	}
	/* Search the fonts folder for any swf files and assume they are sIFR fonts. */
	function font_list() {
		global $fonts_list; // Set it up as a global so I can keep referring back to the global rather than re-reading the folder.
		if (isset($fonts_list))	return $fonts_list;

		$fonts_list= array();
		$files = @glob(TEMPLATEPATH."/fonts/*.swf"); // I'll likely have to change this to use something other than glob as glob won't work universally.
		if (is_array($files) && count($files) > 0) {
			$fonts_list = array();
			foreach ($files as $file)
				array_push($fonts_list,str_replace(TEMPLATEPATH."/fonts/","",$file));
		}
		return($fonts_list);
	}
	/* Simple filter to override the header font sizes. */
	function header_CSS() {
		echo "<style type=\"text/css\" media=\"all\">\n\t<!--\n";
		if (get_theme_option("header_hide")) {
			echo "\t#title-text {position:absolute;top:-1000em;}\n";
		} else {
			echo "\th1#main-page-title {font-size:".get_theme_option("title_size")."%;}\n";
			echo "\th2#tag-line{font-size:".get_theme_option("tag_size")."%;}\n";
		}
		echo "\t-->\n</style>\n";
	}

	/* Add the page to the back end and process any post variables that need processing.*/
	function add_theme_page() {
		if ($_GET["page"] == basename(__FILE__)) { /* If the admin is using this file then execute the following. */
			add_action("admin_head", array($this,"admin_head")); /* Add my CSS to the header. */
			add_action("admin_footer", array($this,"admin_foot")); /*Add my JS to the footer of the admin page. I like my JS at the foot as I can usually be sure the rest of the page has loaded. */

			/* Process the post variable and check and filter anything that shouldn't be there.*/
			if (count($_POST) > 0) {
				$schemes = $this->find_schemes(); /* Retreive the list  of available schemes. */
				if ($_POST["theme_style"] != "") {
					foreach ($schemes as $scheme)
						if ($scheme["folder"] == $_POST["theme_style"]) {
							$this->theme_style 	= $scheme["folder"];
							$this->colours 		= $scheme["colours"];
						}
				} else {
					unset($this->theme_style);
					unset($this->colours);
				}
				/* Check the sIFR toggle switch, if on then well check the fonts at the same time.  If someone changes
				a font then sets it to disabled then the change will not be saved, not a big loss in my book though. Could
				cause confustion to people with JS turned off as the font dropdowns will still be usable.*/
				if ($_POST["sIFR"] == "on") {
					$this->sIFR = true;
					unset($this->sIFRtitle); unset($this->sIFRtag); unset($this->sIFRwidgettitle); unset($this->sIFRposttitle); unset($this->sIFRFooter);
					foreach ($this->font_list() as $font) {
						if ($_POST["sIFRtitle"] == $font) $this->sIFRtitle = $font;
						if ($_POST["sIFRtag"] == $font) $this->sIFRtag = $font;
						if ($_POST["sIFRwidgettitle"] == $font) $this->sIFRwidgettitle = $font;
						if ($_POST["sIFRposttitle"] == $font) $this->sIFRposttitle = $font;
						if ($_POST["sIFRFooter"] == $font) $this->sIFRFooter = $font;
						$this->settings_updated["warn"]["fonts"] = "Caution: If using sIFR fonts for the post/page title or sidebar widget title the navigation bar drop down menus may fall behind the sIFR fonts.";
					}
				} else {
					$this->sIFR = false;
				}
				/* Check and save the font scaling */
				if ($_POST["title_size"] != "" && in_array($_POST["title_size"],$this->font_size_array))
					$this->title_size = floatval($_POST["title_size"]);
				if ($_POST["tag_size"] != ""  && in_array($_POST["tag_size"],$this->font_size_array))
					$this->tag_size = floatval($_POST["tag_size"]);
				if (($this->title_size != 300) || ($this->tag_size != 125))
					$this->settings_updated["warn"]["font-size"] = "Be cautious when setting font sizes for the header, if you have sIFR turned on you should check that it doesn't break the page with sIFR off.";
				if ($_POST["header_hide"] == "on") {
					$this->header_hide = true;
				} else {
					$this->header_hide = false;
				}
				/* Filter the header image file post var. */
				$theimage = strip_tags(stripslashes($_POST["header_image"])); // Get rid of stuff that someone may try and add to the string.
				if (preg_match("/^https:\/\/.+\.(png|gif|jpeg|jpg|jpe)$/",$theimage)) { // Lets make sure we have the full path and a gif, jpg or png extension.
					$this->header_image = $theimage;
				} else {
					$this->header_image = "";
					if ($theimage != "") $this->settings_updated["errors"]["header"] = "The image URL is invalid. The path should start http:// and end with jpeg, jpg, jpe, gif or png.";
				}

				/*The links space to the right of the header section, lets see what is wanted there. */
				switch ($_POST["header_links"]) { /* Header links, pages or off.*/
					case 0: $this->header_links = 0; break;
					case 1: $this->header_links = 1;
							$this->settings_updated["warn"]["links"] = "The links space in the header when set to links is limited to the 10 most recent links.";
					break;
					case 2: $this->header_links = 2;
							$this->settings_updated["warn"]["links"] = "Notice: If you have no pages set to show in the header space the space will act as though it is off.<br/>Edit a perexistsing Page or create a new one and use the options panel in the sidebar to set a page to the header.";
					break;
					default: $this->header_links = 1; break;
				}

				/* Do we want the vavigation bar on or not and what content do we want in it. */
				if ($_POST["enableNAV"] == "on") 	{$this->enableNAV = true;} 	else {$this->enableNAV = false;}
				if ($_POST["homelink"] == "on") 	{$this->homelink = true;} 	else {$this->homelink = false;}
				switch ($_POST["navcontent"]) {
					default:
					case 1: $this->navcontent = 1; break;
					case 2: $this->navcontent = 2; break;
					case 3: $this->navcontent = 3; break;
					case 4: $this->navcontent = 4; break;
				}

				/* Should we see the sidebar when in a single page or post. */
				if ($_POST["sidebarpostoff"] == "on")
					$this->sidebarpostoff = true;
				else
					$this->sidebarpostoff = false;
				/* Will the sidebar be on the left, the right or off. */
				switch ($_POST["sidebarposition"]) {
					case 0: $this->sidebarposition = 0; break;
					case 1: $this->sidebarposition = 1; break;
					default:
					case 2: $this->sidebarposition = 2; break;
				}

				/* Check the text that'll make the home link. If the field is blank then disable the home link, leave the text unchanged.*/
				if (preg_match("/^[\w\d ]+$/",$_POST["hometext"]))
					$this->hometext = $_POST["hometext"];
				elseif ($_POST["hometext"] == "")
					$this->homelink = false;


				/*Save the settings back to the DB.*/
				$settings = array(
					"theme_style" => $this->theme_style,
					"header_image" => $this->header_image,
					"title_size" => $this->title_size,
					"tag_size" => $this->tag_size,
					"header_links" => $this->header_links,
					"enableNAV" => $this->enableNAV,
					"navcontent" => $this->navcontent,
					"hometext" => $this->hometext,
					"homelink" => $this->homelink,
					"sidebarposition" => $this->sidebarposition,
					"sIFR" => $this->sIFR,
					"sIFRtitle" => $this->sIFRtitle,
					"sIFRtag" => $this->sIFRtag,
					"sIFRwidgettitle" => $this->sIFRwidgettitle,
					"sIFRposttitle" => $this->sIFRposttitle,
					"sIFRFooter" => $this->sIFRFooter,
					"colours" => $this->colours,
					"sidebarpostoff" => $this->sidebarpostoff,
					"header_hide" => $this->header_hide
				);
				/*Update my updated flag */
				$this->settings_updated["update"] = true;

				update_option(get_theme_option("theme_name")."_options",$settings);
			}
		}
		add_theme_page(__("Change theme settings"), __("Customise the theme"), "edit_themes", basename(__FILE__), array($this,"theme_page")); /* Add my HTML to the back end.*/
	}

	/* Some simple additional styles required for the admin page. The only one that is really important is ".disabled". Also add a call to my checkbox toggle routine. */
	function admin_head() {?>
		<script src="<?php bloginfo('template_directory');?>/js/checkbox-toggle.js" type="text/javascript" language="JavaScript"></script>
		<style type="text/css" media="all">
			.disabled {
				color:#ccc;
			}
			label {
				width:320px;
				float:left;
			}
			.subsection p {
				border-bottom:solid 1px #C6D9E9;
				padding:0.5em 2em;
			}
			.subsection {
				border: solid 1px #C6D9E9;
				margin-top:1em;
				padding:1em;
				background-color:#E4F2FD
			}
		</style><?php
	}

	/* Add some javascript calls to the footer. */
	function admin_foot() {?>
		<script type="text/javascript" language="JavaScript">
		<!--
		ICITCheckboxTogggle('ICITThemeOptions','header_hide','HeaderHide',true);
		ICITCheckboxTogggle("ICITThemeOptions","sIFR","sIFRToggle");
		ICITCheckboxTogggle('ICITThemeOptions','enableNAV','enableNAVToggle');
		ICITCheckboxTogggle('ICITThemeOptions','homelink','homeToggle');
		-->
		</script><?php
	}

	/* Add an interface on my custom meta tags to the page/post edit window makes life a little easier for people. */
	function single_page_options() {
		global $post;
		/* Read in the current settings so I know which boxes need to be ticked in the interface. */
		if ($post->ID) {
			if (get_post_meta($post->ID,"hide-sidebar",true) == "1"){ $sidebarchecked = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"hide-title",true) == "1"){ $titlechecked = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"nav-exclude",true) == "1"){ $navexchecked = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"header",true) == "1"){ $ICITHeader = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"footer",true) == "1"){ $ICITFooter = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"exclude",true) == "1"){ $ICITExclude = " checked=\"checked\"";}
		}

		if (!function_exists("add_meta_box" )) { // Lets do a simple check for wp2.5
		// If we're no in wp2.5 then lets go with the old method.?>
		<fieldset class="dbx-box" id="single_page_options">
			<h3 class="dbx-handle dbx-handle-cursor" title="click-down and drag to move this box">Options for this page<a href="javascript:void(null)"></a></h3>
			<div class="dbx-content">
				<label for="ICITHideSidebar" class="selectit"><input id="ICITHideSidebar"<?php echo $sidebarchecked;?> name="ICITHideSidebar" type="checkbox" value="on" />&nbsp;Hide the sidebar.</label>
				<label for="ICITHideTitle" class="selectit"><input id="ICITHideTitle"<?php echo $titlechecked;?> name="ICITHideTitle" type="checkbox" value="on" />&nbsp;Hide the title.</label>
				<hr/>
				<label for="ICITHeader" class="selectit"><input id="ICITHeader"<?php echo $ICITHeader;?> name="ICITHeader" type="checkbox" value="on" />&nbsp;Link in the header</label>
				<label for="ICITFooter" class="selectit"><input id="ICITFooter"<?php echo $ICITFooter;?> name="ICITFooter" type="checkbox" value="on" />&nbsp;Link in the footer</label>
				<hr/>
				<label for="ICITHideNav" class="selectit"><input id="ICITHideNav"<?php echo $navexchecked;?> name="ICITHideNav" type="checkbox" value="on" />&nbsp;Hide from Navigation bar</label>
				<label for="ICITExclude" class="selectit"><input id="ICITExclude"<?php echo $ICITExclude;?> name="ICITExclude" type="checkbox" value="on" />&nbsp;Hide from widgets</label>
			</div>
		</fieldset><?php
		} else { // This is the layout as it shows in wp2.5. ?>
		<label for="ICITHideSidebar" class="selectit"><input id="ICITHideSidebar"<?php echo $sidebarchecked;?> name="ICITHideSidebar" type="checkbox" value="on" />&nbsp;Hide the sidebar.</label><br/>
		<label for="ICITHideTitle" class="selectit"><input id="ICITHideTitle"<?php echo $titlechecked;?> name="ICITHideTitle" type="checkbox" value="on" />&nbsp;Hide the title.</label><br/>
		<label for="ICITHeader" class="selectit"><input id="ICITHeader"<?php echo $ICITHeader;?> name="ICITHeader" type="checkbox" value="on" />&nbsp;Link in the header</label><br/>
		<label for="ICITFooter" class="selectit"><input id="ICITFooter"<?php echo $ICITFooter;?> name="ICITFooter" type="checkbox" value="on" />&nbsp;Link in the footer</label><br/>
		<label for="ICITHideNav" class="selectit"><input id="ICITHideNav"<?php echo $navexchecked;?> name="ICITHideNav" type="checkbox" value="on" />&nbsp;Hide from Navigation bar</label><br/>
		<label for="ICITExclude" class="selectit"><input id="ICITExclude"<?php echo $ICITExclude;?> name="ICITExclude" type="checkbox" value="on" />&nbsp;Hide from widgets</label><?php
		}
	}
	/* Far few options for posts than pages but essentially the same as the above. */
	function single_post_options() {
		global $post;
		if ($post->ID) {
			if (get_post_meta($post->ID,"hide-sidebar",true) == "1"){ $sidebarchecked = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"hide-date",true) == "1"){ $HideDate = " checked=\"checked\"";}
			if (get_post_meta($post->ID,"hide-author",true) == "1"){ $HideAuthor = " checked=\"checked\"";}
		}
		if (!function_exists("add_meta_box" )) { // Lets do a simple check for wp2.5
		// If we're no in wp2.5 then lets go with the old method.?>
		<fieldset class="dbx-box" id="single_page_options">
			<h3 class="dbx-handle dbx-handle-cursor" title="click-down and drag to move this box">Options for this page<a href="javascript:void(null)"></a></h3>
			<div class="dbx-content">
				<label for="ICITHideSidebar" class="selectit"><input id="ICITHideSidebar"<?php echo $sidebarchecked;?> name="ICITHideSidebar" type="checkbox" value="on" />&nbsp;Hide the sidebar.</label>
				<label for="ICITHideDate" class="selectit"><input id="ICITHideDate"<?php echo $HideDate;?> name="ICITHideDate" type="checkbox" value="on" />&nbsp;Hide the Date of this post.</label>
				<label for="ICITHideAuthor" class="selectit"><input id="ICITHideAuthor"<?php echo $HideAuthor;?> name="ICITHideAuthor" type="checkbox" value="on" />&nbsp;Hide the Author of this post.</label>
			</div>
		</fieldset><?php
		} else {?>
			<label for="ICITHideSidebar" class="selectit"><input id="ICITHideSidebar"<?php echo $sidebarchecked;?> name="ICITHideSidebar" type="checkbox" value="on" />&nbsp;Hide the sidebar.</label><br/>
			<label for="ICITHideDate" class="selectit"><input id="ICITHideDate"<?php echo $HideDate;?> name="ICITHideDate" type="checkbox" value="on" />&nbsp;Hide the Date of this post.</label><br/>
			<label for="ICITHideAuthor" class="selectit"><input id="ICITHideAuthor"<?php echo $HideAuthor;?> name="ICITHideAuthor" type="checkbox" value="on" />&nbsp;Hide the Author of this post.</label><br/><?php
		}
	}

	/* The backend HTML */
	function theme_page () {
		if (!empty($this->settings_updated["errors"])) {?>
		<div id="ErrorMessages" class="error fade">
			<p>There seems to have been a problem.</p>
			<ul><?php
			foreach($this->settings_updated["errors"] as $error) {
				echo "<li>$error</li>";
			}?>
			</ul>
		</div><?php
		}
		if ($this->settings_updated["update"]) {?>
		<div id="Messages" class="updated fade">
			<p>Settings Updated</p><?php
			if(!empty($this->settings_updated["warn"])) {?>
				<ul><?php
				foreach($this->settings_updated["warn"] as $warn) {
					echo "<li>$warn</li>";
				}?>
				</ul><?php
			}?>
		</div><?php
		}?>
		<div class="wrap">
			<h2>Theme options</h2>
			<form method="post" action="" id="ICITThemeOptions">
				<div class="subsection">
					<h3>Global options.</h3>
					<p>
					<?php $schemes = $this->find_schemes(); ?>
					<label for="theme_style">Available Styles</label>
					<select name="theme_style" id="theme_style"><?php
						foreach($schemes as $scheme) {?>
							<option value="<?php echo $scheme["folder"];?>" <?php echo (get_theme_option("theme_style") == $scheme["folder"] ? "selected=\"selected\"" : ""); ?>><?php echo $scheme["title"];?></option><?php
						}?>
					</select>
					</p>
					<p><label for="sIFR">Enable sIFR fonts</label>
					<input type="checkbox" id="sIFR" name="sIFR" onclick="ICITCheckboxTogggle('ICITThemeOptions','sIFR','sIFRToggle')" <?php echo (get_theme_option("sIFR") ? "checked=\"checked\"" : ""); ?>/></p>
				</div>
				<p class="submit"><input type="submit" value="Submit"/></p>
				<div class="subsection">
					<h3>Header.</h3>
					<p>
						<label for="header_image">Header image url (full path)</label>
						<input type="text" name="header_image" id="header_image" value="<?php echo get_theme_option("header_image");?>" /> &nbsp;eg:&nbsp;http://www.example.com/example.png
					</p>
					<p>
						<label for="header_hide">Hide the title text and tag line</label>
						<input type="checkbox" name="header_hide" id="header_hide" onclick="ICITCheckboxTogggle('ICITThemeOptions','header_hide','HeaderHide',true)" <?php echo (get_theme_option("header_hide") ? "checked=\"checked\"" : ""); ?>/>
					</p>
					<p class="sIFRToggle HeaderHide"><label for="title-sIFR">Title font</label>
						<select name="sIFRtitle" id="title-sIFR" class="sIFRToggle HeaderHide">
							<option value="" >Disabled</option><?php
							foreach($this->font_list() as $font) {
								echo "<option".($font == get_theme_option("sIFRtitle") ? " selected=\"selected\"" : "")." value=\"$font\">".str_replace(".swf","",$font)."</option>";
							}?>
						</select>
					</p>
					<p class="sIFRToggle HeaderHide"><label for="sIFR-tag">Tagline font</label>
						<select name="sIFRtag" id="sIFR-tag" class="sIFRToggle HeaderHide">
							<option value="">Disabled</option><?php
							foreach($this->font_list() as $font) {
								echo "<option".($font == get_theme_option("sIFRtag") ? " selected=\"selected\"" : "")." value=\"$font\">".str_replace(".swf","",$font)."</option>";
							}?>
						</select>
					</p>
					<p class="HeaderHide"><label for="title_size">Title font size</label>
						<select name="title_size" id="title_size" class="HeaderHide"><?php
						foreach(array_keys($this->font_size_array) as $key) {?>
							<option <?php echo ($this->font_size_array[$key] == get_theme_option("title_size") ? "selected=\"selected\"" : ""); ?> value="<?php echo $this->font_size_array[$key];?>"><?php echo $key; ?>pt</option><?php
						}?>
						</select>
					<p class="HeaderHide"><label for="tag_size">Tagline font size</label>
						<select name="tag_size" id="tag_size" class="HeaderHide"><?php
							foreach(array_keys($this->font_size_array) as $key) {?>
								<option <?php echo ($this->font_size_array[$key] == get_theme_option("tag_size") ? "selected=\"selected\"" : ""); ?> value="<?php echo $this->font_size_array[$key];?>"><?php echo $key; ?>pt</option><?php
							}?>
						</select>

					</p>
					<p><label for="header_links">Header links space.</label>
						<select name="header_links" id="header_links">
							<option value="0"<?php echo (get_theme_option("header_links") == 0 ? " selected=\"selected\"" : ""); ?>>Off</option>
							<option value="1"<?php echo (get_theme_option("header_links") == 1 ? " selected=\"selected\"" : ""); ?>>Links</option>
							<option value="2"<?php echo (get_theme_option("header_links") == 2 ? " selected=\"selected\"" : ""); ?>>Pages</option>
						</select>
					</p>
				</div>
				<p class="submit"><input type="submit" value="Submit"/></p>
				<div class="subsection">
					<h3>Navigation bar.</h3>
					<p>
						<label for="enableNAV">Enable/Disable the NAV bar.</label>
						<input type="checkbox" name="enableNAV" id="enableNAV" onclick="ICITCheckboxTogggle('ICITThemeOptions','enableNAV','enableNAVToggle')" <?php echo (get_theme_option("enableNAV") ? "checked=\"checked\"" : ""); ?>/>
					</p>
					<p class="enableNAVToggle">
						<label for="homelink">Show home link on NAV bar.</label>
						<input type="checkbox" name="homelink" id="homelink" onclick="ICITCheckboxTogggle('ICITThemeOptions','homelink','homeToggle')" class="enableNAVToggle" <?php echo (get_theme_option("homelink") ? "checked=\"checked\"" : ""); ?>/>
					</p>
					<p class="homeToggle enableNAVToggle">
						<label for="hometext">The text that should be shown in the home link</label>
						<input class="homeToggle enableNAVToggle" type="text" name="hometext" id="hometext" value="<?php echo get_theme_option("hometext");?>"/>
					</p>
					<p class="enableNAVToggle">
						<label for="navcontent">What should be shown on the NAV bar?</label>
						<select class="enableNAVToggle" name="navcontent" id="navcontent">
							<option value="1"<?php echo (get_theme_option("navcontent") == 1 ? " selected=\"selected\"" : ""); ?>>Pages</option>
							<option value="2"<?php echo (get_theme_option("navcontent") == 2 ? " selected=\"selected\"" : ""); ?>>Categories</option>
							<option value="3"<?php echo (get_theme_option("navcontent") == 3 ? " selected=\"selected\"" : ""); ?>>Pages/Categories</option>
							<option value="4"<?php echo (get_theme_option("navcontent") == 4 ? " selected=\"selected\"" : ""); ?>>Categories/Pages</option>
						</select>
					</p>
				</div>
				<p class="submit"><input type="submit" value="Submit"/></p>
				<div class="subsection">
					<h3>Content area.</h3>
					<p class="sIFRToggle">
						<label for="sIFRposttitle">Post/Page title font</label>
						<select name="sIFRposttitle" id="sIFRposttitle" class="sIFRToggle">
							<option value="">Disabled</option><?php
							foreach($this->font_list() as $font) {
								echo "<option".($font == get_theme_option("sIFRposttitle") ? " selected=\"selected\"" : "")." value=\"$font\">".str_replace(".swf","",$font)."</option>";
							}?>
						</select>
					</p>
					<p class="sIFRToggle">
						<label for="sIFRwidgettitle">Widget title font</label>
						<select name="sIFRwidgettitle" id="sIFRwidgettitle" class="sIFRToggle">
							<option value="">Disabled</option><?php
							foreach($this->font_list() as $font) {
								echo "<option".($font == get_theme_option("sIFRwidgettitle") ? " selected=\"selected\"" : "")." value=\"$font\">".str_replace(".swf","",$font)."</option>";
							}?>
						</select>
					</p>
					<p><label for="sidebarpostoff">Sidebar off in Pages and Posts</label>
					<input type="checkbox" id="sidebarpostoff" name="sidebarpostoff" <?php echo (get_theme_option("sidebarpostoff") ? "checked=\"checked\"" : ""); ?>/></p>
					<p>
						<label for="sidebarposition">Where do you want the sidebar?</label>
						<select name="sidebarposition" id="sidebarposition">
							<option value="0"<?php echo (get_theme_option("sidebarposition") == 0 ? " selected=\"selected\"" : ""); ?>>Off</option>
							<option value="1"<?php echo (get_theme_option("sidebarposition") == 1 ? " selected=\"selected\"" : ""); ?>>Left</option>
							<option value="2"<?php echo (get_theme_option("sidebarposition") == 2 ? " selected=\"selected\"" : ""); ?>>Right</option>
						</select>
					</p>
				</div>
				<p class="submit"><input type="submit" value="Submit"/></p>
				<div class="subsection">
					<h3>Footer widgets</h3>
						<p class="sIFRToggle"><label for="sIFRFooter">Footer widget header font</label>
						<select name="sIFRFooter" id="sIFRFooter" class="sIFRToggle">
							<option value="">Disabled</option><?php
							foreach($this->font_list() as $font) {
								echo "<option".($font == get_theme_option("sIFRFooter") ? " selected=\"selected\"" : "")." value=\"$font\">".str_replace(".swf","",$font)."</option>";
							}?>
						</select>
					</p>

				</div>
				<p class="submit"><input type="submit" value="Submit"/></p>
			</form>
		</div><?php
	}
	/* Sidebar options from individual posts are processed here. */
	function process_single_page_options($ID) {
		if ($_POST["ICITHideSidebar"] == "on") { if (!update_post_meta($ID,"hide-sidebar","1")) add_post_meta($ID,"hide-sidebar","1"); } else delete_post_meta($ID,"hide-sidebar");
		if ($_POST["ICITHideTitle"] == "on") { if (!update_post_meta($ID,"hide-title","1")) add_post_meta($ID,"hide-title","1"); } else delete_post_meta($ID,"hide-title");
		if ($_POST["ICITHideNav"] == "on") { if (!update_post_meta($ID,"nav-exclude","1")) add_post_meta($ID,"nav-exclude","1"); } else delete_post_meta($ID,"nav-exclude");
		if ($_POST["ICITHeader"] == "on") { if (!update_post_meta($ID,"header","1")) add_post_meta($ID,"header","1"); } else delete_post_meta($ID,"header");
		if ($_POST["ICITFooter"] == "on") { if (!update_post_meta($ID,"footer","1")) add_post_meta($ID,"footer","1"); } else delete_post_meta($ID,"footer");
		if ($_POST["ICITExclude"] == "on") { if (!update_post_meta($ID,"exclude","1")) add_post_meta($ID,"exclude","1"); } else delete_post_meta($ID,"exclude");
		if ($_POST["ICITHideDate"] == "on") { if (!update_post_meta($ID,"hide-date","1")) add_post_meta($ID,"hide-date","1"); } else delete_post_meta($ID,"hide-date");
		if ($_POST["ICITHideAuthor"] == "on") { if (!update_post_meta($ID,"hide-author","1")) add_post_meta($ID,"hide-author","1"); } else delete_post_meta($ID,"hide-author");
	}

	// This is in no way comprehensive but does help to ident IE for style sheet hacking.
	function get_agent_body_class($class = array()){
		$useragent = getenv('HTTP_USER_AGENT');

		if(preg_match('!gecko/\d+!i',$useragent))
			$class[] = 'gecko';
		elseif(preg_match('!(applewebkit|konqueror)/[\d\.]+!i',$useragent))
			$class[] = 'webkit';
		elseif (preg_match('!msie\s+(\d+\.\d+)!i',$useragent,$match)) {
			$class[] = 'ie';
			$version = floatval($match[1]);

			/* Add an identifier for IE versions. */
			if ($version >= 9)						array_push($class,'ienew');
			if ($version >= 8 &&	$version < 9)	array_push($class,'ie8');
			if ($version >= 7 &&	$version < 8)	array_push($class,'ie7');
			if ($version >= 6 &&	$version < 7)	array_push($class,'ie6');
			if ($version >= 5.5 &&	$version < 6)	array_push($class,'ie55');
			if ($version >= 5 &&	$version < 5.5)	array_push($class,'ie5');
			if ($version < 5) 						array_push($class,'ieold');
		}

		return $class;
	}
}

function sidebar_position(){
	/*  Find out where the sidebar should be. Returns sidebar-off, sidebar-left or sidebar-right which just happens to coincide with the the class I use. :D */
	switch (get_theme_option("sidebarposition")){
		case 0: $sidebar_position = "sidebar-off"; break;
		case 1:
			if (get_theme_option("sidebarpostoff") && (is_page() || is_single()))
				$sidebar_position = "sidebar-off";
			else
				$sidebar_position = "sidebar-left";
			break;
		default:
		case 2:
			if (get_theme_option("sidebarpostoff") && (is_page() || is_single()))
				$sidebar_position = "sidebar-off";
			else
				$sidebar_position = "sidebar-right";
			break;
	}
	if (is_attachment()) $sidebar_position = "sidebar-off"; // I never want to see the sidebar in an attachment.
	/* The following  will read the post_meta and check that sidebar is not set to off. If it is then we'll hide it. If it is set to off globally this'll make no difference.*/
	if (is_page() || is_single()) {
		global $post;
		$current_page = $post->ID;
		if ($current_page != "") {
			$sidebar_override = get_post_meta($current_page,"hide-sidebar",true);
			if ($sidebar_override == 1)
				$sidebar_position = "sidebar-off";
		}
	}
	return $sidebar_position;
}

/*
 I call this from within the body tag to add a couple of classes to it to help
 me with the CSS in certain browsers, namely IE.
 Seems this function has been added to wp2.8. I've split out the user agent
 sniffing into another function and added that to the new filter.
*/
if (!function_exists('body_class')) {
	function body_class() {
		if (is_page())			$class[] = 'page';
		if (is_home())			$class[] = 'home';
		if (is_single())		$class[] = 'single';
		if (is_category())		$class[] = 'category';
		if (is_author())		$class[] = 'author';
		if (is_search())		$class[] = 'search';
		if (is_date())			$class[] = 'date';
		if (is_year())			$class[] = 'year';
		if (is_month())			$class[] = 'month';
		if (is_day())			$class[] = 'day';
		if (is_time())			$class[] = 'time';
		if (is_404())			$class[] = 'error404';
		if (is_paged())			$class[] = 'paged';
		if (is_attachment())	$class[] = 'attachment';
		if (is_archive() && !is_category()) $class[] = 'archive';

		$class = apply_filters('body_class',$class);

		$class = implode(' ',$class);
		if (!empty($class)) {
			echo " class=\"$class\"";
		}
	}
}

if(!function_exists('get_theme_option')) {
	function get_theme_option ($option='')	{
		global $spectaculaTheme;
		return $spectaculaTheme->$option;
	}
}
?>
