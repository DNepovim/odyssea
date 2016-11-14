<?php
/*
Plugin Name: WassUp Real Time Analytics
Plugin URI: http://www.wpwp.org
Description: Analyze your visitors traffic with accurate, real-time stats and lots of chronological information displayed in a clear, understandable manner. Comes with 2 aside widgets that show current visitors online and the latest top stats/trending data from your site, plus a dashboard widget with visitor summary and chart. For Wordpress 2.2 or higher.
Version: 1.9
Author: Michele Marcucci, Helene Duncker
Author URI: http://www.michelem.org/
Text Domain: wassup

Copyright (c) 2007-2015 Michele Marcucci
Released under the GNU General Public License GPLv2 or later 
http://www.gnu.org/licenses/gpl-2.0.html

Disclaimer:
  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of 
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the GNU General Public License for more details.
*/
//# Stop any attempt to call "wassup.php" directly
$wassupfile=preg_replace('/\\\\/','/',__FILE__); //for windows
if(!defined('ABSPATH')|| empty($GLOBALS['wp_version'])|| preg_match('#'.preg_quote(basename($wassupfile)).'#',$_SERVER['PHP_SELF'])|| !empty($_SERVER['SCRIPT_FILENAME'])&& realpath($wassupfile)===realpath($_SERVER['SCRIPT_FILENAME'])){
	if(!headers_sent()){header('Location: /?p=404page&err=wassup403');exit;}
	elseif(function_exists('wp_die')){wp_die('<strong>'.__("Sorry. Unable to display requested page.","wassup").'</strong>');}
	else{die('<strong>Sorry. Unable to display requested page.</strong>');}
	exit;
}
//### I: Setup and startup functions
/**
 * Set up WassUp environment in Wordpress
 * @since v1.9
 */
function wassup_init($init_settings=true){
	global $wp_version,$wassup_options,$wdebug_mode;
	//define wassup globals & constants
	define('WASSUPVERSION','1.9');
	define('WASSUPDIR', dirname(preg_replace('/\\\\/','/',__FILE__))); 
	//turn on debugging (global)...Use cautiously! Will display errors from all plugins, not just WassUp
	$wdebug_mode=false;
	if(defined('WP_DEBUG') && WP_DEBUG===true) $wdebug_mode=true;
	//load language translation
	if(empty($current_locale) || strlen($current_locale)>5) $current_locale=get_locale();
	if(!empty($current_locale)){
		$moFile=WASSUPDIR."/language/".$current_locale.".mo";
		if(@is_readable($moFile)) load_textdomain('wassup',$moFile);
	}
	//load required modules
	//load backward compatibility modules for Wordpress <3.1 and PHP <5.2 before using 'plugins_url' function
	if(version_compare($wp_version,'3.1','<') || defined('PHP_VERSION') && version_compare(PHP_VERSION,'5.2','<')){
		include_once(WASSUPDIR.'/lib/compat-lib/compat_functions.php');
	}
	if(defined('PHP_VERSION') && version_compare(PHP_VERSION,'5.2','<')){
		require_once(WASSUPDIR.'/lib/compat-lib/wassup.class.php');
	}else{
		require_once(WASSUPDIR.'/lib/wassup.class.php');
	}
	define('WASSUPURL',plugins_url(basename(WASSUPDIR)));
	//additional modules are loaded as needed
	//require_once(WASSUPDIR.'/lib/main.php');
	//include_once(WASSUPDIR.'/lib/uadetector.class.php');
	//
	//initialize wassup settings for new multisite subsites
	if($init_settings){
		$wassup_options=new wassupOptions;
		if(empty($wassup_options->wassup_version) || $wassup_options->wassup_version != WASSUPVERSION){
			$wassup_options=new wassupOptions(true);
			//save settings for multisite subsites only
			if(is_multisite() && !is_network_admin() && !is_main_site() && $wassup_options->network_activated_plugin()){
				$wassup_options->wassup_version=WASSUPVERSION;
				$wassup_options->saveSettings();
			}
		}
	}
} //end init

/**
 * Install or upgrade Wassup plugin
 *
 *  -check Wordpress version and configuration for compatibility
 *  -set initial plugin settings 
 *  -check for multisite and set initial wassup network settings
 *  -create/upgrade Wassup tables.
 *  -save wassup settings and wassup network settings.
 * @todo - enable network activation for subdomain networks
 * @param boolean (for multisite network activation)
 * @return void
 */
function wassup_install($network_wide=false) {
	global $wpdb,$wp_version,$wassup_options;
	$wfile=preg_replace('/\\\\/','/',__FILE__);
	if(!defined("WASSUPVERSION")) wassup_init(false);
	//WassUp works only in WP2.2 or higher
	if(version_compare($wp_version,'2.2','<')){
		echo __("Sorry, WassUp requires WordPress 2.2 or higher to work","wassup");
		exit(1);
	}
	//additional install/upgrade functions in "upgrade.php" module
	if (file_exists(WASSUPDIR.'/lib/upgrade.php')) {
		require_once(WASSUPDIR.'/lib/upgrade.php');
	} else {
		echo sprintf(__("File %s does not exist!","wassup"),WASSUPDIR.'/lib/upgrade.php');
		exit(1);
	}
	//initialize/update wassup_settings in wp_options
	$wassup_options = new wassupOptions(true);
	$wassup_network_settings=array();
	if(is_multisite()){
		//WassUp works only in WP3.1 or higher for multisite 
		if(version_compare($wp_version,'3.1','<')){
			echo __("Sorry, WassUp requires WordPress 3.1 or higher to work in multisite setups","wassup");
			exit(1);
		}
		//New in v1.9: network-wide settings for multisite
		if(is_network_admin()){
			$network_wide=true;
			//For multisite..no network activation in subdomain networks, subdomain sites must activate Wassup separately @TODO
			if(is_subdomain_install()){
				echo __("Sorry! Network activation is DISABLED in subdomain networks. WassUp plugin must be activated on each subdomain site separately.","wassup")." ".__("Activate Wassup on your main site/parent domain to set default options for network.","wassup");
				exit(1);
			}
			$wassup_network_settings=wassup_network_install($network_wide);
		}else{
			$network_wide=false;
			$wassup_network_settings=wassup_network_install($network_wide);
			$subsite_settings=wassup_subsite_install($wassup_network_settings);
			if(!empty($subsite_settings)) $wassup_options->loadSettings($subsite_settings);
		}
	}
	$active_status=1;
	//#set table names
	//v1.9 bugfix: change table name if wordpress prefix changes
	if(empty($wassup_options->wassup_table) || !wassupDb::table_exists($wassup_options->wassup_table)){
		if($network_wide && !empty($wassup_network_settings['wassup_table'])) $wassup_table= $wassup_network_settings['wassup_table'];
		else $wassup_table=$wpdb->prefix . "wassup";
	}else{
		$wassup_table=$wassup_options->wassup_table;
	}
	$wassup_meta_table=$wassup_table."_meta";
	$wassup_options->wassup_table=$wassup_table;
	//v1.9 bugfix: settings are saved after install/upgrade only to prevent settings from being left behind if new install fails
	if(!empty($wassup_options->wassup_version)){
		$active_status=$wassup_options->wassup_active;
		//in case Wassup wp-cron jobs were not canceled
		if(version_compare($wp_version,'2.8','>=')){
			wp_clear_scheduled_hook('wassup_scheduled_cleanup');
			wp_clear_scheduled_hook('wassup_scheduled_purge');
		}
	}
	//Do the table upgrade
	$admin_message="";
	$wsuccess=false;
	//v1.9 bugfix: upgrade table only for new version of WassUp or after reset-to-default
	if(empty($wassup_options->wassup_version) || WASSUPVERSION != $wassup_options->wassup_version || empty($wassup_options->wassup_upgraded) || !wassup_upgradeCheck()){
		//v1.9 bugfix: increase script timeout to 16 minutes to prevent activation failure due to script timeout (browser timeout can still occur)
		if(!ini_get('safe_mode')){
			$stimeout=ini_get("max_execution_time");
			if(!is_numeric($stimeout)||(int)$stimeout<990)
				set_time_limit(990);
		}
		//do the table upgrade
		$wassup_options->wassup_upgraded=0;
		$wsuccess=wassup_tableInstaller($wassup_table);
		if($wsuccess){
			$admin_message=__("Database created/upgraded successfully","wassup");
		}else{
			$admin_message=__("An error occurred during the upgrade. WassUp table structure may not have been updated properly.","wassup");
		}
	}else{
		//New in v1.9: separate message for re-activation without table upgrade
		if(!is_multisite() || is_main_site() || is_network_admin()){
			$admin_message=__("activation successful. No upgrade necessary.","wassup");
		}else{
			$admin_message=__("activation successful","wassup");
		}
	}
	//verify that main table is installed, then save settings
	$wassup_table=$wassup_options->wassup_table; //in case changed
	$wassup_meta_table=$wassup_table."_meta";
	if(wassupDb::table_exists($wassup_options->wassup_table)){
		$wassup_options->wassup_alert_message=$admin_message;
		//update settings
		if($wsuccess){
			//update multisite settings
			if(is_multisite() && (is_network_admin() || is_main_site())){
				if($network_wide && !is_subdomain_install()) $wassup_network_settings['wassup_table']=$wassup_table;
				else unset($wassup_network_settings['wassup_table']);
				$wassup_network_settings['wassup_active']=1;
				update_site_option('wassup_network_settings',$wassup_network_settings);
			}
			//update site settings
			wassup_settings_install($wassup_table);
			$wassup_options->wassup_version=WASSUPVERSION;
		}elseif(!wassup_upgradeCheck($wassup_table)){
			//table not upgraded - exit with error
			if(!empty($admin_message)) $error_msg=$admin_message;
			else $error_msg='<strong style="color:#c00;padding:5px;">'.sprintf(__("%s: database upgrade failed!","wassup"),"Wassup ".WASSUPVERSION).'</strong>';
			if($wdebug_mode) $error_msg .=" <br/>wassup table: $wassup_table &nbsp; wassup_meta table: $wassup_meta_table";
			echo $error_msg;
			exit(1);
		}
		//save wassup version#
		$wassup_options->wassup_active=$active_status;
		$wassup_options->saveSettings();

		//New in v1.9: schedule regular cleanup of temp recs
		if(version_compare($wp_version,'2.8','>=')){
			wp_schedule_event(time()+1800,'hourly','wassup_scheduled_cleanup');
			//do regular purge of old records
			if(!empty($wassup_options->delete_auto) && $wassup_options->delete_auto!="never"){
				//do purge at 2am
				$starttime=strtotime("tomorrow 2:00am");
				wp_schedule_event($starttime,'daily','wassup_scheduled_purge');
			}
		}
	}else{
		//table not upgraded - exit with error
		$error_msg='<strong style="color:#c00;padding:5px;">'.sprintf(__("%s: plugin install/upgrade failed!","wassup"),"Wassup ".WASSUPVERSION).'</strong>';
		if($wdebug_mode) $error_msg .=" <br/>wassup table: $wassup_table &nbsp; wassup_meta table: $wassup_meta_table";
		echo $error_msg;
		exit(1); //exit with error
	}
} //end wassup_install

/** 
 * Completely remove all wassup tables and options from Wordpress and deactivate plugin. 
 * - no Wassup classes, globals, constants or functions are used during uninstall per Wordpress 'uninstall' hook requirement
 * @param boolean (for multisite uninstall)
 * @return void
 */ 
function wassup_uninstall($network_wide=false){
	global $wpdb,$wp_version,$current_user;
	$wfile=preg_replace('/\\\\/','/',__FILE__);
	$network_settings=array();
	$subsite_ids = array("0");
	if(empty($current_user->ID)) wp_get_current_user();
	//for multisite uninstall
	if(!$network_wide && function_exists('is_network_admin') && is_network_admin()) $network_wide=true;
	if($network_wide){
		$network_settings=get_site_option('wassup_network_settings');
		$subsite_ids=$wpdb->get_col(sprintf("SELECT `blog_id` FROM $wpdb->blogs WHERE `site_id`=%d ORDER BY `blog_id` DESC",$GLOBALS['current_site']->id)); //could also be $GLOBALS['current_blog']->site_id
		//wassup should not be active during network uninstall
		if(isset($network_settings['wassup_active'])) $network_settings['wassup_active']=0;
		update_site_option('wassup_network_settings',$network_settings);
	}elseif(is_multisite()){
		$subsite_ids=array($GLOBALS['current_blog']->blog_id);
	}
	//New in v1.9: For multisite...loop thru subsite ids and remove Wassup tables and settings from each subsite
	foreach($subsite_ids as $subsite_id){
		if($network_wide) switch_to_blog($subsite_id);
		$wassup_settings = get_option('wassup_settings');
		//first, stop recording
		if(!$network_wide && !empty($wassup_settings['wassup_active'])){
			$wassup_settings['wassup_active']="0";
			update_option('wassup_settings',$wassup_settings);
		}
		//remove all traces of aside widgets from Wordpress
		$wassup_widgets=array('wassup_online','wassup_topstats');
		foreach ($wassup_widgets AS $wwidget){
			if(function_exists('unregister_widget')){
				unregister_widget($wwidget."Widget");
			}elseif(function_exists('wp_unregister_sidebar_widget')){
				wp_unregister_sidebar_widget($wwidget);
			}else{
				unregister_sidebar_widget($wwidget);
			}
			//cleanup aside widget options from wp_option table
			$deleted=$wpdb->query(sprintf("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE 'widget_%s'",$wwidget.'%'));
		}
		//if plugin is still running, deactivate it
		if(function_exists('is_plugin_active') && function_exists('deactivate_plugins')){
			$wassupplugin=plugin_basename($wfile);
			if(is_plugin_active($wassupplugin)){
				deactivate_plugins($wassupplugin);
			}elseif(is_plugin_active(dirname($wassupplugin))){
				deactivate_plugins($wassupplugin);
			}
		}
		//remove wassup tables and options
		//$wassup_table = $wassup_settings['wassup_table'];
		$wassup_table = $wpdb->prefix."wassup";
		$table_tmp_name = $wassup_table."_tmp";
		$table_meta_name = $wassup_table."_meta";
		//purge wassup tables- WARNING: this is a permanent erase!!
		if(version_compare($wp_version,"2.8",">=")){
			$dropped=$wpdb->query("DROP TABLE IF EXISTS $table_meta_name");
			$dropped=$wpdb->query("DROP TABLE IF EXISTS $table_tmp_name");
			$dropped=$wpdb->query("DROP TABLE IF EXISTS $wassup_table");
		}else{
			//"if exists" in wpdb::query causes error in early versions of Wordpress
			mysql_query("DROP TABLE IF EXISTS $table_meta_name");
			mysql_query("DROP TABLE IF EXISTS $table_tmp_name");
			mysql_query("DROP TABLE IF EXISTS $wassup_table");
		}
		//delete 'wassup_settings' record from wp_option
		delete_option('wassup_settings');
		delete_user_option($current_user->ID,'_wassup_settings');
		if(version_compare($wp_version,'2.8','>=')){
			wp_clear_scheduled_hook('wassup_scheduled_cleanup');
			wp_clear_scheduled_hook('wassup_scheduled_purge');
		}
	} //end foreach
	//lastly, delete network setting and delete user settings
	//delete user setting for network_wide and single sites only
	if($network_wide){
		restore_current_blog();
		delete_site_option('wassup_network_settings');
		$deleted=$wpdb->query(sprintf("DELETE FROM %s WHERE `meta_key` LIKE '%%_wassup_settings'",$wpdb->base_prefix."usermeta"));
	}elseif(!is_multisite()){
		$deleted=$wpdb->query(sprintf("DELETE FROM %s WHERE `meta_key` LIKE '%%_wassup_settings'",$wpdb->prefix."usermeta"));
	}
} //end wassup_uninstall

function wassup_deactivate(){
	global $wp_version;
	if(version_compare($wp_version,'2.8','>=')){
		wp_clear_scheduled_hook('wassup_scheduled_cleanup');
		wp_clear_scheduled_hook('wassup_scheduled_purge');
	}
}
/**
 * Start Wassup plugin
 * -hook" Wassup functions to wordpress core actions
 * @since v1.9
 */
function wassup_start(){
	global $wp_version;
	add_action('init','wassup_preload',11);
	add_action('admin_init','wassup_admin_preload',12);
	add_action('plugins_loaded','wassup_load');
	//for cleanup of inactive wassup_tmp records and expired cache records
	if(version_compare($wp_version,'2.8','>=')){
		if(!has_action('wassup_scheduled_cleanup')) add_action('wassup_scheduled_cleanup','wassup_temp_cleanup');
		if(!has_action('wassup_scheduled_purge')) add_action('wassup_scheduled_purge','wassup_auto_cleanup');
	}
}
/**
 * Perform plugin actions for before http headers are sent
 *  -check for sql injection
 *  -start wassup tracking
 *  -renamed from 'wassup_init' in v1.9
 */
