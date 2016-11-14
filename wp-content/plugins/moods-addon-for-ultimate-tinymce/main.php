<?php
/**
 * @package Moods Addon for Ultimate TinyMCE
 * @version 1.2
 */
/*
Plugin Name: Moods Addon for Ultimate TinyMCE
Plugin URI: http://www.joshlobe.com/2011/10/ultimate-tinymce/
Description: Add over 50 animated smilies to your visual tinymce editor.
Author: Josh Lobe
Version: 1.2
Author URI: http://joshlobe.com

*/

/*  Copyright 2011  Josh Lobe  (email : joshlobe@joshlobe.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function tinymce_add_button_moodsplugin($mce_buttons) { 
	$pos = array_search('wp_help',$mce_buttons,true);
    if ($pos !== false) {
        $tmp_buttons = array_slice($mce_buttons, 0, $pos+1);
        $tmp_buttons[] = 'moods';
        $mce_buttons = array_merge($tmp_buttons, array_slice($mce_buttons, $pos+1));
    }
    return $mce_buttons;
} 
add_filter("mce_buttons_2", "tinymce_add_button_moodsplugin");


// Add the plugin array for moods
function jwl_moodsplugin_external_plugins( $plugin_array ) {
		$plugin_array['moods'] = plugin_dir_url(__FILE__) . 'moods/editor_plugin.js';
		   
		return $plugin_array;
}
add_filter( 'mce_external_plugins', 'jwl_moodsplugin_external_plugins' );
?>