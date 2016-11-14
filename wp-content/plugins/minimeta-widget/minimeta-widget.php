<?php
/*
Plugin Name: MiniMeta Widget
Plugin URI: http://danielhuesken.de/portfolio/minimeta/
Description: WordPress (Mini)Meta Widget with different logon types (form,link) and additional admin links. All links can enabeld/disabeld.
Author: Daniel H&uuml;sken
Version: 4.5.3
Author URI: http://danielhuesken.de
Text Domain: MiniMetaWidget
Domain Path: /lang/
*/

/*  
	Copyright 2007  Daniel Hüsken  (email : daniel@huesken-net.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

// don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');

//Set plugin dirname
define('WP_MINMETA_PLUGIN_DIR', dirname(plugin_basename(__FILE__)));
//Ste Plugin Version
define('WP_MINMETA_VERSION', '4.5.3');
global $wp_version;
//load Text Domain
load_plugin_textdomain('MiniMetaWidget', false, WP_MINMETA_PLUGIN_DIR.'/lang');	 //TextDomain 

//Load functions file
require_once(WP_PLUGIN_DIR.'/'.WP_MINMETA_PLUGIN_DIR.'/app/functions.php');
//install
register_activation_hook(__FILE__, 'minimeta_install');
//uninstall
register_uninstall_hook(__FILE__, 'minimeta_uninstall');

//Version checks
if (version_compare($wp_version, '2.8', '<')) { // Let only Activate on WordPress Version 2.8 or heiger
	add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('Sorry, MiniMeta Widget works only under WordPress 2.5 or higher',"MiniMetaWidget") . '</strong></p></div>\';'));
} else {
	//Plugin init	
	add_action('plugins_loaded', 'minimeta_init');
}
?>