function wassup_preload(){
	global $wp_version,$wassup_options,$wdebug_mode;
	if(!defined('WASSUPVERSION')) wassup_init();

	//block any obvious sql injection attempts involving WassUp
	$request_uri=$_SERVER['REQUEST_URI'];
	if(!$request_uri) $request_uri=$_SERVER['SCRIPT_NAME']; // IIS
	if(strstr($request_uri,'&err=wassup403')===false && (stristr($request_uri,'wassup')!==false ||(isset($_SERVER['HTTP_REFERER'])&& stristr($_SERVER['HTTP_REFERER'],'wassup')!==false))){
		$error_msg="";
		if((empty($wassup_options) || !$wassup_options->is_admin_login()) && preg_match('/[&?].+\=(\-(1|9)+|.*(select|update|delete|alter|drop|union|create)[ %&].*(?:from)?.*wp_\w+)/i',str_replace(array('\\','&#92;','"','%22','&#34;','&quot;','&#39;','\'','`','&#96;'),'',$request_uri))>0){
			$error_msg=__('Bad request!','wassup');
			if($wdebug_mode)$error_msg .=" wassup_preset#1";
		}elseif(preg_match('/(<|&lt;|&#60;|%3C)script[^a-z0-9]/i',$request_uri)>0){
			$error_msg=__('Bad request!','wassup');
			if($wdebug_mode)$error_msg .=" wassup_preset#2";
		}elseif(empty($_SERVER['HTTP_USER_AGENT'])){
			$error_msg=__('Bad request!','wassup');
			if($wdebug_mode)$error_msg .=" Empty user-agent.";
		}
		//redirect bad requests
		if(!empty($error_msg)){
			if($wdebug_mode){
				wp_die($error_msg.' :'.esc_attr(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI']),array())));
			}
			if(!headers_sent()) header('Location: /?p=404page&err=wassup403');
			else wp_die($error_msg);
			exit;
		}
	}
	//New in v1.9: wassup user settings are reset at login
	add_action('wp_login',array($wassup_options,'resetUserSettings'),9,2);
	//New in v1.9: for scheduled database tasks such as retroactive updates and table optimization
	if(version_compare($wp_version,'2.8','>=')){
		if(!has_action('wassup_scheduled_dbtasks')) add_action('wassup_scheduled_dbtasks',array('wassupDb','scheduled_dbtask'),10,1);
		if(!has_action('wassup_scheduled_optimize')) add_action('wassup_scheduled_optimize',array('wassupDb','scheduled_dbtask'),10,1);
	}
	//for backward compatibility with older versions of Wordpress
	if(is_admin() && version_compare($wp_version,'2.5',"<")){
		do_action('admin_init');
	}
	//Start visitor tracking
	if(!empty($wassup_options->wassup_active)){
		wassupPrepend();
	}
} // end wassup_preload

/**
 * Perform plugin actions for before start of page rendering
 * -load Wassup widgets
 * -load footer tag
 * -renamed from 'wassup_loader' since v1.9
 */
function wassup_load() {
	global $wassup_options;
	if(!defined('WASSUPVERSION')) wassup_init();
	//load widgets and visitor tracking content
	if (!empty($wassup_options->wassup_active)) {
		add_action("widgets_init",'wassup_widget_init',9);
		if(is_admin()) add_action('admin_footer','wassup_foot');
		else add_action('wp_footer','wassup_foot');
	}
	if(is_admin()) wassup_admin_load();
}
//### II: Admin screens/report functions
/**
 * Perform plugin tasks for before admin http headers are sent
 *
 *  -set deactivation hook for network subsite uninstall
 *  -do export to file requests
 *  -load ajax libraries and frameworks (enqueue scripts).
 * @since v1.9
 */
function wassup_admin_preload() {
	global $wpdb, $wp_version, $wassup_options, $wdebug_mode;
	if(!defined('WASSUPVERSION')) wassup_init();
	//uninstall on deactivation when 'wassup_uninstall' option is set - applies to Wordpress multisite subdomains and Wordpress 2.X installations only 
	if(!empty($wassup_options->wassup_uninstall)) register_deactivation_hook(WASSUPDIR,'wassup_uninstall');
	else register_deactivation_hook(WASSUPDIR,'wassup_deactivate');
	//run export request 
	//..moved here so that username can be verified inside export function (requires pluggable.php loaded)
	if (isset($_REQUEST['export']) && isset($_GET['page']) && stristr($_GET['page'],"wassup")!==false){
		export_wassup();
		exit;
	}
	//wassup scripts and css
	add_action('admin_enqueue_scripts','wassup_add_scripts',11);
	//for backward compatibility with old versions of Wordpress
	if(version_compare($wp_version,'2.8','<')) do_action('admin_enqueue_scripts');
} //end wassup_admin_preload

/**
 * Perform plugin tasks for before start of admin page rendering
 *
 * -load Wassup user and network settings
 * -add hooks and filters for wassup admin pages:
 *   1. admin_head: add embeded javascripts and css to document head
 *   2. admin_menu: add Wassup main menu to admin menus
 *   3. admin_notices: add Wassup alert messages to admin_notices
 *   4. wp_dashboard_setup: add Wassup dashboard widget to admin dashboard.
 * @since v1.9
 */
function wassup_admin_load(){
	global $wp_version,$current_user,$wassup_options;
	if(!defined('WASSUPVERSION')) wassup_init();
	//New in v1.9: get/set user-specific wassup_settings
	if(!is_object($current_user) || empty($current_user->ID)) wp_get_current_user();
	$wassup_user_settings=get_user_option('_wassup_settings',$current_user->ID);
	//reset user settings after plugin upgrade
	if(!empty($wassup_user_settings) && (empty($wassup_user_settings['uversion']) || $wassup_user_settings['uversion']!=WASSUPVERSION)){
		$wassup_user_settings = $wassup_options->resetUserSettings($current_user->user_login,$current_user);
	}
	//embed javascripts and css tags in admin head
	add_action('admin_head','wassup_embeded_scripts');
	add_action('admin_head','wassup_add_css',11);
	if($wassup_options->network_activated_plugin() && is_network_admin()){
		add_action('network_admin_menu','wassup_add_pages');
	}else{
		add_action('admin_menu','wassup_add_pages');
	}
	//New in v1.9: admin_notices filter to show Wassup messages
	if(is_network_admin()){
		add_action('network_admin_notices',array(&$wassup_options,'showMessage'));
	}elseif(empty($_GET['page'])|| stristr($_GET['page'],'wassup')!==false){
		add_action('admin_notices',array(&$wassup_options,'showMessage'));
	}elseif(!empty($wassup_user_settings['ualert_message'])){
		//show user-specific messages in all admin panels
		add_action('admin_notices',array(&$wassup_options,'showMessage'));
	}
	if(is_multisite()) $network_settings=get_site_option('wassup_network_settings');
	else $network_settings=array();
	//Wassup must be active for site and network
	if(!empty($wassup_options->wassup_active) && (empty($network_settings)|| !empty($network_settings['wassup_active']))){
		//dashboard widget setup
		if(!empty($wassup_options->wassup_dashboard_chart) || (is_network_admin() && !empty($network_settings['wassup_table']))){
			if(!class_exists('wassup_Dashboard_Widgets')){
				if(defined('PHP_VERSION') && version_compare(PHP_VERSION,'5.2','<')) include_once(WASSUPDIR."/lib/compat-lib/admin.class.php");
				else include_once(WASSUPDIR."/lib/admin.class.php");	
			}
			if(is_network_admin() && !empty($network_settings['wassup_table'])){
				add_action('wp_network_dashboard_setup',array('wassup_Dashboard_Widgets','init'));
			}elseif(!is_network_admin()){
				add_action('wp_dashboard_setup',array('wassup_Dashboard_Widgets','init'));
		 		//for backward compatibility to WP 2.2
				if(empty($_GET['page'])&& version_compare($wp_version,'2.5','<')&& (substr($_SERVER['REQUEST_URI'],-10)=='/wp-admin/' || strpos($_SERVER['REQUEST_URI'],'index.php')>0)){
					do_action('wp_dashboard_setup');
				}
			}
		}
	}
	if(!empty($_GET['page'])&& stristr($_GET['page'],'wassup')!==FALSE){
		//initialize user settings as needed
		if(empty($wassup_user_settings)) {
			$wassup_user_settings=$wassup_options->defaultSettings('wassup_user_settings');
			update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		}
		//for display of Wassup page contents...only add-on modules need do this
		//add_action('wassup_page_content','wassup_page_contents',10,1);
	}
} //end wassup_admin_load

/**
 * Adds link tags for javascript libraries and css files to Wassup admin pages
 *
 * Uses "wp_enqueue_script" and "wp_enqueue_style" Wordpress hooks to include library dependencies and avoid redundancy
 * -resets jqueryUI and thickbox to Wassup copies
 * -resets jquery library to Wassup's copy in old Wordpress versions
 * -enqueues wassup.js, jquery.js, jqueryUI.js, jquery-migrate.js, thickbox.js for Wassup admin panels
 * -enqueues wassup.css for Wassup admin panel and widgets admin panel
 */
function wassup_add_scripts(){
	global $wp_version,$wdebug_mode;
	$vers=WASSUPVERSION;
	if($wdebug_mode)$vers.='b'.rand(0,9999);
	if(!empty($_GET['page']) && stristr($_GET['page'],'wassup') !== FALSE){
		$wassuppage=wassupURI::get_menu_arg();
		//NOTE: jquery, ui.tabs, and thickbox built into Wordpress since 2.7
		if(file_exists(WASSUPDIR.'/js/thickbox/thickbox.js')){
			wp_deregister_script('thickbox');
		}
		//load legacy jQuery v1.8.3 for WordPress 2.2 - 3.5
		if(version_compare($wp_version,'3.5','<') && file_exists(WASSUPDIR.'/js/jquery.js')){
			wp_deregister_script('jquery');	
			wp_register_script('jquery', WASSUPURL.'/js/jquery.js',FALSE,'1.8.3'); 
		}else{
			//jquery+jquery.migrate plugin required for Wordpress 3.6+
			if(file_exists(ABSPATH.WPINC."/js/jquery/jquery.migrate.min.js")) wp_enqueue_script('jquery-migrate');
			else wp_register_script('jquery-migrate',WASSUPURL.'/js/jquery-migrate.js',array('jquery'),'1.2.1');
		} 
		if($wassuppage == "wassup-spia" || $wassuppage=="wassup-spy"){
			wp_enqueue_script('spia', WASSUPURL.'/js/spia.js', array('jquery'), $vers);
		}elseif($wassuppage == "wassup-options"){
			//use wassup's jquery-ui v1.10.4 (legacy version with IE7 support)
			wp_deregister_script('jqueryui');
			wp_deregister_script('jquery-ui-core');	
			wp_deregister_script('jquery-ui-widget');
			wp_deregister_script('jquery-ui-tabs');	
			wp_deregister_script('jquery-ui-dialog');
			if($wdebug_mode){
				wp_enqueue_script('jqueryui', WASSUPURL.'/js/jquery-ui/js/jquery-ui.min.js', array('jquery'), '1.10.4');
			}else{
				//load jquery-ui from Google ajax CDN
				$allow_url_fopen=@ini_get('allow_url_fopen');
				if(!empty($allow_url_fopen)&& is_readable('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js')){
					wp_enqueue_script('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js',array('jquery'),'1.10.4');
				}else{
					//load Wassup's copy of juery-ui
					wp_enqueue_script('jqueryui',WASSUPURL.'/js/jquery-ui/js/jquery-ui.min.js',array('jquery'),'1.10.4');
				}
			}
			if(function_exists('wp_dequeue_style')){
				wp_dequeue_style('jquery-ui-tabs.css');
				wp_dequeue_style('jquery-ui-theme.css');
				wp_dequeue_style('jquery-ui.css');
			}
		}else{
			if(file_exists(WASSUPDIR.'/js/thickbox/thickbox.js')){
				wp_enqueue_script('thickbox',WASSUPURL.'/js/thickbox/thickbox.js',array('jquery'),'3');
			}
			if(function_exists('wp_dequeue_style')) wp_dequeue_style('thickbox.css');
		}
		//New in v1.9: some common wassup javascripts moved to separate file
		wp_enqueue_script('wassup',WASSUPURL.'/js/wassup.js',array(),$vers);
		if(function_exists('wp_enqueue_style')) wp_enqueue_style('wassup', WASSUPURL.'/css/wassup.css',array(),$vers);
	}elseif(strpos($_SERVER['REQUEST_URI'],'/widgets.php')!==false || strpos($_SERVER['REQUEST_URI'],'/customize.php')!==false){
		//css for wassup-widget control in customizer
		if(function_exists('wp_enqueue_style')) wp_enqueue_style('wassup',WASSUPURL.'/css/wassup.css',array(),$vers);
	} //end if GET['page']
} //end wassup_add_scripts

/**
 * Embed javascripts into document head of Wassup admin panel pages
 *
 * -embed timer and automatic reload javascripts in visitor details
 * -embed jquery code for ajax actions in visitor details/online
 * -embed Google!Map API tag and scripts for map setup in Wassup spy.
 * -embed thickbox loading image tag to Wassup pages
 * @since v1.9
 * @param string $wassuppage
 * @return void
 * @uses lib/action.php
 */
function wassup_embeded_scripts($wassuppage="") {
	global $wp_version,$current_user,$wassup_options,$wdebug_mode;

	$vers=WASSUPVERSION;
	if($wdebug_mode)$vers.='b'.rand(0,9999);
	//Restrict embedded javascripts to wassup admin pages only...
	if(!empty($_GET['page']) && stristr($_GET['page'],'wassup')!== FALSE){
		if(empty($wassuppage)) $wassuppage=wassupURI::get_menu_arg();
		//assign a value to whash, if none
		if (empty($wassup_options->whash)) {
			$wassup_options->whash = $wassup_options->get_wp_hash();
			$wassup_options->saveSettings();
		}
		if(empty($current_user->ID)) wp_get_current_user();
		$wassup_user_settings=get_user_option('_wassup_settings');
		$wnonce=(!empty($wassup_user_settings['unonce'])?$wassup_user_settings['unonce']:'');
		//preassign parameters for "action.php" ajax module
		$action_param='&whash='.$wassup_options->whash;
		if ($wdebug_mode) {
			$action_param .= '&debug_mode=true';
		}
		//'wpabspath' is "action.php" parameter for when "/wp-content/" is located outside of Wordpress' install directory.
		if (defined('WP_CONTENT_DIR') && strpos(WP_CONTENT_DIR,ABSPATH)===FALSE) {
			$action_param .= '&wpabspath='.urlencode(base64_encode(ABSPATH));
		}
		$wrefresh = (int) $wassup_options->wassup_refresh;
	//embed javascripts on wassup pages
	if($wassuppage=="wassup"){
		//set auto refresh URL 
		$refresh_loc='location.reload(true)';
		//don't use "reload" when post array or deletemarked is set
		if(!empty($_POST) || isset($_GET['deleteMARKED']) || isset($_GET['chart']) || isset($_GET['dip']) || isset($_GET['mark']) || isset($_GET['search-submit'])){
			$URLQuery=trim(html_entity_decode($_SERVER['QUERY_STRING']));
			if(empty($URLQuery) && preg_match('/[^\?]+\?([A-Za-z\-_]+.*)/',html_entity_decode($_SERVER['REQUEST_URI']),$pcs)>0) $URLQuery=$pcs[1];
			if(!empty($URLQuery)) $refresh_loc='location.href="?'.$URLQuery.'"';
			//New in v1.9: remove "delete","search", and "chart" query parameters from auto-refresh location url
			if(isset($_GET['deleteMARKED']) || isset($_GET['chart']) || isset($_GET['dip']) || isset($_GET['mark']) || isset($_GET['search-submit'])){
				$newQuery=preg_replace(array('/&deleteMARKED=[^&]+/','/&dip=[^&]+/','/&chart=[^&]+/','/&mark=[^&]+/','/&submit\-search=[^&]+/'),"",$URLQuery);
 				if(!empty($newQuery) && $newQuery!= $URLQuery) $refresh_loc='location.href="?'.$newQuery.'"';
			}
		}
		//restrict refresh to range 0-180 mins (3 hrs)
 		if($wrefresh < 0 || $wrefresh >180){
			$wrefresh=3; //3 minutes default;
		} 
		//add refresh timer javascripts...
?>
<script type='text/javascript'>
//<![CDATA[
  var tb_pathToImage="<?php echo WASSUPURL.'/js/thickbox/loadingAnimation.gif';?>";
  var paused = " *<?php _e('paused','wassup'); ?>* ";
<?php
		if ($wrefresh > 0) {
		//New in v1.9: addLoadEvent replaces "onload()" function for compatibility with other plugins (chains onload functions)
?>
  var selftimerID=0;
  function wassupReload<?php echo $wnonce;?>(wassuploc){if(wassuploc!=="")location.href=wassuploc;}
  function wSelfRefresh(){<?php echo $refresh_loc;?>}
  selftimerID=setTimeout('wSelfRefresh()',<?php echo ($wrefresh*60000)+2000;?>);
  addLoadEvent(function(){ActivateCountDown("CountDownPanel",<?php echo ($wrefresh*60);?>);});
<?php
		} //end if $wrefresh > 0
?>
  jQuery(document).ready(function($){
	$("a.showhide").click(function(){var id=$(this).attr('id');$("div.navi"+id).toggle("slow");return false;});
	$("a.toggleagent").click(function(){var id=$(this).attr('id');$("div.naviagent"+id).slideToggle("slow");return false;});
	$("img.delete-icon").mouseover(function(){$(this).attr("src","<?php echo WASSUPURL.'/img/b_delete2.png';?>");}).mouseout(function() {$(this).attr("src","<?php echo WASSUPURL.'/img/b_delete.png';?>");});
	$("img.table-icon").mouseover(function(){$(this).attr("src","<?php echo WASSUPURL.'/img/b_select2.png';?>");}).mouseout(function(){$(this).attr("src","<?php echo WASSUPURL.'/img/b_select.png';?>");});<?php
		echo "\n";
		//only administrators can delete
		if(current_user_can('manage_options')){?>
	$("a.deleteID").click(function(){
		var id = $(this).attr('id');
		$("div#delID" + id).css("background-color","#ffcaaa");
		$("div#delID" + id).find("ul.url li").css("background-color","#ffcaaa");
		$.ajax({
		  url: "<?php echo WASSUPURL.'/lib/action.php?action=deleteID'.$action_param; ?>&id=" + id,
		  async: false,
		  success: function(html){
		  	if (html == "") $("div#delID" + id).fadeOut("slow");
		  	else $("div#delID" + id).find('p.delbut').append("<br/><br/><small style='color:#404;font-weight:bold;text-align:right;float:right;'> <nobr><?php _e('Sorry, delete failed!','wassup'); ?></nobr> " + html + "</small>");
		  	},
		  error: function (XMLHttpReq, txtStatus, errThrown) {
		  	$("div#delID" + id).find('p.delbut').append("<br/><br/><small style='color:#404;font-weight:bold;text-align:right;float:right;'> <nobr><?php _e('Sorry, delete failed!','wassup'); ?></nobr> " + txtStatus + ": " + errThrown + "</small>");
		  	}
		});
		return false;
	});<?php
			echo "\n";
		}?>
	$("a.show-search").toggle(function(){<?php
		if (empty($_GET['search'])){?>$("div.search-ip").slideDown("slow");$("a.show-search").html("<?php _e('Hide Search','wassup');?>");},function(){$("div.search-ip").slideUp("slow");$("a.show-search").html("<?php _e('Search','wassup');?>");return false;<?php	
		} else { ?>$("div.search-ip").slideUp("slow");$("a.show-search").html("<?php _e('Search','wassup');?>");},function(){$("div.search-ip").slideDown("slow");$("a.show-search").html("<?php _e('Hide Search','wassup');?>");return false;<?php	
		} ?>});
	$("a.toggle-all").toggle(function(){$("div.togglenavi").slideDown("slow");$("a.toggle-all").html("<?php _e('Collapse All','wassup');?>");},function(){$("div.togglenavi").slideUp("slow");$("a.toggle-all").html("<?php _e('Expand All','wassup');?>");return false;});
	$("a.toggle-allcrono").toggle(function(){$("div.togglecrono").slideUp("slow");$("a.toggle-allcrono").html("<?php _e('Expand Chronology','wassup');?>");},function(){$("div.togglecrono").slideDown("slow");$("a.toggle-allcrono").html("<?php _e('Collapse Chronology','wassup');?>");return false;});
<?php
		if ($wrefresh > 0) {  ?>
	$("#CountDownPanel").click(function(){var timeleft=_currentSeconds*1000;if(tickerID !=0){clearInterval(tickerID);clearTimeout(selftimerID);tickerID=0;$(this).css('color','#999').html(paused);}else{if(_currentSeconds < 1)timeleft=1000;selftimerID=setTimeout('wSelfRefresh()',timeleft);tickerID=window.setInterval("CountDownTick()",1000);$(this).css('color','#555');}});
<?php
		} //end if $wrefresh > 0 (2nd)
?>
  }); //end jQuery(document).ready
//]]>
</script><?php
		echo "\n";
	}elseif($wassuppage == "wassup-online"){
		//always refresh wassup-online page every 1-3 mins
		if($wrefresh >3 || $wrefresh < 1) $wrefresh=3;
?>
<script type="text/javascript">
//<![CDATA[
  var tb_pathToImage="<?php echo WASSUPURL.'/js/thickbox/loadingAnimation.gif';?>";
  function wSelfRefresh(){location.reload(true)}
  var selftimerID=setTimeout('wSelfRefresh()',<?php echo ($wrefresh*60000)+2000;?>);
  jQuery(document).ready(function($){
	$("a.showhide").click(function(){var id=$(this).attr('id');$("div.navi"+id).toggle("slow");return false;});
	$("a.toggle-all").toggle(function(){$("div.togglenavi").slideDown("slow");$("a.toggle-all").html("<?php _e('Collapse All','wassup'); ?>");},function(){$("div.togglenavi").slideUp("slow");$("a.toggle-all").html("<?php _e('Expand All','wassup');?>");return false;});
  });
//]]>
</script><?php
		echo "\n";
	}elseif($wassuppage == "wassup-options"){
?>
<script type="text/javascript">
  //<![CDATA[
  jQuery(document).ready(function($) {
	  var tabs=$('#tabcontainer').tabs();
	  $('.submit-opt').click(function(){$(this).css("background-color","#d71");});
	  $('.default-opt').click(function(){$(this).css("background-color","#d71");});
  });
  //]]>
</script><?php
		echo "\n";
	}elseif($wassuppage == "wassup-spia" || $wassuppage=="wassup-spy"){
		// GEO IP Map
		//New in v1.9: google!Maps map init and marker javascripts moved to document head
		if($wassup_user_settings['spy_map']== 1 || !empty($_GET['map'])){
			//check for api key for Google!maps v3 - still needed in case of Google!maps overuse by a user
			if(!empty($wassup_options->wassup_googlemaps_key)){
				echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$wassup_options->wassup_googlemaps_key.'&sensor=false" type="text/javascript"></script>';
			}else{
				echo '<script src="https://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>';
			}
		} //end if spy_map
?>
<script type="text/javascript">
//<![CDATA[
function wassupReload<?php echo $wnonce;?>(wassuploc){if(wassuploc!=="")location.href=wassuploc;}
jQuery(document).ready(function($){
	$('#spyContainer > div:gt(4)').fadeEachDown(); // initial fade
	$('#spyContainer').spy({limit:15,fadeLast:5,ajax:'<?php echo WASSUPURL."/lib/action.php?action=spia&".$action_param;?>',timeout:5000,'timestamp':spiaTimestamp,'method':"html",fadeInSpeed:800,});
	$('#spy-pause').click(function(){$(this).css("background-color","#ebb");$("#spy-play").css("background-color","#eae9e9");<?php
		if(!empty($wassup_user_settings['spy_map'])|| !empty($_GET['map']))echo '$("div#spia_map").css({"opacity":"0.7","background":"none"});';?>});
	$('#spy-play').click(function(){$(this).css("background-color","#cdc");$("#spy-pause").css("background-color","#eae9e9");<?php
		if(!empty($wassup_user_settings['spy_map'])|| !empty($_GET['map']))echo '$("div#spia_map").css("opacity","1");';?>});
});
<?php
		if ($wassup_user_settings['spy_map']== 1 || !empty($_GET['map'])) {?>
var spiamap;
var pinuser={url:"<?php echo WASSUPURL.'/img/marker_user.png';?>",size: new google.maps.Size(20.0,34.0),origin: new google.maps.Point(0,0),anchor: new google.maps.Point(10.0,34.0)};
var pinlogged={url:"<?php echo WASSUPURL.'/img/marker_loggedin.png';?>",size: new google.maps.Size(20.0,34.0),origin: new google.maps.Point(0,0),anchor: new google.maps.Point(10.0,34.0)};
var pinauthor={url: "<?php echo WASSUPURL.'/img/marker_author.png';?>",size: new google.maps.Size(20.0,34.0),origin: new google.maps.Point(0,0),anchor: new google.maps.Point(10.0,34.0)};
var pinbot={url: "<?php echo WASSUPURL.'/img/marker_bot.png';?>",size: new google.maps.Size(20.0,34.0),origin: new google.maps.Point(0,0),anchor: new google.maps.Point(10.0,34.0)};
function wassupMapinit(canvas,clat,clon){
	var mapOptions={zoom:3, mapTypeId:google.maps.MapTypeId.ROADMAP};
	spiamap=new google.maps.Map(document.getElementById(canvas), mapOptions);
	var pos=new google.maps.LatLng(clat,clon);
	spiamap.setCenter(pos);
}
function showMarkerinfo(mmap,mlat,mlon,marker,markerwin){
	document.body.scrollTop=document.documentElement.scrollTop=0;
	mmap.panTo(new google.maps.LatLng(mlat,mlon));
	mmap.setZoom(5);
	markerwin.open(mmap,marker);
}
<?php
		} //end if spy_map
?>
//]]>
</script><?php
		echo "\n";
	}else{ //end if wassuppage == "wassup-spia"
?>
<script type='text/javascript'>var tb_pathToImage="<?php echo WASSUPURL.'/js/thickbox/loadingAnimation.gif';?>";</script>
<?php
	}
	} //end if _GET['page']
} //end wassup_embeded_scripts

/**
 * Add wassup stylesheets tags and embeds css code in document head
 * -add link tags to jquery-ui stylesheets in Wassup options page
 * -add thickbox.css link tag in wassup pages (as override)
 * -add wassup.css link tag in Wassup admin pages (if not enqueued)
 * -embed styles for overriding some Wordpress & plugins styles
 * -assign an admin body class (wassup, wassup-wp-legacy) for wassup page styling
 */
function wassup_add_css() {
	global $wp_version,$wdebug_mode;
	$vers=WASSUPVERSION;
	if($wdebug_mode)$vers.='b'.rand(0,9999);
	//add wassup.css, jqueryui-css, and thickbox.css to wassup pages
	$wassuppage=wassupURI::get_menu_arg();
	if(!empty($wassuppage) && strpos($wassuppage,'wassup')!==FALSE){
		//TODO: Add a WassUp favicon to wassup pages
		//output the stylesheet links
		if($wassuppage=="wassup-options"){?>
<link href="<?php echo WASSUPURL;?>/js/jquery-ui/css/jquery.ui.theme.css" rel="stylesheet" type="text/css" />
<link href="<?php echo WASSUPURL;?>/js/jquery-ui/css/jquery.ui.tabs.css" rel="stylesheet" type="text/css" /><?php
			echo "\n";
		}
		if($wassuppage=="wassup" || $wassuppage=="wassup-online"){?>
<link rel="stylesheet" href="<?php echo WASSUPURL.'/js/thickbox/thickbox.css';?>" type="text/css" /><?php
			echo "\n";
		}
		//add wassup stylesheet after other stylesheets
		if(!function_exists('wp_enqueue_style')){?>
<link rel="stylesheet" href="<?php echo WASSUPURL.'/css/wassup.css?ver='.$vers;?>" type="text/css" /><?php
			echo "\n";
		}
		// Override some Wordpress css and Wassup default css settings on Wassup pages- TODO: check against latest version of ozh drop-down plugin
?>
<style type="text/css">
#ozh_menu_wrap{margin-top:-3px !important;} /* ozh drop-down plugin */
#contextual-help-link{display:none;}
.update-nag{display:none;} /* nag messes up menus, so hide it */
<?php
		if(version_compare($wp_version,'4.2','<') && version_compare($wp_version,'3.3','>')) echo '#wassup-screen-links{margin-top:-2px;}'."\n";
		if(version_compare($wp_version,'2.8','<')) echo '#wassup-message{padding-left:35px;}';
?>
</style>
<!--[if lt IE 8]>
<style type="text/css">#wassup-menu li{width:120px;}</style>
<![endif]-->
<?php
		echo "\n";
	}else{
		//embed style for wassup admin notices in admin panels
?>
<style type="text/css">
#wassup-message{font-size:13px;color:#447;padding:10px;}
#wassup-message.error{color:#d00;}
#wassup-message.notice-warning{color:#a21;}
#wassup-message.updated{color:#040;}
</style><?php
	}
	//New in v1.9: "wassup" and "wassup_legacy" body classes for styling Wassup pages and widget
	add_filter('admin_body_class','wassup_add_body_class');
} //end wassup_add_css

/**
 * Add "wassup" and "wassup-wp-legacy" body class to Wassup pages.
 * @since v1.9
 * @param string (comma-separated classes)
 * @return string 
 */
function wassup_add_body_class($classes) {
	global $wp_version;
	$body_class="";
	if(empty($_GET['page'])|| stristr($_GET['page'],'wassup')!==FALSE){ 
		$body_class="wassup";
		if(version_compare($wp_version,'3.8','<')) $body_class="wassup-wp-legacy";
	}elseif(strpos($_SERVER['REQUEST_URI'],'widgets.php')>0){
		if(version_compare($wp_version,'3.8','<')) $body_class="wassup-wp-legacy";
	}
	if(!empty($body_class)){
		if(is_array($classes)) $classes[]=$body_class;
		else $classes .=" $body_class";
	}
	return $classes;
}

/**
 * WassUp admin menus, submenus, and links setup
 * - adds Wassup main admin menu
 * - adds 'wassup-stats' admin dashboard submenu
 * - adds 'settings' link to plugins panel.
 */
function wassup_add_pages() {
	global $wp_version, $wassup_options;
	if(!defined("WASSUPVERSION")) wassup_init();
	//New in v1.9: Replaced user level with capability string for Wordpress 3.X-4.X compatibility
	$menu_access=$wassup_options->get_access_capability();
	$wassupfolder=basename(WASSUPDIR);
	//New in v1.9: only administrators are allowed access to wassup main menu...other users see the "Wassup-stats" submenu only
	$show_wassup_menu=false;
	if(current_user_can('manage_options')){
		$show_wassup_menu=true;
		if(is_multisite() && !is_super_admin() && !is_network_admin()){
			$network_settings=get_site_option('wassup_network_settings');
			if(empty($network_settings['wassup_menu'])) $show_wassup_menu=false;
		}
	}
	if($show_wassup_menu){
		// add the default submenu first (important!)
		if(version_compare($wp_version,'3.8','>=')) add_menu_page('Wassup','WassUp',$menu_access,$wassupfolder,'WassUp','dashicons-chart-area');
		else add_menu_page('Wassup','WassUp',$menu_access,$wassupfolder,'WassUp');
		add_submenu_page($wassupfolder,__("Visitor Details","wassup"),__("Visitor Details","wassup"),$menu_access,$wassupfolder,'WassUp');
		add_submenu_page($wassupfolder,__("Spy Visitors","wassup"),__("SPY Visitors","wassup"),$menu_access,'wassup-spia','WassUp');
		add_submenu_page($wassupfolder,__("Current Visitors Online","wassup"),__("Current Visitors Online","wassup"),$menu_access, 'wassup-online','WassUp');
		//WassUp settings available at 'manage_options' access level only
		add_submenu_page($wassupfolder,__("Options","wassup"),__("Options","wassup"),'manage_options','wassup-options','WassUp');
	}
	//add Wassup Stats submenu on WP2.7+ dashboard menu
	//add "settings" to action links on "plugins" page
	if(version_compare($wp_version,'2.5','>=')){
		if(version_compare($wp_version,'2.7','>=')){
			add_submenu_page('index.php',__("WassUp Stats","wassup"),__("WassUp Stats","wassup"),$menu_access,'wassup-stats','WassUp');

			add_filter("plugin_action_links_".$wassupfolder."/wassup.php",'wassup_plugin_links',-10,2);	//WP 2.7+ filter
		}else{
			add_filter('plugin_action_links','wassup_plugin_links',-10,2);	//WP 2.5+ filter
		}
	}
} //end wassup_add_pages

/**
 * hook function for Wordpress plugin links
 *  - appends 'settings' link (for wassup-options) to plugin action links on "Plugins" page.
 * @since v1.8
 * @param (2) array, string
 * @return array
 */
function wassup_plugin_links($links, $file){
	global $wassup_options;
	if(!defined('WASSUPVERSION')) wassup_init();
	if($file == plugin_basename(WASSUPDIR."/wassup.php")){
		if(is_multisite() && is_network_admin() && $wassup_options->network_activated_plugin()){
			$links[] = '<a href="'.network_admin_url('admin.php?page=wassup-options').'">'.__("Settings").'</a>';
		}else{
			$links[] = '<a href="'.admin_url('admin.php?page=wassup-options').'">'.__("Settings").'</a>';
		}
	}
	return $links;
} // end function wassup_plugin_links

/**
 * Add a horizontal navigation menu to Wassup:
 * - menu tabs are links to Wassup pages from wassup_add_pages() above plus a "Donate" tab.
 * - will automatically add tabs for add-on modules to Wassup, if any.
 * @author Helene D.
 * @since v1.9
 */
function wassup_menu_links($selected=""){
	global $submenu,$wp_version,$wassup_options,$wdebug_mode;
	if(empty($selected)){
		$selected=(isset($_GET['page'])?$_GET['page']:"");
		$i=strpos($selected,"#");
		if(!empty($i))$selected=substr($selected,0,$i);
	}
	$wassupfolder=basename(WASSUPDIR);
	echo "\n";?>
	<div id="wassup-screen-links">
	<ul id="wassup-menu"><?php
	if(!empty($submenu[$wassupfolder]) && is_array($submenu[$wassupfolder])){
		$wassupmenu=$submenu[$wassupfolder];
		//submenus from wassup addons are included here
		$submenu_count=count($wassupmenu);
		for($i=$submenu_count-1;$i>=0;$i--){
			$menu_access=$wassupmenu[$i][1];
			$menu_page=$wassupmenu[$i][2];
			$menu_name=$wassupmenu[$i][3];
			$menu_class="";
			if($menu_page=="$selected"){
				$menu_class=" current";
			}elseif($menu_page==$wassupfolder && ($selected=="wassup-stats" || $selected=="wassup")){
				$menu_class=" current";
			}elseif($menu_page=="wassup-spia" && $selected=="wassup-spy"){
				$menu_class=" current";
			}
			if(current_user_can($menu_access)){
				if($menu_page=="wassup-online")$menu_name =__("Current Visitors Online","wassup");
				echo "\n";?>
		<li class="wassup-menu-link<?php echo $menu_class;?>"><?php
				if(is_multisite() && is_network_admin()) echo '<a href="'.network_admin_url("admin.php?page=$menu_page").'">'.$menu_name.'</a>';
				else echo '<a href="'.admin_url("admin.php?page=$menu_page").'">'.$menu_name.'</a>';?></li><?php
			}
		}//end for
		$donate_link_url="";
		if(is_multisite() && is_network_admin()){
			$donate_link_url=network_admin_url('admin.php?page=wassup-options&tab=donate');
		}elseif(current_user_can('manage_options')){
			$donate_link_url=admin_url('admin.php?page=wassup-options&tab=donate');
		}
		wassup_donate_link($donate_link_url);
	}else{
		if (($selected=="wassup-stats" || $selected=="wassup") && !empty($_GET['ml']))
			$selected=$_GET['ml'];
		echo "\n";?>
		<li class="wassup-menu-link<?php if($selected=="wassup-online") echo " current";?>"><a href="<?php if(is_multisite() && is_network_admin()) echo network_admin_url('index.php?page=wassup-stats&ml=wassup-online'); else echo admin_url('index.php?page=wassup-stats&ml=wassup-online');?>"><?php _e('Current Visitors Online','wassup');?></a></li>
		<li class="wassup-menu-link<?php if($selected=="wassup-spia" || $selected=="wassup-spy") echo " current";?>"><a href="<?php if(is_multisite() && is_network_admin()) echo network_admin_url('index.php?page=wassup-stats&ml=wassup-spia'); else echo admin_url('index.php?page=wassup-stats&ml=wassup-spia'); ?>"><?php _e('SPY Visitors','wassup');?></a></li>
		<li class="wassup-menu-link<?php if($selected=="wassup" || $selected==$wassupfolder || $selected=="wassup-stats") echo " current";?>"><a href="<?php if(is_multisite() && is_network_admin()) echo network_admin_url('index.php?page=wassup-stats'); else echo admin_url('index.php?page=wassup-stats');?>"><?php _e('Visitor Details','wassup');?></a></li><?php
		wassup_donate_link();
	} //end if submenu
	echo "\n";?>
	</ul><div style="clear:right;"></div>
	</div><?php
} //end wassup_menu_links

function wassup_donate_link($link_url=""){
	global $wdebug_mode;
	//add menu tab for donate link to Paypal
	echo "\n";?>
	<li id="donate-link" class="wassup-menu-link"><?php
	if(!empty($link_url) && strpos($link_url,'//')!==false){
		echo '<a href="'.$link_url.'"><img src="'.WASSUPURL.'/img/donate-button-sm.png" alt="'.__("Donate","wassup").'"/></a>';
	}else{
		echo "\n";?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="michele@befree.it">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="Wassup Wordpress Plugin">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest">
<input type="image" src="<?php echo WASSUPURL.'/img/donate-button-sm.png';?>" border="0" name="submit" id="submit-donate" alt="DONATE" style="margin:0;padding:1px 3px;vertical-align:center;" align="center"/><?php 
		if(!$wdebug_mode){
			echo "\n";?><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"><?php
		}
		echo "\n";?>
		</form>
		<?php
	}?></li><?php
} //end wassup_donate_link

/**
 * Wassup's admin page manager - displays WassUp admin pages, displays tab menu links, does form updates and bulk deletes, performs maintenance, and displays admin messages to screen
 */
function WassUp() {
	global $wpdb,$wp_version,$current_user,$wassup_options,$wdebug_mode;
	$starttime = microtime_float();	//start script runtime
	//extend php script timeout..to 3 minutes
	if (!ini_get('safe_mode')) {	//TODO: safe_mode deprecated
		$stimeout=@ini_get('max_execution_time');
		if($stimeout<180) @set_time_limit(180+1);
	}
	$wassuppage=wassupURI::get_menu_arg();
	$wassupfolder=plugin_basename(WASSUPDIR);
	//New in v1.9: get/set user-specific wassup_settings
	if(!is_object($current_user) || empty($current_user->ID)) wp_get_current_user();
	$wassup_user_settings = get_user_option('_wassup_settings',$current_user->ID);
	$tab=0;
	$admin_message="";
	$wassup_table = $wassup_options->wassup_table;
	$wassup_tmp_table = $wassup_table."_tmp";
	//select for subsite in multisite/network-activated
	$multisite_whereis="";
	if(is_multisite() && !is_subdomain_install() && $wassup_options->network_activated_plugin()){
		$multisite_whereis=sprintf(" AND `subsite_id`=%d",$GLOBALS['current_blog']->blog_id);
	}
	// RUN THE DELETE/SAVE/RESET FORM OPTIONS 
	// Processed here so that any resulting "admin_message" or errors will display with page
	//DELETE NOW options...
	if(!empty($_POST) && ($wassuppage== "wassup-options" || $wassuppage == "wassup" || $wassuppage=="wassup-stats")){
	if($wassuppage=="wassup-options"){
	//New in v1.9/security fix: admin referer/wp nonce validation for form update and records delete
	if(current_user_can('manage_options') && wassupURI::is_valid_admin_referer('wassupsettings-'.$current_user->ID)){
	//v1.9 bugfix: added workaround code for Google Chrome's empty 'onclick=submit()' "delete NOW" value
	if((isset($_POST['delete_now']) || 
	    isset($_POST['do_delete_manual']) || 
	    isset($_POST['do_delete_auto']) || 
	    isset($_POST['do_delete_recid']) || 
	    isset($_POST['do_delete_empty'])) &&
	   !isset($_POST['submit-options']) && 
	   !isset($_POST['submit-options2']) && 
	   !isset($_POST['submit-options3']) &&
	   !isset($_POST['submit-options4']) &&
	   !isset($_POST['reset-to-default'])){
		$deleted=0;
	if (isset($_POST['do_delete_manual'])){
	if (!empty($_POST['delete_manual']) && $_POST['delete_manual'] !== "never") {
		$delete_filter = ""; 
		$do_delete=false;
		$timenow=current_time("timestamp");
		$to_date=@strtotime($_POST['delete_manual'],$timenow);
		if (is_numeric($to_date) && $to_date < $timenow) {
			if(!empty($_POST['delete_filter_manual'])){
			if($_POST['delete_filter_manual']!="all") {
				$delete_filter=$wassup_options->getFieldOptions("delete_filter","sql",esc_attr($_POST['delete_filter_manual']));
				if(!empty($delete_filter))$do_delete=true;
			}else{
				$do_delete=true;
			}
			}
			$delete_filter.= $multisite_whereis;
			if($do_delete){
				$deleted=$wpdb->query(sprintf("DELETE FROM %s WHERE `timestamp`<'%d' %s",$wassup_table,$to_date,$delete_filter));
			}
			if($wdebug_mode){
				echo "\n<!-- Delete Manual: ";
				echo "delete_filter=\$delete_filter";
				echo "\n -->";
			}
		}
	} //end if delete_manual
	}elseif(isset($_POST['do_delete_auto'])){
	if (!empty($_POST['delete_auto']) && $_POST['delete_auto'] !== "never") {
		$delete_filter = ""; 
		$do_delete=false;
		$wassup_options->delete_auto=esc_attr($_POST['delete_auto']);
		$wassup_options->delete_filter=esc_attr($_POST['delete_filter']);
		if($wassup_options->saveSettings())$admin_message = __("Wassup options updated successfully","wassup")."." ;
		$timenow=current_time("timestamp");
		$to_date=@strtotime($_POST['delete_auto'],$timenow);
		if (is_numeric($to_date)&& $to_date < $timenow) {
			if(!empty($_POST['delete_filter'])){
			if($_POST['delete_filter']!="all") {
				$delete_filter=$wassup_options->getFieldOptions("delete_filter","sql",esc_attr($_POST['delete_filter']));
				if(!empty($delete_filter))$do_delete=true;
			}else{
				$do_delete=true;
			}
			}
			$delete_filter .= $multisite_whereis;
			if($do_delete){
				$deleted=$wpdb->query(sprintf("DELETE FROM %s WHERE `timestamp`<'%d' %s",$wassup_table,$to_date,$delete_filter));
				//log daily delete time to prevent multiple auto deletes in 1 day
				if($deleted>0){
					$expire=time()+24*3600;
					$cache_id=wassupDb::update_wassupmeta($wassup_table,'_delete_auto',$timestamp,$expire);
				}
			}
			if($wdebug_mode){
				echo "\n<!-- Delete auto: ";
				echo "delete_filter=\$delete_filter";
				echo "\n -->";
			}
		} //end if numeric
	} //end if delete_auto
	}elseif(isset($_POST['do_delete_recid'])){
		//New in v1.9: Delete up to specific recid number
		if(!empty($_POST['delete_recid'])&& is_numeric($_POST['delete_recid'])){
			$delete_filter=$multisite_whereis;
			$delete_recid=(int)$_POST['delete_recid'];
			if($delete_recid >0){
				$deleted=$wpdb->query(sprintf("DELETE FROM $wassup_table WHERE `id`<=%d %s",$delete_recid,$delete_filter));
			}
		}
	}elseif (!empty($_POST['do_delete_empty'])) {
		$delete_filter=$multisite_whereis;
		if(!empty($delete_filter)){
			$deleted=$wpdb->query(sprintf("DELETE FROM %s WHERE `id`>0 %s",esc_attr($wassup_table),$delete_filter));
		}else{
			$deleted=$wpdb->query(sprintf("DELETE FROM %s",esc_attr($wassup_table)));
		}
	}else{
		$admin_message = __("Nothing to do! Check a \"Delete\" option and try again","wassup");
	}
		if ($deleted > 0) {
			$admin_message=sprintf(__("%d records DELETED permanently!","wassup"),$deleted);
			//clear table_status cache after delete
			$result=wassupDb::delete_wassupmeta("",$wassup_table,'_table_status');
			//New in v1.9: schedule table optimize after bulk delete
			if($deleted>250 && !empty($wassup_options->wassup_optimize)&& !isset($_POST['do_delete_empty'])){
				$last_week=current_time("timestamp")-7*24*3600;
				if($wassup_options->wassup_optimize>$last_week){
					$wassup_options->wassup_optimize=$last_week;
					$wassup_options->saveSettings();
				}
			}
		}
		if(empty($admin_message))
			$admin_message=__("0 records deleted!","wassup");
		$tab=3;
	} //end if delete_now
	if (!isset($_POST['delete_now'])) {
	if (isset($_POST['submit-options']) || 
	    isset($_POST['submit-options2']) || 
	    isset($_POST['submit-options3'])) {
		//New in v1.9/security fix: settings form entry are validated and saved in wassupOptions::saveFormChanges()
		$admin_message=$wassup_options->saveFormChanges();
		if(isset($_POST['submit-options']))$tab=1;
		if(isset($_POST['submit-options2']))$tab=2;
		if(isset($_POST['submit-options3']))$tab=3;
	} elseif (isset($_POST['submit-options4'])) {	//uninstall checkbox
		if (!empty($_POST['wassup_uninstall'])) {
			$wassup_options->wassup_uninstall="1";
			$wassup_options->wassup_active="0"; //disable recording now
		} else {
			$wassup_options->wassup_uninstall = "0";
		}
		if ($wassup_options->saveSettings()) {
			$admin_message = __("Wassup uninstall option updated successfully","wassup")."." ;
		}
		$tab=4;
	} elseif (isset($_POST['reset-to-default'])) {
		$wassup_options->loadDefaults();
		if ($wassup_options->saveSettings()) {
			$admin_message = __("Wassup options reset successfully","wassup")."." ;
			$wassup_user_settings=$wassup_options->resetUserSettings();
		}
	}
	} //end if !delete_now
	}else{
		$admin_message = __("Sorry! You're not allowed to do that.","wassup");
	} //end if current_user_can
	} //end if wassup_options
	if($wassuppage=="wassup" && isset($_POST['submit-spam'])){
		if(current_user_can('manage_options') && wassupURI::is_valid_admin_referer('wassupspam-'.$current_user->ID,$_GET['page'])){
			$wassup_options->wassup_spamcheck =(!empty($_POST['wassup_spamcheck'])?"1":"0");
			$wassup_options->wassup_spam=(!empty($_POST['wassup_spam'])?"1":"0");
			$wassup_options->wassup_refspam=(!empty($_POST['wassup_refspam'])?"1":"0");
			$wassup_options->wassup_hack=(!empty($_POST['wassup_hack'])?"1":"0");
			$wassup_options->wassup_attack=(!empty($_POST['wassup_attack'])?"1":"0");
			if ($wassup_options->saveSettings()) {
				$admin_message = __("Wassup spam options updated successfully","wassup")."." ;
			}
		}else{
			$admin_message = __("Sorry! You're not allowed to do that.","wassup");
		}
	}
	} //end if _POST
	//deleteMARKED processed here so admin messages will display
	if(($wassuppage == "wassup" || $wassuppage=="wassup-stats") && !empty($_GET['deleteMARKED']) && !empty($_GET['dip'])){
		// DELETE EVERY RECORD MARKED BY IP
		//do wp_nonce validation of deleteMarked
		if(current_user_can('manage_options') && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'],'wassupdelete-'.$current_user->ID)){
			$dip=$wassup_options->cleanFormText($_GET['dip']);
			$deleted=0;
			if(!empty($dip) && $dip == $wassup_user_settings['uip']){
				$to_date = current_time("timestamp");
				if(isset($_GET['last']) && is_numeric($_GET['last'])) $wlast=$_GET['last'];
				else $wlast = $wassup_user_settings['detail_time_period'];
				//delete within selected date range
				if($wlast == 0){
					$from_date="0";	//all time
				}else{
					$from_date=$to_date - (int)(($wlast*24)*3600);
					//extend start date to within a rounded time	
					if($wlast < .25) $from_date=((int)($from_date/60))*60;
					elseif($wlast < 7) $from_date=((int)($from_date/300))*300;
					elseif($wlast < 30) $from_date=((int)($from_date/1800))*1800;
					elseif($wlast < 365) $from_date=((int)($from_date/86400))*86400;
					else $from_date=((int)($from_date/604800))*604800;
				}
				$sql=sprintf("DELETE FROM $wassup_table WHERE `ip`='%s' AND `timestamp` BETWEEN '%d' AND '%d' %s",$dip,$from_date,$to_date,$multisite_whereis);
				$deleted=$wpdb->query($sql);
				if(is_wp_error($deleted)){
					$errno=$deleted->get_error_code();
					$error_msg=" deleteMARKED error#$errno ".$deleted->get_error_message()."\n SQL=".$sql;
					$deleted=$wpdb->rows_affected+0;
				}
			}
			$admin_message="";
			if(!empty($error_msg) && $wdebug_mode) $admin_message= $error_msg." ";
			$admin_message .= $deleted." ".__('records deleted','wassup');
		}else{
			$admin_message = __("Sorry! You're not allowed to delete records.","wassup");
		} //end if current_user_can
	} //end if deleteMarked
	//add a horizontal menu for easier menu navigation in WP 2.7+
	if (version_compare($wp_version, '2.7', '>=')) { 
		wassup_menu_links($wassuppage);
	}
	//#display an admin message or an alert.
	//..must be above "wassup-wrap" div, but below wassup menus
	if(empty($wassup_options->wassup_alert_message) && empty($wassup_user_settings['ualert_message'])){
		if(empty($admin_message)){
			if ($wassup_options->wassup_active!="1"){
				//display as a system message when not recording...
				$admin_message = __("WARNING: WassUp is NOT recording new statistics.","wassup")."  ".__("To collect visitor data you must check \"Enable statistics recording\" in \"WassUp-Options: General Setup\" tab","wassup");
			}elseif(is_multisite()){
				$network_settings=get_site_option('wassup_network_settings');
				if(!empty($network_settings) && empty($network_settings['wassup_active'])){
					$admin_message = __("WARNING: WassUp is NOT recording new statistics.","wassup")."  ".__("Contact your site administrator about enabling statistics recording.","wassup");
				}
			}
		}
		if(!empty($admin_message)){
			$wassup_options->wassup_alert_message=$admin_message;
			$wassup_options->saveSettings();
			do_action('admin_notices');
		}
	}
	if(is_network_admin()) do_action('network_admin_notices');?>
	<div id="wassup-wrap" class="wrap <?php echo $wassuppage;if(version_compare($wp_version,'2.3','<')) echo ' wassup-wp-legacy';?>">
		<div id="icon-plugins" class="icon32 wassup-icon"></div><?php
	// DISPLAY PAGE CONTENT
	if ($wdebug_mode) echo "\n<!--  wassup page=".$wassuppage." -->";
	//New in v1.9: separate action to display page contents that can be used by add-on modules
	if(has_action('wassup_page_content')){
		do_action('wassup_page_content',array('wassuppage'=>$wassuppage,'tab'=>$tab));
	}elseif($wassuppage=="wassup" || $wassuppage=="wassup-stats" || $wassuppage==$wassupfolder){?>
		<h2>WassUp - <?php _e("Latest hits", "wassup"); ?></h2><?php 
		wassup_page_contents(array('wassuppage'=>$wassuppage,'tab'=>$tab));
	}elseif ($wassuppage == "wassup-online"){?>
		<h2>WassUp - <?php _e("Current Visitors Online", "wassup"); ?></h2><?php
		wassup_page_contents(array('wassuppage'=>$wassuppage,'tab'=>$tab));
	}elseif ($wassuppage == "wassup-spia" || $wassuppage == "wassup-spy"){?>
		<h2>WassUp - <?php _e("SPY Visitors", "wassup"); ?></h2><?php
		wassup_page_contents(array('wassuppage'=>$wassuppage,'tab'=>$tab));
	}elseif ($wassuppage == "wassup-options"){?>
		<h2>WassUp - <?php _e('Options','wassup'); ?></h2><?php
		if (!function_exists('wassup_optionsView')) include_once(WASSUPDIR.'/lib/settings.php');
		wassup_optionsView($tab);
	}else{
		return;
	}
	// End calculating execution time of script
	$totaltime = sprintf("%8.8s",(microtime_float() - $starttime)); ?>
	<p><small><a href="http://www.wpwp.org" title="<?php _e('Donate','wassup');?>" target="_blank"><?php echo __("Donations are really welcome","wassup");?></a> | WassUp ver: <?php echo WASSUPVERSION.' <span class="separator">|</span> '.__("Check the official","wassup").' <a href="http://www.wpwp.org" target="_BLANK">WassUp</a> '.__("page for updates, bug reports and your hints to improve it","wassup").' <span class="separator">|</span> <a href="https://wordpress.org/support/plugin/wassup" title="'.__("WassUp Support","wassup").'">'.__("Wassup Support","wassup").'</a>'; ?>
	<nobr><span class="separator">|</span> <?php echo __('Exec time','wassup').": $totaltime"; ?></nobr></small></p>
	</div>	<!-- end wassup-wrap --><?php
} //end WassUp

/**
 * Display the contents of a page from Wassup's admin panel
 * @param string
 * @return none
 */
function wassup_page_contents($args=array()){
	global $wpdb, $wp_version, $current_user, $wassup_options, $wdebug_mode;
	if(!empty($args) && is_array($args)) extract($args);	
	if ($wdebug_mode) {
		$mode_reset=@ini_get('display_errors');
		//don't check for 'strict' php5 standards (part of E_ALL since PHP 5.4)
		if (defined('PHP_VERSION') && version_compare(PHP_VERSION, 5.4, '<'))
			@error_reporting(E_ALL);
		else
			@error_reporting(E_ALL ^ E_STRICT); //E_STRICT=php5 only
		@ini_set('display_errors','On');	//debug
		echo "\n<!-- *WassUp DEBUG On-->\n";
		echo "<!-- *normal setting: display_errors=$mode_reset ";
		echo " parameters=";
		if(is_array($args)) print_r($args);
		else echo $args;
		echo "-->\n";
	}
	//load additional wassup modules as needed
	if(!class_exists('WassupItems')){
		require_once(WASSUPDIR.'/lib/main.php');
		include_once(WASSUPDIR.'/lib/uadetector.class.php');
	}
	$stimer_start=time(); //start script timer, to avoid timeouts
	$stimeout=0;
	//extend php script timeout length for large datasets
	if (!ini_get('safe_mode')) {	//TODO: safe_mode deprecated
		$stimeout=@ini_get('max_execution_time');
		if($stimeout<180){ //extend php script timeout..
			@set_time_limit(180); 	//  ..to 3 minutes
			$stimeout=180;
		}
	}
	//for generating page link urls....
	$wpurl=wassupURI::get_wphome();
	$blogurl=wassupURI::get_sitehome();
	$wassup_options->loadSettings();	//needed in case "update_option is run elsewhere in wassup (widget)
	$wassup_table = $wassup_options->wassup_table;
	$wassup_tmp_table = $wassup_table."_tmp";
	//for subsite queries in multisite/network-activated setup
	$multisite_whereis="";
	if($wassup_options->network_activated_plugin()){
		if(!is_network_admin() && !empty($GLOBALS['current_blog']->blog_id)) $multisite_whereis = sprintf(" AND `subsite_id`=%s",$GLOBALS['current_blog']->blog_id);
	}
	//get custom wassup settings for current user
	if(empty($current_user->ID)) wp_get_current_user();
	$wassup_user_settings=get_user_option('_wassup_settings');
	$wnonce=(!empty($wassup_user_settings['unonce'])?$wassup_user_settings['unonce']:'');
	$show_avatars=get_option('show_avatars');
	if(!empty($show_avatars)) $show_avatars=true;
	else $show_avatars=false;
	//"action_param" are preassigned "GET" parameters used for "action.php" external/ajax calls like "top ten" 
	$action_param='&whash='.$wassup_options->whash;
	if ($wdebug_mode) {
		$action_param .= '&debug_mode=true';
	}
	//wpabspath param required for non-standard wp-content directory location
	if (defined('WP_CONTENT_DIR') && strpos(WP_CONTENT_DIR,ABSPATH)===FALSE) {
		$action_param .= '&wpabspath='.urlencode(base64_encode(ABSPATH));
	}

	$wassupfolder=plugin_basename(WASSUPDIR);
	if(empty($wassuppage))$wassuppage=wassupURI::get_menu_arg();
	if($_GET['page']=="wassup-stats" && isset($_GET['ml'])) $wassuppageurl='index.php?page=wassup-stats&ml='.$_GET['ml'];
	else $wassuppageurl='admin.php?page='.$_GET['page'];
	$expcol='
	<table width="100%" class="toggle"><tbody><tr>
		<td align="left" class="legend"><a href="#" class="toggle-all">'.__('Expand All','wassup').'</a></td>
	</tr></tbody></table>';
	$scrolltop='<div class="scrolltop"><a href="#wassup-wrap" onclick="wScrollTop();return false;">'.__("Top","wassup").'&uarr;</a></div>';

	//for stringShortener calculated values
	if (!empty($wassup_options->wassup_screen_res)) $screen_res_size = (int) $wassup_options->wassup_screen_res;
	else $screen_res_size = 800;
	$max_char_len = (int)($screen_res_size)/($screen_res_size*0.01);
	if(version_compare($wp_version,'2.7','<')|| (version_compare($wp_version,'3.1','>=')&& is_admin_bar_showing()===false)){
		//set larger chart size and screen_res when there is no admin sidebar
		$screen_res_size=$screen_res_size+160;
		$max_char_len=$max_char_len+16;
	}
	//for wassup chart size
	$res = (int)$wassup_options->wassup_screen_res;
	if(empty($res)) $res = $screen_res_size;
	if ($res < 800) $res=620;
	elseif ($res < 1024) $res=740;
	else $res=1000; //1000 is Google api's max chart width 

	// HERE IS THE VISITORS ONLINE VIEW
	if ($wassuppage == "wassup-online") {
		echo "\n";?>
		<p class="legend"><?php echo __("Legend", "wassup").': &nbsp; <span class="box-log">&nbsp;&nbsp;</span> '.__("Logged-in Users", "wassup").' &nbsp; <span class="box-aut">&nbsp;&nbsp;</span> '.__("Comment Authors", "wassup").' &nbsp; <span class="box-spider">&nbsp;&nbsp;</span> '.__("Spiders/bots", "wassup"); ?></p><br />
		<?php
		//New in v1.9: variable timeframes for online counts: spiders for 1 min, regular visitors for 3 minutes, logged-in users for 10 minutes
		$to_date = current_time('timestamp')-3;
		$from_date = $to_date - 10*60;	//-10 minute from timestamp for logged-in user counts
		$whereis=sprintf("`timestamp`>'%d' AND (`username`!='' OR `timestamp`>'%d' OR (`timestamp`>'%d' AND `spider`='')) %s",$from_date,$to_date - 1*60,$to_date - 3*60,$multisite_whereis);
		if($wdebug_mode) echo "\n<!--   Online whereis=$whereis -->";
		$currenttot=0;
		$currentlogged=0;
		$currentauth=0;
		$qryC=false;
		$TotOnline=New WassupItems($wassup_tmp_table,"","",$whereis);
		if(!empty($TotOnline->totrecords))
			$currenttot = $TotOnline->calc_tot("count",null,null,"DISTINCT");
		if ($currenttot > 0) {
			$currentlogged = $TotOnline->calc_tot("count",null,"AND `username`!=''","DISTINCT");
			$currentauth = $TotOnline->calc_tot("count",null,"AND `comment_author`!='' AND `username`=''","DISTINCT");
			$sql=sprintf("SELECT SQL_NO_CACHE `id`, wassup_id, count(wassup_id) as numurl, max(`timestamp`) as max_timestamp, `ip`, `hostname`, `searchengine`, `search`, `searchpage`, `urlrequested`, `referrer`, `agent`, `browser`, `spider`, `feed`, `os`, `screen_res`, GROUP_CONCAT(DISTINCT `username` ORDER BY `username` SEPARATOR ', ') AS login_name, `comment_author`, `language`, `spam` AS malware_type, `url_wpid` FROM $wassup_tmp_table WHERE %s GROUP BY `wassup_id` ORDER BY max_timestamp DESC",$whereis);
			$qryC=$wpdb->get_results($sql);
			if(!empty($qryC) && is_wp_error($qryC)){
				$errno=$qryC->get_error_code();
				$error_msg=" qryC error#$errno ".$qryC->get_error_message()."\n whereis=".esc_attr($whereis)."\n SQL=".esc_attr($sql);
				$qryC=false;
			}
		}
		//New in v1.9: online summary counts
	?><div class="centered"><div id="usage">
		<ul>
		<li><?php echo "<span>".(int)$currenttot."</span> ".__('Visitors online','wassup');?></li>
		<li><?php echo "<span>".(int)$currentlogged."</span> ".__('Logged-in Users','wassup');?></li>
		<li><?php echo "<span>".(int)$currentauth."</span> ".__('Comment authors','wassup');?></li>
		</ul>
	</div></div><?php
		if(!empty($qryC) && is_array($qryC)){
			echo "\n";?>
	<div id="onlineContainer" class="main-tabs"><?php
			print $expcol;
		foreach($qryC as $cv){
			if($wassup_options->wassup_time_format == 24) $timed=gmdate("H:i:s", $cv->max_timestamp);
			else $timed=gmdate("h:i:s a",$cv->max_timestamp);
			$ip=wassup_clientIP($cv->ip);
			if(empty($ip))$ip=__("unknown","wassup");
			if($cv->referrer != '' && stristr($cv->referrer,$wpurl)!=$cv->referrer){
				if($cv->searchengine == ""){
					$referrer=wassupURI::referrer_link($cv->referrer,$cv->urlrequested,$max_char_len,$cv->malware_type);
				}else{
					$referrer=wassupURI::se_link($cv->referrer,$max_char_len,$cv->malware_type);
				}
			} else { 
				$referrer=__("Direct hit", "wassup"); 
			} 
			$numurl=$cv->numurl;
			$Ousername="";
			$ulclass="users";
			$unclass="";
			// User is logged in or is a comment's author
			if($cv->login_name != "" || $cv->comment_author !=""){
				$utype="";
				$logged_user="";
				if($cv->login_name != ""){
					$logged_user=trim($cv->login_name,', ');
					if(strpos($logged_user,',')!==false){
						$loginnames=explode(',',$logged_user);
						foreach($loginnames AS $name){
							$logged_user=trim($name);
						if(!empty($logged_user)){
							break;
						}
						}
					}
					$utype=__("LOGGED IN USER","wassup");
					$ulclass = "userslogged";
					$udata=false;
					if(!empty($logged_user)) $udata=get_user_by("login",esc_attr($logged_user));
					if($udata!==false && $wassup_options->is_admin_login($udata)){
						$utype=__("ADMINISTRATOR","wassup");
						$ulclass .=" adminlogged";
					}
					if(!empty($udata->ID)){
						if($show_avatars) $Ousername='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.get_avatar($udata->ID,'20').' '.esc_attr($logged_user).'</strong></span></li>';
						else $Ousername='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.esc_attr($logged_user).'</strong></span></li>';
					}else{
						$Ousername='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.esc_attr($cv->login_name).'</strong></span></li>';
					}
					$unclass="sum-box-log";
				}
				if($cv->comment_author != ""){
					$Ousername .='<li class="users"><span class="indent-li-agent">'.__("COMMENT AUTHOR","wassup").': <strong>'.esc_attr($cv->comment_author).'</strong></span></li>';
					$ulclass = "users";
					if(empty($unclass)) $unclass="sum-box-aut";
				}
			}
			if(!empty($cv->spider)) $unclass="sum-box-spider";
			if(!empty($cv->malware_type)) $unclass="sum-box-spam";
			echo "\n";?>
		<div class="sum-rec"><?php
		// Visitor Record - raw data (hidden)
		$raw_div="raw-".substr($cv->wassup_id,0,25).rand(0,99);
		echo "\n";?>
		<div id="<?php echo $raw_div;?>" style="display:none;"><?php
			$args=array('numurl'=>$numurl,'rk'=>$cv);
			wassup_rawdataView($args);?>
		</div>
		<div class="sum-nav">
			<div class="sum-box">
				<span class="sum-box-ip <?php echo $unclass;?>"><?php if($numurl >1){ ?><a href="#" class="showhide" id="<?php echo (int)$cv->id;?>"><?php echo esc_attr($ip);?></a><?php }else{ echo esc_attr($ip);}?></span>
			</div>
			<div class="sum-det">
				<p class="delbut"><a href="#TB_inline?height=400&width=<?php echo $res.'&inlineId='.$raw_div;?>" class="thickbox"><img class="table-icon" src="<?php echo WASSUPURL.'/img/b_select.png" alt="'.__('show raw table','wassup').'" title="'.__('Show the items as raw table','wassup');?>" /></a></p>
				<span class="det1"> <?php echo wassupURI::url_link($cv->urlrequested,$max_char_len,$cv->malware_type);?> </span>
				<span class="det2"><strong><?php echo $timed;?> - </strong><?php echo $referrer;?></span>
			</div>
		</div>
		<div class="detail-data"><?php
		if(!empty($Ousername)){
			echo "\n";?>
		<ul class="<?php print $ulclass; ?>">
			<?php print $Ousername; ?>
		</ul>
<?php
		}
		if($numurl >1){ ?>
			<div style="display: none;" class="togglenavi navi<?php echo (int)$cv->id ?>">
				<ul class="url"><?php 
			$sql=sprintf("SELECT SQL_NO_CACHE `timestamp`, `urlrequested`, `spam` FROM $wassup_tmp_table WHERE `wassup_id`='%s' AND `timestamp`>'%d' %s ORDER BY `timestamp`",$cv->wassup_id,$from_date,$multisite_whereis);
			$qryCD=$wpdb->get_results($sql);
			if(!empty($qryCD) && is_wp_error($qryCD)){
				$errno=$qryCD->get_error_code();
				$error_msg=" qryCD error#$errno ".$qryCD->get_error_message()."\n SQL=$sql";
				$qryCD=false;
			}
			$i=1;
			if(!empty($qryCD) && is_array($qryCD)){
			foreach ($qryCD as $cd) {
				if ($wassup_options->wassup_time_format == 24) $time2 = '<span class="time">'.gmdate("H:i:s", $cd->timestamp).' </span>';
				else $time2 = '<span class="time">'.gmdate("h:i:s a", $cd->timestamp).'</span>';
				$num = ($i&1);
				if ($num == 0) $classodd = "urlodd";
				else  $classodd = "url";
				echo "\n";?>
			<li class="<?php echo $classodd.' navi'.(int)$cv->id; ?>"><span class="request-time"><?php echo $time2.' &rarr; ';?></span><span class="request-uri"><?php echo wassupURI::url_link($cd->urlrequested,$max_char_len,$cv->malware_type);?></span></li><?php
				$i++;
			} //end foreach qryCD
			} //end if qryCD
			echo "\n";?>
			</ul>
		</div>
<?php		} //end if numurl
		echo "\n";?>
		</div><!-- /detail-data -->
		</div><!-- /sum-rec --><?php
		} //end foreach qryC
		echo $expcol;
		} //end if currenttot
		echo "\n";?>
	</div><!-- /main-tabs -->
	<?php if(!empty($witemstot) && $witemstot >=10) echo $scrolltop;?>
<?php
	// HERE IS THE SPY MODE VIEW
	} elseif ($wassuppage=="wassup-spy" || $wassuppage=="wassup-spia"){
		//parameter to filter spy by visitor type
		if (isset($_GET['spiatype'])) {
			$spytype = $wassup_options->cleanFormText($_GET['spiatype']);
			$wassup_user_settings['spy_filter']=$spytype;
			update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		}elseif(!empty($wassup_user_settings['spy_filter'])){
			$spytype=$wassup_user_settings['spy_filter'];
		}elseif(!empty($wassup_options->wassup_default_spy_type)){
			$spytype=$wassup_options->wassup_default_spy_type;
		}else{
			$spytype=$wassup_options->wassup_default_type;
		}
		echo "\n";?>
	<p class="legend" style="padding:2px 0 0 5px; margin:0;"><?php echo __("Legend", "wassup").': &nbsp; <span class="box-log">&nbsp;&nbsp;</span> '.__("Logged-in Users", "wassup").' &nbsp; <span class="box-aut">&nbsp;&nbsp;</span> '.__("Comments Authors", "wassup").' &nbsp; <span class="box-spider">&nbsp;&nbsp;</span> '.__("Spiders/bots", "wassup"); ?></p>
	<form id="spy-opts-form">
	<table class="legend"><tbody>
	<tr><td align="left" width="150">
		<span id="spy-pause"><a href="#?" onclick="return pauseSpy();"><?php _e("Pause", "wassup"); ?></a></span>
		<span id="spy-play"><a href="#?" onclick="return playSpy();"><?php _e("Play", "wassup"); ?></a></span>
	</td><td align="right" width="105"><?php
		if(!empty($_GET['map'])){
			$wassup_user_settings['spy_map']=1;
			update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		}elseif(isset($_GET['map'])){
			$wassup_user_settings['spy_map']=0;
			update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		}
		if(empty($wassup_user_settings['spy_map'])){
			echo "\n";?>
			<span style="text-align:right"><a href="<?php if(is_multisite() && is_network_admin()) echo network_admin_url($wassuppageurl.'&map=1'); else echo admin_url($wassuppageurl.'&map=1');?>" class="icon"><img src="<?php echo WASSUPURL.'/img/map_add.png" alt="'.__('Show map','wassup').'" title="'.__('Show ip geo location on map','wassup'); ?>"/></a> <a href="<?php if(is_multisite() && is_network_admin()) echo network_admin_url($wassuppageurl.'&map=1'); else echo admin_url($wassuppageurl.'&map=1');?>"><?php _e("Show map","wassup");?></a></span> <span class="separator">|</span><?php
		}
		//filter by type of visitor (wassup_default_spy_type)
		$selected=$spytype;
		if(is_multisite() && is_network_admin()) $optionargs=network_admin_url($wassuppageurl).'&spiatype='; else $optionargs=admin_url($wassuppageurl).'&spiatype=';
		echo "\n";?>
		<span class="spy-opt-right"><?php _e('Spy items by','wassup'); ?>: 
		<select name="navi" onChange="wassupReload<?php echo $wnonce;?>(this.options[this.selectedIndex].value);"><?php
		$wassup_options->showFieldOptions("wassup_default_spy_type","$selected","$optionargs");?>
		</select> &nbsp;</span>
	</td></tr>
	</tbody></table>
	</form><?php
		//New in v1.9: set map's initial center from Wordpress' timezone location
		if(!empty($wassup_user_settings['spy_map'])){
			//get the initial center position for map 
			$tz_name=get_option('timezone_string');
			if(!empty($tz_name)){
			if(stristr($tz_name,'America/')!==false)$pos="37,-97";
			elseif(stristr($tz_name,'Africa/')!==false)$pos="0,0";
			elseif(stristr($tz_name,'Asia/')!==false)$pos="31,121";
			elseif(stristr($tz_name,'Australia/')!==false)$pos="-27.4,153";
			elseif(stristr($tz_name,'Europe/')!==false)$pos="45.5,9.4";
			elseif(stristr($tz_name,'Indian/')!==false)$pos="28.6,77";
			elseif(stristr($tz_name,'Pacific/')!==false)$pos="21,-158";
			}
			//...or set default center position to either USA or Europe, depending on Wordpress "date" format
			if(empty($pos)){
				$pos="37,-97"; //center is USA
				//center is Europe
				if(!$wassup_options->is_USAdate())$pos="45.5,9.4";
			}
			echo "\n";?>
	<div id="map_placeholder" class="placeholder">
		<div id="spia_map" style="width:90%;height:370px;"></div>
	</div>
	<?php
			echo '<script type="text/javascript">wassupMapinit(\'spia_map\','.$pos.');</script>';
		} //end if spy_map
		echo "\n";?>
	<div id="spyContainer"><?php
		//display last few hits here
		$to_date=current_time('timestamp');
		$from_date=($to_date - 24*(60*60)); //display last 10 visits in 24 hours...
		wassup_spiaView($from_date,0,$spytype,$wassup_table); ?>
	</div><!-- /spyContainer -->
	<?php echo $scrolltop;?>
	<br />
<?php
	// HERE IS THE MAIN/DETAILS VIEW
	}elseif ($wassuppage=="wassup" || $wassuppage==$wassupfolder || $wassuppage=="wassup-stats"){
		if ($wassup_options->wassup_active != 1) { ?>
			<p style="color:red; font-weight:bold;"><?php _e("WassUp recording is disabled", "wassup"); ?></p><?php
		}
		if($_GET['page']=="wassup-stats"){
			if(is_multisite() && is_network_admin()) $pagination_url=network_admin_url("index.php?page=wassup-stats"); else $pagination_url=admin_url("index.php?page=wassup-stats");
		}else{
			if(is_multisite() && is_network_admin()) $pagination_url=network_admin_url("admin.php?page=$wassupfolder"); else $pagination_url=admin_url("admin.php?page=$wassupfolder");
		}
		$remove_it=array(); //for GET param cleanup
		$stickyFilters=""; //filters that remain in effect after page reloads
		//## GET parameters that can change user settings
		if (isset($_GET['chart'])) { // [0|1] only
			if ($_GET['chart'] == 0) {
				$wassup_user_settings['detail_chart']=0;
			} elseif ($_GET['chart'] == 1) {
				$wassup_user_settings['detail_chart']=1;
			}
			$remove_it[]='/&chart=[^&]+/';
		}
		//## GET params that filter detail display
		//
		//# Filter detail list by IP address
		//Get the current marked IP, if set
		$wip="";
		$dip="";
		if (isset($_GET['mark'])) { // [0|1] only
			if ($_GET['mark'] == 0) {
				$wassup_user_settings['umark']="0";
				$wassup_user_settings['uip'] = "";
				$remove_it[]='/&wip=[^&]+/';
				$wip="";
			}elseif (isset($_GET['wip'])){
				$wassup_user_settings['umark'] = "1";
				$wip=$wassup_options->cleanFormText($_GET['wip']);
				$wassup_user_settings['uip']=$wip;
			}
			$remove_it[]='/&mark=[^&]+/';
		}elseif (isset($_GET['wip'])){
			$wassup_user_settings['umark'] = "1";
			$wip=$wassup_options->cleanFormText($_GET['wip']);
		}elseif(!empty($wassup_user_settings['umark'])){
			//v1.9 bugfix: clear wmark setting when 'mark' and 'wip' are not on query string (visitor detail)
			$wassup_user_settings['umark']="0";
			$wassup_user_settings['uip'] = "";
		}
		//# Filter detail list by date range...
		$to_date = current_time("timestamp");	//wordpress time function
		if (isset($_GET['last']) && is_numeric($_GET['last'])) { 
			$wlast = $_GET['last'];
		} else {
			$wlast = $wassup_user_settings['detail_time_period']; 
		}
		if ($wlast == 0) {
			$from_date = "0";	//all time
		} else {
			$from_date = $to_date - (int)(($wlast*24)*3600);
			//extend start date to within a rounded time	
			if ($wlast < .25) { 	//start on 1 minute
				$from_date = ((int)($from_date/60))*60;
			} elseif ($wlast < 7) {
				$from_date = ((int)($from_date/300))*300;
			} elseif ($wlast < 30) {
				$from_date = ((int)($from_date/1800))*1800;
			} elseif ($wlast < 365) {
				$from_date = ((int)($from_date/86400))*86400;
			} else {
				$from_date = ((int)($from_date/604800))*604800;
			}
		}
		//# Filter detail lists by visitor type...
		if (isset($_GET['type'])) {
			$wtype = $wassup_options->cleanFormText($_GET['type']);
		} else {
			$wtype = $wassup_user_settings['detail_filter'];
		}
		//Show a specific page and number of items per page...
		$witems = (int)$wassup_user_settings['detail_limit'];
		if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
			$witems = (int)$_GET['limit']; 
			if ($witems >0 && $witems != (int)$wassup_user_settings['detail_limit']) $wassup_user_settings['detail_limit']=$witems;
		}
		if ((int)$witems < 1 ) { $witems = 10; }
		// current page and items per page as limit
		if (isset($_GET['pp']) && is_numeric($_GET['pp'])) {
			$wpages = (int)$_GET['pp'];
		} else {
			$wpages = 1;
		}
		if ( $wpages > 1 ) {
			$wlimit = " LIMIT ".(($wpages-1)*$witems).",$witems";
		} else {
			$wlimit = " LIMIT $witems";
		}
		// Filter detail lists by a searched item
		if(!empty($_GET['search'])){
			$wsearch=$wassup_options->cleanFormText($_GET['search']);
		}else{
			$wsearch="";
			//remove blank search parameter
			if(isset($_GET['search'])) $remove_it[]='/&search=[^&]+/';
		}
		if(isset($_GET['submit-search'])) $remove_it[]='/&submit\-search=[^&]+/';
		//for clean up of deleted info from query string
		if (isset($_GET['deleteMARKED'])) {
			$remove_it[]='/&deleteMARKED=[^&]+/';
			$remove_it[]='/&dip=[^&]+/';
			if(isset($_GET['dip'])) $dip=$wassup_options->cleanFormText($_GET['dip']);
			if(!empty($dip)){
				if($dip == $wip){
					$remove_it[]='/&wip=[^&]+/';
					$wip="";
				}
				if($dip == $wsearch){
					$remove_it[]='/&search=[^&]+/';
					$wsearch="";
				}
			}
		}elseif(isset($_GET['dip'])){
			$remove_it[]='/&dip=[^&]+/';
		}
		//for adding sticky filters to query string
		//new in v1.9: 'wip' added to sticky filters
		if(!empty($wip))$stickyFilters.='&wip='.$wip;
		if(isset($wlast))$stickyFilters.='&last='.$wlast;
		if(!empty($wtype))$stickyFilters.='&type='.$wtype;
		if(!empty($wsearch))$stickyFilters .='&search='.$wsearch;
		//new in v1.9: wwhereis clause is parameter for 'wassupItems' and all calculations
		$wwhereis=$multisite_whereis;
		if (!empty($wtype) && $wtype != 'everything') {
			$wwhereis.=$wassup_options->getFieldOptions("wassup_default_type","sql",$wtype);
		}
		//new in v1.9: wwhereis clause contains marked-ip search when user selects "filter by IP" option
		if(!empty($wip) && $wip == $wsearch && empty($_GET['deleteMARKED'])){
			$wwhereis.=" AND `ip`='$wip'";
		}
		//$wassup_options->saveSettings();
		update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		//to prevent browser timeouts, send <!--heartbeat--> output
		echo "<!--heartbeat-->\n";
		// Instantiate class to count items
		$wTot = New WassupItems($wassup_table,$from_date,$to_date,$wwhereis,$wlimit);
		$wTot->WpUrl=$wpurl;
		$witemstot=0;
		$wpagestot=0;
		$wspamtot=0;
		$markedtot=0;
		$searchtot=0;
		$wmain=array();
		$ipsearch="";
		//don't apply "search" for marked ip (in whereis)
		if(!empty($wsearch) && $wsearch==$wip){
			$ipsearch=$wsearch;
			$wsearch="";
		}
		echo "\n<!--heartbeat-->";
		// MAIN QUERY
		if(!empty($wTot->totrecords)){
			$wmain=$wTot->calc_tot("main",$wsearch);
			echo "\n<!--heartbeat-->";
			$witemstot=$wTot->calc_tot("count",$wsearch,null,"DISTINCT");
			echo "\n<!--heartbeat-->";
			if(!empty($wsearch))$wpagestot=$wTot->calc_tot("count",$wsearch);
			else $wpagestot=(int)$wTot->totrecords;
			echo "\n<!--heartbeat-->";
			$wspamtot=$wTot->calc_tot("count",$wsearch,"AND `spam`>'0'");
			// Check if some records were marked
			if (!empty($wip)){
				//New in v1.9: avoid redundant calculations when search and mark/wip are the same
				if (empty($ipsearch)){
					echo "\n<!--heartbeat-->";
					$markedtot=$wTot->calc_tot("count",$wsearch," AND `ip`='".$wip."'","DISTINCT");
				}else{
					$markedtot=$witemstot;
				}
			}
			// Check if some records were searched
			if(!empty($wsearch)) {
				//v1.9 bugfix: searchtot is the same query as witemstot above and shouldn't be re-calculated (visitor detail fix)
				//$searchtot=$wTot->calc_tot("count",$wsearch,null,"DISTINCT");
				$searchtot=$witemstot;
			}elseif(!empty($ipsearch)){
				$searchtot=$markedtot;
			}
		}
		if(!empty($ipsearch))$wsearch=$ipsearch;
		//Clear non-sticky filter parameters from URL before applying new filters 
		$URLQuery=trim(html_entity_decode($_SERVER['QUERY_STRING']));
		if(empty($URLQuery) && preg_match('/[^\?]+\?([A-Za-z\-_]+.*)/',html_entity_decode($_SERVER['REQUEST_URI']),$pcs)>0) $URLQuery=$pcs[1];
		//v1.9 bugfix: replaced 'str_replace' with 'preg_replace' because 'str_replace' did unexpected substitutions that could trigger a 500 configuration error when query_string was loaded (this fix is also in 'wSelfRefresh' javascript function) (visitor detail fix)
		if(!empty($remove_it)){
			$newQuery=preg_replace($remove_it,"",$URLQuery);
			$URLQuery=$newQuery;
		}?>
	<form id="detail-opts-form">
		<table class="legend"><tbody>
		<tr><td align="left"> &nbsp; </td><td class="legend" align="left"><?php
		//selectable filter by date range
		$selected=$wlast;
		$new_last=preg_replace(array('/&last=[^&]+/','/&pp=[^&]+/'),'',$URLQuery);
		_e('Show details from the last','wassup');?>:
		<select name="last" onChange="wassupReload<?php echo $wnonce;?>(this.options[this.selectedIndex].value);"><?php 
		$optionargs=esc_attr("?".$new_last."&last=");
		$wassup_options->showFieldOptions("wassup_time_period","$selected","$optionargs");
		echo "\n";?>
		</select><?php
		if($wdebug_mode){
			echo "\n<!-- \$new_last=$new_last   \$optionargs=$optionargs -->\n";
		}?></td>
		<td class="legend" align="right"><?php _e('Items per page','wassup'); ?>: <select name="navi" onChange="wassupReload<?php echo $wnonce;?>(this.options[this.selectedIndex].value);"><?php
		//selectable filter by number of items on page
		$selected=$witems;
		$new_limit = preg_replace(array('/&pp=[^&]+/','/&limit=[^&]+/'),'',$URLQuery);
		$optionargs=esc_attr("?".$new_limit."&limit=");
		$wassup_options->showFieldOptions("wassup_default_limit","$selected","$optionargs");
		echo "\n";?>
		</select><span class="separator">|</span>
		<?php
		//selectable filter by type of visitor
		_e('Filter items for','wassup');?>: <select name="type" onChange="wassupReload<?php echo $wnonce;?>(this.options[this.selectedIndex].value);"> <?php
		$selected=$wtype;
		$new_type=preg_replace(array('/&pp=[^&]+/','/&type=[^&]+/'),"",$URLQuery);
		$optionargs=esc_attr("?".$new_type."&type=");
		$wassup_options->showFieldOptions("wassup_default_type","$selected","$optionargs");
		echo "\n";?>
		</select>
		</td></tr>
		</tbody></table>
	</form><?php
		// Print Site Usage summary
		echo "\n";?>
	<div class='centered'>
		<div id='usage'>
			<ul><li><span style="border-bottom:2px solid #0077CC;"><?php echo (int)$witemstot;?></span> <?php _e('Visits','wassup');?></li><li><span style="border-bottom:2px dashed #FF6D06;"><?php echo (int)$wpagestot;?></span> <?php _e('Pageviews','wassup');?></li><li><span><?php echo @number_format(($wpagestot/$witemstot),2);?></span> <?php _e('Pages/Visits','wassup');?></li><li><span class="spamtoggle"><?php
		//add spam form overlay when spamcheck is enabled and user is admin or can 'manage_options'
		$hidden_spam_form=false;
		if($wassup_options->wassup_spamcheck == 1 && ($wassup_options->is_admin_login() || current_user_can('manage_options'))) $hidden_spam_form=true;
		if($hidden_spam_form) echo '<a href="#TB_inline?width=400&inlineId=hiddenspam" class="thickbox">';
		echo $wspamtot.'<span class="plaintext">(';
		if(!empty($wspamtot))echo @number_format(($wspamtot*100/$wpagestot),1);else echo "0";
		echo '%)</span>';
		if($hidden_spam_form) echo '</a>';
		echo '</span> '.__('Spams','wassup');?></li></ul><br/>
			<div id="chart_placeholder" class="placeholder" align="center"></div>
		</div>
	</div><?php 
		// Page breakdown
		// paginate only when total records > items per page
		if ($witemstot > $witems) {
			$p=new wassup_pagination();
			$p->items($witemstot);
			$p->limit($witems);
			$p->currentPage($wpages);
			$p->target($pagination_url.$stickyFilters);
			echo "<!--heartbeat-->\n";
			$p->calculate();
			$p->adjacents(5);
		}
		$checked='checked="CHECKED"';
		// hidden spam options
		if($hidden_spam_form){
			echo "\n";?>
	<div id="hiddenspam" style="display:none;">
		<h2><?php _e('Spam/Malware Options','wassup'); ?></h2>
		<form id="hiddenspam-form" action="" method="post">
		<?php
		//New in v1.9: wp_nonce field added to spam form for referer validation and security
		wp_nonce_field('wassupspam-'.$current_user->ID);
		echo "\n";?>
		<p><input type="checkbox" name="wassup_spamcheck" value="1" <?php if($wassup_options->wassup_spamcheck==1)echo $checked;?>/> <strong><?php _e('Enable Spam and Malware Check on Records','wassup');?></strong></p>
		<p class="indent-opt"><input type="checkbox" name="wassup_spam" value="1" <?php if($wassup_options->wassup_spam==1)echo $checked;?>/> <?php _e('Record Akismet comment spam attempts','wassup');?></p>
		<p class="indent-opt"><input type="checkbox" name="wassup_refspam" value="1" <?php if($wassup_options->wassup_refspam==1)echo $checked;?>/> <?php _e('Record referrer spam attempts','wassup');?></p>
		<p class="indent-opt"><input type="checkbox" name="wassup_attack" value="1" <?php if($wassup_options->wassup_attack==1)echo $checked;?>/> <?php _e("Record attack/exploit attempts (libwww-perl agent)","wassup");?></p>
		<p class="indent-opt"><input type="checkbox" name="wassup_hack" value="1" <?php if($wassup_options->wassup_hack==1)echo $checked;?>/> <?php _e("Record admin break-in/hacker attempts","wassup");?></p>
		<p><input type="submit" name="submit-spam" value="<?php _e('Save Settings','wassup'); ?>" /></p>
		</form>
	</div> <!-- /hiddenspam --><?php
		}
		echo "\n";?>
	<table class="legend"><tbody><tr>
	<td align="left" width="28">
		<a href="#" onclick='wSelfRefresh();'><img src="<?php echo WASSUPURL; ?>/img/reload.png" id="refresh" class="icon" alt="<?php echo __('refresh screen','wassup').'" title="'.__('refresh screen','wassup');?>" /></a></td>
	<td class="legend" align="left"><?php 
		echo sprintf(__('Auto refresh in %s seconds','wassup'),'<span id="CountDownPanel">---</span>');?></td>
	<td align="right" class="legend"><?php
		echo "\n";
		//chart options
		if($wassup_user_settings['detail_chart'] == "1"){?>
		<a href="?<?php echo esc_attr($URLQuery.'&chart=0');?>" class="icon"><img src="<?php echo WASSUPURL.'/img/chart_delete.png" class="icon" alt="'.__('hide chart','wassup').'" title="'.__('Hide the chart','wassup');?>"/></a><a href="?<?php echo esc_attr($URLQuery.'&chart=0');?>"><?php _e("Hide chart","wassup");?></a><?php 
		}else{?>
		<a href="?<?php echo esc_attr($URLQuery.'&chart=1');?>" class="icon"><img src="<?php echo WASSUPURL.'/img/chart_add.png" alt="'.__('show chart','wassup').'" title="'.__('Show the chart','wassup'); ?>"/></a><a href="?<?php echo esc_attr($URLQuery.'&chart=1');?>"><?php _e("Show chart","wassup");?></a><?php
		}?> <span class="separator">|</span>
		<?php
		$wdformat = get_option("date_format");
		if(($to_date - $from_date)>24*3600) $stats_range=gmdate("$wdformat",$from_date)." - ".gmdate("$wdformat",$to_date);
		else $stats_range=gmdate("$wdformat H:00",$from_date)." - ".gmdate("$wdformat H:00",$to_date);
		$statsurl=WASSUPURL.'/lib/action.php?action=Topstats&from_date='.$from_date.'&to_date='.$to_date.$action_param;
		?> <a id="topstats_win" href="<?php echo $statsurl.'&KeepThis=true&height=400&width='.($res+250).'" class="thickbox" title="'.sprintf(__('Top Stats for %s','wassup'),$stats_range);?>"><?php _e('Show top stats','wassup');?></a> <?php
		//New in v1.9: top stats popup window selection
		?><a id="topstats_popup" class="icon" onclick="window.open('<?php echo $statsurl.'&popup=1\',\'topstats-popup\',\'height=400,width='.($res+250).',left=100,top=50,status=1,scrollbars=1,location=0,toolbar=0,statusbar=0,menubar=0';?>');return false;" href="#" title="<?php echo sprintf(__('Top stats for %s in popup','wassup'),$stats_range);?>"><img src="<?php echo WASSUPURL; ?>/img/popup.png" alt="" title="Top Stats in popup window" /></a> <span class="separator">|</span> 
		<a href="#" class='show-search'><?php 
		if(!empty($wsearch)) _e('Hide Search','wassup'); 
		else _e('Search','wassup');?></a>
	</td></tr>
	<tr><td align="left" class="legend" colspan="2"><?php
		//Searched items
		if (!empty($wsearch)) { 
			echo sprintf(__('%s matches found for search','wassup'),'<strong>'.(int)$searchtot.'</strong>').": <strong>$wsearch</strong><br/>";
		}
		// Marked items
		if($wassup_user_settings['umark']==1){
			echo sprintf(__("%s items marked for IP","wassup"),'<strong>'.(int)$markedtot.'</strong>').": <strong>$wip</strong>";
			if(empty($wsearch)){?> <span class="separator">|</span> <a href="?<?php echo esc_attr(preg_replace('/&pp=[^&]+/','',$URLQuery)."&search=".$wip).'" title="'.__("Filter by marked IP","wassup");?>"><?php _e("Filter by marked IP","wassup");?></a><?php }
		}
		//Search form
		?></td>
	<td align="right" class="legend">
		<div class="search-ip" <?php if (empty($wsearch)) echo 'style="display: none;"'; ?>>
		<form id="wassup-ip-search" class="wassup-search" action="" method="get">
		<input type="hidden" name="page" value="<?php echo $wassupfolder; ?>" /><?php
		if (!empty($stickyFilters)) {
			$wfilterargs=wGetQueryVars(preg_replace(array('/&type=[^&]+/','/&wip=[^&]+/'),"",$stickyFilters));
			if (!empty($wfilterargs) && is_array($wfilterargs)) {
				foreach($wfilterargs AS $fkey=>$fval){
					echo "\n"; ?>
		<input type="hidden" name="<?php echo $fkey.'" value="'.$fval; ?>" /><?php
				}
			}
		}
		echo "\n"; ?>
		<input type="text" size="25" name="search" value="<?php echo esc_attr($wsearch);?>"/><input type="submit" name="submit-search" value="<?php echo __('Search');?>" class="button button-secondary wassup-button"/>
		</form>
		</div> <!-- /search-ip -->
	</td></tr>
	</tbody></table>
	<div id="detailContainer" class="main-tabs"><?php
		$expcol = '
	<table width="100%" class="toggle"><tbody><tr>
		<td align="left" class="legend"><a href="#" class="toggle-all">'.__('Expand All','wassup').'</a></td>
		<td align="right" class="legend"><a href="#" class="toggle-allcrono">'.__('Collapse Chronology','wassup').'</a></td>
	</tr></tbody></table>';
		echo $expcol;
	//show pagination
	if ($witemstot > $witems) {
		echo "\n";?>
		<div id="pag" align="center"><?php $p->show();?></div><?php
 	}
	//# Detailed List of Wassup Records...
	if($witemstot>0 && is_array($wmain)&& count($wmain)>0){
		$rkcount=0;
	foreach($wmain as $rk){
		//New in v1.9: monitor for script timeout limit and extend, if needed
		if((time()-$stimer_start)>$stimeout-15){
			//extend timeout if more than 90% done
			if(($rkcount/$witems)*100 >90){
				@set_time_limit($stimeout);
				$stimer_start=time();
			}else{ //report is hung, so terminate here
				break;
			}
		}
		$rkcount++;
		$dateF = gmdate("d M Y", $rk->max_timestamp);
		if ($wassup_options->wassup_time_format == 24) {
			$datetimeF = gmdate('Y-m-d H:i:s', $rk->max_timestamp);
			$timeF = gmdate("H:i:s", $rk->max_timestamp);
		} else {
			$datetimeF = gmdate('Y-m-d h:i:s a', $rk->max_timestamp);
			$timeF = gmdate("h:i:s a", $rk->max_timestamp);
		}
		$ip=wassup_clientIP($rk->ip);
		if ($rk->hostname != "" && $rk->hostname !="unknown") $hostname = $rk->hostname; 
		else $hostname = __("unknown");
		$numurl = (int)$rk->page_hits;
		$unclass="";
		$ulclass="users";
		$Ouser="";
		$Ospider="";
		//for logged-in user/administrator in ul list
		if($rk->login_name != ""){
			$logged_user=trim($rk->login_name,', ');
			if(strpos($logged_user,',')!==false){
				$loginnames=explode(',',$logged_user);
				foreach($loginnames AS $name){
					$logged_user=trim($name);
					if(!empty($logged_user)){
						break;
					}
				}
			}
			$utype=__("LOGGED IN USER","wassup");
			$ulclass="userslogged";
			$udata=false;
			//check for administrator
			if(!empty($logged_user)){
				$udata=get_user_by("login",esc_attr($logged_user));
				if($wassup_options->is_admin_login($udata)){
					$utype = __("ADMINISTRATOR","wassup");
					$ulclass .= " adminlogged";
				}
			}
			if(!empty($udata->ID)){
				if($show_avatars) $Ouser='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.get_avatar($udata->ID,'20').' '.esc_attr($logged_user).'</strong></span></li>';
				else $Ouser='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.esc_attr($logged_user).'</strong></span></li>';
			}else{
				$Ouser='<li class="users"><span class="indent-li-agent">'.$utype.': <strong>'.esc_attr($rk->login_name).'</strong></span></li>';
			}
			$unclass="sum-box-log";
			if($wdebug_mode){
				if (!empty($udata->roles)){
					echo "\n <!-- udata-roles=\c";
					print_r($udata->roles);
					echo "\n -->";
				}
			}
		}
		//for comment author in ul list
		if($rk->comment_author != ""){
			$Ouser .='<li class="users"><span class="indent-li-agent">'.__("COMMENT AUTHOR","wassup").': <strong>'.esc_attr($rk->comment_author).'</strong></span></li>';
			if(empty($unclass)) $unclass="sum-box-aut";
		}
		//for spider/feed in ul list
		if(!empty($rk->spider)){
			if($rk->feed != ""){
				$Ospider='<li class="feed"><span class="indent-li-agent">'.__("FEEDREADER","wassup").': <strong><a href="#" class="toggleagent" id="'.(int)$rk->id.'">'.esc_attr($rk->spider).'</a></strong></span></li>';
				if(is_numeric($rk->feed)){
					$Ospider .='<li class="feed"><span class="indent-li-agent">'.__("SUBSCRIBER(S)","wassup").': <strong>'.(int)$rk->feed.'</strong></span></li>';
				}
			}else{
				$Ospider='<li class="spider"><span class="indent-li-agent">'.__("SPIDER","wassup").': <strong><a href="#" class="toggleagent" id="'.(int)$rk->id.'">'.esc_attr($rk->spider).'</a></strong></span></li>';
			}
			$unclass="sum-box-spider";
		}
		//for spam in ul list
		if(!empty($rk->malware_type)){
			$unclass="sum-box-spam";
		}
		echo "\n";?>
	<div id="delID<?php echo esc_attr($rk->wassup_id);?>" class="sum-rec <?php if($wassup_user_settings['umark']==1 && $wassup_user_settings['uip']==$ip) echo 'sum-mark';?>"> <?php
		// Visitor Record - raw data (hidden)
		$raw_div="raw-".substr($rk->wassup_id,0,25).rand(0,99);
		echo "\n"; ?>
		<div id="<?php echo $raw_div;?>" style="display:none;"><?php
			$args=array('numurl'=>$numurl,'rk'=>$rk);
			wassup_rawdataView($args);?>
		</div>
		<div class="sum-nav<?php if ($wassup_user_settings['umark']==1 && $wassup_user_settings['uip']==$ip) echo ' sum-nav-mark';?>">
			<div class="sum-box">
				<span class="sum-box-ip <?php echo $unclass;?>"><?php if($numurl >1){ ?><a href="#" class="showhide" id="<?php echo (int)$rk->id;?>"><?php echo esc_attr($ip);?></a><?php }else{ echo esc_attr($ip);}?></span>
				<span class="sum-date"><?php print $datetimeF; ?></span>
			</div>
			<div class="sum-det">
				<p class="delbut"><?php
		// Mark/Unmark IP
		echo "\n";
		$deleteurl="";
		if($wassup_user_settings['umark']==1 && $wassup_user_settings['uip']==$ip){
			if(is_multisite() && is_network_admin()){
				$deleteurl=wp_nonce_url(network_admin_url('admin.php?'.$URLQuery.'&deleteMARKED=1&dip='.$ip),'wassupdelete-'.$current_user->ID);
			}elseif(current_user_can('manage_options')){
				$deleteurl=wp_nonce_url(admin_url('admin.php?'.$URLQuery.'&deleteMARKED=1&dip='.$ip),'wassupdelete-'.$current_user->ID);
			}
			if(!empty($deleteurl)){?>
					<a href="<?php echo $deleteurl;?>" class="deleteIP"><img class="delete-icon" src="<?php echo WASSUPURL.'/img/b_delete.png" alt="'.__('delete','wassup').'" title="'.__('Delete ALL marked records with this IP','wassup');?>"/></a><?php
			}?>
					<a href="?<?php echo esc_attr($URLQuery.'&mark=0');?>"><img class="unmark-icon" src="<?php echo WASSUPURL.'/img/error_delete.png" alt="'.__('unmark','wassup').'" title="'.__('UnMark IP','wassup');?>"/></a><?php
		}else{
			if(current_user_can('manage_options')){?>
					<a href="#" class="deleteID" id="<?php echo esc_attr($rk->wassup_id);?>"><img class="delete-icon" src="<?php echo WASSUPURL.'/img/b_delete.png" alt="'.__('delete','wassup').'" title="'.__('Delete this record','wassup');?>"/></a><?php
			}?>
					<a href="?<?php echo esc_attr($URLQuery.'&mark=1&wip='.$ip);?>"><img class="mark-icon" src="<?php echo WASSUPURL.'/img/error_add.png" alt="'.__('mark','wassup').'" title="'.__('Mark IP','wassup');?>"/></a><?php
		}
		echo "\n";?>
					<a href="#TB_inline?height=400&width=<?php echo $res.'&inlineId='.$raw_div; ?>" class="thickbox"><img class="table-icon" src="<?php echo WASSUPURL.'/img/b_select.png" alt="'.__('show raw table','wassup').'" title="'.__('Show the items as raw table','wassup'); ?>" /></a>
				</p>
				<span class="det1"><?php
			$char_len=round($max_char_len*.9,0);
			echo wassupURI::url_link($rk->urlrequested,$char_len,$rk->malware_type);?></span>
				<span class="det2"><strong><?php
			_e('Referrer','wassup');
			if(empty($rk->searchengine)) $referrer=wassupURI::referrer_link($rk->referrer,$rk->urlrequested,$char_len,$rk->malware_type);
			else $referrer=wassupURI::se_link($rk->referrer,$rk->urlrequested,$char_len,$rk->malware_type);?>: </strong><?php print $referrer; ?><br />
				<strong><?php _e('Hostname','wassup'); ?>:</strong> <?php echo esc_attr($hostname); ?></span>
			</div>
		</div> <!-- /sum-nav -->
		<div class="detail-data">
			<?php 
			// Referer is search engine
			if($rk->searchengine != ""){
				$seclass = 'searcheng';
			if(stristr($rk->searchengine,"images")!==FALSE || stristr($rk->referrer,'&imgurl=')!==FALSE){
				$seclass .= ' searchmedia'; 
				$pagenum = intval(number_format(($rk->searchpage / 19),1))+1;
				$url = parse_url($rk->referrer); 
				$page = (number_format(($rk->searchpage / 19), 0) * 18); 
				$ref = $url['scheme']."://".$url['host']."/images?q=".str_replace(' ', '+', $rk->search)."&start=".$page;
			}else{
				if(stristr($rk->searchengine,"video")!==FALSE || stristr($rk->searchengine,"music")!==FALSE) $seclass .=' searchmedia';
				$pagenum = (int)$rk->searchpage;
				$ref = $rk->referrer;
			}?><ul class="<?php echo $seclass; ?>">
			<li class="searcheng"><span class="indent-li-agent"><?php _e('SEARCH ENGINE','wassup'); ?>: <strong><?php print esc_attr($rk->searchengine)." (".__("page","wassup").": $pagenum)"; ?></strong></span></li>
			<li class="searcheng"><span><?php _e("KEYWORDS","wassup"); ?>: <strong><a href="<?php print wassupURI::cleanURL($ref);  ?>" target="_BLANK"><?php if($rk->search == "_notprovided_") echo '('.__("not provided","wassup").')';else echo stringShortener($rk->search, round($max_char_len*.6,0));?></a></strong></span></li>
			</ul>
<?php 			} //end if searchengine
			if(!empty($Ouser)){
				echo "\n";?>
			<ul class="<?php echo $ulclass;?>">
				<?php echo $Ouser;?>
			</ul><?php
			}
			// Visitor is a Spider or Bot
			if(!empty($rk->spider)){
				if($rk->feed != ""){
					echo "\n";?>
			<ul class="spider feed"><?php echo $Ospider;?></ul><?php 
				}else{
					echo "\n";?>
			<ul class="spider"><?php echo $Ospider;?></ul>
<?php				}
			}
			// Visitor is a Spammer
			if ($rk->malware_type > 0 && $rk->malware_type < 3){ ?>
			<ul class="spam">
			<li class="spam"><span class="indent-li-agent"><?php
				echo '<strong>'.__("Probably SPAM!","wassup").'</strong>'; 
				if ($rk->malware_type==2) {
					echo ' ('.__("Referer Spam","wassup").')';
				}elseif (!empty($wassup_options->spam)) { 
					echo ' (Akismet '.__("Spam","wassup").')';
				} else {
					echo ' ('.__("Comment Spam","wassup").')';
				} ?> </span></li>
			</ul><?php
			// Visitor is MALWARE/HACK attempt
			} elseif ($rk->malware_type == 3) {
				echo "\n";?>
			<ul class="spam">
			<li class="spam"><span class="indent-li-agent">
			<?php _e("Probably hack/malware attempt!","wassup"); ?></span></li>
			</ul><?php 
			}
			//hidden user agent string
			?><div class="togglenavi naviagent<?php echo $rk->id ?>" style="display: none;"><ul class="useragent">
				<li class="useragent"><span><?php _e('User Agent','wassup'); ?>: <strong><?php echo wassupURI::disarm_attack($rk->agent);?></strong></span></li>
			</ul></div><?php
			// User flag/os/browser
			if ($rk->spider == "" && ($rk->os != "" || $rk->browser != "")) {
				$flag="&nbsp; ";
				if ($rk->language != "") {
					$lang=esc_attr($rk->language);
					if(file_exists(WASSUPDIR."/img/flags/".$lang.".png")) $flag='<img src="'.WASSUPURL.'/img/flags/'.$lang.'.png" alt="'.$lang.'" title="'.__("Language","wassup").': '.strtoupper($lang).'"/>';
					else $flag=$lang;
				}
				echo "\n";?>
			<ul class="agent">
			<li class="agent"><span class="indent-li-agent"><?php echo $flag.'&nbsp; '.__("OS","wassup"); ?>: <strong><a href="#" class="toggleagent" id="<?php echo (int)$rk->id;?>"><?php echo esc_attr($rk->os);?></a></strong></span></li>
			<li class="agent"><span><?php _e("BROWSER","wassup"); ?>: <strong><a href="#" class="toggleagent" id="<?php echo (int)$rk->id;?>"><?php echo esc_attr($rk->browser);?></a></strong></span></li><?php 
				if ($rk->screen_res != "") {
					echo "\n";?>
			<li class="agent"><span><?php _e("RESOLUTION","wassup"); ?>: <strong><?php echo esc_attr($rk->screen_res); ?></strong></span></li><?php
				}
				echo "\n";?>
			</ul><?php
			}
			echo "\n";
			if($numurl >1){
			?><div style="display:visible;" class="togglecrono navi<?php echo (int)$rk->id ?>">
			<ul class="url"><?php 
				$sql=sprintf("SELECT CONCAT_WS('', SUBSTRING(`timestamp`, 1, 7), TRIM(TRAILING '/' FROM`urlrequested`)) AS urlid, `timestamp`, `urlrequested` FROM $wassup_table WHERE `wassup_id`='%s' %s ORDER BY `timestamp` ASC",esc_attr($rk->wassup_id),$multisite_whereis);
				$qryCD=$wpdb->get_results($sql);
				if(!empty($qryCD) && is_wp_error($qryCD)){
					$errno=$qryCD->get_error_code();
					$error_msg=" qryCD error#$errno ".$qryCD->get_error_message()."\n SQL=".esc_attr($sql);
					$qryCD=false;
				}
				$i=1;
				$char_len = round($max_char_len*.92,0);
				$urlid="";
			if(!empty($qryCD) && is_array($qryCD)){
			foreach ($qryCD as $cd){
				if ($wassup_options->wassup_time_format == 24) {
					$time2 = '<span class="time">'.gmdate("H:i:s", $cd->timestamp).' </span>';
				} else {
					$time2 = '<span class="time">'.gmdate("h:i:s a", $cd->timestamp).'</span>';
				}
				$num = ($i&1);
				if ($num == 0) $classodd = "urlodd"; 
				else  $classodd = "url";
				//skip duplicate urls within 15mins
				if ($i==$numurl || $cd->urlid != $urlid){
					echo "\n"; ?>
			<li class="<?php echo $classodd.' navi'.(int)$rk->id; ?>"><span class="request-time"><?php echo $time2.' &rarr; ';?></span><span class="request-uri"><?php echo wassupURI::url_link($cd->urlrequested,$char_len,$rk->malware_type);?></span></li><?php
				}
				$urlid=$cd->urlid;
				$i++;
			}
			}
			echo "\n";?>
			</ul>
			</div><!-- /url --><?php
			} //end if numurl>1
?>
		</div><!-- /detail-data -->
		<p class="sum-footer"></p>
	</div><!-- /delID... --><?php
		} //end foreach wmain as rk
		echo $expcol;
	} //end if witemstot > 0
	echo "\n";
	if ($witemstot > $witems) {?>
	<div align="center"><?php $p->show();?></div><br /><?php
		echo "\n";
	} ?>
	</div><!-- /main-tabs --><?php
	// Print Google chart last to speed up detail display
	if (!empty($wassup_user_settings['detail_chart']) || (!empty($_GET['chart']) && "1" == esc_attr($_GET['chart']))) {
		$chart_type = (!empty($wassup_options->wassup_chart_type))? $wassup_options->wassup_chart_type: "2";
		//show Google!Charts image
		if ($wpagestot > 12) {
			//New in v1.9: extend script timeout for chart
			if((time()-$stimer_start)>$stimeout-30){
				@set_time_limit($stimeout);
				$stimer_start=time();
			}
			$chart_url=$wTot->TheChart($wlast,$res,"180",$wsearch,$chart_type,"bg,s,e9e9ea|c,lg,90,deeeff,0,e9e9ea,0.8","page",$wtype);
			$html='<img src="'.$chart_url.'" alt="'.__("Graph of visitor hits","wassup").'" class="chart" width="'.$res.'" />';
		} else {
			$html='<p style="padding-top:10px;">'.__("Too few records to print chart","wassup").'...</p>';
		}
	} else {
		$html='<p style="padding-top:10px">&nbsp;</p>';
	} //end if wassup_chart==1
	echo "\n";?>
	<script type="text/javascript">jQuery('div#chart_placeholder').html(<?php echo "'".$html."'";?>).css("background-image","none");</script>
	<?php if(!empty($witemstot) && $witemstot >=10) echo $scrolltop;?><?php
	} else {
		echo "\n<h3>".sprintf(__("Invalid page request %s","wassup"),"$wassuppage").'</h3>';
	} //end MAIN/DETAILS VIEW

	//display MySQL errors/warnings - for debug
	if($wdebug_mode){
		if(!empty($error_msg)) echo "\n".__FUNCTION__." ERROR: ".$error_msg;
		@ini_set('display_errors',$mode_reset);	//turn off debug
	}
} //end wassup_page_contents

/**
 * Check validity of export request then run 'wassupDb::backup_table' to export WassUp main table data into SQL format
 * New in v1.9: optional starting record id number as parameter and more validation tests for export request
 * @param integer
 * @return void
 */
function export_wassup(){
	global $wpdb, $current_user, $wassup_options, $wdebug_mode;

	//#1st verify that export request is valid
	$exportdata=false;
	$badrequest=false;
	$err_msg="";
	$wassup_user_settings=array();
	//new in v1.9 (security fix): user must be logged in to export
	if(!is_object($current_user) || empty($current_user->ID)){
		$user=wp_get_current_user();
	}
	if(!empty($current_user->ID)){
		$wassup_user_settings=get_user_option('_wassup_settings',$current_user->ID);
		//new in v1.9: wp_nonce validation of export request
		if(empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'],'wassupexport-'.$current_user->ID)) {
			$err_msg=__("Export ERROR: nonce failure!","wassup");
		}
	}else{
		 $err_msg=__("Export ERROR: login required!","wassup");
	}
	//abort invalid export requests
	if($err_msg){
		if(empty($current_user->ID)){
			wp_die($err_msg);
		}else{
			$wassup_user_settings['ualert_message']=$err_msg;
			update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
			if(is_multisite() && is_network_admin()) wp_safe_redirect(network_admin_url('admin.php?page=wassup-options&tab=3'));
			else wp_safe_redirect(admin_url('admin.php?page=wassup-options&tab=3'));
		}
		exit;
	}
	$err_msg="";
	$wassup_table=$wassup_options->wassup_table;
	$wherecondition="";
	//for multisite compatibility
	$multisite_whereis="";
	if($wassup_options->network_activated_plugin()){
		if(!is_network_admin() && !empty($GLOBALS['current_blog']->blog_id)) $multisite_whereis = sprintf(" AND `subsite_id`=%d",(int)$GLOBALS['current_blog']->blog_id);
	}
	$start_recid=0;
	if(isset($_REQUEST['startid'])&& is_numeric($_REQUEST['startid'])) $start_recid=(int)$_REQUEST['startid'];
	if(!empty($start_recid)) $wherecondition="WHERE `id`>".(int)$start_recid.$multisite_whereis;
	elseif(!empty($multisite_whereis)) $wherecondition="WHERE `id`>0 ".$multisite_whereis;
	//# check for records before exporting...
	$filename='wassup.'.gmdate('Y-m-d').'.sql';
	$numrecords=0;
	$exportdata=false;
	$result=$wpdb->get_var(sprintf("SELECT COUNT(wassup_id) FROM %s %s",esc_attr($wassup_table),$wherecondition));
	if(!is_wp_error($result) && is_numeric($result)) $numrecords=$result;
	$result=$wpdb->get_var(sprintf("SELECT MAX(`id`) FROM %s %s",esc_attr($wassup_table),$wherecondition));
	if($numrecords > 0){
		//New in v1.9: save "failed export" message beforehand in case of script interruption
		$wassup_user_settings['ualert_message']=__("Export failed due to script interruption or timeout error!","wassup");
		update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
		if($numrecords > 10000){
			//Could take a long time, so increase script execution time-limit to 11 min
			//TODO 'safe_mode' deprecated in PHP 5.3
			if(!@ini_get('safe_mode')){
				$timeout=ini_get('max_execution_time');
				if(empty($timeout)|| $timeout<(11*60))@set_time_limit(11*60);
			}
		}
		//get the data 
		$exportdata=wassupDb::backup_table("$wassup_table","$wherecondition");
	}else{
		//New in v1.9: save failed export message
		$wassup_user_settings['ualert_message']=__("ERROR: Nothing to Export.","wassup");
		update_user_option($current_user->ID,'_wassup_settings',$wassup_user_settings);
	} //end if numrecords > 0
	if (!empty($exportdata)) {
		//TODO: use compressed file transfer when zlib available...
		//do_action('export_wassup');
		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=$filename");
		header('Content-Type: text/plain charset=' . get_option('blog_charset'), true);

		echo $exportdata;
		die(); 	//sends output and flushes buffer
	}else{
		//something went wrong with export, reload screen to show error message
		$reload_uri=preg_replace(array('/&export(?:[^&]+|$)/','/&whash=[^&]+/'),'',$_SERVER["REQUEST_URI"]);
		wp_safe_redirect($reload_uri);
		exit;
	}
} //end export_wassup

if (!function_exists('microtime_float')) {
function microtime_float() {	//replicates microtime(true) from PHP5
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
}
//### III: Tracking functions
/**
 * Start tracking: check for user login and cookie
 */
function wassupPrepend() {
	global $wpdb,$wp_version,$wassup_options,$current_user,$wscreen_res,$wdebug_mode;
	if(empty($wassup_options)) $wassup_options=new wassupOptions;
	//wassup must be active for tracking to begin
	if(empty($wassup_options->wassup_active)){	//do nothing
		return;
	}elseif(is_multisite()){
		$network_settings=get_site_option('wassup_network_settings');
		if(!empty($network_settings) && empty($network_settings['wassup_active'])) return;
	}
	$wassup_table=$wassup_options->wassup_table;
	$wassup_tmp_table=$wassup_table."_tmp";
	$wscreen_res="";
	$timenow=current_time('timestamp');
	//session tracking with cookie
	$session_timeout=true;
	$wassup_timer=0;
	$wassup_id="";
	$subsite_id=(!empty($GLOBALS['current_blog']->blog_id)?$GLOBALS['current_blog']->blog_id:0);
	$cookie_subsite=0;
	$sessionhash=$wassup_options->whash;
	//in case whash was reset
	if(!isset($_COOKIE['wassup'.$sessionhash])) $sessionhash=wassup_get_sessionhash();
	if(isset($_COOKIE['wassup'.$sessionhash])){
		//New in v1.9: cookie separator changed to '##' because of potential conflict with ipv6 '::' address notation
		$cookie_data = explode('##',esc_attr(base64_decode(urldecode($_COOKIE['wassup'.$sessionhash]))));
		if(count($cookie_data)>3){
			$wassup_id = $cookie_data[0];
			$wassup_timer = $cookie_data[1];
			if(!empty($cookie_data[2])) $wscreen_res = $cookie_data[2];
			$timer=(int)$wassup_timer - time();
			if($timer > 0 && $timer < 86400) $session_timeout=false;
			//don't reuse wassup_id when subsite changed
			if(is_multisite()){
				if(preg_match('/^([0-9]+)b_/',$wassup_id,$pcs)>0) $cookie_subsite=$pcs[1];
				if($subsite_id != $cookie_subsite) $wassup_id="";
			}
		}
	}
	//for tracking 404 hits when it is 1st visit record
	if (is_404()) $req_code = 404;
	else $req_code = (isset($_SERVER['REDIRECT_STATUS'])?(int)$_SERVER['REDIRECT_STATUS']:200);
	//get screen resolution from cookie or browser header data, if any
	if (empty($wscreen_res) && isset($_COOKIE['wassup_screen_res'.$sessionhash])) {
		$wscreen_res = esc_attr(trim($_COOKIE['wassup_screen_res'.$sessionhash]));
		if ($wscreen_res == "x") $wscreen_res="";
	} 
	if (empty($wscreen_res) && isset($_SERVER['HTTP_UA_PIXELS'])) {
		//resolution in IE/IEMobile header sometimes
		$wscreen_res = str_replace('X',' x ',$_SERVER['HTTP_UA_PIXELS']);
	}
	if (empty($wscreen_res) || preg_match('/(\d+\sx\s\d+)/i',$wscreen_res)==0){
		$wscreen_res = "";
		$ua=(!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"");
		if(strstr($ua,'MSIE')===false && strstr($ua,'rv:11')===false && strstr($ua,'Edge/')===false){
			//place wassup tag and javascript in document head
			if(is_admin()) add_action('admin_head','wassup_head');
			else add_action('wp_head','wassup_head');
		}
	}
	//get logged-in user info 
	$logged_user="";
	$is_admin_login=false;
	if(empty($current_user->user_login)) wp_get_current_user();
	if(!empty($current_user->user_login)){
		$logged_user=$current_user->user_login;
		$is_admin_login=$wassup_options->is_admin_login($current_user);
	}
	//exclude valid wordpress admin page visits and admin hits
	if(($wassup_options->wassup_admin == "1" || !$is_admin_login) && (!is_admin() || empty($logged_user))){
		//track visitor hits: 
		$urlRequested=$_SERVER['REQUEST_URI'];
		//'send_headers' hook used for media, downloads, and feed hit tracking, and for writing wassup cookie
		if(preg_match("#([^?\#&]+\.([a-z]{1,4}))(?:[?&\#]|$)#i",$urlRequested)>0 && basename($urlRequested)!="robots.txt"){
			//this is a multimedia or general file request
			if(!headers_sent() && !is_admin()) add_action('send_headers','wassupAppend');
			else wassupAppend();
		}elseif(preg_match("/(\.(3gp|7z|f4[pv]|mp[34])(?:[?&\#]|$))|[=\/](feed|atom)/i", $urlRequested)>0){
			//this is an audio, archive, or feed request
			if(!headers_sent() && !is_admin()) add_action('send_headers','wassupAppend');
			else wassupAppend();
		}elseif($req_code !=200 || $session_timeout || empty($wassup_id)){
			if(!headers_sent() && !is_admin()) add_action('send_headers','wassupAppend',15);
			else wassupAppend();
		}else{
			//shutdown hook if no cookie to write...1-priority so runs before other shutdown actions
			add_action('shutdown','wassupAppend',1);
		}
	}else{
		//successful login
		$in_temp=0;
		//check if user had a previous visit before login
		if(empty($wassup_id)) $wsession_id=wassup_get_sessionid();
		else $wsession_id=$wassup_id;
		$wassup_dbtask=array();
		//check that user is in temp for online counts
		$result=$wpdb->get_results(sprintf("SELECT COUNT(*) FROM $wassup_tmp_table WHERE `timestamp`>'%d' AND `wassup_id`='%s'",$timenow - 600,$wsession_id));
		if(!empty($result) && !is_wp_error($result)) $in_temp=$result;
		//retroactively undo "hack attempt" label - queue the update because of "delayed insert", then add temp record for online counts
		if($in_temp >0 && $in_temp <6){
			$wassup_dbtask[]=sprintf("UPDATE LOW_PRIORITY `$wassup_tmp_table` SET `username`='%s', `spam`='0' WHERE `wassup_id`='%s'",$logged_user,$wsession_id);
			$wassup_dbtask[]=sprintf("UPDATE LOW_PRIORITY `$wassup_table` SET `username`='%s', `spam`='0' WHERE `wassup_id`='%s'",$logged_user,$wsession_id);
			$args=array('dbtasks'=>$wassup_dbtask);
			if(is_admin() || version_compare($wp_version,'2.8','<')){
				wassupDb::scheduled_dbtask($args);
			}else{
				wp_schedule_single_event(time()+30,'wassup_scheduled_dbtasks',$args);
			}
		}else{
			//call wassupAppend to add logged-in user record to wassup_temp for accurate current-online counts
			if(!headers_sent() && !is_admin()) add_action('send_headers','wassupAppend',15);
			else add_action('shutdown','wassupAppend',1);
		}
	} //end if wassup_admin
} //end wassupPrepend

//Track visitors and save record in wassup table, after page is displayed
function wassupAppend() {
	global $wpdb,$wp_version,$current_user,$wassup_options,$wscreen_res,$wdebug_mode;
	if(!defined('WASSUPVERSION')) wassup_init();
	//wassup must be active for recording to begin
	$network_settings=array();
	if(empty($wassup_options->wassup_active)){	//do nothing
		return;
	}elseif(is_multisite()){
		$network_settings=get_site_option('wassup_network_settings');
		if(!empty($network_settings) && empty($network_settings['wassup_active'])) return;
	}
	//load additional wassup modules as needed
	if(!class_exists('wDetector')) require_once(WASSUPDIR.'/lib/main.php');
	if(!class_exists('UADetector')) include_once(WASSUPDIR.'/lib/uadetector.class.php');
	$wpurl=wassupURI::get_wphome();
	$blogurl=wassupURI::get_sitehome();
	//identify media requests
	$is_media=false;
	$fileRequested="";
	if(preg_match("#(^/[0-9a-z\-/\._]+\.(3gp|avi|bmp|flv|gif|ico|img|jpe?g|mkv|mov|mpa|mpe?g|mp[234]|ogg|oma|omg|png|pdf|pp[st]x?|psd|svg|swf|tiff|vob|wav|wma|wmv))(?:[?\#&]|$)#i",$_SERVER['REQUEST_URI'],$pcs)>0){
		$is_media=true;
		if(@ini_get('allow_url_fopen')) $fileRequested=$blogurl.$pcs[1];
	}
	$debug_output="";
	if ($wdebug_mode) {
		//turn off debug mode for media and non-html requests 
		if($is_media || (!is_page() && !is_home() && !is_single() && !is_archive())){
			$wdebug_mode=false;
		}else{
			$mode_reset=ini_get('display_errors');
			$debug_reset=$wdebug_mode;
			//don't check for 'strict' php5 standards (part of E_ALL since PHP 5.4)
			if (defined('PHP_VERSION') && version_compare(PHP_VERSION, 5.4, '<')) @error_reporting(E_ALL);
			else @error_reporting(E_ALL ^ E_STRICT); //E_STRICT=php5 only
			@ini_set('display_errors','On');
			//Debug: Output open comment tag to hide PHP errors from visitors
			if(headers_sent()){
				echo "\n<!-- *WassUp DEBUG On\n";   //hide errors
				echo date('H:i:s');
			}else{
				$debug_output="\n<!-- *WassUp DEBUG On\n";   //hide errors
				$debug_output.=date('H:i:s');
			}
		}
	} else {
		//do only fatal error reporting
		// note: this won't work if PHP in safe mode
		$errlvl = @error_reporting();
		if (!empty($errlvl)) @error_reporting(E_ERROR);
	} //end if $wdebug_mode
	$error_msg="";
	$wassup_table = $wassup_options->wassup_table;
	$wassup_tmp_table = $wassup_table . "_tmp";
	$wassup_meta_table = $wassup_table."_meta";
	$wassup_recid=0;
	$temp_recid=0;
	$wassup_dbtask=array();	//for scheduled db operations
	$wassup_rec=array();
	//init wassup table fields...
	$wassup_id = "";
	$timestamp = current_time("timestamp");
	$ipAddress = "";
	$IP="";
	$hostname = "";
	$urlRequested = $_SERVER['REQUEST_URI'];
	if(empty($urlRequested) && !empty($_SERVER['SCRIPT_NAME'])) $urlRequested=$_SERVER['SCRIPT_NAME']; // IIS
	$referrer = (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '');
	$userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? rtrim($_SERVER['HTTP_USER_AGENT']) : '');
	if(strlen($userAgent) >255) $userAgent=substr(str_replace(array('  ','%20%20','++'),array(' ','%20','+'),$userAgent),0,255);
	$search_phrase="";
	$searchpage="0";
	$searchengine="";
	$os="";
	$browser="";
	$language = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? esc_attr($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '');
	//fields and vars for spam detection...
	$spider="";
	$feed="";
	$logged_user="";
	$comment_user = (isset($_COOKIE['comment_author_'.COOKIEHASH]) ? utf8_encode($_COOKIE['comment_author_'.COOKIEHASH]) : '');
	$spam=0;
	$post_ID=0;
	$subsite_id=(!empty($GLOBALS['current_blog']->blog_id)?$GLOBALS['current_blog']->blog_id:0);
	$multisite_whereis="";
	if($wassup_options->network_activated_plugin() && !empty($GLOBALS['current_blog']->blog_id)){
		$multisite_whereis = sprintf(" AND `subsite_id`=%d",(int)$GLOBALS['current_blog']->blog_id);
	}
	//Set more table fields from http_header and other visit data
	$unknown_spider=__("Unknown Spider","wassup");
	$unknown_browser=__("Unknown Browser","wassup");
	$ua = new UADetector();
	if(!empty($ua->name)){
		if($ua->agenttype == "B"){
			$browser = $ua->name;
			if(!empty($ua->version)){ 
				$browser .= " ".wMajorVersion($ua->version);
				if (strstr($ua->version,"Mobile")!==false) $browser .= " Mobile";
			}
		}else{
			$spider = $ua->name;
			if ($ua->agenttype == "F") {
				if(!empty($ua->subscribers)) $feed=$ua->subscribers;
				else $feed=$spider;
			} elseif ($ua->agenttype == "H" || $ua->agenttype == "S") {
				//it's a script injection bot|spammer
				if ($spam == "0") $spam = 3;
			}
		} //end else agenttype
		$os=$ua->os;
		if(!empty($ua->resolution)) $wscreen_res=(preg_match('/^\d+x\d+$/',$ua->resolution)>0)?str_replace('x',' x ',$ua->resolution):$ua->resolution;
		if(!empty($ua->language) && empty($language)) $language=$ua->language;
		if($wdebug_mode){
			if(headers_sent()) echo "\nUAdetecter results: \$ua=".serialize($ua);
			else $debug_output .= "\nUAdetecter results: \$ua=".serialize($ua);
		}
	} //end if $ua->name
	//Set visitor identifier fields: username, wassup_id, ipAddress, hostname
	//re-lookup username in case login was not detected in 'init'
	$is_admin_login = false;
	if(empty($current_user->user_login))
		$user = wp_get_current_user();	//resets $current_user
	if(!empty($current_user->user_login)) {
		$logged_user = $current_user->user_login;
		$is_admin_login = $wassup_options->is_admin_login($current_user);
	}
	$session_timeout = false;
	$wassup_timer=0;
	$cookieIP = "";
	$cookieHost = "";
	$cookieUser = "";
	$sessionhash=$wassup_options->whash;
	//in case hash was reset
	if(!isset($_COOKIE['wassup'.$sessionhash])) $sessionhash=wassup_get_sessionhash();
	//# Check for cookies in case this is an ongoing visit
	if(isset($_COOKIE['wassup'.$sessionhash])){
		$cookie_data = explode('##',esc_attr(base64_decode(urldecode($_COOKIE['wassup'.$sessionhash]))));
		if(count($cookie_data)>3){
			$wassup_id = $cookie_data[0];
			$wassup_timer = $cookie_data[1];
			if(!empty($cookie_data[2])) $wscreen_res = $cookie_data[2];
			$cookieIP = $cookie_data[3];
			if(!empty($cookie_data[4])) $cookieHost = $cookie_data[4];
			//Since v1.8.3: username in wassup cookie
			if(!empty($cookie_data[5])) $cookieUser = $cookie_data[5];
		}
	}
	if(!empty($wassup_id)){
		if((int)$wassup_timer - time() < 1) $session_timeout=true;
		if(preg_match('/^([0-9]+)b_/',$wassup_id,$pcs)>0){
			if($pcs[1]!=$subsite_id) $wassup_id="";
		}
	}
	//Get visitor ip/hostname from http_header
	if(!empty($cookieIP)){
		$ipAddress = $_SERVER['REMOTE_ADDR'];
		$IP=wassup_clientIP($ipAddress);
		if($cookieIP==$IP){
			$hostname=$cookieHost;
		}elseif(strpos($_SERVER['REMOTE_ADDR'],$cookieIP)!==false){
			$IP=$cookieIP;
			$hostname=$cookieHost;
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strpos($_SERVER['HTTP_X_FORWARDED_FOR'],$cookieIP)!==false){
			$ipAddress=$_SERVER['HTTP_X_FORWARDED_FOR'];
			$IP=$cookieIP;
			$hostname=$cookieHost;
		}else{
			$ipAddress=wassup_get_clientAddr();
			$IP=wassup_clientIP($ipAddress);
			if($cookieIP==$IP) $hostname=$cookieHost;
			else $hostname=wassup_get_hostname($IP);
		}
	}else{
		$ipAddress=wassup_get_clientAddr();
		$IP=wassup_clientIP($ipAddress);
		if($cookieIP==$IP)$hostname=$cookieHost;
		else $hostname=wassup_get_hostname($IP);
	}
	if (empty($ipAddress)) $ipAddress = $_SERVER['REMOTE_ADDR'];
	if (empty($IP)) $IP = wassup_clientIP($ipAddress);
	if (empty($hostname)) $hostname = "unknown";
	if (empty($logged_user)) $logged_user = $cookieUser;
	//get screen resolution value from cookie or browser header data, if any - do before new cookie is written
	if(empty($wscreen_res)){
		if(isset($_COOKIE['wassup_screen_res'.$sessionhash])) {
			$wscreen_res=esc_attr(trim($_COOKIE['wassup_screen_res'.$sessionhash]));
			if($wscreen_res == "x") $wscreen_res = "";
		} 
		if(empty($wscreen_res) && isset($_SERVER['HTTP_UA_PIXELS'])) {
			//resolution in IE/IEMobile header sometimes
			$wscreen_res=str_replace('X',' x ',esc_attr($_SERVER['HTTP_UA_PIXELS']));
		}
	}
	//assign a wassup_id for visit - before writing cookie
	if(empty($wassup_id) || $session_timeout){
		$args=array('ipAddress'=>$ipAddress,'hostname'=>$hostname,'logged_user'=>$logged_user,'timestamp'=>$timestamp,'subsite_id'=>$subsite_id);
		$wassup_id=wassup_get_sessionid($args);
		$wassup_timer=((int)time() + 2700); //use 45 minutes timer
		//#new in v1.9: longer session time for logged-in users
		if(!empty($logged_user))$wassup_timer=(int)time() + 5400;
	}
	//write wassup cookie for new visits, visit timeout (45 mins) or empty screen_res
	if(empty($wassup_id) || $session_timeout || (!empty($wscreen_res) && empty($cookie_data[2]))){
		//put the cookie in the oven and set the timer...
		//this must be done before headers sent
		if(!headers_sent()){
			if (defined('COOKIE_DOMAIN')) {
				$cookiedomain = preg_replace('#(https?\://)?(www\.)?#','',strtolower(COOKIE_DOMAIN));
				if(defined('COOKIEPATH')) $cookiepath=COOKIEPATH;
				else $cookiepath = "/";
			} else {
				$cookieurl = parse_url(get_option('home'));
				$cookiedomain = preg_replace('/^www\./i','',$cookieurl['host']);
				$cookiepath = $cookieurl['path'];
			}
			$expire = 0; //expire on browser close - based on utc timestamp, not on Wordpress time
			$wassup_cookie_value = urlencode(base64_encode($wassup_id.'##'.$wassup_timer.'##'.$wscreen_res.'##'.$IP.'##'.$hostname.'##'.$logged_user));
			setcookie("wassup".$sessionhash, "$wassup_cookie_value", $expire, $cookiepath, $cookiedomain);
		}
		unset($temp_id, $tempUA, $templen);
	} //end if empty(wassup_id)
	$req_code=200;
	if(is_404()) $req_code=404;
	elseif(isset($_SERVER['REDIRECT_STATUS'])) $req_code=(int)$_SERVER['REDIRECT_STATUS'];
	//sometimes missing media can show as 200, so if request is a media file, also check that file exist
	if($is_media){
		if($req_code==200 && !empty($fileRequested) && !file_exists($fileRequested)){
			$req_code=404;
		}
	}elseif(preg_match("#(^/[0-9a-z\-/\._]+\.([a-z]{1,4}))(?:[?&\#]|$)#i",$urlRequested,$pcs)>0 && basename($urlRequested)!="robots.txt"){
		//identify file requests
		if(@ini_get('allow_url_fopen')) $fileRequested=$blogurl.$pcs[1];
	}
	$hackercheck=true;
	@ignore_user_abort(1); // finish script in background if visitor aborts
	//## Start Exclusion controls:
	//#1 First exclusion control is for admin user
	if ($wassup_options->wassup_admin == "1" || !$is_admin_login || strpos($urlRequested,'wp-login.php')>0 || $req_code!=200) {
	//#2 Exclude wp-cron utility hit unless from an external host
	if (stristr($urlRequested,"/wp-cron.php?doing_wp_cron")===false || (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']!=$IP)) {
		//no hack checks on admin users, feed, multimedia, and simple file and archive requests
		if($is_admin_login || empty($wassup_options->wassup_hack) || is_feed() || $is_media || preg_match('#^/[0-9a-z\-\._]+\.(css|gz|jar|pdf|rdf|rtf|txt|xls|xlt|xml|Z|zip)$#i',$_SERVER['REQUEST_URI'])>0){
			$hackercheck=false;
		}
	//#3 Exclude wp-admin visits unless spam/malware attempt
	if ((!is_admin() && stristr($urlRequested,"/wp-admin/")===false && stristr($urlRequested,"/wp-includes/")===false) || $req_code!=200 || $hackercheck){
		//Get post/page id, if any
		if(!is_admin()){
			if(isset($GLOBALS['post']) && (is_single() || is_page())) $post_ID=get_the_ID();
			if(empty($post_ID) && isset($GLOBALS['posts'])){
				//save ID if only 1 post in archive
				if(is_archive() || is_search() && count($GLOBALS['posts'])==1 && !empty($GLOBALS['posts'][0]->ID)) $post_ID=$GLOBALS['posts'][0]->ID;
			}
		}
	//#4 Exclude users on exclusion list
	//New in v1.9: replaced 'explode' with 'preg_match' for faster matching of users, url requests, and ip addresses
	if (empty($wassup_options->wassup_exclude_user) || empty($logged_user) || preg_match('/(?:^|\s*,)\s*('.preg_quote($logged_user).')\s*(?:,|$)/',$wassup_options->wassup_exclude_user)==0){
		//TODO: exclude page requests by post_id
	//#5 Exclude urls on exclusion list
	if (empty($wassup_options->wassup_exclude_url) || preg_match('#(?:^|\s*,)\s*((?:'.str_replace('#','\#',preg_quote($blogurl)).')?'.str_replace('#','\#',preg_quote($urlRequested)).')\s*(?:,|$)#i',$wassup_options->wassup_exclude_url)==0){
		//url matching may be affected by html-encoding, url-encoding, query parameters, and labels on the url - so do those exclusions separately
		$exclude_visit = false;
		if (!empty($wassup_options->wassup_exclude_url)) {
			$exclude_list = explode(',',str_replace(', ',',',$wassup_options->wassup_exclude_url));
			//reverse the pattern/item checked in regex
			foreach ($exclude_list as $exclude_url) {
				$xurl=str_replace($blogurl,'',rtrim(trim($exclude_url),'/'));
			if(!empty($xurl)){
				$regex='#^('.str_replace('#','\#',preg_quote($xurl)).')([&?\#/].+|$)#i';
				if(preg_match($regex,$urlRequested)>0){
					$exclude_visit=true;
					break;
				}elseif(preg_match($regex,esc_attr($urlRequested))>0){
					$exclude_visit=true;
					break;
				}elseif(preg_match($regex,urlencode($urlRequested))>0){
					$exclude_visit=true;
					break;
				}
			}
			}//end foreach
		}
	//#6 Exclude IPs on exclusion list... 
	if ((empty($wassup_options->wassup_exclude) || preg_match('#(?:^|\s*,)\s*('.preg_quote($IP).')\s*(?:,|$)#',$wassup_options->wassup_exclude)==0) && !$exclude_visit){
		//New in v1.9: match for wildcards in exclude list
		if(strpos($wassup_options->wassup_exclude,'*')!= 0){
			$exclude_list = explode(',',str_replace(', ',',',$wassup_options->wassup_exclude));
			//reverse the pattern/item_checked in regex
			foreach ($exclude_list as $xip) {
			if(!empty($xip) && strpos($xip,'*')!=0){
				$regex='/^'.str_replace('\*','([0-9a-f\.:]+)',preg_quote($xip)).'$/i';
				if(preg_match($regex,$IP)>0){
					$exclude_visit=true;
					break;
				}
			}
			}//end foreach
		}
	//#7 New in v1.9: Exclude hostnames on exclusion list... 
	if ((empty($wassup_options->wassup_exclude_host) || preg_match('#(?:^|\s*,)\s*('.preg_quote($hostname).')\s*(?:,|$)#',$wassup_options->wassup_exclude_host)==0) && !$exclude_visit){
		//match for wildcards in exclude list
		if(strpos($wassup_options->wassup_exclude_host,'*')!==false){
			$exclude_list = explode(',',str_replace(', ',',',$wassup_options->wassup_exclude_host));
			//reverse the pattern/item_checked in regex
			foreach ($exclude_list as $xhost) {
			if(!empty($xhost) && strpos($xhost,'*')!==false){
				$regex='/^'.str_replace('\*','([0-9a-z\-_]+)',preg_quote($xhost)).'$/i';
				if(preg_match($regex,$hostname)>0){
					$exclude_visit=true;
					break;
				}
			}
			}//end foreach
		}
	//#8 New in v1.9: Exclude hostnames (with wildcards) on exclusion list
	if (!$exclude_visit) {
	//#9 Exclude requests for themes and plugins from recordings
	if (stristr($urlRequested,"/wp-content/themes") === FALSE || stristr($urlRequested,"comment") !== FALSE || $req_code!=200) {
		
	//#10 Exclude for logged-in users
	if ($wassup_options->wassup_loggedin == 1 || !is_user_logged_in()) {
	//#11 Exclude for wassup_attack (libwww-perl)
	if ($wassup_options->wassup_attack == 1 || stristr($userAgent,"libwww-perl") === FALSE ) {
		// Check for duplicates, previous spam check, and screen resolution and get previous settings to prevent redundant checks on same visitor. 
		// Dup==same wassup_id and URL, and timestamp<180 secs
		$dup_urlrequest=0;
		$wpageviews=0;
		$spamresult=0;
		$recent_hit=array();
		//don't wait for slow responses...set mysql wait timeout to 7 seconds
		$mtimeout=$wpdb->get_var("SELECT @@session.wait_timeout AS mtimeout FROM DUAL");
		if(is_wp_error($mtimeout)|| !is_numeric($mtimeout))$mtimeout=0;
		else $wpdb->query("SET wait_timeout=7");
		if($wdebug_mode){
			if(headers_sent()) echo "\nSet MySQL wait_timeout=7 from ".$mtimeout;
			else $debug_output .="\nSet MySQL wait_timeout=7 from ".$mtimeout;
		}
		//get recent hits with same wassup_id
		$result=$wpdb->get_results(sprintf("SELECT SQL_NO_CACHE `wassup_id`, `ip`, `timestamp`, `urlrequested`, `screen_res`, `username`, `browser`, `os`, `spider`, `feed`, `spam`, `language`, `agent`, `referrer` FROM %s WHERE wassup_id='%s' AND `timestamp` >'%d' %s ORDER BY `timestamp` DESC",$wassup_tmp_table,esc_attr($wassup_id),$timestamp-183,$multisite_whereis));
		if (!empty($result) && !is_wp_error($result)) $recent_hit=$result;
	if(!empty($recent_hit)){
		$wpageviews=count($recent_hit);
		//check 1st record only
		//record is dup if same url and same user-agent
		if($recent_hit[0]->agent == $userAgent || empty($recent_hit[0]->agent)){
			if($recent_hit[0]->urlrequested == $urlRequested || $recent_hit[0]->urlrequested == '[404] '.$urlRequested){
				$dup_urlrequest=1;
			}elseif($is_media && $req_code == 200 && preg_match("/\.(gif|ico|jpe?g|png|tiff)$/i",$fileRequested) >0){
				//exclude images/photos only after confirmation of other valid page hit by visitor
				$dup_urlrequest=1;
			}
		}
		//retrieve previous spam check results
		$spamresult = $recent_hit[0]->spam;
		if (empty($spamresult) || !is_numeric($spamresult)) $spamresult=0;
		//check for screen resolution and update, if not previously recorded
		//...queue the update because of "delayed insert"
		if (empty($recent_hit[0]->screen_res) && !empty($wscreen_res)) {
			$wassup_dbtask[]=sprintf("UPDATE LOW_PRIORITY `$wassup_table` SET `screen_res`='%s' WHERE `wassup_id`='%s' AND `screen_res`='' ",$wscreen_res,$wassup_id);
		}
		//get previously recorded settings for this visitor to avoid redundant tests
		if ($dup_urlrequest == 0) {
			if (empty($wscreen_res) && !empty($recent_hit[0]->screen_res)) {
				$wscreen_res = $recent_hit[0]->screen_res;
			}
			if($spam==0 && (int)$spamresult >0) $spam=$spamresult;
			if ($recent_hit[0]->agent == $userAgent || empty($userAgent)) {
				$browser = $recent_hit[0]->browser;
				$spider = $recent_hit[0]->spider;
				$os = $recent_hit[0]->os;
				//feed reader only if this page is feed
				if (!empty($recent_hit[0]->feed) && is_feed()) {
					$feed = $recent_hit[0]->feed;
				}
			}
		}
		// Detect disguised spiders and harvesters by checking for excessive pageviews (threshold: 8+ views in < 16 secs)
		if($wpageviews >7 && empty($spider) && empty($logged_user)){
			$pageurls = array();
			$visitstart = $recent_hit[7]->timestamp;
			if (($timestamp - $recent_hit[7]->timestamp) < 16 && empty($logged_user)) {
				$is_spider=true;
				$pageurls[]="$urlRequested";
				//a spider is unlikely to hit same page 2+ times
				foreach ($recent_hit AS $w_pgview) {
					if(stristr($w_pgview->urlrequested,"robots.txt")!==false){
						$is_spider=true;
						break;
					}elseif(in_array($w_pgview->urlrequested,$pageurls)){
						$is_spider=false;
						break;
					}else{
						$pageurls[]=$w_pgview->urlrequested;
					}
				}
				if($is_spider){
					$spider=$unknown_spider;
					$wassup_dbtask[]="UPDATE LOW_PRIORITY `$wassup_table` SET `spider`='$spider' WHERE `wassup_id`='$wassup_id' AND `spider`=''";
				}
			}
		} //end if wpageviews >7
		if($wdebug_mode){	//debug
			$safe_debug=array_map('htmlentities',(array)$recent_hit[0]);
			if(headers_sent()){
				echo "\n".date('H:i:s').' Recent data lookup results: $recent_hit[0]=';
				print_r($safe_debug);
				if($dup_urlrequest==1) echo "\nDuplicate record!";
			}else{
				$debug_output .="\n".date('H:i:s').' Recent data lookup results: $recent_hit[0]=';
				$debug_output .=print_r($safe_debug,true);
				if($dup_urlrequest==1) $debug_output.= "\nDuplicate record!\n";
				if ($recent_hit[0]->agent != $userAgent) {
					$debug_output .="\nUser Agents NOT Identical:";
					$debug_output .="\n\tCurrent user agent: ".strip_tags(esc_attr(htmlspecialchars(html_entity_decode($userAgent))));
					$debug_output .="\n\tPrevious user agent:".strip_tags(esc_attr(htmlspecialchars(html_entity_decode($recent_hit[0]->agent))))."\n";
				}
			}
		}
	}else{
		if($wdebug_mode){
			if(headers_sent()){
				echo "\nNo Recent visit data found in wassup_tmp.";
			}else{
				$debug_output .= "<br />\nNo Recent visit data found in wassup_tmp.\n"; //debug
			}
		}
	} //end if recent_hit
	//done duplicate check...restore normal timeout
	if(!empty($mtimeout)&& is_numeric($mtimeout)) $wpdb->query("SET wait_timeout=$mtimeout");

	//#12 Exclude for 404 hits unless it is 1st visit or hack attempt
	if($req_code == 200 || empty($recent_hit) || ($hackercheck && (stristr($urlRequested,"/wp-")!==FALSE || preg_match('/\.(php\d?|ini|aspx?|dll|cgi|js|jsp)|(\.\.\/\.\.\/|root[^a-z0-9\-_]|[^a-z0-9\-_]passw|\=admin[^a-z0-9\-_]|(?:user|author|admin|id)\=\-?\d+|\=\-\d+|(bin|etc)\/)|[\*\,\'"\:\(\)$`]|administrator|bin|code|config|cookie|delete|document|drop|drupal|exec|function|insert|install|joomla|root|script|select|setting|setup|table|upgrade|update|upload|where|window|wordpress)/i',$urlRequested)>0))){
		//identify hackers/malware 
		if($hackercheck){
			$pcs=array();
		//visitors requesting non-existent server-side scripts are up to no good
		if(preg_match('#(?:([a-z0-9_\*\-\#\,]+\.php[456]?)|(\.(?:cgi|aspx?|jsp?))|([\*\,\'"\:\(\)$`].*)|(.+\=\-1))(?:[^0-9a-z]|$)#i',$urlRequested,$pcs)>0){
			if(empty($logged_user)){
				if(!empty($pcs[2]) || !empty($pcs[4])){
					if(empty($recent_hit) || $req_code==404){
						$spam=3;
					}elseif(!empty($fileRequested)){
						if(!file_exists($fileRequested)){
							$req_code=404;
							$spam=3;
						}
					}else{
						$spam=3;
					}
				}elseif(!empty($pcs[1]) && $req_code == 404 && $pcs[1]!="index.php" && $pcs[1]!="admin-ajax.php"){
					$spam=3;
				}elseif(!empty($pcs[3]) && preg_match('/([0-9\.\-=;]+)/',$urlRequested)>0){
					$spam=3;
				}elseif(preg_match('/(administrator|bin|code|config|cookie|delete|dll|document|drop|etc|exec|function|href|ini|insert|install|login|passw|root|script|select|setting|setup|table|update|upgrade|upload|where|window)/',$urlRequested)>0){
					$spam=3;
				}elseif((strpos($urlRequested,'wp-admin/')>0 && $pcs[1]!="admin-ajax.php") || strpos($urlRequested,'wp-includes/')>0){
					$spam=3;
				}
			}elseif(!empty($pcs[3]) || !empty($pcs[4])){
				$spam=3;
			}
		}
		//non-admin users trying to access root files, password or ids or upgrade script are up to no good
		if(empty($spam)){
			$pcs=array();
			if(preg_match('#\.\./\.\./(etc/passwd|\.\./\.\./)#i',$urlRequested)>0){
				if(empty($logged_user)) $spam=3;
			}elseif(preg_match('#[\[&\?/\-_](code|dir|document_root\]?|id|page|thisdir)\=(https?\://.+)?#i',$urlRequested,$pcs)>0){
				if(!empty($pcs[2])) $spam=3;
				elseif($req_code == 404 && empty($logged_user)) $spam=3;
			}elseif(preg_match('#\/wp\-admin.*[^0-9a-z_](install(\-helper)?|update(\-core)?|upgrade)\.php([^0-9a-z\-_]|$)#i',$urlRequested)>0){
				$spam=3;
			}
		}
		//regular visitors trying to access admin area are up to no good
		if(empty($spam) && empty($logged_user)){
			$pcs=array();
			if(preg_match('#[^0-9a-z_]wp\-admin\/.+\.php\d?([^a-z0-9_]|$)#i',$urlRequested)>0){
				if($req_code == 404) $spam=3;
				elseif(strpos('/admin-ajax.php',$urlRequested)===false) $spam=3;
			}elseif(preg_match('#\/wp\-(config|load|settings)\.php#',$urlRequested)>0){
			//regular visitor trying to access setup files is up to no good
				$spam=3;
			}elseif(preg_match('#(([a-z0-9_\*\-\#\,]+\.php[456]?)|\.(?:cgi|aspx?))#i',$urlRequested,$pcs)>0) {
			//regular visitor requesting server-side scripts is likely malware
				if(empty($pcs[2])) $spam=3;
				elseif(wIsAttack($urlRequested)) $spam=3;
			//regular visitor querying userid/author or other non-page item by id number is likely malware
			}elseif(preg_match('#[?&]([0-9a-z\-_]+)\=(\-)?\d+$#i',$urlRequested,$pcs)>0){
				if($req_code == 404) $spam=3;
				elseif(!empty($pcs[2])) $spam=3;
				elseif(preg_match('#(code|dir|document_root|id|path|thisdir)#',$pcs[1])>0) $spam=3;
				elseif(wIsAttack($urlRequested)) $spam=3;
			//regular visitor attempts to access "upload" page is likely malware
			}elseif(preg_match('#[?&][0-9a-z\-_]*(page\=upload)(?:[^0-9a-z\-_]|$)#i',$urlRequested)>0){
				$spam=3;
			}elseif(wIsAttack($urlRequested)){
				if($req_code == 404) $spam=3;
			}
			//lastly check referrer and user-agents strings for obvious attack codes
			if(empty($spam)){
				if(!empty($referrer) && $referrer != $blogurl.$urlRequested){
					if(wIsAttack($referrer)) $spam=3;
				}
				//TODO check userAgent string for attack codes
				//if(empty($spam) && !empty($userAgent)){
				//}
			}
		}
		} //end if hackercheck
		//retroactively update record for hack/malware attempt
		if ($spam == "3" && $spamresult == "0" && !empty($recent_hit)) {
			$wassup_dbtask[] = sprintf("UPDATE `$wassup_table` SET `spam`='3' WHERE `wassup_id`='%s' AND `spam`='0' ",$wassup_id);
		}
	//#13 Exclude duplicates 
	if ($dup_urlrequest == 0) {
		//#Identify user-agent...
		$agent=(!empty($browser)?$browser:$spider);
		//identify agent with wGetBrowser
 		if(empty($agent) || stristr($agent,'unknown')!==false || $agent==$unknown_browser || $agent==$unknown_spider || stristr($agent,"mozilla")==$agent || stristr($agent,"netscape")==$agent || stristr($agent,"default")!==false){
			if(!empty($userAgent)){
				list($browser,$os)=wGetBrowser($userAgent);
				if(!empty($browser)) $agent=$browser;
				if ($wdebug_mode){
					if(headers_sent()) echo "\n".date('H:i:s.u').' wGetBrowser results: $browser='.$browser.'  $os='.$os;
					else $debug_output .= "\n".date('H:i:s.u').' wGetBrowser results: $browser='.$browser.'  $os='.$os;
				}
			}
		}
		//# Some spiders, such as Yahoo and MSN, don't always give a unique useragent, so test against known hostnames/IP to identify these spiders
		$spider_hosts='/^((65\.55|207\.46)\.\d{3}.\d{1,3}|.*\.(crawl|yse)\.yahoo\.net|ycar\d+\.mobile\.[a-z0-9]{3}\.yahoo\.com|msnbot.*\.search\.msn\.com|crawl[0-9\-]+\.googlebot\.com|baiduspider[0-9\-]+\.crawl\.baidu\.com|(crawl(?:er)?|spider|robot)\-?\d*\..*)$/';
		//#Identify spiders from known spider domains
		if(empty($agent) || preg_match($spider_hosts,$hostname)>0|| stristr($agent,'unknown')!==false){
			list($spider,$spidertype,$feed) = wGetSpider($userAgent,$hostname,$browser);
			if($wdebug_mode){
				if(headers_sent()) echo "\n".date('H:i:s.u').' wGetSpider results: $spider='.$spider.'  $spidertype='.$spidertype.' $feed='.$feed;
				else $debug_output .= "\n".date('H:i:s.u').' wGetSpider results: $spider='.$spider.'  $spidertype='.$spidertype.' $feed='.$feed;
			}
			//it's a browser
			if($spidertype == "B" && $urlRequested != "/robots.txt"){ 
				if (empty($browser)) $browser = $spider;
				$spider = "";
				$feed = "";
			//it's a script injection bot|spammer
			}elseif($spidertype == "H" || $spidertype == "S"){
				if ($spam == "0") $spam = 3;
			}
		//#Identify spiders and feeds with wGetSpider...
		}elseif(empty($logged_user) && !empty($userAgent) && (strlen($agent)<5 || empty($os) || preg_match("#\s?([a-z]+(?:bot|crawler|spider|reader|agent))[^a-z]#i",$userAgent)>0 || strstr($urlRequested,"robots.txt")!==FALSE || is_feed())){
			list($spider,$spidertype,$feed) = wGetSpider($userAgent,$hostname,$browser);
			if($wdebug_mode){
				if(headers_sent()) echo "\n".date('H:i:s.u').' wGetSpider results: $spider='.$spider.'  $spidertype='.$spidertype.' $feed='.$feed;
				else $debug_output .= "\n".date('H:i:s.u').' wGetSpider results: $spider='.$spider.'  $spidertype='.$spidertype.' $feed='.$feed;
			}
			//it's a browser
			if($spidertype == "B" && $urlRequested != "/robots.txt"){ 
				if(empty($browser)) $browser=$spider;
				$spider="";
				$feed="";
			}elseif($spidertype == "H" || $spidertype == "S"){
				if($spam == "0") $spam=3;
			}
		//no userAgent == spider
		}elseif(empty($userAgent) && empty($agent)){
			$spider=$unknown_spider;
		}
		//if 1st request is "robots.txt" then this is a bot
		if(empty($logged_user) && empty($spider) && strstr($urlRequested,"robots.txt")!==FALSE && empty($recent_hit)){
			$spider=$unknown_spider;
		//empty userAgent is a bot
		}elseif(empty($logged_user) && empty($spider) && empty($browser) && empty($userAgent)){
			$spider=$unknown_spider;
		}
		// Finally, check for disguised spiders by looking for excessive pageviews in recent activity (threshold: 8+ views in < 16 secs)
		if(empty($spider) && empty($logged_user) && $wpageviews >7){
			$pageurls=array();
			$visitstart=$recent_hit[7]->timestamp;
			if(($timestamp - $recent_hit[$wpageviews-1]->timestamp < 16 && $wpageviews >9)|| $timestamp - $visitstart < 12) {
				$is_spider=true;
				$pageurls[]="$urlRequested";
				$n_404=0;
				//a spider is unlikely to hit same page 2+ times
			foreach($recent_hit AS $w_pgview){
				if(stristr($w_pgview->urlrequested,"robots.txt")!==false){
					$is_spider=true;
					break;
				}elseif(in_array($w_pgview->urlrequested,$pageurls)){
					$is_spider=false;
					break;
				//not spider when have multiple 404's
				}elseif(preg_match('/^\[\d{3}\]\s/',$w_pgview->urlrequested)>0){
					if($n_404 >1){
						$is_spider=false;
						break;
					}
					$n_404=$n_404+1;
				}else{
					$pageurls[]=$w_pgview->urlrequested;
				}
			} //end foreach
				if($is_spider) $spider=$unknown_spider;
			} //end if timestamp
		}
		if(!empty($spider)){
			//identify spoofers of Google/Yahoo 
			if(!empty($hostname) && preg_match('/^(googlebot|yahoo\!\ slurp)/i',$spider)>0 && preg_match('/\.(googlebot|yahoo)\./i',$hostname)==0){
				$spider= __("Spoofer bot","wassup");
				//if($spam == "0") $spam=3;
			}
			//for late spider identification, update previous records
			if($wpageviews >1 && empty($recent_hit[0]->spider)){
				$wassup_dbtask[]=sprintf("UPDATE `$wassup_table` SET `spider`='%s' WHERE `wassup_id`='%s' AND `spider`='' ",esc_attr($spider),$recent_hit[0]->wassup_id);
			}
		}
	//#14 Spider exclusion control
	if ($wassup_options->wassup_spider == 1 || $spider == '') {
		// some valid spiders to exclude from spam check below
		$goodbot = false;
		if($hostname!="" && !empty($spider) && preg_match('#^(googlebot|bingbot|msnbot|yahoo\!\sslurp)#i',$spider)>0 && preg_match('#\.(googlebot|live|msn|yahoo)\.(com|net)$#i',$hostname)>0){
			$goodbot = true;
		}
		//do spam exclusion controls, unless disabled in wassup_spamcheck
		if($wassup_options->wassup_spamcheck == 1 && $spam == 0 && !$goodbot){
			$spamComment = New wassup_checkComment;
		//## Check for referrer spam...
		if($wassup_options->wassup_refspam == 1 && !empty($referrer) && !$is_admin_login){
			//...skip if referrer is own blog
			if (stristr($referrer,$wpurl)===FALSE && stristr($referrer,$blogurl)===FALSE && !$wdebug_mode) {
			//check if referrer is a previous comment spammer
			if($spamComment->isRefSpam($referrer)>0){
				$spam=2;
			}else{
				//check for known referer spammer
			 	$isspam=wGetSpamRef($referrer,$hostname);
				if ($isspam) $spam = 2;
			}
			}
		}
		//## Check for comment spammer...
		// No spam check on spiders unless there is a comment or forum page request...
		if ($spam == 0 && (empty($spider) || stristr($urlRequested,"comment")!== FALSE || stristr($urlRequested,"forum")!== FALSE  || !empty($comment_user))) { 
			//check for previous spammer detected by anti-spam plugin
			$spammerIP = $spamComment->isSpammer($IP); //TODO: IP or ipAddress?
			if($spammerIP > 0) $spam=1;
			//set as spam if both URL and referrer are "comment" and browser is obsolete or Opera
			if ($spam== 0 && $wassup_options->wassup_spam==1 && stristr($urlRequested,"comment")!== FALSE && stristr($referrer,"#comment")!==FALSE && (stristr($browser,"opera")!==FALSE || preg_match('/^(AOL|Netscape|IE)\s[1-6]$/',$browser)>0)) {
				$spam=1;
			}
			//#lastly check for comment spammers using Akismet API
			if ($spam == 0 && $wassup_options->wassup_spam == 1 && stristr($urlRequested,"comment")!== FALSE && stristr($urlRequested,"/comments/feed/")== FALSE && !$is_media) {
				$akismet_key = get_option('wordpress_api_key');
				$akismet_class = WASSUPDIR.'/lib/akismet.class.php';
			if (!empty($akismet_key) && is_readable($akismet_class)) {
				include_once($akismet_class);
				// load array with comment data 
				$comment_user_email = (!empty($_COOKIE['comment_author_email_'.COOKIEHASH])? utf8_encode($_COOKIE['comment_author_email_'.COOKIEHASH]):"");
				$comment_user_url = (!empty($_COOKIE['comment_author_url_'.COOKIEHASH])? utf8_encode($_COOKIE['comment_author_url_'.COOKIEHASH]):"");
				$Acomment = array(
					'author' => $comment_user,
					'email' => $comment_user_email,
					'website' => $comment_user_url,
					'body' => (isset($_POST["comment"])? $_POST["comment"]:""),
					'permalink' => $urlRequested,
					'user_ip' => $ipAddress,
					'user_agent' => $userAgent);

				//v1.9 bugfix: akismet class renamed to fix conflict with Akismet 3.0
				$akismet=new wassup_Akismet($wpurl,$akismet_key,$Acomment);
				// Check if it's spam
				if ( $akismet->isSpam() && !$akismet->errorsExist()) {
					$spam = 1;
				}
			} //end if !empty(akismet_key)
			} //end if wassup_spam
			//retroactively update visitor's hits as spam, in case late detection
			if (!empty($recent_hit) && !empty($spam) && $spamresult==0) {
				//queue the update...
				$wassup_dbtask[]=sprintf("UPDATE `$wassup_table` SET `spam`='%d' WHERE `wassup_id`='%s' AND `spam`='0'",$spam,$wassup_id);
			}
		} //end if spam == 0
		} //end if wassup_spamcheck == 1

	//#15 Exclusion control for spam/malware...
	if ($spam == 0 || ($wassup_options->wassup_spam == 1 && $spam == 1) || ($wassup_options->wassup_refspam == 1 && $spam == 2) || ($wassup_options->wassup_hack == 1 && $spam == 3)) {
	//#16 exclusion for wp-content/plugins
	if (stristr($urlRequested,"/wp-content/plugins/")===FALSE || $spam == 3) {
		//## More user/referrer details for recording
		//get language/locale
		if(empty($language) && !empty($recent_hit[0]->language)) $language=$recent_hit[0]->language;
		if($wdebug_mode){
			if(headers_sent()) echo "\n  language=$language";
			else $debug_output .= "\n  language=$language";
		}
		if(preg_match('/\.[a-z]{2,3}$/i',$hostname) >0 || preg_match('/[a-z\-_]+\.[a-z]{2,3}[^a-z]/i',$referrer) >0 || strlen($language)>2){
			//get language/locale info from hostname or referrer data
			$language=wGetLocale($language,$hostname,$referrer);
		}
		if($wdebug_mode){
			if(headers_sent()) echo "\n...language=$language (after geoip/wgetlocale)";
			else $debug_output .= " ...language=$language (after geoip/wgetlocale)";
		}
		// get search engine and search keywords from referrer
		$searchengine="";
		$search_phrase="";
		$searchpage="";
		$searchcountry="";
		//don't check own blog for search engine data
		if (!empty($referrer) && $spam == "0" && stristr($referrer,$blogurl)!=$referrer && !$wdebug_mode) {
			$ref=(is_string($referrer)?$referrer:mb_convert_encoding(strip_tags($_SERVER['HTTP_REFERER']),"HTML-ENTITIES","auto"));
			//New in v1.9: Test for Google secure search and use generic "_notprovided_" for keyword
			//TODO: Yahoo now has secure searching since 4/2014
			$pcs=array();
			if (preg_match('#^https\://(www\.google(?:\.com?)?\.([a-z]{2,3}))/(url\?(?:.+[^q]+q=([^&]*)(?:&|$)))?#',$ref,$pcs)>0){
				$searchdomain=$pcs[1];
				$searchengine="Google";
				if($pcs[2]!="com" && $pcs[2]!="co"){
					$selocale=$pcs[2];
					$searchengine .=" ".strtoupper($se['locale']);
				}
				//get the query keywords - will always be empty, until Google changes its policy
				if(empty($pcs[4]))$search_phrase="_notprovided_";
				else $search_phrase=$pcs[4];
				if(!empty($pcs[3])&& preg_match('/&cd\=(\d+)(?:&|$)/',$ref,$pcs2)>0)$searchpage=$pcs2[1];
				unset($pcs,$pcs2);

			//get GET type search results, ex: search=x
			}elseif (strpos($ref,'=')!==false) {
				$se=wGetSE($ref);
				if(is_array($se) && !empty($se['searchengine'])){
					$searchengine=$se['searchengine'];
					$search_phrase=$se['keywords'];
					$searchpage=$se['page'];
					$searchlang=$se['language'];
					$searchlocale=$se['locale'];
				}
				if ($search_phrase != '') {
					$sedomain = parse_url($referrer);
					$searchdomain = $sedomain['host'];
				}
			}
			//get other search results type, ex: search/x
			if ($search_phrase == '') {
				$se=wSeReferer($ref);
				if (!empty($se['Query']))  {
					$search_phrase = $se['Query'];
					$searchpage = $se['Pos'];
					$searchdomain = $se['Se'];
				//New in v1.9: check for empty secure searches
				} elseif(strpos($ref,'https://www.bing.')!==false || strpos($ref,'https://www.yahoo.')!==false || strpos($ref,'https://www.google.')!==false) {
					$search_phrase = "_notprovided_";
					$searchpage = 1;
					$searchdomain = substr($ref,8);
				} else {
					$searchengine = "";
				}
			}
			if ($search_phrase != '')  {
			if (!empty($searchengine)) {
				if (stristr($searchengine,"images")===FALSE && stristr($referrer,'&imgurl=')===FALSE) {
				// 2011-04-18: "page" parameter is now used on referrer string for Google Images et al.
				if (preg_match('#page[=/](\d+)#i',$referrer,$pcs)>0) {
					if ($searchpage != $pcs[1]) {
						$searchpage = $pcs[1];
					}
				} else {
				// NOTE: Position retrieved in Google Images is the position number of image NOT page rank position like web search
					$searchpage=(int)($searchpage/10)+1;
				}
				}
				//append country code to search engine name
				if (preg_match('/(\.([a-z]{2})$|^([a-z]{2})\.)/i',$searchdomain,$match)) {
					if(!empty($match[2]))$searchcountry=$match[2];
					elseif (!empty($match[3]))$searchcountry=$match[3];
					if(!empty($searchcountry)&& $searchcountry!="us"){
						$searchengine .=" ".strtoupper($searchcountry);
						if($language == "us" || empty($language) || $language=="en"){
							//make tld consistent with language
							if($searchcountry=="uk") $searchcountry="gb";
							elseif($searchcountry=="su") $searchcountry="ru";
							$language=$searchcountry;
						}
					}
				}
			} else {
				$searchengine = $searchdomain;
			}
			//use search engine country code as locale
			$searchlocale = trim($searchlocale);
			if(!empty($searchlocale)){
				if($language == "us" || empty($language) || $language=="en") $language=$searchlocale;
			}
		} //end if search_phrase
		} //end if (!empty($referrer)
		if ($searchpage == "") $searchpage = 0;
		//Prepare to save to table...
		//make sure language is 2-digits and lowercase
		$pcs=array();
		if(!empty($language) && preg_match('/^(?:[a-z]{2}\-)?([a-z]{2})(?:$|,)/i',$language,$pcs)>0) $language=strtolower($pcs[1]);
		else $language="";
		//tag 404 requests in table
		if($req_code==404) $urlRequested="[404] ".$_SERVER['REQUEST_URI'];
		// #Record visit in wassup tables...
		// #create record to add to wassup tables...	
		$wassup_rec = array('wassup_id'=>$wassup_id, 
				'timestamp'=>$timestamp, 
				'ip'=>$ipAddress, 
				'hostname'=>$hostname, 
				'urlrequested'=>$urlRequested, 
				'agent'=>$userAgent,
				'referrer'=>$referrer, 
				'search'=>$search_phrase,
				'searchpage'=>$searchpage,
				'searchengine'=>$searchengine,
				'os'=>$os, 
				'browser'=>$browser, 
				'language'=>$language, 
				'screen_res'=>$wscreen_res, 
				'spider'=>$spider, 
				'feed'=>$feed, 
				'username'=>$logged_user, 
				'comment_author'=>$comment_user, 
				'spam'=>$spam,
				'url_wpid'=>$post_ID,
				'subsite_id'=>$subsite_id,
				);
		// Insert the visit record into wassup temp table
		$temp_recid = wassupDb::table_insert($wassup_tmp_table,$wassup_rec);
		//New in v1.9: Exclude link prefetch and preview requests from wassup main table (keep in wassup_tmp only)
		if((!isset($_SERVER['HTTP_X_MOZ'])||(strtolower($_SERVER['HTTP_X_MOZ'])!='prefetch'))&&(!isset($_SERVER["HTTP_X_PURPOSE"])||strtolower($_SERVER['HTTP_X_PURPOSE'])!='preview')){
			// Insert the visit record into wassup table
			if(!empty($wassup_options->delayed_insert)) $wassup_recid=wassupDb::table_insert($wassup_table,$wassup_rec,true);
			else $wassup_recid=wassupDb::table_insert($wassup_table,$wassup_rec);
			if(is_wp_error($wassup_recid)){
				$errno=$wassup_recid->get_error_code();
				if(!empty($errno)) $error_msg="\nError saving record: $errno: ".$wassup_recid->get_error_message()."\n";
				
				$wassup_recid=false;
			}elseif(empty($wassup_recid) || !is_numeric($wassup_recid)){
				if(!empty($wpdb->insert_id)) $wassup_recid=$wpdb->insert_id;
				elseif(is_numeric($temp_recid)) $wassup_recid=$temp_recid; //positive val for delayed insert
			}
			if($wdebug_mode){
				if(headers_sent()){
					if(!empty($wassup_recid)){
						echo "\nWassUp record data:";
						print_r($wassup_rec);
						echo "*** Visit recorded ***";
					}else{
						echo "\n *** Visit was NOT recorded! *** -->";
					}
				}else{
					if(!empty($wassup_recid)){
						$debug_output .= "\n WassUp record data:\n";
						$debug_output .=print_r($wassup_rec,true); //debug
						$debug_output .= "\n *** Visit recorded ***"; //debug
					}else{
						$debug_output .= "\n *** Visit was NOT recorded! ***"; //debug
					}
				}
			}
		} //end if prefetch
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #16 Excluded by: wp-content/plugins";
		else $debug_output .="\n #16 Excluded by: wp-content/plugins";
	} //end if !wp-content/plugins
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #15 Excluded by: wassup_spam";
		else $debug_output .="\n #15 Excluded by: wassup_spam";
	} //end if $spam == 0
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #14 Excluded by: wassup_spider";
		else $debug_output .="\n #14 Excluded by: wassup_spider";
	} //end if wassup_spider
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #13 Excluded by: dup_urlrequest";
		else $debug_output .="\n #13 Excluded by: dup_urlrequest";
	} //end if dup_urlrequest == 0
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #12 Excluded by: is_404";
		else $debug_output .="\n #12 Excluded by: is_404";
	} //end if !is_404
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #11 Excluded by: wassup_attack";
		else $debug_output .="\n #11 Excluded by: wassup_attack";
	} //end if wassup_attack
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #10 Excluded by: wassup_loggedin";
		else $debug_output .="\n #10 Excluded by: wassup_loggedin";
	} //end if wassup_loggedin
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #9 Excluded by: wp-content/themes";
		else $debug_output .="\n #9 Excluded by: wp-content/themes";
	} //end if !themes
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #8 Excluded by: exclude_visit";
		else $debug_output .="\n #8 Excluded by: exclude_visit";
	} //end if !exclude_visit
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #7 Excluded by: exclude_host";
		else $debug_output .="\n #7 Excluded by: exclude_host";
	} //end if wassup_exclude_host
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #6 Excluded by: wassup_exclude";
		else $debug_output .="\n #6 Excluded by: wassup_exclude";
	} //end if wassup_exclude
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #5 Excluded by: exclude_url";
		else $debug_output .="\n #5 Excluded by: exclude_url";
	} //end if wassup_exclude_url
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #4 Excluded by: exclude_user";
		else $debug_output .="\n #4 Excluded by: exclude_user";
	} //end if wassup_exclude_user
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #3 Excluded by: is_admin";
		else $debug_output .="\n #3 Excluded by: is_admin";
	} //end if !is_admin
	} //end if wp-cron.php?doing_wp_cron===FALSE
	}elseif($wdebug_mode){
		if(headers_sent()) echo "\n #1 Excluded by: is_admin_login";
		else $debug_output .="\n #1 Excluded by: is_admin_login";
	} //end if !is_admin_login

	//New in v.1.9: add excluded visitors to wassup_tmp table for accurate online counts
	if(empty($temp_recid)){
		$in_temp=0;
		//check that visitor is not already in temp
		if(!empty($logged_user)){
			//check for username
			$result=$wpdb->get_var(sprintf("SELECT COUNT(`wassup_id`) AS in_temp FROM $wassup_tmp_table WHERE `wassup_id`='%s' AND `timestamp`>'%d' AND `username`!=''",$wassup_id,$timestamp - 540));
		}else{
			$result=$wpdb->get_var(sprintf("SELECT COUNT(`wassup_id`) AS in_temp FROM $wassup_tmp_table WHERE `wassup_id`='%s' AND `timestamp`>'%d' AND spider=''",$wassup_id,$timestamp-160));
		}
		if(is_numeric($result)) $in_temp=(int)$result;
		if($wdebug_mode){
			if(headers_sent()) echo "\nin_temp=".$result;
			else $debug_output .="\nin_temp=".$result;
		}
		//add new temp record 
		if($in_temp==0){
		if(empty($wassup_rec)){
			$pcs=array();
			if(!empty($language) && preg_match('/^(?:[a-z]{2}\-)?([a-z]{2})(?:$|,)/i',$language,$pcs)>0) $language=strtolower($pcs[1]);
			else $language="";
			//tag 404 requests in table
			if($req_code=="404") $urlRequested="[404] ".$_SERVER['REQUEST_URI'];
			// #Record visit in wassup tables...
			$wassup_rec = array('wassup_id'=>$wassup_id, 
				'timestamp'=>$timestamp, 
				'ip'=>$ipAddress, 
				'hostname'=>$hostname, 
				'urlrequested'=>$urlRequested, 
				'agent'=>$userAgent,
				'referrer'=>$referrer, 
				'search'=>$search_phrase,
				'searchpage'=>$searchpage,
				'searchengine'=>$searchengine,
				'os'=>$os, 
				'browser'=>$browser, 
				'language'=>$language, 
				'screen_res'=>$wscreen_res, 
				'spider'=>$spider, 
				'feed'=>$feed, 
				'username'=>$logged_user, 
				'comment_author'=>$comment_user, 
				'spam'=>$spam,
				'url_wpid'=>$post_ID,
				'subsite_id'=>$subsite_id,
				);
		}
			//insert the record into the wassup_tmp table
			$temp_recid = wassupDb::table_insert($wassup_tmp_table, $wassup_rec);
		}
	} //end if temp_recid
	$timenow=time();
	//## Automatic database monitoring and cleanup tasks...check every few visits
	//# Notify admin if alert is set and wassup table > alert
	if((int)$timestamp%139 == 0){
	if($wassup_options->wassup_remind_flag == 1 && (int)$wassup_options->wassup_remind_mb>0){
	//for multisite with network table, only main site gets alert
	if(!is_multisite() || empty($network_settings['wassup_table']) || is_main_site()){
		$tusage=0;
		$fstatus = wassupDb::table_status($wassup_table);
		$data_lenght = 0;
		if (!empty($fstatus) && is_object($fstatus)) {
			//New in v1.9: db size includes index size
			$data_lenght=$fstatus->Data_length+$fstatus->Index_length;
			$tusage = ($data_lenght/1024/1024);
		} 
		if($tusage >0 && $tusage > $wassup_options->wassup_remind_mb){
			$recipient = get_bloginfo('admin_email');
			$sender = 'From: '.get_bloginfo('name').' <wassup_noreply@'.parse_url($blogurl,PHP_URL_HOST).'>';
                        $subject=sprintf(__("%s WassUp Plugin table has reached maximum size!","wassup"),'['.__("ALERT","wassup").']');
                        $message = __('Hi','wassup').",\n".__('you have received this email because your WassUp Database table at your Wordpress blog','wassup')." ($wpurl) ".__('has reached the maximum value set in the options menu','wassup')." (".$wassup_options->wassup_remind_mb." Mb).\n\n";
                        $message .= __('This is only a reminder, please take the actions you want in the WassUp options menu','wassup')." (".admin_url("admin.php?page=wassup-options").")\n\n".__('This alert now will be removed and you will be able to set a new one','wassup').".\n\n";
                        $message .= __('Thank you for using WassUp plugin. Check if there is a new version available here:','wassup')." http://wordpress.org/extend/plugins/wassup/\n\n".__('Have a nice day!','wassup')."\n";
                        wp_mail($recipient, $subject, $message, $sender);
                        $wassup_options->wassup_remind_flag = 2;
                        $wassup_options->saveSettings();
		}
	} //end if !multisite
	} //if wassup_remind_flag
	} //if timestamp%139
	
	//# schedule purge of temporary records - also done hourly in wp-cron
	if(((int)$timestamp)%11 == 0){
		$starttime=0;
		if(version_compare($wp_version,'2.8','<')) $starttime=wp_next_scheduled('wassup_scheduled_cleanup');
		if(empty($starttime) || ($starttime - $timenow) >660){
			//New in v1.9: keep logged-in user records in temp for up to 10 minutes, anonymous user records for up to 3 minutes, and spider records for only 1 minute
			$wassup_dbtask[]=sprintf("DELETE FROM `%s` WHERE `timestamp`<'%d' OR (`timestamp`<'%d' AND `username`='') OR (`timestamp`<'%d' AND `spider`!='')",$wassup_tmp_table,(int)$timestamp - 10*60,(int)$timestamp - 3*60,(int)$timestamp - 60);
			if(((int)$timestamp)%5 == 0){
				//Purge expired cache data from wassup_meta 
				$result=$wpdb->query(sprintf("DELETE FROM `%s` WHERE `meta_expire`>'0' AND `meta_expire`<'%d'",$wassup_meta_table,$timenow - 3600));
			}
		}
	}
	//# schedule table optimization ...check every few visits
	if(((int)$timestamp)%141 == 0){
		//New in v1.9: wassup table is optimized once a week - to prevent overuse of this costly mysql operation
		$optimize_dbtask=array();
		//Optimize table when optimize timestamp is older than current time
		if(!empty($wassup_options->wassup_optimize) && is_numeric($wassup_options->wassup_optimize) && $timenow >(int)$wassup_options->wassup_optimize){
			$optimize_sql=sprintf("OPTIMIZE TABLE %s",$wassup_table);
			$args=array('dbtasks'=>array("$optimize_sql"));
			if(version_compare($wp_version,'2.8','<')){
				$wassup_dbtask[]=$optimize_sql;
			}else{
				wp_schedule_single_event(time()+620,'wassup_scheduled_optimize',$args);
			}
			//save new optimize timestamp
			$wassup_options->wassup_optimize = $wassup_options->defaultSettings('wassup_optimize');
			$wassup_options->saveSettings();
		}
	}
	//# Lastly, perform scheduled database tasks 
	if(count($wassup_dbtask)>0){
		$args=array('dbtasks'=>$wassup_dbtask);
		if(is_admin() || version_compare($wp_version,'2.8','<')){
			wassupDb::scheduled_dbtask($args);
		}else{
			wp_schedule_single_event(time()+40,'wassup_scheduled_dbtasks',$args);
		}
		if($wdebug_mode){
			if(headers_sent()){
				echo "\nWassup scheduled tasks:";
				print_r($wassup_dbtask);
			}
		}
	}
	if($wdebug_mode){ //close comment tag to hide debug data from visitors 
		if(headers_sent()){
			echo "\n--> \n";
		}else{
			$debug_output .= "<br />\n--> \n";
			//add debug output to wp_footer output - TODO
			//update_wassupmeta('wassup_footer','debug_output',$expire,$debug_output); //TODO
		}
		//restore normal mode
		@ini_set('display_errors',$mode_reset);
	} //end if wdebug_mode
} //end wassupAppend()

/**
 * Assign an id for current visitor session from a combination of date/hour/min/ip/loggeduser/useragent/hostname.
 * This is not unique so that multiple visits from the same ip/userAgent within a 30 minute-period, can be tracked, even when session/cookies is disabled. 
 * @since v1.9
 * @param args (array)
 * @return string
 */
function wassup_get_sessionid($args=array()){
	if(!empty($args) && is_array($args)) extract($args);
	if(empty($timestamp)) $timestamp=current_time('timestamp');
	if(empty($ipAddress)) $ipAddress=wassup_get_clientAddr();
	if(empty($hostname)){
		if(empty($IP)) $IP=wassup_clientIP($ipAddress);
		$hostname=wassup_get_hostname($IP);
	}
	if(empty($subsite_id)) $subsite_id=(!empty($GLOBALS['current_blog']->blog_id)?$GLOBALS['current_blog']->blog_id:0);
	$tempUA="";
	if(isset($_SERVER['HTTP_USER_AGENT'])) $tempUA=str_replace(array(' ','	','http://','www.','%20','+','%','&','#','.','$',';',':','-','>','<','`','*','/','\\','"','\'','!','@','=','_',')','('),'',preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['HTTP_USER_AGENT']));
	$templen=strlen($tempUA);
	if($templen==0) $tempUA="UnknownSpider";
	elseif($templen<10) $tempUA .=$templen."isTooSmall";
	if(!empty($logged_user)){
		$sessiontime=intval(gmdate('i',$timestamp)/90);
		$temp_id=sprintf("%-040.40s",gmdate('Ymd',$timestamp).$sessiontime.str_replace(array('.',':','-'),'',substr(strrev($ipAddress),2).strrev($logged_user).strrev($tempUA).$sessiontime.gmdate('dmY',$timestamp).strrev($hostname)).$templen.rand());
	}else{
		$sessiontime=intval(gmdate('i',$timestamp)/30);
		$temp_id=sprintf("%-040.40s",gmdate('YmdH',$timestamp).$sessiontime.str_replace(array('.',':','-'),'',substr(strrev($ipAddress),2).strrev($tempUA).$sessiontime.gmdate('HdmY',$timestamp).strrev($hostname)).$templen.rand());
	}
	//#assign new wassup id from "temp_id" 
	$wassup_id= (int)$subsite_id.'b_'.md5($temp_id);
	return $wassup_id;
} //end wassup_get_sessionid

/** 
 * Retrieve a hash value to assign to a session cookie
 * - replaces 'COOKIEHASH' which breaks up a continuous session with user login/reauthorization
 */
function wassup_get_sessionhash($ip=0){
	global $wassup_options;
	if(empty($ip)) $ip=$_SERVER['REMOTE_ADDR'];
	$sessionhash=wassupDb::get_wassupmeta($ip,'_sessionhash');
	if(empty($sessionhash)){
		$sessionhash=$wassup_options->whash;
		//keep this hash value for 2 hours so still works even if wassup_options (and whash) is reset
		$expire=time()+2*3600;
		$cacheid=wassupDb::update_wassupmeta($ip,'_sessionhash',$sessionhash,$expire);
	}
	return $sessionhash;
}
/**
 * search url string for key=value pairs and assign to assoc array
 * @access public
 * @return array
 */
function wGetQueryVars($urlstring){
	$qvar = array();
	if (!empty($urlstring)) {
		$wtab=parse_url($urlstring);
		if (key_exists("query",$wtab)){
			$query=$wtab["query"];
		} else {
			$query=$urlstring; //for partial urls
		}
		$i=0;
		//New in v1.9: replaced explode with preg_match for faster performance
		while($query){
			if(preg_match('/^[?&]?([^=&]+)(=[^&]+)?/',$query,$pcs)>0){
				$name=$pcs[1];
				if(empty($pcs[2]))$qvar[$name]=true;
				else $qvar[$name]=substr($pcs[2],1);
				$newquery=substr($query,strlen($pcs[0])+1);
				$query=$newquery;
			}else{
				$name=$query;
				$qvar[$name]=true;
				$query="";
			}
			$i++;
			if($i >=20)break; //bad query, end loop
		} //end while
	}
	return $qvar;
} //end wGetQueryVars

/**
 * Find search engine referrals from lesser-known search engines or from engines that use a url-format (versus GET) for search query
 * @param boolean
 * @return array
 */
function wSeReferer($ref = false) {
	$SeReferer = (is_string($ref) ? $ref : mb_convert_encoding(strip_tags($_SERVER['HTTP_REFERER']), "HTML-ENTITIES", "auto"));
	if ($SeReferer == "") { return false; }
	//Check against Google, Yahoo, MSN, Ask and others
	if(preg_match("#^https?://([^/]+).*[&\?](prev|q|p|s|search|searchfor|as_q|as_epq|query|keywords|term|encquery)=([^&]+)#i",$SeReferer,$pcs) > 0){
		$SeDomain = trim(strtolower($pcs[1]));
		if ($pcs[2] == "encquery") { 
			$SeQuery = " *".__("encrypted search","wassup")."* ";
		} else { 
			$SeQuery = $pcs[3];
		}

	//Check for search engines that show query as a url with 'search' and keywords in path (ex: Dogpile.com)
	} elseif (preg_match("#^https?://([^/]+).*/(results|search)/web/([^/]+)/(\d+)?#i", $SeReferer,$pcs)>0){
		$SeDomain = trim(strtolower($pcs[1]));
		$SeQuery = $pcs[3];
		if (!empty($pcs[4])) {
			$SePos=(int)$pcs[4];	//v1.9 bugfix
		}
	//Check for search engines that show query as a url with 'search' in domain and keywords in path (ex: twitnitsearch.appspot.com)
	} elseif (preg_match("#^https?://([a-z0-9_\-\.]*(search)(?:[a-z0-9_\-\.]*\.(?:[a-z]{2,4})))/([^/]+)(?:[a-z_\-=/]+)?/(\d+)?#i",$SeReferer."/",$pcs)>0) {
		$SeDomain = trim(strtolower($pcs[1]));
		$SeQuery = $pcs[3];
		if (!empty($pcs[4])) {
			$SePos=(int)$pcs[4];	//v1.9 bugfix
		}
	}
	unset ($pcs);
	//-- We have a query
	if(isset($SeQuery)){ 
		// Multiple URLDecode Trick to fix DogPile %XXXX Encodes
		if (strstr($SeQuery,'%')) {
			$OldQ=$SeQuery;
			$SeQuery=urldecode($SeQuery);
			while($SeQuery != $OldQ){
				$OldQ=$SeQuery;
				$SeQuery=urldecode($SeQuery);
			}
		}
		if (!isset($SePos)) { 
			if (preg_match("#[&\?](start|startpage|b|cd|first|stq|pi|page)[=/](\d+)#i",$SeReferer,$pcs)) {
				$SePos = $pcs[2];
			} else {
				$SePos = 1;
			}
			unset ($pcs);
		}
		$searchdata=array("Se"=>$SeDomain, "Query"=>$SeQuery,
				  "Pos"=>$SePos, "Referer"=>$SeReferer);
	} else {
		$searchdata=false;
	}
	return $searchdata;
} //end function wSeReferrer

/**
 * Parse referrer string for match from a list of known search engines and, if found, return an array containing engine name, search keywords, results page#, and language.
 * Notes:
 * -To distinguish "images", "mobile", or other types of searches from regular text searches, the "images" and "mobile" domains must be listed above the "text" domain in search engines array.
 * - New or obscure search engines, search engines with a URL-formatted referrer string, and any search engine not listed here, are identified by another function, "wSeReferer()".
 *
 * @param string
 * @return array
 */
function wGetSE($referrer = null){
	$key = null;
	$search_phrase="";
	$searchpage="";
	$searchengine="";
	$searchlang="";
	$selocale="";
	$blogurl = preg_replace('#(https?\://)?(www\.)?#','',strtolower(get_option('home')));
	//list of well known search engines. 
	//  Structure: "SE Name|SE Domain(partial+unique)|query_key|page_key|language_key|locale|charset|"
	$lines = array(
		"360search|so.360.com|q|||cn|utf8|",
		"360search|www.so.com|q|||cn|utf8|",
		"Abacho|.abacho.|q|||||",
		"ABC Sok|verden.abcsok.no|q|||no||",
		"Aguea|chercherfr.aguea.com|q|||fr||",
		"Alexa|.alexa.com|q|||||",
		"Alice Adsl|rechercher.aliceadsl.fr|qs|||fr||",
		"Allesklar|www.allesklar.|words|||de||",
		"AllTheWeb|.alltheweb.com|q|||||",
		"All.by|.all.by|query|||||",
		"Altavista|.altavista.|q|||||",
		"Altavista|.altavista.|aqa|||||", //advanced query
		"Apollo Lv|apollo.lv|q|||lv||",
		"Apollo7|apollo7.de|query|||de||",
		"AOL|recherche.aol.|query|||||",
		"AOL|search.aol.|query|||||",
		"AOL|.aol.|query|||||",
		"AOL|.aol.|q|||||",
		"Aport|sm.aport.ru|r|||ru||",
		"Arama|.arama.com|q|||de||",
		"Arcor|.arcor.de|Keywords|||de||",
		"Arianna|arianna.libero.it|query|||it||",
		"Arianna|.arianna.com|query|||it|",
		"Ask|.ask.com|ask|||||",
		"Ask|.ask.com|q|||||",
		"Ask|.ask.com|searchfor|||||",
		"Ask|.askkids.com|ask|||||",
		"Atlas|search.atlas.cz|q|||cz||",
		"Atlas|searchatlas.centrum.cz|q|||cz||",
		"auone Images|image.search.auone.jp|q|||jp||",
		"auone|search.auone.jp|q|||jp||",
		"Austronaut|.austronaut.at|q|||at||",
		"avg.com|search.avg.com|q|cd|hl|||",
		"Babylon|search.babylon.com|q|||||",
		"Baidu|.baidu.com|wd|||cn|utf8|", 
		"Baidu|.baidu.com|word|||cn|utf8|", 
		"Baidu|.baidu.com|kw|||cn|utf8|",
		"Biglobe Images|images.search.biglobe.ne.jp|q|||jp||",
		"Biglobe|.search.biglobe.ne.jp|q|||jp||",
		"Bing Images|.bing.com/images/|q|first||||", 
		"Bing Images|.bing.com/images/|Q|first||||", 
		"Bing|.bing.com|q|first||||", 
		"Bing|.bing.com|Q|first||||", 
		"Bing|search.msn.|q|first|||", 
		"Bing|.it.msn.com|q|first||it||", 
		"Bing|msnbc.msn.com|q|first||||", 
		"Bing Cache|cc.bingj.com|q|first||||", 
		"Bing Cache|cc.bingj.com|Q|first||||", 
		"Blogdigger|.blogdigger.com|q|||||",
		"Blogpulse|.blogpulse.com|query|||||",
		"Bluewin|.bluewin.ch|q|||ch||",
		"Bluewin|.bluewin.ch|searchTerm|||ch||",
		"Centrum|.centrum.cz|q|||cz||",
		"chedot.com|search.chedot.com|text|||||",
		"Claro Search|claro-search.com|q|||||",
		"Clix|pesquisa.clix.pt|question|||pt||",
		"Conduit Images|images.search.conduit.com|q|||||",
		"Conduit|search.conduit.com|q|||||",
		"Comcast|search.comcast.net|q|||||",
		"Crawler|www.crawler.com|q|||||",
		"Compuserve|websearch.cs.com|query|||||",
		"Darkoogle|.darkoogle.com|q|cd|hl|||",
		"DasTelefonbuch|.dastelefonbuch.de|kw|||de||",
		"Daum|search.daum.net|q|||||",
		"Delfi Lv|smart.delfi.lv|q|||lv||",
		"Delfi EE|otsing.delfi.ee|q|||ee||",
		"Digg|digg.com|s|||||",
		"dir.com|fr.dir.com|req|||fr||",
		"DMOZ|.dmoz.org|search|||||",
		"DuckDuckGo|duckduckgo.com|q|||||",
		"Earthlink|.earthlink.net|q|||||",
		"Eniro|.eniro.se|q|||se||",
		"Euroseek|.euroseek.com|string|||||",
		"Everyclick|.everyclick.com|keyword|||||",
		"Excite|search.excite.|q|||||",
		"Excite|.excite.co.jp|search|||jp||",
		"Exalead|.exalead.fr|q|||fr||",
		"Exalead|.exalead.com|q|||fr||",
		"eo|eo.st|x_query|||st||",
		"Facebook|.facebook.com|q|||||",
		"Facemoods|start.facemoods.com|s|||||",
		"Fast Browser Search|.fastbrowsersearch.com|q|||||",
		"Francite|recherche.francite.com|name|||fr||",
		"Findhurtig|.findhurtig.dk|q|||dk||",
		"Fireball|.fireball.de|q|||de||",
		"Firstfind|.firstsfind.com|qry|||||",
		"Fixsuche|.fixsuche.de|q|||de||",
		"Flix.de|.flix.de|keyword|||de||",
		"Fooooo|search.fooooo.com|q|||||",
		"Free|.free.fr|q|||fr||",
		"Free|.free.fr|qs|||fr||",
		"Freecause|search.freecause.com|p|||||",
		"Freenet|suche.freenet.de|query|||de||",
		"Freenet|suche.freenet.de|Keywords|||de||",
		"Gnadenmeer|.gnadenmeer.de|keyword|||de||",
		"Godago|.godago.com|keywords||||",	//check
		"Gomeo|.gomeo.com|Keywords|||||",
		"goo|.goo.ne.jp|MT|||jp||",
		"Good Search|goodsearch.com|Keywords|||||",
		"Google|.googel.com|q|cd|hl|||",
		"Google|www.googel.|q|cd|hl|||",
		"Google|wwwgoogle.com|q|cd|hl|||",
		"Google|gogole.com|q|cd|hl|||",
		"Google|gppgle.com|q|cd|hl|||",
		"Google Blogsearch|blogsearch.google.|q|start||||",
		"Google Custom Search|google.com/cse|q|cd|hl|||",
		"Google Custom Search|google.com/custom|q|cd|hl|||",
		"Google Groups|groups.google.|q|start||||", 
		"Google Images|.google.com/images?|q|cd|hl|||",
		"Google Images|images.google.|q|cd|hl|||",
		"Google Images|/imgres?imgurl=|prev|start|hl|||", //obsolete
		"Google Images JP|image.search.smt.docomo.ne.jp|MT|cd|hl|jp||",
		"Google JP|search.smt.docomo.ne.jp|MT|cd|hl|jp||",
		"Google JP|.nintendo.co.jp|gsc.q|cd|hl|jp||",
		"Google Maps|maps.google.|q||hl|||",
		"Google News|news.google.|q|cd|hl|||",
		"Google Scholar|scholar.google.|q|cd|hl|||",
		"Google Shopping|google.com/products|q|cd|hl|||",
		"Google syndicated search|googlesyndicatedsearch.com|q|cd|hl|||",
		"Google Translate|translate.google.|prev|start|hl|||",
		"Google Translate|translate.google.|q|cd|hl|||",
		"Google Translate|translate.googleusercontent.com|prev|start|hl|||",
		"Google Translate|translate.googleusercontent.com|q|cd|hl|||",
		"Google Video|video.google.com|q|cd|hl|||",
		"Google Cache|.googleusercontent.com|q|cd|hl|||",
		"Google Cache|http://64.233.1|q|cd|hl|||", 
		"Google Cache|http://72.14.|q|cd|hl|||", 
		"Google Cache|http://74.125.|q|cd|hl|||", 
		"Google Cache|http://209.85.|q|cd|hl|||", 
		"Google|www.google.|q|cd|hl|||",
		"Google|www.google.|as_q|start|hl|||",
		"Google|.google.com|q|cd|hl|||",
		"Google|.google.com|as_q|start|hl|||",
		"Gooofullsearch|.gooofullsearch.com|q|cd|hl|||",
		"Goyellow.de|.goyellow.de|MDN|||de||",
		"Hit-Parade|.hit-parade.com|p7|||||",
		"Hooseek.com|.hooseek.com|recherche|||||",
		"HotBot|hotbot.|query|||||",
		"ICQ Search|.icq.com|q|||||",
		"Ilse NL|.ilse.nl|search_for|||nl||",
		"Incredimail|.incredimail.com|q|||||",
		"InfoSpace|infospace.com|q|||||",
		"InfoSpace|dogpile.com|q|||||",
		"InfoSpace|search.fbdownloader.com|q|||||",
		"InfoSpace|searches3.globososo.com|q|||||",
		"InfoSpace|search.kiwee.com|q|||||",
		"InfoSpace|metacrawler.com|q|||||",
		"InfoSpace|tattoodle.com|q|||||",
		"InfoSpace|searches.vi-view.com|q|||||",
		"InfoSpace|webcrawler.com|q|||||",
		"InfoSpace|webfetch.com|q|||||",
		"InfoSpace|search.webssearches.com|q|||||",
		"Ixquick|ixquick.com|query|||||", 
		"Ixquick|ixquick.de|query|||de||", 
		"Jyxo|jyxo.1188.cz|q|||cz||",
		"Jumpy|.mediaset.it|searchWord|||it||",
		"Kataweb|kataweb.it|q|||it||",
		"Kvasir|kvasir.no|q|||no||",
		"Kvasir|kvasir.no|searchExpr|||no||",
		"Latne|.latne.lv|q|||lv||",
		"Looksmart|.looksmart.com|key|||||",
		"Lo.st|.lo.st|x_query|||||",
		"Lycos|.lycos.com|query|||||",
		"Lycos|.lycos.it|query|||it||",
		"Lycos|.lycos.|query||||",
		"Lycos|.lycos.|q|||||",
		"maailm.com|.maailm.com|tekst|||||",
		"Mail.ru|.mail.ru|q|||ru|utf8|",
		"MetaCrawler DE|.metacrawler.de|qry|||de||",
		"Metager|meta.rrzn.uni-hannover.de|eingabe|||de||",
		"Metager|metager.de|eingabe|||de||",
		"Metager2|.metager2.de|q|||de||",
		"Meinestadt|.meinestadt.de|words|||||",
		"Mister Wong|.mister-wong.com|keywords|||||",
		"Mister Wong|.mister-wong.de|keywords|||||",
		"Monstercrawler|.monstercrawler.com|qry|||||",
		"Mozbot|.mozbot.fr|q|||fr||",
		"Mozbot|.mozbot.co.uk|q|||gb||",
		"Mozbot|.mozbot.com|q|||||",
		"El Mundo|.elmundo.es|q|||es||",
		"MySpace|.myspace.com|qry|||||",
		"MyWebSearch|.mywebsearch.com|searchfor||||||",
		"MyWebSearch|.mywebsearch.com|searchFor||||||",
		"MyWebSearch|.mysearch.com|searchfor||||||",
		"MyWebSearch|.mysearch.com|searchFor||||||",
		"MyWebSearch|search.myway.com|searchfor||||||",
		"MyWebSearch|search.myway.com|searchFor||||||",
		"Najdi|.najdi.si|q|||si||",
		"Nate|.nate.com|q|||kr|EUC-KR|", //check charset
		"Naver|.naver.com|query|||kr||",
		"Needtofind|search.need2find.com|searchfor|||||",
		"Neti|.neti.ee|query|||ee|iso-8859-1|",
		"Nifty Videos|videosearch.nifty.com|kw|||||",
		"Nifty|.nifty.com|q|||||",
		"Nifty|.nifty.com|Text|||||",
		"Nifty|search.azby.fmworld.net|q|||||",
		"Nifty|search.azby.fmworld.net|Text|||||",
		"Nigma|nigma.ru|s|||ru||",
		"Onet.pl|szukaj.onet.pl|qt|||pl||",
		"OpenDir.cz|.opendir.cz|cohledas|||cz||",
		"Opplysningen 1881|.1881.no|Query|||no||",
		"Orange|busca.orange.es|q|||es||",
		"Orange|lemoteur.ke.voila.fr|kw|||fr||",
		"PagineGialle|paginegialle.it|qs|||it||",
		"Picsearch|.picsearch.com|q|||||",
		"Poisk.Ru|poisk.ru|text|||ru|windows-1251|",
		"qip.ru|search.qip.ru|query|||ru||",
		"Qualigo|www.qualigo.|q|||||",
		"Rakuten|.rakuten.co.jp|qt|||jp||",
		"Rambler|nova.rambler.ru|query|||ru||",
		"Rambler|nova.rambler.ru|words|||ru||",
		"RPMFind|rpmfind.net|query|||||",
		"Road Runner|search.rr.com|q|||||",
		"Sapo|pesquisa.sapo.pt|q|||pt||",
		"Search.com|.search.com|q|||||", 
		"Search.ch|.search.ch|q|||ch||",
		"Searchy|.searchy.co.uk|q|||gb||",
		"Setooz|.setooz.com|query|||||",
		"Seznam Videa|videa.seznam.cz|q|||cz||",
		"Seznam|.seznam.cz|q|||cz||",
		"Sharelook|.sharelook.fr|keyword|||fr||",
		"Skynet|.skynet.be|q|||be||",
		"sm.cn|.sm.cn|q|||cn||",
		"sm.de|.sm.de|q|||de||",
		"SmartAdressbar|.smartaddressbar.com|s|||||",
		"So-net Videos|video.so-net.ne.jp|kw|||jp||",
		"So-net|.so-net.ne.jp|query|||jp||",
		"Sogou|.sogou.com|query|||cn|gb2312|",
		"Sogou|.sogou.com|keyword|||cn|gb2312|",
		"Soso|.soso.com|q|||cn|gb2312|",
		"Sputnik|.sputnik.ru|q|||ru||",
		"Start.no|start.no|q|||||", 
		"Startpagina|.startpagina.nl|q|cd|hl|nl||",
		"Suche.info|suche.info|Keywords|||||",
		"Suchmaschine.com|.suchmaschine.com|suchstr|||||",
		"Suchnase|.suchnase.de|q|||de||",
		"Supereva|supereva.it|q|||it||",
		"T-Online|.t-online.de|q|||de|",
		"TalkTalk|.talktalk.co.uk|query|||gb||",
		"Teoma|.teoma.com|q|||||",
		"Terra|buscador.terra.|query|||||",
		"Tiscali|.tiscali.it|query|||it||",
		"Tiscali|.tiscali.it|key|||it||",
		"Tiscali|.tiscali.cz|query|||cz||",
		"Tixuma|.tixuma.de|sc|||de||",
		"La Toile Du Quebec|.toile.com|q|||ca||",
		"Toppreise.ch|.toppreise.ch|search|||ch|ISO-8859-1|",
		"TrovaRapido|.trovarapido.com|q|||||",
		"Trusted-Search|.trusted-search.com|w|||||",
		"URL.ORGanzier|www.url.org|q||l|||",
		"Vinden|.vinden.nl|q|||nl||",
		"Vindex|.vindex.nl|search_for|||nl||",
		"Virgilio|mobile.virgilio.it|qrs|||it||",
		"Virgilio|.virgilio.it|qs|||it||",
		"Voila|.voila.fr|rdata|||fr||",
		"Voila|.lemoteur.fr|rdata|||fr||",
		"Volny|.volny.cz|search|||cz||",
		"Walhello|.walhello.info|key|||||",
		"Walhello|www.walhello.|key|||||",
		"Web.de|suche.web.de|su|||de||",
		"Web.de|suche.web.de|q|||de||",
		"Web.nl|.web.nl|zoekwoord|||nl||",
		"Weborama|.weborama.fr|QUERY|||fr||",
		"WebSearch|.websearch.com|qkw|||||",
		"WebSearch|.websearch.com|q|||||",
		"Wedoo|.wedoo.com|keyword|||||",
		"Winamp|search.winamp.com|q|||||",
		"Witch|www.witch.de|search|||de||",
		"Wirtualna Polska|szukaj.wp.pl|szukaj|||pl||",
		"Woopie|www.woopie.jp|kw|||jp||",
		"wwW.ee|search.www.ee|query|||ee||",
		"X-recherche|.x-recherche.com|MOTS|||||",
		"Yahoo! Directory|search.yahoo.com/search/dir|p|||||",
		"Yahoo! Videos|video.search.yahoo.co.jp|p|||jp||",
		"Yahoo! Images|image.search.yahoo.co.jp|p|||jp||",
		"Yahoo! Images|images.search.yahoo.com|p|||||",
		"Yahoo! Images|images.search.yahoo.com|va|||||",
		"Yahoo! Images|images.yahoo.com|p|||||",
		"Yahoo! Images|images.yahoo.com|va|||||",
		"Yahoo!|search.yahoo.co.jp|p|||jp||",
		"Yahoo!|search.yahoo.co.jp|vp|||jp||",
		"Yahoo!|jp.hao123.com|query|||jp||",
		"Yahoo!|home.kingsoft.jp|keyword|||jp||",
		"Yahoo!|search.yahoo.com|p|||||",
		"Yahoo!|search.yahoo.com|q|||||",
		"Yahoo!|search.yahoo.|p|||||",
		"Yahoo!|search.yahoo.|q|||||",
		"Yahoo!|answers.yahoo.com|p|||||",
		"Yahoo!|.yahoo.com|p|||||",
		"Yahoo!|.yahoo.com|q|||||",
		"Yam|search.yam.com|k|||||",
		"Yandex Images|images.yandex.ru|text|||ru||",
		"Yandex Images|images.yandex.com|text|||ru||",
		"Yandex Images|images.yandex.|text|||||",
		"Yandex|yandex.ru|text|||ru||",
		"Yandex|yandex.com|text|||ru||",
		"Yandex|.yandex.|text|||||",
		"Yasni|.yasni.com|query|||||",
		"Yasni|www.yasni.|query|||||",
		"Yatedo|.yatedo.com|q|||||",
		"Yatedo|.yatedo.fr|q|||fr||",
		"Yippy|search.yippy.com|query|||||",
		"YouGoo|www.yougoo.fr|q|||fr||",
		"Zapmeta|.zapmeta.com|q|||||",
		"Zapmeta|.zapmeta.com|query|||||",
		"Zapmeta|www.zapmeta.|q|||||",
		"Zapmeta|www.zapmeta.|query|||||",
		"Zhongsou|p.zhongsou.com|w|||||",
		"Zoohoo|zoohoo.cz|q|||cz|windows-1250|",
		"Zoznam|.zoznam.sk|s|||sk||",
		"Zxuso|.zxuso.com|wd|||||",
	);
	foreach($lines as $line_num => $serec) {
		list($nome,$domain,$key,$page,$lang,$selocale,$charset)=explode("|",$serec);
		//match on both domain and key..
		if (strpos($domain,'http') === false) {
			$se_regex='/^https?\:\/\/[a-z0-9\.\-]*'.preg_quote($domain,'/').'.*[&\?]'.$key.'\=([^&]+)/i';
		} else {
			$se_regex='/^'.preg_quote($domain,'/').'.*[&\?]'.$key.'\=([^&]+)/i';
		}
		$se = preg_match($se_regex,$referrer,$match);
		if (!$se && strpos($referrer,$domain)!==false && strpos(urldecode($referrer),$key.'=')!==false) {
			$se=preg_match($se_regex,urldecode($referrer),$match);
		}
		if ($se) {	// found it!
			$searchengine = $nome;
			$search_phrase = "";
			$svariables=array();
			// Google Images or Google Translate needs additional processing of search phrase after 'prev='
			if ($nome == "Google Images" || $nome == "Google Translate") {
				//'prev' is an encoded substring containing actual "q" query, so use html_entity_decode to show [&?] in url substring
				$svariables = wGetQueryVars(html_entity_decode(preg_replace('#/\w+\?#i','', urldecode($match[1]))));
				$key='q';	//q is actual search key
			} elseif ($nome == "Google Cache") {
				$n = strpos($match[1],$blogurl);
				if ($n !== false) {
				//blogurl in search phrase: cache of own site
					$search_phrase = esc_attr(urldecode(substr($match[1],$n+strlen($blogurl))));
					$svariables = wGetQueryVars($referrer);
				} elseif (strpos($referrer,$blogurl)!==false && preg_match('/\&prev\=([^&]+)/',$referrer,$match)!==false) {
					//NOTE: 'prev=' requires html_entity_decode to show [&?] in url substring
					$svariables = wGetQueryVars(html_entity_decode(preg_replace('#/\w+\?#i','', urldecode($match[1]))));
				} else {
				//no blogurl in search phrase: cache of an external site with referrer link
					$searchengine = "";
					$referrer = "";
				}
			} else {
				$search_phrase = esc_attr(urldecode($match[1]));
				$svariables = wGetQueryVars($referrer);
			}
			//retrieve search engine parameters
			//New in v1.9: removed slow 'explode' command
			if(!empty($svariables[$key])&& empty($search_phrase))$search_phrase=esc_attr($svariables[$key]);
			if(!empty($page)&& !empty($svariables[$page])&& is_numeric($svariables[$page]))$searchpage=(int)$svariables[$page];
			if(!empty($lang)&& !empty($svariables[$lang])&& strlen($svariables[$lang])>1)$searchlang=esc_attr($svariables[$lang]);
			//Indentify locale via Google search's parameter, 'gl'
			if(strstr($nome,'Google')!==false && !empty($svariables['gl']))$selocale=esc_attr($svariables['gl']);
			break 1;

		} elseif (strstr($referrer,$domain)!==false) {
			$searchengine = $nome;
		} //end if preg_match
	} //end foreach
	//search engine or key is not in list, so check for search phrase instead
	if (empty($search_phrase) && !empty($referrer)) {
		//Check for general search phrases
		if (preg_match("#^https?://([^/]+).*[&?](q|search|searchfor|as_q|as_epq|query|keywords?|term|text|encquery)=([^&]+)#i",$referrer,$pcs) > 0) {
			if (empty($searchengine)) $searchengine=trim(strtolower($pcs[1]));
			if ($pcs[2] =="encquery") $search_phrase=" *".__("encrypted search","wassup")."* ";
			else $search_phrase = $pcs[3];
		//Check separately for queries that use nonstandard search variable to avoid retrieving values like "p=parameter" when "q=query" exists
		} elseif(preg_match("#^https?://([^/]+).*(?:results|search|query).*[&?](aq|as|p|su|s|kw|k|qo|qp|qs|string)=([^&]+)#i",$referrer,$pcs) > 0) {
			if (empty($searchengine)) $searchengine = trim(strtolower($pcs[1]));
			$search_phrase = $pcs[3];
		}
	} //end if search_phrase
	//do a separate check for page number, if not found above
	if (!empty($search_phrase)) {
		if (empty($searchpage) && preg_match("#[&\?](start|startpage|b|cd|first|stq|p|pi|page)[=/](\d+)#i",$referrer,$pcs)>0) {
			$searchpage = $pcs[2];
		}
	}
	return array('keywords'=>$search_phrase,'page'=>$searchpage,'searchengine'=>$searchengine,'language'=>$searchlang,'locale'=>$selocale);
} //end wGetSE

/**
 * Extract browser and platform info from a user agent string and return the values
 * @param string
 * @return array (browser, os)
 */
function wGetBrowser($agent="") {
	global $wassup_options,$wdebug_mode;
	if(empty($agent)) $agent=$_SERVER['HTTP_USER_AGENT'];
	$browsercap=array();
	$browscapbrowser="";
	$browser="";
	$os="";
	//check PHP browscap data for browser and platform
	//'browscap' must be set in "php.ini", 'ini_set' doesn't work
	if(ini_get("browscap")!=""){
		$browsercap = get_browser($agent,true);
		if(!empty($browsercap['platform'])){
		if(stristr($browsercap['platform'],"unknown") === false){
			$os=$browsercap['platform'];
			if(!empty($browsercap['browser'])){
				$browser=$browsercap['browser'];
			}elseif(!empty($browsercap['parent'])){
				$browser=$browsercap['parent'];
			}
			if(!empty($browser) && !empty($browsercap['version'])){
				$browser=$browser." ".wMajorVersion($browsercap['version']);
			}
		}
		}
		//reject generic browscap browsers (ex: mozilla, default)
		if(preg_match('/^(mozilla|default|unknown)/i',$browser) >0){
			$browscapbrowser="$browser";	//save just in case
			$browser="";
		}
		$os=trim($os); 
		$browser=trim($browser);
		if($wdebug_mode){
			if(headers_sent()){
				echo "\nPHP Browscap data from get_browser: \c";
				if(is_array($browsercap)|| is_object($browsercap)) print_r($browsercap);
				else echo $browsercap;
			}
		}
	}
	//use wDetector class when browscap browser is empty/unknown
	if ( $os == "" || $browser == "") {
		$dip=@new wDetector("",$agent);	//v1.9 bugfix
		if(!empty($dip)){
			$browser=trim($dip->browser." ".wMajorVersion($dip->browser_version));
			if($dip->os!="") $os=trim($dip->os." ".$dip->os_version);
		}
		//use saved browscap info when Detector has no result
		if(!empty($browscapbrowser) && $browser == "") $browser=$browscapbrowser;
	}
	return array($browser,$os);
} //end function wGetBrowser

//return a major version # from a version string argument
function wMajorVersion($versionstring) {
	$version=0;
	if (!empty($versionstring)) {
		$n = strpos($versionstring,'.');
		if ($n >0) {
			$version= (int) substr($versionstring,0,$n);
		}
		if ($n == 0 || $version == 0) {
			$p = strpos($versionstring,'.',$n+1);
			if ($p) $version= substr($versionstring,0,$p);
		}
	}
	if ($version > 0) return $version;
	else return $versionstring;
}

/**
 * Extract spider information from a user agent string and return an array.
 *  Return values: (name, type=[R|B|F|H|L|S|V], feed subscribers) where types are: R=robot, B=browser/downloader, F=feed reader, H=hacker/spoofer/injection bot, L=Link checker/sitemap generator, S=Spammer/email harvester, V=css/html Validator
 * @param string(3)
 * @return array
 */
function wGetSpider($agent="",$hostname="", $browser=""){
	if(empty($agent)&& !empty($_SERVER['HTTP_USER_AGENT']))$agent=$_SERVER['HTTP_USER_AGENT'];	//v1.9 bugfix
	$ua=rtrim($agent);
	//if(empty($ua)) return false;	//nothing to do
	$spiderdata=false;
	$crawler="";
	$feed="";
	$os="";
	$pcs=array();
	//identify obvious script injection bots 
	if(!empty($ua)){
		if(stristr($ua,'location.href')!==FALSE){
			$crawlertype="H";
			$crawler="Script Injection bot";
		}elseif(preg_match('/(<|&lt;|&#60;)a(\s|%20|&#32;|\+)href/i',$ua)>0){
			$crawlertype="H";
			$crawler="Script Injection bot";
		}elseif(preg_match('/(<|&lt;|&#60;)script/i',$ua)>0){
			$crawlertype="H";
			$crawler="Script Injection bot";
		}elseif(preg_match('/select.*(\s|%20|\+|%#32;)from(\s|%20|\+|%#32;)wp_/i',$ua)>0){
			$crawlertype = "H";
			$crawler = "Script Injection bot";
		}
	}
	//check for crawlers that mis-identify themselves as a browser but come from a known crawler domain - the most common of these are MSN (ie6,win2k3), and Yahoo!
	if(substr($_SERVER["REMOTE_ADDR"],0,6) == "65.55." || substr($_SERVER["REMOTE_ADDR"],0,7) == "207.46." || substr($_SERVER["REMOTE_ADDR"],0,7)=="157.55."){
		$crawler = "BingBot";
		$crawlertype="R";
	}elseif(!empty($hostname) && preg_match('/([a-z0-9\-\.]+){1,}\.(?:[a-z]+){2,4}$/',$hostname)>0){
		if(substr($hostname,-14)==".yse.yahoo.net" || substr($hostname,-16)==".crawl.yahoo.net" || (substr($hostname,-10)==".yahoo.com" && substr($hostname,0,3)=="ycar")){
			if(!empty($ua) && stristr($ua,"Slurp")){
				$crawler = "Yahoo! Slurp";
				$crawlertype="R";
			}else{
				$crawler = "Yahoo!";
				$crawlertype="R";
			}
		}elseif(substr($hostname,-8) == ".msn.com" && strpos($hostname,"msnbot")!== FALSE){
			$crawler = "BingBot";
			$crawlertype="R";
		}elseif(substr($hostname,-14) == ".googlebot.com"){
			//googlebot mobile can show as browser, sometimes
			if(!empty($ua) && stristr($ua,"mobile")){
				$crawler="Googlebot-Mobile";
				$crawlertype="R";
			}else{
				$crawler="Googlebot";
				$crawlertype="R";
			}
		}elseif(substr($hostname,0,11)=="baiduspider"){
			$crawler="Baiduspider";
			$crawlertype="R";

		}
	} //end if $hostname
	$pcs=array();
	$pcs2=array();
	//# check for crawlers that identify themselves clearly in their user agent string with words like bot, spider, and crawler
	if(empty($crawler)){
		if ((!empty($ua) && preg_match("#(\w+[ \-_]?(bot|crawl|google|reader|seeker|spider|feed|indexer|parser))[0-9/ -:_.;\)]#",$ua,$pcs) >0) || preg_match("#(crawl|feed|google|indexer|parser|reader|robot|seeker|spider)#",$hostname,$pcs2) >0){
			if(!empty($pcs[1])) $crawler=$pcs[1];
			elseif(!empty($pcs2[1])) $crawler="unknown_spider";
			$crawlertype="R";
		}
		if(empty($crawler) && !empty($ua) && ini_get("browscap")!=""){
			//check browscap data for crawler if available
			$browsercap = get_browser($ua,true);
			//if no platform(os), assume crawler...
			if(!empty($browsercap['platform'])) {
				if($browsercap['platform'] != "unknown") $os=$browsercap['platform'];

			}
			if(!empty($browsercap['crawler']) || !empty($browsercap['stripper']) || $os == ""){
				if(!empty($browsercap['browser'])){
					$crawler=$browsercap['browser'];
				}elseif(!empty($browsercap['parent'])){
					$crawler=$browsercap['parent'];
				}
				if (!empty($crawler) && !empty($browsercap['version'])){
					$crawler=$crawler." ".$browsercap['version'];
				}
			}
			//reject unknown browscap crawlers (ex: default)
			if(preg_match('/^(default|unknown|robot)/i',$crawler) > 0){
				$crawler="";
			}
		}
	}
	//get crawler info. from a known list of bots and feedreaders that don't list their names first in UA string.
	//Note: spaces are removed from UA string for this bot comparison
	$crawler=trim($crawler);
	if(empty($crawler) || $crawler=="unknown_spider"){
		$uagent=str_replace(" ","",$ua);
		$key=null;
		// array format: "Spider Name|UserAgent keywords (no spaces)| Spider type (R=robot, B=Browser/downloader, F=feedreader, H=hacker, L=Link checker, M=siteMap generator, S=Spammer/email harvester, V=CSS/Html validator)
		$lines=array(
			"Internet Archive|archive.org_bot|R|", 
			"Internet Archive|.archive.org|R|", 
			"Baiduspider|Baiduspider/|R|",
			"Baiduspider|.crawl.baidu.com|R|",
			"BingBot|MSNBOT/|R|","BingBot|msnbot.|R|",
			"Exabot|Exabot/|R|",
			"Exabot|.exabot.com|R|",
			"Googlebot|Googlebot/|R|",
			"Googlebot|.googlebot.com|R|",
			"Google|.google.com||",
			"SurveyBot|SurveyBot/||",
			"WebCrawler.link|.webcrawler.link|R|",
			"Yahoo!|Yahoo!Slurp|R|",
			"Yahoo!|.yse.yahoo.net|R|",
			"Yahoo!|.crawl.yahoo.net|R|",
			"Yandex|YandexBot/|R|",
			"AboutUsBot|AboutUsBot/|R|", 
			"80bot|80legs.com|R|", 
			"Aggrevator|Aggrevator/|F|", 
			"AlestiFeedBot|AlestiFeedBot||", 
			"Alexa|ia_archiver|R|", "AltaVista|Scooter-|R|", 
			"AltaVista|Scooter/|R|", "AltaVista|Scooter_|R|", 
			"AMZNKAssocBot|AMZNKAssocBot/|R|",
			"AppleSyndication|AppleSyndication/|F|",
			"Apple-PubSub|Apple-PubSub/|F|",
			"Ask.com/Teoma|AskJeeves/Teoma)|R|",
			"Ask Jeeves/Teoma|ask.com|R|",
			"AskJeeves|AskJeeves|R|",
			"BlogBot|BlogBot/|F|","Bloglines|Bloglines/|F|",
			"Blogslive|Blogslive|F|",
			"BlogsNowBot|BlogsNowBot|F|",
			"BlogPulseLive|BlogPulseLive|F|",
			"IceRocket BlogSearch|icerocket.com|F|",
			"Charlotte|Charlotte/|R|", 
			"Xyleme|cosmos/0.|R|", "cURL|curl/|R|",
			"Daumoa|Daumoa-feedfetcher|F|",
			"Daumoa|DAUMOA|R|",
			"Daumoa|.daum.net|R|",
			"Die|die-kraehe.de|R|", 
			"Diggit!|Digger/|R|", 
			"disco/Nutch|disco/Nutch|R|",
			"DotBot|DotBot/|R|",
			"Emacs-w3|Emacs-w3/v||", 
			"ananzi|EMC||", 
			"EnaBot|EnaBot||", 
			"esculapio|esculapio/||", "Esther|esther||", 
			"everyfeed-spider|everyfeed-spider|F|", 
			"Evliya|Evliya||", "nzexplorer|explorersearch||", 
			"eZ publish Validator|eZpublishLinkValidator||",
			"FacebookExternalHit|facebook.com/externalhit|R|",
			"FastCrawler|FastCrawler|R|", 
			"FDSE|FDSErobot|R|", 
			"Feed::Find|Feed::Find||",
			"FeedBurner|FeedBurner|F|",
			"FeedDemon|FeedDemon/|F|",
			"FeedHub FeedFetcher|FeedHub|F|", 
			"Feedreader|Feedreader|F|", 
			"Feedshow|Feedshow|F|", 
			"Feedster|Feedster|F|",
			"FeedTools|feedtools|F|",
			"Feedfetcher-Google|Feedfetcher-google|F|", 
			"Felix|FelixIDE/||", 
			"FetchRover|ESIRover||", 
			"fido|fido/||", 
			"Fish|Fish-Search-Robot||", "Fouineur|Fouineur||", 
			"Freecrawl|Freecrawl|R|", 
			"FriendFeedBot|FriendFeedBot/|F|",
			"FunnelWeb|FunnelWeb-||",
			"gammaSpider|gammaSpider||","gazz|gazz/||",
			"GCreep|gcreep/||",
			"GetRight|GetRight|R|",
			"GetURL|GetURL.re||","Golem|Golem/||",
			"GreatNews|GreatNews|F|",
			"Gregarius|Gregarius/|F|",
			"Gromit|Gromit/||", 
			"gsinfobot|gsinfobot||", 
			"Gulliver|Gulliver/||", "Gulper|Gulper||", 
			"GurujiBot|GurujiBot||", 
			"havIndex|havIndex/||",
			"heritrix|heritrix/||", "HI|AITCSRobot/||",
			"HKU|HKU||", "Hometown|Hometown||", 
			"HostTracker|host-tracker.com/|R|",
			"ht://Dig|htdig/|R|","HTMLgobble|HTMLgobble||",
			"Hyper-Decontextualizer|Hyper||",
			"iajaBot|iajaBot/||",
			"IBM_Planetwide|IBM_Planetwide,||",
			"ichiro|ichiro||",
			"Popular|gestaltIconoclast/||",
			"Ingrid|INGRID/||","Imagelock|Imagelock||",
			"IncyWincy|IncyWincy/||",
			"Informant|Informant||",
			"InfoSeek|InfoSeek||",
			"InfoSpiders|InfoSpiders/||",
			"Inspector|inspectorwww/||",
			"IntelliAgent|IAGENT/||",
			"ISC Systems iRc Search|ISCSystemsiRcSearch||",
			"Israeli-search|IsraeliSearch/||",
			"IRLIRLbot/|IRLIRLbot||",
			"Italian Blog Rankings|blogbabel|F|", 
			"Jakarta|Jakarta||", "Java|Java/||", 
			"JBot|JBot||", 
			"JCrawler|JCrawler/||", 
			"JoBo|JoBo||", "Jobot|Jobot/||", 
			"JoeBot|JoeBot/||",
			"JumpStation|jumpstation||", 
			"image.kapsi.net|image.kapsi.net/|R|", 
			"kalooga/kalooga|kalooga/kalooga||", 
			"Katipo|Katipo/||", 
			"KDD-Explorer|KDD-Explorer/||", 
			"KIT-Fireball|KIT-Fireball/||", 
			"KindOpener|KindOpener||", "kinjabot|kinjabot||", 
			"KO_Yappo_Robot|yappo.com/info/robot.html||", 
			"Krugle|Krugle||", 
			"LabelGrabber|LabelGrab/||",
			"Larbin|larbin_||",
			"libwww-perl|libwww-perl||",
			"lilina|Lilina||",
			"Link|Linkidator/||","LinkWalker|LinkWalker|L|",
			"LiteFinder|LiteFinder||",
			"logo.gif|logo.gif||","LookSmart|grub-client||",
			"Lsearch/sondeur|Lsearch/sondeur||",
			"Lycos|Lycos/||",
			"Magpie|Magpie/||","MagpieRSS|MagpieRSS|F|",
			"Mail.ru|Mail.ru||",
			"marvin/infoseek|marvin/infoseek||",
			"Mattie|M/3.||","MediaFox|MediaFox/||",
			"Megite2.0|Megite.com||",
			"NEC-MeshExplorer|NEC-MeshExplorer||",
			"MindCrawler|MindCrawler||",
			"Missigua Locator|MissiguaLocator||",
			"MJ12bot|MJ12bot|R|","mnoGoSearch|UdmSearch||",
			"MOMspider|MOMspider/||","Monster|Monster/v||",
			"Moreover|Moreoverbot||","Motor|Motor/||",
			"MSNBot|MSNBOT/|R|","MSN|msnbot.|R|",
			"MSRBOT|MSRBOT|R|","Muninn|Muninn/||",
			"Muscat|MuscatFerret/||",
			"Mwd.Search|MwdSearch/||",
			"MyBlogLog|Yahoo!MyBlogLogAPIClient|F|",
			"Naver|NaverBot||",
			"Naver|Cowbot||",
			"NDSpider|NDSpider/||", 
			"Nederland.zoek|Nederland.zoek||", 
			"NetCarta|NetCarta||", 
			"NetMechanic|NetMechanic||", 
			"NetScoop|NetScoop/||", 
			"NetNewsWire|NetNewsWire||", 
			"NewsAlloy|NewsAlloy||",
			"newscan-online|newscan-online/||",
			"NewsGatorOnline|NewsGatorOnline||",
			"Exalead NG|NG/|R|",
			"NHSE|NHSEWalker/||","Nomad|Nomad-V||",
			"Nutch/Nutch|Nutch/Nutch||",
			"ObjectsSearch|ObjectsSearch/||",
			"Occam|Occam/||",
			"Openfind|Openfind||",
			"OpiDig|OpiDig||",
			"Orb|Orbsearch/||",
			"OSSE Scanner|OSSEScanner||",
			"OWPBot|OWPBot||",
			"Pack|PackRat/||","ParaSite|ParaSite/||",
			"Patric|Patric/||",
			"PECL::HTTP|PECL::HTTP||",
			"PerlCrawler|PerlCrawler/||",
			"Phantom|Duppies||","PhpDig|phpdig/||",
			"PiltdownMan|PiltdownMan/||",
			"Pimptrain.com's|Pimptrain||",
			"Pioneer|Pioneer||",
			"Portal|PortalJuice.com/||","PGP|PGP-KA/||",
			"PlumtreeWebAccessor|PlumtreeWebAccessor/||",
			"Poppi|Poppi/||","PortalB|PortalBSpider/||",
			"psbot|psbot/|R|",
			"Python-urllib|Python-urllib/|R|",
			"R6_CommentReade|R6_CommentReade||",
			"R6_FeedFetcher|R6_FeedFetcher|F|",
			"radianrss|RadianRSS||",
			"Raven|Raven-v||",
			"relevantNOISE|relevantnoise.com||",
			"Resume|Resume||", "RoadHouse|RHCS/||", 
			"RixBot|RixBot||",
			"Robbie|Robbie/||", "RoboCrawl|RoboCrawl||", 
			"RoboFox|Robofox||",
			"Robozilla|Robozilla/||", 
			"Rojo|rojo1|F|", 
			"Roverbot|Roverbot||", 
			"RssBandit|RssBandit||", 
			"RSSMicro|RSSMicro.com|F|",
			"Ruby|Rfeedfinder||", 
			"RuLeS|RuLeS/||", 
			"Runnk RSS aggregator|Runnk||", 
			"SafetyNet|SafetyNet||", 
			"Sage|(Sage)|F|",
			"SBIder|sitesell.com|R|", 
			"Scooter|Scooter/||", 
			"ScoutJet|ScoutJet||",
			"Screaming Frog SEO Spider|ScreamingFrogSEOSpider/|L|",
			"SearchProcess|searchprocess/||",
			"Seekbot|seekbot.net|R|", 
			"SimplePie|SimplePie/|F|", 
			"Sitemap Generator|SitemapGenerator||", 
			"Senrigan|Senrigan/||", 
			"SeznamBot|SeznamBot/|R|",
			"SeznamScreenshotator|SeznamScreenshotator/|R|",
			"SG-Scout|SG-Scout||", "Shai'Hulud|Shai'Hulud||", 
			"Simmany|SimBot/||", 
			"SiteTech-Rover|SiteTech-Rover||", 
			"shelob|shelob||", 
			"Sleek|Sleek||", 
			"Slurp|.inktomi.com/slurp.html|R|",
			"Snapbot|.snap.com|R|", 
			"SnapPreviewBot|SnapPreviewBot|R|",
			"Smart|ESISmartSpider/||", 
			"Snooper|Snooper/b97_01||", "Solbot|Solbot/||", 
			"Sphere Scout|SphereScout|R|",
			"Sphere|sphere.com|R|",
			"spider_monkey|mouse.house/||",
			"SpiderBot|SpiderBot/||", 
			"Spiderline|spiderline/||",
			"SpiderView(tm)|SpiderView||", 
			"SragentRssCrawler|SragentRssCrawler|F|",
			"Site|ssearcher100||",
			"StackRambler|StackRambler||",
			"Strategic Board Bot|StrategicBoardBot||",
			"Suke|suke/||",
			"SummizeFeedReader|SummizeFeedReader|F|",
			"suntek|suntek/||",
			"Sygol|.sygol.com||",
			"Syndic8|Syndic8|F|",
			"TACH|TACH||","Tarantula|Tarantula/||",
			"tarspider|tarspider||","Tcl|dlw3robot/||",
			"TechBOT|TechBOT||","Technorati|Technoratibot||",
			"Teemer|Teemer||","Templeton|Templeton/||",
			"TitIn|TitIn/||","TITAN|TITAN/||",
			"Twiceler|.cuil.com/twiceler/|R|",
			"Twiceler|.cuill.com/twiceler/|R|",
			"Twingly|twingly.com|R|",
			"UCSD|UCSD-Crawler||", "UdmSearch|UdmSearch/||",
			"UniversalFeedParser|UniversalFeedParser|F|", 
			"UptimeBot|uptimebot||", 
			"URL_Spider|URL_Spider_Pro/|R|", 
			"VadixBot|VadixBot||", "Valkyrie|Valkyrie/||", 
			"Verticrawl|Verticrawlbot||", 
			"Victoria|Victoria/||", 
			"vision-search|vision-search/||", 
			"void-bot|void-bot/||", "Voila|VoilaBot||",
			"Voyager|.kosmix.com/html/crawler|R|",
			"VWbot|VWbot_K/||", 
			"W3C_Validator|W3C_Validator/|V|",
			"w3m|w3m/|B|", "W3M2|W3M2/||", "w3mir|w3mir/||", 
			"w@pSpider|w@pSpider/||", 
			"WallPaper|CrawlPaper/||",
			"WebCatcher|WebCatcher/||", 
			"webCollage|webcollage/|R|", 
			"webCollage|collage.cgi/|R|", 
			"WebCopier|WebCopierv|R|",
			"WebFetch|WebFetch|R|", "WebFetch|webfetch/|R|", 
			"WebMirror|webmirror/||", 
			"webLyzard|webLyzard||", "Weblog|wlm-||", 
			"WebReaper|webreaper.net|R|", 
			"WebVac|webvac/||", "webwalk|webwalk||", 
			"WebWalker|WebWalker/||", 
			"WebWatch|WebWatch||", 
			"WebStolperer|WOLP/||", 
			"WebThumb|WebThumb/|R|", 
			"Wells Search II|WellsSearchII||", 
			"Wget|Wget/||",
			"whatUseek|whatUseek_winona/||", 
			"whiteiexpres/Nutch|whiteiexpres/Nutch||",
			"wikioblogs|wikioblogs||", 
			"WikioFeedBot|WikioFeedBot||", 
			"WikioPxyFeedBo|WikioPxyFeedBo||",
			"Wild|Hazel's||", 
			"Wired|wired-digital-newsbot/||", 
			"Wordpress Pingback/Trackback|Wordpress/||",
			"WWWC|WWWC/||", 
			"XGET|XGET/||",
			"Xenu Link Sleuth|XenuLinkSleuth/|L|",
			"yacybot|yacybot||",
			"Yahoo FeedSeeker|YahooFeedSeeker|F|",
			"Yahoo MMAudVid|Yahoo-MMAudVid/|R|",
			"Yahoo MMCrawler|Yahoo-MMCrawler/|R|",
			"Yahoo!SearchMonkey|Yahoo!SearchMonkey|R|",
			"YahooSeeker|YahooSeeker/|R|",
			"YoudaoBot|YoudaoBot|R|", 
			"Tailrank|spinn3r.com/robot|R|",
			"Tailrank|tailrank.com/robot|R|",
			"Yesup|yesup||",
			"Internet|User-Agent:||",
			"Robot|Robot||", "Spider|spider||");
		foreach($lines as $line_num => $spider) {
			list($nome,$key,$crawlertype)=explode("|",$spider);
			if($key !=""){
				if(strstr($uagent,$key)!==false || (strstr($hostname,$key)!==false && strlen($key)>6)){ 
					$crawler=trim($nome);
					if(!empty($crawlertype) && $crawlertype == "F") $feed=$crawler;
					break 1;
				}
			}
		}
	} // end if crawler
	//If crawler not on list, use first word in useragent for crawler name
	if(empty($crawler)){
		$pcs=array();
		//Assume first word in useragent is crawler name
		if(preg_match("/^(\w+)[\/ \-\:_\.;]/",$ua,$pcs) > 0){
			if(strlen($pcs[1])>1 && $pcs[1]!="Mozilla"){ 
				$crawler=$pcs[1];
			}
		}
		//Use browser name for crawler as last resort
		//if (empty($crawler) && !empty($browser)) $crawler = $browser;
	}
	//#do a feed check and get feed subcribers, if available
	if(preg_match("/([0-9]{1,10})\s?subscriber/i",$ua,$subscriber) > 0){
		// It's a feedreader with some subscribers
		$feed=$subscriber[1];
		if(empty($crawler) && empty($browser)){
			$crawler=__("Feed Reader","wassup");
			$crawlertype="F";
		}
	}elseif(empty($feed) && (is_feed() || preg_match("/(feed|rss)/i",$ua)>0)){
		if(!empty($crawler)){ 
			$feed=$crawler;
		}elseif(empty($browser)){
			$crawler=__("Feed Reader","wassup");
			$feed=__("feed reader","wassup");
		}
		$crawlertype="F";
	}
	if($crawler=="Spider" || $crawler=="unknown_spider" || $crawler=="robot") $crawler = __("Unknown Spider","wassup");
	$spiderdata=array($crawler,$crawlertype,trim($feed));
	return $spiderdata;
} //end function wGetSpider

//#get the visitor locale/language
function wGetLocale($language="",$hostname="",$referrer="") {
	global $wdebug_mode;
	$clocale="";
	$country="";
	$langcode = trim(strtolower($language));
	$llen=strlen($langcode);
	//change language code to 2-digits
	if($llen >2 && preg_match('/([a-z]{2})(?:-([a-z]{2}))?(?:[^a-z]|$)/i',$langcode,$pcs)>0){
		if(!empty($pcs[2]))$language=strtolower($pcs[2]);
		elseif(!empty($pcs[1]))$language=strtolower($pcs[1]);
	}elseif($llen >2){
		$langarray=explode("-",$langcode);
		$langarray=explode(",",$langarray[1]);
		list($language)=explode(";",$langarray[0]);
	}
	//use 2-digit top-level domains (TLD) for country code, if any
	if (strlen($hostname)>2 && preg_match("/\.[a-z]{2}$/i", $hostname)>0){
		$country=strtolower(substr($hostname,-2));
		//ignore domains commonly used for media
		if($country == "tv" || $country == "fm") $country="";
	}
	$pcs=array();
	if(empty($language) || $language=="us" || $language=="en"){
		//major USA-only ISP hosts always have "us" as language code
		if (empty($country) && strlen($hostname)>2 && preg_match('/(\.[a-z]{2}\.comcast\.net|\.verizon\.net|\.windstream\.net)$/',$hostname,$pcs)>0) {
			$country="us";
		//retrieve TLD country code and language from search engine referer string
		}elseif(!empty($referrer)){
			$pcs=array();
			//google search syntax: hl=host language
			if (preg_match('/\.google\.(?:com?\.([a-z]{2})|([a-z]{2})|com)\/[a-z]+.*(?:[&\?]hl\=([a-z]{2})\-?(\w{2})?)/i',$referrer,$pcs)>0) {
				if(!empty($pcs[1])){
					$country=strtolower($pcs[1]);
				}elseif(!empty($pcs[2])){
					$country=strtolower($pcs[2]);
				}elseif(!empty($pcs[3]) || !empty($pcs[4])){
					if(!empty($pcs[4])) $language=strtolower($pcs[4]);
					else $language=strtolower($pcs[3]);
				}
			}
		}
	}
	//Make tld code consistent with locale code
	if(!empty($country)){
		if($country=="uk"){	//United kingdom
			$country="gb";
		}elseif($country=="su"){ //Soviet Union
			$country="ru";
		}
	}
	//Make language code consistent with locale code
	if($language == "en"){		//"en" default is US
		if(empty($country)) $language="us";
		else $language=$country;
	}elseif($language == "uk"){	//change UK to UA (Ukranian)
		$language="ua";
	}elseif($language == "ja"){	//change JA to JP
		$language="jp";
	}elseif($language == "ko"){	//change KO to KR
		$language="kr";
	}elseif($language == "da"){	//change DA to DK
		$language="dk";
	}elseif($language == "ur"){	//Urdu = India or Pakistan
		if($country=="pk") $language=$country;
		else $language="in";
	}elseif($language == "he" || $language == "iw"){
		if(empty($country)) $language="il";	//change Hebrew (iso) to IL
		else $language=$country;
	}
	//Replace language with locale for widely spoken languages
	if(!empty($country)){
		if(empty($language) || $language=="us" || preg_match("/^([a-z]{2})$/",$language)==0){
			$language=$country;
		}elseif($language=="es"){
			//for Central/South American locales
			$language=$country;
		}
	}
	if(!empty($language) && preg_match("/^[a-z]{2}$/",$language)>0){
		$clocale=$language;
	}
	return $clocale;
} //end function wGetLocale

/**
 * Check referrer string and referrer host (or hostname) for referrer spam
 *
 *   -checks referer host against know list of spammer
 *   -checks referrer string for spammer content (faked referrer).
 * @param string $referrer, string $hostname
 * @return boolean
 */
function wGetSpamRef($referrer,$hostname="") {
	global $wdebug_mode;
	$ref=esc_attr(strip_tags(str_replace(" ","",html_entity_decode($referrer))));
	$badhost=false;
	$referrer_host = "";
	$referrer_path = "";
	if(empty($referrer) && !empty($hostname)){
		$referrer_host=$hostname;
		$hostname="";
	}elseif(!empty($referrer)){
		$rurl=parse_url(strtolower($ref));
		if(isset($rurl['host'])){
			$referrer_host=$rurl['host'];
			$thissite=parse_url(get_option('home'));
			//exclude current site as referrer
			if(isset($thissite['host']) && $referrer_host == $thissite['host']){
				$referrer_host="";
			//Since v1.8.3: check the path|query part of url for spammers
			}else{
				//rss.xml|sitemap.txt in referrer is faked
				if(preg_match('#.+/(rss\.xml|sitemap\.txt)$#',$ref)>0) $badhost=true;
				//membership|user id in referrer is faked
				elseif(preg_match('#.+[^a-z0-9]((?:show)?user|u)\=\d+$#',$ref)>0) $badhost=true;
				//youtube video in referrer is faked
				elseif(preg_match('#(\.|/)youtube\.com/watch\?v\=.+#',$ref)>0) $badhost=true;
				//some facebook links in referrer are faked
				elseif(preg_match('#(\.|/)facebook\.com\/ASeaOfSins$#',$ref)>0) $badhost=true;
			}
		} else {	//faked referrer string
			$badhost=true;
		}
		if(!$badhost){
			//shortened URL is likely FAKED referrer string!
			if(!empty($referrer_host) && wassup_urlshortener_lookup($referrer_host)) $badhost=true;
			//a referrer with domain in all caps is likely spam
			elseif(preg_match('#https?\://[0-9A-Z\-\._]+\.([A-Z]{2,4})$#',$ref)>0) $badhost=true;
		}
	} //end elseif
	//#Assume any referrer name similar to "viagra/zanax/.." is spam and mark as such...
	if (!$badhost && !empty($referrer_host)) {
		$lines = array(	"allegra", "ambien", "ativan", "blackjack",
			"bukakke", "casino","cialis","ciallis", "celebrex",
			"cumdripping", "cumeating", "cumfilled",
			"cumpussy", "cumsucking", "cumswapping",
			"diazepam", "diflucan", "drippingcum", "eatingcum",
			"enhancement", "finasteride", "fioricet",
			"gabapentin", "gangbang", "highprofitclub",
			"hydrocodone", "krankenversicherung", "lamisil",
			"latinonakedgirl", "levitra", "libido", "lipitor",
			"lortab", "melatonin", "meridia", "NetCaptor",
			"orgy-", "phentemine", "phentermine", "propecia",
			"proscar", "pussycum", "sildenafil", "snowballing",
			"suckingcum", "swappingcum", "swingers",
			"tadalafil", "tigerspice", "tramadol", "ultram-",
			"valium", "valtrex", "viagra", "viagara","vicodin",
			"xanax", "xenical", "xxx-",
			"zoloft", "zovirax", "zanax"
			);
		foreach ($lines as $badreferrer) {
			if (strstr($referrer_host, $badreferrer)!== FALSE){
				$badhost=true;
				break 1;
			}
		}
	}
	//check against lists of known bad hosts (spammers)
	if(!$badhost){
		if($hostname != $referrer_host)
			$badhost = wassup_badhost_lookup($referrer_host,$hostname);
		elseif(!empty($hostname))
			$badhost = wassup_badhost_lookup($hostname);
	}
	return $badhost;
} //end wGetSpamRef

/**
 * Compare a hostname (and referrer hostname) arguments against a list of known spammers.
 * @param string(2)
 * @return boolean
 * @since v1.9
 */
function wassup_badhost_lookup($referrer_host,$hostname="") {
	global $wdebug_mode;

	if ($wdebug_mode) echo "\$referrer_host = $referrer_host.\n";
	$badhost=false;

	//1st compare against a list of recent referer spammers
	$lines = array(	'209\.29\.25\.180',
			'78\.185\.148\.185',
			'93\.90\.243\.63',
			'amsterjob\.com',
			'burger\-imperia\.com',
			'buttons\-for\-website\.com',
			'canadapharm\.atwebpages\.com',
			'candy\.com',
			'celebritydietdoctor\.com',
			'celebrity\-?diets\.(com|org|net|info|biz)',
			'chairrailmoldingideas\.com',
			'[a-z0-9]+\.cheapchocolatesale\.com',
			'cheapguccinow\.com',
			'chocolate\.com',
			'competmy24site\.com',
			'couplesresortsonline\.com',
			'creditcardsinformation\.info',
			'.*\.css\-build\.info',
			'.*dietplan\.com',
			'Digifire\.net',
			'disimplantlari\.(net|org)',
			'disimplantlari\.gen\.tr',
			'disteli\.org',
			'disteli\.gen\.tr',
			'diszirkonyum\.(net|org)',
			'diszirkonyum\.gen\.tr',
			'dogcareinsurancetips\.sosblog\.com',
			'dollhouserugs\.com',
			'dreamworksdentalcenter\.com',
			'e\-gibis\.co\.kr',
			'ebellybuttonrings\.blogspot\.com',
			'epuppytrain\.blogspot\.com',
			'estetik\.net\.tr',
			'exactinsurance\.info',
			'find1friend\.com',
			'freefarmvillesecrets\.info',
			'frenchforbeginnerssite\.com',
			'gameskillinggames\.net',
			'gardenactivities\.webnode\.com',
			'globalringtones\.net',
			'gossipchips\.com',
			'gskstudio\.com',
			'hearcam\.org',
			'hearthealth\-hpe\.org',
			'highheelsale\.com',
			'homebasedaffiliatemarketingbusiness\.com',
			'hosting37\d{2}\.com/',
			'howgrowtall\.(com|info)',
			'hundejo\.com',
			'hvd\-store\.com',
			'insurancebinder\.info',
			'internetserviceteam\.com',
			'intl\-alliance\.com',
			'it\.n\-able\.com',
			'justanimal\.com',
			'justbazaar\.com',
			'knowledgehubdata\.com',
			'koreanracinggirls\.com',
			'lacomunidad\.elpais\.com',
			'lactoseintolerancesymptoms\.net',
			'laminedis\.gen\.tr',
			'leadingleaders\.net',
			'lhzyy\.net',
			'linkwheelseo\.net',
			'liquiddiet[a-z\-]*\.com',
			'locksmith[a-z\-]+\.org',
			'lockyourpicz\.com',
			'materterapia\.net',
			'menshealtharts\.com',
			'ip87\-97\.mwtv\.lv',
			'mydirtyhobbycom\.de',
			'myhealthcare\.com',
			'myoweutthdf\.edu',
			'odcadide\.iinaa\.net',
			'onlinemarketpromo\.com',
			'outletqueens\.com',
			'pacificstore\.com',
			'peter\-sun\-scams\.com',
			'pharmondo\.com',
			'pinky\-vs\-cherokee\.com',
			'pinkyxxx\.org',
			'[a-z]+\.pixnet\.net',
			'pizza\-imperia\.com',
			'pizza\-tycoon\.com',
			'play\-mp3\.com',
			'21[89]\-124\-182\-64\.cust\.propagation\.net',
			'propertyjogja\.com',
			'prosperent\-adsense\-alternative\.blogspot\.com',
			'qweojidxz\.com',
			'ragedownloads\.info',
			'rankings\-analytics\.com',
			'[a-z\-]*ringtone\.net',
			'rufights\.com',
			'scripted\.com',
			'seoindiawizard\.com',
			'singlesvacationspackages\.com',
			'sitetalk\-revolution\.com',
			'smartforexsignal\.com',
			'springhouseboston\.org',
			'stableincomeplan\.blogspot\.com',
			'staphinfectionpictures\.org',
			'static\.theplanet\.com',
			'[a-z]+\-[a-z]+\-symptoms\.com',
			'thebestweddingparty\.com',
			'thik\-chik\.com',
			'thisweekendsmovies\.com',
			'uggbootsnewest\.net',
			'uggsmencheap\.com',
			'uggsnewest\.com',
			'unassigned\.psychz\.net',
			'ultrabait\.biz',
			'usedcellphonesforsales\.info',
			'vietnamvisa\.co',
			'[a-z\-\.]+vigra\-buy\.info',
			'vitamin\-d\-deficiency\-symptoms\.com',
			'vpn\-privacy\.org',
			'watchstock\.com',
			'web\-promotion\-services\.net',
			'wh\-tech\.com',
			'wholesalelivelobster\.com',
			'wineaccessories\-winegifts\.com',
			'wordpressseo\-plugin\.info',
			'writeagoodcoverletter\.com',
			'writeagoodresume\.net',
			'yeastinfectionsymptomstreatments\.com'
			);
	foreach($lines as $spammer) {
		if(!empty($spammer)){
		if(preg_match('#^'.$spammer.'$#',$referrer_host)>0){
			// found it!
			$badhost=true;
			break 1;
		}elseif(!empty($hostname) && preg_match('#(^|\.)'.$spammer.'$#i',$hostname)>0){	//v1.9 bugfix - changed quotes
			$badhost=true;
			break 1;
		}
		}
	}
	//2nd check against a customized spammer list...
	if (!$badhost) {
		$badhostfile= WASSUPDIR.'/badhosts.txt';
		if (preg_match('/\.[a-z]{2}$/',$referrer_host)>0) {
			$badhostfile= WASSUPDIR.'/badhosts-intl.txt';
		}
		if (file_exists($badhostfile)) {
			$lines = file($badhostfile,FILE_IGNORE_NEW_LINES);
			$i=0;
		foreach($lines as $spammer){ 
			$i++;
			if($i >6 && !empty($spammer)) {
			if (preg_match('#(^|\.)'.$spammer.'$#',$referrer_host)>0) {
                          	// found it!
			  	$badhost=true;
			  	break 1;
			} elseif(!empty($hostname) && preg_match('#(^|\.)'.$spammer.'$#i',$hostname)>0) {
				$badhost=true;
				break 1;
			}
			}
		}
		}
	}
	return $badhost;
} //end wassup_badhost_lookup()

/**
 * Returns true when hostname is from a known url shortener domain
 * @since v1.9
 * @param string
 * @return boolean
 */
function wassup_urlshortener_lookup($urlhost){
	$is_shortenedurl=false;
	if(!empty($urlhost)){
		if(strpos($urlhost,'/')!==false){
			$hurl=parse_url($urlhost);
			if(!empty($hurl['host'])) $urlhost=$hurl['host'];
			else $urlhost="";
		}
	}
	if(!empty($urlhost)){
		//some urls from http://longurl.org/services and https://code.google.com/p/shortenurl/wiki/URLShorteningServices (up to "m")
		$url_shorteners=array(
			'0rz.tw','1url.com',
			'2.gp','2big.at','2.ly','2tu.us',
			'4ms.me','4sq.com','4url.cc',
			'6url.com','7.ly',
			'a.gg','adf.ly','adjix.com','alturl.com','amzn.to',
			'b23.ru','bcool.bz','binged.it','bit.do','bit.ly','budurl.com',
			'canurl.com','chilp.it','chzb.gr','cl.ly','clck.ru','cli.gs','coge.la','conta.cc','cort.as','cot.ag','crks.me','ctvr.us','cutt.us',
			'dlvr.it','durl.me','doiop.com',
			'fon.gs',
			'gaw.sh','gkurl.us','goo.gl',
			'hj.to','hurl.me','hurl.ws',
			'ikr.me','is.gd',
			'j.mp','jdem.cz','jijr.com',
			'kore.us','krz.ch',
			'l.pr','lin.io','linkee.com','ln-s.ru','lnk.by','lnk.gd','lnk.ly','lnk.ms','lnk.nu','lnkd.in','ly.my',
			'migre.me','minilink.org','minu.me','minurl.fr','moourl.com','mysp.in','myurl.in',
			'ow.ly',
			'shorte.st','shorturl.com','shrt.st','shw.me','snurl.com','sot.ag','su.pr','sur.ly',
			't.co','tinyurl.com','tr.im',
		);
		if(in_array($urlhost,$url_shorteners)) $is_shortenedurl=true;
		elseif(preg_match('/(^|[^0-9a-z\-_])(tk|to)\.$/',$urlhost)>0) $is_shortenedurl=true;
	}
	return $is_shortenedurl;
} //end wassup_urlshortener_lookup

/**
 * return a validated ip address from http header
 * @since v1.9
 * @param string
 * @return string
 */
function wassup_get_clientAddr($ipAddress=""){
	$proxy = "";
	$hostname = "";
	$IP="";
	//Get the visitor IP from Http_header
	if(empty($ipAddress))$ipAddress=(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:"");
	$IPlist=$ipAddress;
	$proxylist=$ipAddress;
	$serverAddr=(isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:"");
	//for computers behind proxy servers:
	//if(!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) $serverAddr=$_SERVER['HTTP_X_FORWARDED_HOST'];
	//elseif(!empty($_SERVER['HTTP_X_FORWARDED_SERVER'])) $serverAddr=$_SERVER['HTTP_X_FORWARDED_SERVER'];
	//
	//check that the client IP is not equal to the host server IP
	if(isset($_SERVER['HTTP_CLIENT_IP'])&& $serverAddr!=$_SERVER['HTTP_CLIENT_IP'] && $ipAddress!=$_SERVER['HTTP_CLIENT_IP']){
		if(strpos($proxylist,$_SERVER["HTTP_CLIENT_IP"])===false){
			$IPlist=$_SERVER['HTTP_CLIENT_IP'].",".$proxylist;
			$proxylist=$IPlist;
		}
		$ipAddress=$_SERVER['HTTP_CLIENT_IP'];
	}
	if(isset($_SERVER['HTTP_X_REAL_IP'])&& $serverAddr!=$_SERVER['HTTP_X_REAL_IP'] && $ipAddress!=$_SERVER['HTTP_X_REAL_IP']){
		if(strpos($proxylist,$_SERVER["HTTP_X_REAL_IP"])===false){
			$IPlist=$_SERVER['HTTP_X_REAL_IP'].",".$proxylist;
			$proxylist=$IPlist;
		}
		$ipAddress=$_SERVER['HTTP_X_REAL_IP'];
	}
	//check for IP addresses from Cloudflare CDN-hosted sites
	if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])&& $serverAddr!=$_SERVER['HTTP_CF_CONNECTING_IP'] && $ipAddress!=$_SERVER['HTTP_CF_CONNECTING_IP']){
		if(strpos($proxylist,$_SERVER["HTTP_CF_CONNECTING_IP"])===false){
			$IPlist=$_SERVER['HTTP_CF_CONNECTING_IP'].",".$proxylist;
			$proxylist=$IPlist;
		}
		$ipAddress=$_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	//check for proxy addresses
	if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])&& $serverAddr!=$_SERVER['HTTP_X_FORWARDED_FOR'] && $ipAddress!=$_SERVER['HTTP_X_FORWARDED_FOR']){
		if(strpos($proxylist,$_SERVER['HTTP_X_FORWARDED_FOR'])===false){
			$IPlist=$_SERVER['HTTP_X_FORWARDED_FOR'].",".$proxylist;
			$proxylist=$IPlist;
		}
		$ipAddress=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	if(!empty($_SERVER["HTTP_X_FORWARDED"])&& $serverAddr!=$_SERVER["HTTP_X_FORWARDED"] && $ipAddress!=$_SERVER['HTTP_X_FORWARDED']){
		if(strpos($proxylist,$_SERVER['HTTP_X_FORWARDED'])===false){
			$IPlist=$_SERVER['HTTP_X_FORWARDED'].",".$proxylist;
			$proxylist=$IPlist;
		}
		$ipAddress=$_SERVER['HTTP_X_FORWARDED'];
	}
	//try get valid IP
	$IP = wValidIP($ipAddress);
	if(empty($IP) && $ipAddress!=$proxylist){
		$proxylist=preg_replace('/(^|[^0-9\.])'.preg_quote($ipAddress).'($|[^0-9\.])/','',$IPlist);
		$IP=wValidIP($proxylist);
	}
	if(!empty($IP)){
		$p=strpos($IPlist,$IP)+strlen($IP)+1;
		if($p < strlen($IPlist)) $proxylist=substr($IPlist,$p);
		else $proxylist="";
	}
	//check client hostname for known proxy gateways
	if(!empty($IP)){
		$hostname=wassup_get_hostname($IP);
		if(preg_match('/(cloudflare\.|cache|gateway|proxy|unknown$|localhost$|\.local(?:domain)?$)/',$hostname)>0){
			$ip1=$IP;
			if(!empty($proxylist)) $IP=wValidIP($proxylist);
			if(!empty($IP)){
				$p=strpos($IPlist,$IP)+strlen($IP)+1;
				if($p < strlen($IPlist)) $proxylist=substr($IPlist,$p);
				else $proxylist="";
			}else{
				$IP=$ip1;
			}
		}
		if(!empty($proxylist))$proxy=wValidIP($proxylist);
		if(!empty($proxy)) $ipAddress=$proxy.','.$IP;
		else $ipAddress=$IP;
	}
	return $ipAddress;
} //end wassup_get_clientAddr

