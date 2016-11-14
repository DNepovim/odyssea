<?PHP
// don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');

	//Function to call for seidbar widget
	function MiniMetaWidgetSidebar($name) {
		foreach (get_option('minimeta_widget_options') as $option => $optionvalue) {
			if (strtolower($optionvalue['optionname'])==strtolower($name)) $mmconfigid=$option;
		}
		minimeta_widget_display($mmconfigid,array('type'=>'PHP'));
	}	

	//copy action login_head to wp-head if login form enabeld for plugin hooks
	function minimeta_head_login() {   
		if (!has_action('login_head')) 
			return;
		$options = get_option('minimeta_widget_options');
		//find out is a ligon form in any MiniMeta Widegt activatet
		if (is_array($options)) {
			foreach ( $options as $number => $value ) {
				for ($i=1;$i<=sizeof($value['out']);$i++) {
					if($value['out'][$i]['part']=='loginform') 
						do_action('login_head');
						break 2;
				}
			}
		}    
	}

	//function to generate admin links    
	function minimeta_generate_adminlinks() { 
		global $menu,$submenu,$parent_file;
		//let orginal Variables unchanged
		$tempmenu=$menu;
		$tempsubmenu=$submenu;
		//scan the menu
		foreach ( $tempmenu as $key => $item ) {
			if ($item[2]=="edit-comments.php") //Overwrite for Comments menu without number since wp 2.5
				$item[0] = sprintf( __('Comments %s'), "" );
			if ($item[2]=="plugins.php") //Overwrite for Plugins menu without number since wp 2.6
				$item[0] = sprintf( __('Plugins %s'), "" );
		  if (!empty($item[0])) { //filter out empty menu entrys since WP 2.7
			$adminlinks[$key]['menu']=strip_tags($item[0]); //write Menues
			if (is_array($tempsubmenu[$item[2]])) { //look if submenu existing to crate submenu on men if they downt exists
				$menulink=false; unset($lowestkey);
				foreach ($tempsubmenu[$item[2]] as $keysub => $itemsub) { // Find submenu wisout an existing menu link
					if(!isset($lowestkey) or $lowestkey>$keysub) $lowestkey=$keysub;
					if ($itemsub[2]==$item[2])
						$menulink=true;
				}
				if (!$menulink) {
					$lowestkey--;
					$tempsubmenu[$item[2]][$lowestkey] = array($item[0], $item[1], $item[2]); //create submenu
					ksort($tempsubmenu[$item[2]]);
				}
			} else {
				$tempsubmenu[$item[2]][0] = array($item[0], $item[1], $item[2]); //create submenu
			}
			$menu_file = $item[2];
			if ( false !== $pos = strpos($menu_file, '?') )
				$menu_file = substr($menu_file, 0, $pos);
			foreach ($tempsubmenu[$item[2]] as $keysub => $itemsub) { //Crate submenus and links
				if ($itemsub[2]=="update-core.php") //Overwrite for Update menu without number since wp 3.0
					$itemsub[0] = sprintf( __('Updates %s'), "" );
				$adminlinks[$key][$keysub][0]=strip_tags($itemsub[0]);
				$adminlinks[$key][$keysub][1]=$itemsub[1];
				$menu_hook = get_plugin_page_hook($itemsub[2], $item[2]);
				$sub_file = $itemsub[2];
				if ( false !== $pos = strpos($sub_file, '?') )
					$sub_file = substr($sub_file, 0, $pos);				
				if ( ( ('index.php' != $itemsub[2]) && file_exists(WP_PLUGIN_DIR . "/$sub_file") ) || ! empty($menu_hook) ) {
					$parent_exists = (file_exists(WP_PLUGIN_DIR . "/$menu_file") && !is_dir(WP_PLUGIN_DIR . "/{$item[2]}") ) || file_exists($menu_file);
					if ( $parent_exists )
						$adminlinks[$key][$keysub][2]= $item[2]."?page=".$itemsub[2];
					elseif ( 'admin.php' == $pagenow || !$parent_exists )
						$adminlinks[$key][$keysub][2]= "admin.php?page=".$itemsub[2];
					else
						$adminlinks[$key][$keysub][2]= $item[2]."?page=".$itemsub[2];
				} else {
					$adminlinks[$key][$keysub][2]= $itemsub[2];
				}
			}
		  }
		}
		update_option('minimeta_adminlinks', $adminlinks);
 	}

	//Thems Option  menu entry
	function minimeta_menu_entry() {
		if (function_exists('add_theme_page')) {
			$hook = add_theme_page(__('MiniMeta Widget','MiniMetaWidget'), __('MiniMeta Widget','MiniMetaWidget'), 'switch_themes', 'minimeta-widget','minimeta_options_form') ;
		}
		//register_column_headers($hook,array('cb'=>'<input type="checkbox" />','id'=>__('ID','MiniMetaWidget'),'name'=>__('Konfig','MiniMetaWidget')));
		register_column_headers($hook,array('id'=>__('ID','MiniMetaWidget'),'name'=>__('Konfig','MiniMetaWidget')));
		add_action('load-'.$hook, 'minimeta_options_load');
		if (current_user_can(10))
			add_action('load-'.$hook,'minimeta_generate_adminlinks',1); //Generate Adminlinks
		add_contextual_help($hook,minimeta_show_help());
	}	
	
	// Help too display
	function minimeta_show_help() {
		$help .= '<div class="metabox-prefs">';
		$help .= '<a href="http://wordpress.org/tags/minimeta-widget/" target="_blank">'.__('Support').'</a>';
		$help .= ' | <a href="http://wordpress.org/extend/plugins/minimeta-widget/faq/" target="_blank">' . __('FAQ') . '</a>';
		$help .= ' | <a href="http://danielhuesken.de/portfolio/minimeta" target="_blank">' . __('Plugin Homepage', 'filebrowser') . '</a>';
		$help .= ' | <a href="http://wordpress.org/extend/plugins/minimeta-widget" target="_blank">' . __('Plugin Home on WordPress.org', 'filebrowser') . '</a>';
		$help .= ' | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=daniel%40huesken-net%2ede&amp;item_name=Daniel%20Huesken%20Plugin%20Donation&amp;item_number=MiniMeta%20Widget&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;lc=DE&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8" target="_blank">' . __('Donate') . '</a>';
		$help .= " | <script type=\"text/javascript\">
			var flattr_btn = 'compact';
			var flattr_url = 'http://danielhuesken.de/portfolio/minimeta/';
			</script><script src=\"http://api.flattr.com/button/load.js\" type=\"text/javascript\"></script>";
		$help .= "</div>\n";	
		$help .= '<div class="metabox-prefs">';
		$help .= __('Version:', 'backwpup').' '.WP_MINMETA_VERSION.' | ';
		$help .= __('Author:', 'backwpup').' <a href="http://danielhuesken.de" target="_blank">Daniel H&uuml;sken</a>';
		$help .= "</div>\n";
		return $help;
	}
	
	//Options Page
	function minimeta_options_form() {
		global $minimeta_options_text,$page_hook;
		require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/options-form.php');
	}
	
	//Options Page
	function minimeta_options_load() {
		global $minimeta_options_text;
		//Css for Admin Section
		wp_enqueue_style('MiniMeta',plugins_url('css/minimeta-admin.css',__FILE__),'',WP_MINMETA_VERSION,'screen');
		wp_enqueue_script('MiniMetaOptions',plugins_url('js/minimeta-options.js',__FILE__),array('jquery','jquery-ui-core','jquery-ui-sortable'),WP_MINMETA_VERSION,true);
		wp_localize_script('MiniMetaOptions','MiniMetaL10n',array('edit'=>__('Edit')));
		//For save Options
		require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/options-save.php');
	}
	
	
    //delete Otions
	function minimeta_uninstall() {
		$option_settings=array('minimeta_widget_wp','minimeta_widget_options', 'minimeta_adminlinks');
		foreach($option_settings as $setting) {
			delete_option($setting);
		}
	}
	
	//Set start Options
	function minimeta_install() {
		add_option('minimeta_widget_options');
		add_option('minimeta_adminlinks');
		minimeta_generate_adminlinks();
	}
	
	//add edit setting to plugins page
	function minimeta_plugins_options_link($links) {
		$settings_link='<a href="admin.php?page=minimeta-widget" title="' . __('Go to Settings Page','MiniMetaWidget') . '" class="edit">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); 
		return $links;
	}

	//add links on plugins page
	function minimeta_plugin_links($links, $file) {
		if ($file == WP_MINMETA_PLUGIN_DIR.'/minimeta-widget.php') {
			$links[] = '<a href="http://wordpress.org/extend/plugins/minimeta-widget/faq/" target="_blank">' . __('FAQ') . '</a>';
			$links[] = '<a href="http://wordpress.org/tags/minimeta-widget/" target="_blank">' . __('Support') . '</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=daniel%40huesken-net%2ede&amp;item_name=Daniel%20Huesken%20Plugin%20Donation&amp;item_number=MiniMeta%20Widget&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;lc=DE&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8" target="_blank">' . __('Donate') . '</a>';
		}
		return $links;
	}
	
	// add all action and so on only if plugin loaded.
	function minimeta_init() {	
		if (has_action('login_head') and !is_user_logged_in())
			add_action('wp_head', 'minimeta_head_login',1);

		add_action('admin_menu', 'minimeta_menu_entry');
		
		if (current_user_can('switch_themes')) 
			add_filter('plugin_action_links_'.WP_MINMETA_PLUGIN_DIR.'/minimeta-widget.php', 'minimeta_plugins_options_link');

		if (current_user_can('install_plugins')) 		
			add_filter('plugin_row_meta', 'minimeta_plugin_links',10,2);
			
		//Generate Adminlinks on plugin page
		if (current_user_can(10)) 
			add_action('load-plugins.php','minimeta_generate_adminlinks',1); //Generate Adminlinkson plugins page
			
		//Support for Wordpress Widgets
		require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/widgets-wp.php');	
		add_action('widgets_init', array('WP_Widget_MiniMeta', 'register'));

		//Widget Displaying
		require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/widget-display.php');
		//Widget Parts
		require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/widget-parts.php');
	} 	

?>