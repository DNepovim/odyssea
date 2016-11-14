<?php
/**
 * Compatibility functions required for Wassup to run in PHP 4.3-5.1 and Wordpress 2.2-3.1
 *
 * Emulates functions from PHP 5.2+ and Wordpress 2.8+. for backwards compatibility with PHP 4.3-5.1 and Wordpress 2.2-3.1
 *
 * @package WassUp Real-time Analytics
 * @subpackage /lib/compat-lib/compat_functions.php module
 * @since:	v1.8
 * @author:	Helene D. <http://helenesit.com>
 */
//no direct request for this plugin module
$wfile=preg_replace('/\\\\/','/',__FILE__); //for windows
if(!defined('ABSPATH')|| !isset($GLOBALS['wp_version'])|| preg_match('#'.preg_quote(basename($wfile)).'#',$_SERVER['PHP_SELF'])>0|| !empty($_SERVER['SCRIPT_FILENAME'])&& realpath($wfile)===realpath($_SERVER['SCRIPT_FILENAME'])){
	if(!headers_sent()){header('Location: /?p=404page&err=wassup403');exit;
	}elseif(function_exists('wp_die')){wp_die("Bad Request: ".esc_attr(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI']),array())));exit;
	}else{die("Bad Request: ".htmlspecialchars(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI'])));exit;}
	exit;
}
unset($wfile);
//-------------------------------------------------
//define PHP5+ functions used in Wassup
if(!function_exists('json_decode')){	//added in PHP 5.2
function json_decode($json,$to_array=false){
	$x=false;
	if(!empty($json)&& strpos($json,'{"')!==false){
		$out='$x='.str_replace(array('{','":','}'),array('array(','"=>',')'),$json);
		eval($out.';');
		if(!$to_array)$x=(object)$x;
	}
	return $x;
}
}
//-------------------------------------------------
//define Wordpress 2.3-2.8 functions used in Wassup
if(!function_exists('wp_safe_redirect')){ //added in Wordpress 2.3
function wp_safe_redirect($location,$status="302"){
	wp_redirect($location,$status);
	exit;
}
}
if(!function_exists('like_escape')){	//added in Wordpress 2.5
function like_escape($text){		//deprecated in Wordpress 4.0
	global $wpdb;
	if(method_exists($wpdb,'esc_like'))$escaped_text=$wpdb->esc_like($text);
	else $escaped_text=str_replace(array("%","_"),array("\\%","\\_"),trim($text));
	return $escaped_text;
}
}
if(!function_exists('get_avatar')){	//added in Wordpress 2.5
function get_avatar($userid=0,$imgsize=18){return "";}
}
if(!function_exists('has_action')){	//added in Wordpress 2.5
function has_filter($tag,$function_to_check=false){
	$wp_filter = $GLOBALS['wp_filter'];
	$has=false;
	if(!empty($wp_filter[$tag])){
		foreach ($wp_filter[$tag] as $callbacks){if(!empty($callbacks)){$has=true;break;}}
		if ($has && $function_to_check!==false){
			$has=false;
			if(is_string($function_to_check))$idx=$function_to_check;
			elseif(function_exists('_wp_filter_build_unique_id'))$idx = _wp_filter_build_unique_id($tag,$function_to_check,10);
			else $idx=false;
			if($idx!==false){
				foreach((array)array_keys($callbacks) as $priority){if(isset($callbacks[$priority][$idx])){$has=$priority;break;}}
			}
		}
	}
	return $has;
}
function has_action($tag,$function_to_check = false){
	return has_filter($tag,$function_to_check);
}
}
if(!function_exists('admin_url')){	//added in Wordpress 2.6
function admin_url($admin_file=""){
	$adminurl=get_bloginfo('wpurl')."/wp-admin/".$admin_file;
	return $adminurl;
}
}
if(!function_exists('plugins_url')){	//added in Wordpress 2.6
function plugins_url($plugin_file=""){
	if(defined('WP_CONTENT_URL')&& defined('WP_CONTENT_DIR')&& strpos(WP_CONTENT_DIR,ABSPATH)===FALSE)$pluginurl=rtrim(WP_CONTENT_URL,"/")."/plugins/".$plugin_file;
	else $pluginurl=get_bloginfo('wpurl')."/wp-content/plugins/".$plugin_file;
	return $pluginurl;
}
}
if(!function_exists('get_user_by')){	//added in Wordpress 2.8
function get_user_by($ufield,$uvalue){
	$user=false;
	if(!empty($uvalue)){
		if($ufield=="login"){
			if(function_exists('get_userdatabylogin')) $user=get_userdatabylogin($uvalue);
		}elseif(is_numeric($uvalue)){
			$user=get_userdata($uvalue); //ID is default
		}
	}
	return $user;
}
}
if(!function_exists('esc_attr')){	//added in Wordpress 2.8
function esc_attr($text){return attribute_escape($text);}
function esc_html($html){return wp_specialchars($html, ENT_QUOTES);}
function esc_url($url,$protocol=null,$context='display'){
	$newurl=clean_url($url,$protocol,$context);
	if(empty($newurl) && !empty($url)){  //oops, clean_url chomp
		$new_url = attribute_escape(strip_tags(html_entity_decode(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$url),array()))));
	}
	return $newurl;
}
function esc_sql($data){
	global $wpdb;
	if (empty($wpdb->use_mysqli)) return mysql_real_escape_string($data);
	else return mysqli_real_escape_string();
}
}
//-------------------------------------------------
//define Wordpress 3+ functions used in Wassup
if(!function_exists('delete_user_option')){ //added in Wordpress 3.0
function delete_user_option($user_id,$option_name,$option_value=''){
	if(function_exists('delete_user_meta')) return delete_user_meta($user_id,$option_name);
	else return delete_usermeta($user_id,$option_name,$option_value);
}
}
if(!function_exists('is_multisite')){	//added in Wordpress 3.0
function is_multisite(){
	if(defined('MULTISITE')) return MULTISITE;
	if(defined('SUBDOMAIN_INSTALL') || defined('VHOST') || defined('SUNRISE')) return true;
 	return false;
}
function is_subdomain_install(){
	if(defined('SUBDOMAIN_INSTALL')) return SUBDOMAIN_INSTALL;
	if(defined('VHOST') && VHOST=='yes') return true;
	return false;
}
function is_main_site($site_id=null) {
	if(!is_multisite()) return true;
	if(!$site_id) $site_id=get_current_blog_id();
	return (int)$site_id === (int)$GLOBALS['current_site']->blog_id;
}
}
if(!function_exists('is_network_admin')){ //added in Wordpress 3.1
function is_network_admin() {
	if(isset($GLOBALS['current_screen'])) return $GLOBALS['current_screen']->in_admin('network');
	elseif(defined('WP_NETWORK_ADMIN')) return WP_NETWORK_ADMIN;
	return false;
}
}
?>