// lookup the hostname from an ip address via cache or via gethostbyaddr command @since v1.9
function wassup_get_hostname($IP=""){
	if(empty($IP))$IP=wassup_clientIP($_SERVER['REMOTE_ADDR']);
	//first check for cached hostname
	$hostname=wassupDb::get_wassupmeta($IP,'hostname');
	if(empty($hostname)){
		if($IP=="127.0.0.1" || $IP=='::1' || $IP=='0:0:0:0:0:0:0:1'){$hostname="localhost";}
		elseif($IP=="0.0.0.0" || $IP=='::' || $IP=='0:0:0:0:0:0:0:0'){$hostname="unknown";}
		else{
			$hostname=@gethostbyaddr($IP);
			if(!empty($hostname)&& $hostname!=$IP && $hostname!="localhost" && $hostname!="unknown"){
				$meta_key='hostname';
				$meta_value=$hostname;
				$expire=time()+48*3600; //cache for 2 days
				$cache_id=wassupDb::update_wassupmeta($IP,$meta_key,$meta_value,$expire);
			}
		}
	}
	return $hostname;
} //end wassup_get_hostname

// Return a single ip (the client IP) from a comma-separated IP address with no ip validation. @since v1.9
function wassup_clientIP($ipAddress){
	$IP=false;
	if(!empty($ipAddress)){
		$ip_proxy=strpos($ipAddress,",");
		//if proxy, get 2nd ip...
		if($ip_proxy!==false)$IP=substr($ipAddress,(int)$ip_proxy+1);
		else $IP=$ipAddress;
	}
	return $IP;
}

//return 1st valid IP address in a comma-separated list of IP addresses -Helene D. 2009-03-01
function wValidIP($multiIP) {
	//in case of multiple forwarding
	$ips = explode(",",$multiIP);
	$goodIP = false;
	//look through forwarded list for a good IP
	foreach ($ips as $ipa) {
		$IP=trim(strtolower($ipa));
		if(!empty($IP)){
			//exclude dummy IPv4 addresses
			if(strpos($IP,'.')>0){
				if($IP!="0.0.0.0" && $IP!="127.0.0.1" && substr($IP,0,8)!="192.168." && substr($IP,0,3)!="10." && substr($IP,0,4)!="172." && substr($IP,0,7)!='192.18.' && substr($IP,0,4)!='255.' && substr($IP,-4)!='.255')$goodIP=$IP;
				elseif(substr($IP,0,4)=="172." && preg_match('/172\.(1[6-9]|2[0-9]|3[0-1])\./',$IP)===false)$goodIP=$IP;
			//New in v1.9: exclude dummy IPv6 addresses
			}elseif(strpos($IP,':')!==false){
				$ipv6=str_replace("0000","0",$IP);
				if($ipv6!='::' && $ipv6!='0:0:0:0:0:0:0:0' && $ipv6!='::1' && $ipv6!='0:0:0:0:0:0:0:1' && substr($ipv6,0,2)!='fd' && substr($ipv6,0,5)!='ff01:' && substr($ipv6,0,5)!='ff02:' && substr($ipv6,0,5)!='2001:')$goodIP=$IP;
			}
			if(!empty($goodIP))break;
		}
	}
	return $goodIP;
} //end function wValidIP

/**
 * Output javascript in page head for wassup tracking 
 * @param none 
 * @return none
 */
function wassup_head() {
	global $wassup_options, $wscreen_res;
	//Since v.1.8: removed meta tag to reduce plugin bloat
	//print '<meta name="wassup-version" content="'.WASSUPVERSION.'" />'."\n";
	//add screen resolution javascript to blog header
	$sessionhash=$wassup_options->whash;
	if($wscreen_res == "" && isset($_COOKIE['wassup_screen_res'.$sessionhash])){
		$wscreen_res=esc_attr(trim($_COOKIE['wassup_screen_res'.$sessionhash]));
		if($wscreen_res == "x") $wscreen_res="";
	}
	if(empty($wscreen_res) && isset($_SERVER['HTTP_UA_PIXELS'])){
		//resolution in IE/IEMobile header sometimes
		$wscreen_res=str_replace('X',' x ',$_SERVER['HTTP_UA_PIXELS']);
	}
	//get visitor's screen resolution with javascript and a cookie
	if(empty($wscreen_res) && !isset($_COOKIE['wassup_screen_res'.$sessionhash])){
		echo "\n";?>
<script type="text/javascript">
//<![CDATA[
	var screen_res=screen.width+" x "+screen.height;
	if(screen_res==" x ") screen_res=window.screen.width+" x "+window.screen.height;
	if(screen_res==" x ") screen_res=screen.availWidth+" x "+screen.availHeight;
	if(screen_res!=" x "){<?php
		if(defined('COOKIE_DOMAIN')){
			$cookiedomain=COOKIE_DOMAIN;
			if(defined('COOKIEPATH'))$cookiepath=COOKIEPATH;
			else $cookiepath="/";
		}else{
			$curl=parse_url(get_option('home'));
			$cookiedomain=preg_replace('/^www\./','',$curl['host']);
			$cookiepath=$curl['path'];
		}?>document.cookie = "wassup_screen_res<?php echo $sessionhash;?>=" + encodeURIComponent(screen_res)+ "; path=<?php echo $cookiepath.'; domain='.$cookiedomain;?>";}
//]]>
</script><?php
	}
} //end function wassup_head

// hook function to put a timestamp in page footer for page caching test
function wassup_foot() {
	global $wassup_options, $wscreen_res, $wdebug_mode;
	//Since 1.8.2: separate 'wassup_screen_res' cookie in footer for IE users because IE does not report screen height or width until after it begins to render the document body.
	$sessionhash=$wassup_options->whash;
	if(empty($wscreen_res) && !isset($_COOKIE['wassup_screen_res'.$sessionhash])){
		$ua=(!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"");
		if(strpos($ua,'MSIE')>0 || strpos($ua,'rv:11')>0 || strstr($ua,'Edge/')>0){
			echo "\n"; ?>
<script language=javascript>
//<![CDATA[
	var screen_res = screen.width + " x " + screen.height;
	if (screen_res!=" x "){document.cookie = "wassup_screen_res<?php echo $sessionhash;?>=" + encodeURIComponent(screen_res)+ "; path=/; domain=" + document.domain;}
//]]>
</script>
<?php
		} //end if MSIE
	} //end if 'wscreen_res'
	//Output a comment with a current timestamp to verify that page is not cached (i.e. visit is being recorded).
	echo "<!--\n<p class=\"small\"> WassUp ".WASSUPVERSION." ".__("timestamp","wassup").": ".date('Y-m-d h:i:sA T')." (".gmdate('h:iA',time()+(get_option('gmt_offset')*3600)).")<br />\n";
	echo __("If above timestamp is not current time, this page is cached","wassup").".</p> -->\n";
} //end wassup_foot

/** for wp-cron scheduling of temp records cleanup */
function wassup_temp_cleanup($dbtasks=array()){
	if(!defined('WASSUPVERSION')) wassup_init();

	//do scheduled cleanup tasks
	if(empty($dbtasks)){
		wassupDb::temp_cleanup();
	}else{
		//do non-scheduled cleanup tasks
		wassupDb::scheduled_dbtask(array('dbtasks'=>$dbtasks));
	}
}
/** for wp-cron scheduling of automatic delete of old records */
function wassup_auto_cleanup(){
	global $wassup_options;
	if(!defined('WASSUPVERSION')) wassup_init();
	//check that ok to do auto delete
	if(!empty($wassup_options->delete_auto) && $wassup_options->delete_auto!="never"){
		//check last auto delete timestamp to ensure purge occurs only once a day
		$wassup_table=$wassup_options->wassup_table;
		$timenow=time();
		$delete_auto_time=wassupDb::get_wassupmeta($wassup_table,'_delete_auto');
		if(empty($auto_delete_time) || $auto_delete_time < $timenow - 24*3600){
			wassupDb::auto_cleanup();
		}
	}
}

// Security functions
/**
 * Detect signs of script injection and hack attempts in http_headers: request_uri, http_referer
 * @author Helene D. <http://helenesit.com>
 * @since version 1.8, updated for HTTP_REFERER and parameter in v1.9
 * @param string
 * @return boolean
 */
function wIsAttack($http_target="") {
	global $wdebug_mode;
	$is_attack=false;
	$targets=array();
	//New in v1.9: request_uri, http_referer headers are now tested for attack by default
	if(!empty($http_target)){
		if(is_array($http_target))$targets=$http_target;
		else $targets[]=$http_target;
	}else{
		$targets[]=$_SERVER['REQUEST_URI'];
		if(!empty($_SERVER['HTTP_REFERER']))$targets[]=$_SERVER['HTTP_REFERER'];
	}
	if(!empty($targets)){
		foreach ($targets AS $target) {
			if(preg_match('#["<>`^]|[^/][~]|\.\*|\*\.#',str_replace(array('&lt;','&#60;','%3C','&rt;','&#62;','%3E','&quot;','%5E'),array("<","<","<",">",">",">","\"",'^'),$target))>0||(preg_match('/[\\\']/',str_replace('%5C','\\',$target))>0 && preg_match('/((?:q|search|s|p)\=[^\\\'&=]+)([\\\']*\'[^\'&]*)&/',str_replace('%5C','\\',$target))==0)){$is_attack=true;break;}
			elseif(preg_match('#(\.+[\\/]){3,}|[<>&\\\|:\?$!]{2,}|[+\s]{5,}|(%[0-9A-F]{2,3}){5,}#',str_replace(array('%20','%21','%24','%26','%2E','%2F','%3C','%3D','%3F','%5C'),array(' ','!','$','&','+','.','/','<','>','?','\\'),$target))>0){$is_attack=true;break;}
			elseif(preg_match('/(?:^|[^a-z_\-])(select|update|delete|alter|drop|union|create)[ %&].*(?:from)?.*wp_\w+/i',str_replace(array('\\','&#92;','"','%22','&#34;','&quot;','&#39;','\'','`','&#96;'),'',$target))>0){$is_attack=true;break;}
			elseif(preg_match('#([\<\;C](script|\?|\?php)[^a-z0-9])|(\.{1,2}/){3,}|\=\.\./#i',$target)>0 || preg_match('/[^a-z0-9_\/\-](function|script|window|cookie)[^a-z0-9_\- ]/i',$target)>0 || preg_match('/[^0-9A-Za-z]+(GET|POST)[^0-9A-Za-z]/',str_replace(array('%20','%2B'),array(' ','+'),$target))>0){$is_attack=true;break;}
			elseif(preg_match('/[^a-z_\-](dir|href|location|path|document_root.?|rootfolder)(\s|%20)?\=/i',$target)>0){$is_attack=true;break;}
			elseif(preg_match('/\.(bat|bin|cfm|cmd|exe|ini|[cr]?sh)([^a-z0-9]+|$)/i',$target)>0 || (preg_match('/\.dll(^a-z0-9_\-]+|$)/',$target)>0 && strpos($target,'.att.net/')===false) || preg_match('/[^0-9a-z_]setup\.[a-z]{2,4}([^0-9a-z]+|$)/',$target)>0){$is_attack=true;break;}
			elseif(preg_match('#[\\/](dev|drivers?|etc|program\sfiles|root|system|system32|windows)[/\\%&]#i',str_replace('%20',' ',$target))>0 || preg_match('#(c|file)\:[\\/]+.*install#i',$target)>0){$is_attack=true;break;}
			elseif(preg_match('/[^a-z0-9$%][$`%]?([a-km-rt-z_][a-z0-9_\-]+)[`%]?\s?\=\s?\-[190x]+/i',str_replace(array('&36;','%24','%20','&#96;','%60','%3D','&#61;','%2D','&#45;'),array('$','$',' ','`','`','=','=','-','-'),$target))>0){$is_attack=true;break;}
			elseif(preg_match('/[^a-z0-9_](admin|administrator|superuser|root|uid|username|user_?id)\=[-&%]/i',$target)>0||preg_match('/(admin|administrator|id|root|user)\=(-1|0[x&]|0$)/',$target)>0){$is_attack=true;break;}
			elseif(preg_match('/[^0-9a-z_][\$\[`]+/',$target)>0 || (preg_match('/[{}]/',$target)>0 && strpos($target,'.asp')===false) || preg_match('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/',$target)>0){$is_attack=true;break;}
		} //end foreach
	} //end if targets
	return $is_attack;
} //end wIsAttack

// ==========================================
// IV: Website content functions
// START initializing Widget
function wassup_widget_init(){
	if(!defined('WASSUPVERSION')) wassup_init();
	$wassup_widget_classes=array(
		'wassup_onlineWidget',
		'wassup_topstatsWidget',
	);
	if(!class_exists('Wassup_Widget')) include_once(WASSUPDIR.'/widgets/widgets.php');
	foreach($wassup_widget_classes as $wwidget){
		if(!empty($wwidget) && class_exists($wwidget))
			register_widget($wwidget);
	}
}

/** 
 * TEMPLATE TAG: wassup_sidebar 
 * Displays Wassup Current Visitors Online widget directly from "sidebar.php" template or from a page template
 * Usage: wassup_sidebar('1:before_widget_tag','2:after_widget_tag','3:before_title_tag','4:after_title_tag','5:title','6:list css-class','7:max-width in chars','8:top_searches_limit, 9:top_referrers_limit, 10:top_browsers_limit, 11:top_os_limit)
 */
function wassup_sidebar($before_widget='',$after_widget='',$before_title='',$after_title='',$wtitle='',$wulclass='',$wchars=0,$wsearchlimit=0,$wreflimit=0,$wtopbrlimit=0,$wtoposlimit=0){
	global $wpdb,$wassup_options,$wdebug_mode;
	if(!defined('WASSUPVERSION')) wassup_init();
	if(!function_exists('wassup_widget_get_cache')){
		include_once(WASSUPDIR.'/widgets/widget_functions.php');
	}
	if(empty($before_widget)|| empty($after_widget)|| strpos($before_widget,'>')===false || strpos($after_widget,'</')===false){
		$before_widget='<div id="wassup_sidebar" class="widget wassup-widget">';
		$after_widget='</div>';
	}
	if(empty($before_title)|| empty($after_title)|| strpos($before_title,'>')===false || strpos($after_title,'</')===false){
		$before_title='<h2 class="widget-title wassup-widget-title">';
		$after_title='</h2>';
	}
	if($wtitle!="")$title=$wtitle;
	else $title=__("Visitors Online","wassup");
	if($wulclass!="" && preg_match('/([^a-z0-9\-_]+)/',$wulclass)>0)$wulclass=""; //no special chars allowed
	if($wulclass!="")$ulclass=' class="'.$wulclass.'"';
	else $ulclass="";
	$chars=(int)$wchars;
	$cache_key="_online";
	//check for cached 'wassup_sidebar' html
	$widget_html=wassup_widget_get_cache('wassup_sidebar',$cache_key);
	if(empty($widget_html)){
		//calculate stats only when WassUp is active
		if(empty($wassup_options->wassup_active)){
			$widget_html="\n".$before_widget;
			if(!empty($title))$widget_html.='
	'.$before_title.$title.$after_title;
			$widget_html.='
	<p class="small">'.__("No Data","wassup").'</p>'.wassup_widget_foot_meta().$after_widget;
		}else{
			$widget_html="";
			$online_html="";
			$top_html="";
			$instance=array(
				'title'=>"",
				'ulclass'=>$wulclass,
				'chars'=>$chars,
				'online_total'=>1,
				'online_loggedin'=>1,
				'online_comauth'=>1,
				'online_anonymous'=>1,
				'online_other'=>1,
				'top_searches'=>(int)$wsearchlimit,
				'top_referrers'=>(int)$wreflimit,
				'top_browsers'=>(int)$wtopbrlimit,
				'top_os'=>(int)$wtoposlimit,
			);
			//get online counts
			$html=wassup_widget_get_online_counts($instance);
			if(!empty($html)){
				$online_html= "\n".$before_widget;
				if(!empty($title))$online_html.='
	'.$before_title.$title.$after_title;
				$online_html.='
	<ul'.$ulclass.'>
	'.$html.'
	</ul>'.wassup_widget_foot_meta().$after_widget;
			}
			//get top stats
			if($instance['top_searches']>0 || $instance['top_referrers']>0 || $instance['top_browsers']>0 || $instance['top_os']>0){
				$to_date=current_time('timestamp');
				$from_date=$to_date-24*60*60;
				$i=0;
				foreach(array('searches','referrers','browsers','os') AS $item){
					$html="";
					$limit=$instance['top_'.$item];
					if($limit >0) $html=wassup_widget_get_topstat($item,$limit,$chars,$from_date);
					if(!empty($html)){
						$title=$before_title.wassup_widget_stat_gettext($item).$after_title;
						if($i>0)$top_html.="\n".$after_widget;
						$top_html.="\n".$before_widget;
						$top_html.='
	'.$title.'
	<ul'.$ulclass.'>'.$html.'
	</ul>';
						$i++;
					}
				} //end foreach
				//append footer meta to end of widget
				if(!empty($top_html)) $top_html.=wassup_widget_foot_meta().$after_widget;
			} //end if top_searches>0
			//cache the sidebar widget
			$widget_html .=$top_html.$online_html;
			if(!empty($widget_html)){
				$refresh=1;
				$cacheid=wassup_widget_save_cache($widget_html,'wassup_sidebar',$cache_key,$refresh);
			}
		} //end else wassup_active
	}
	if(!empty($widget_html)){
		echo "\n".'<div class="wassup_sidebar">'."\n";
		echo wassup_widget_css(true); //embed widget styles
		echo $widget_html;
		echo "\n".'</div>';
	}
} //end wassup_sidebar
// ==========================================
//## Add essential hooks after functions have been defined
//New in v1.9: uninstall hook for complete plugin removal from WordPress - deletes data and files
register_activation_hook($wassupfile,'wassup_install'); 
if(function_exists('register_uninstall_hook')) register_uninstall_hook($wassupfile,'wassup_uninstall');
unset($wassupfile); //to free memory
wassup_start();	//start WassUp
?>
