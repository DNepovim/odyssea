<?php
/*
Plugin Name: Ads by datafeedr.com
Plugin URI: http://www.datafeedr.com/dfads/
Description: Randomly display any type of advertisement anywhere on your site.  Add rotating banner ads, Google Adsense, videos, text links and more to your sidebar, widget areas, posts and pages.
Version: 1.0.12
Tested up to: 4.8
Author: datafeedr.com
Author URI: http://www.datafeedr.com/
*/

/*  
Copyright 2017 Ads by datafeedr.com

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

define( 'DFADS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'DFADS_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'DFADS_METABOX_PREFIX', '_dfads_' );
define( 'DFADS_CONTEXT', 'dfads' );
define( 'DFADS_DOCS_URL', 'http://www.datafeedr.com/dfads/' );
define( 'DFADS_VERSION', '1.0.12' );

/**
 * Require necessary files.
 */
require_once( DFADS_PLUGIN_PATH . 'inc/cpt.class.php' );
require_once( DFADS_PLUGIN_PATH . 'inc/dfads.class.php' );

if (is_admin()) {
	require_once( DFADS_PLUGIN_PATH . 'inc/admin.php' );
	require_once( DFADS_PLUGIN_PATH . 'inc/metaboxes.class.php' );
}

// Load jQuery on front end pages.
function dfads_load_js() {
	if ( !is_admin() ) {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'init', 'dfads_load_js' );

/**
 * Instantiate the DFADS class.
 */
function dfads( $args='' ) {
	$ads = new DFADS();
	if ( is_array( $args ) ) {
		$args = http_build_query( $args );
	}
	return $ads->get_ads( $args );
}

/**
 * WP Ajax for front and backend.
 */
function dfads_ajax_load_ads(){
	echo dfads( $_REQUEST );
	die;
}
add_action('wp_ajax_nopriv_dfads_ajax_load_ads', 'dfads_ajax_load_ads');
add_action('wp_ajax_dfads_ajax_load_ads', 'dfads_ajax_load_ads');

/**
 * Add shortcode functionality to Text widget.
 */
function dfads_enable_shortcodes_in_text_widget() {
    $output = get_option( 'dfads-settings' );
	if ( isset( $output['dfads_enable_shortcodes_in_widgets'] ) ) {
		add_filter( 'widget_text', 'do_shortcode' );
	}
}
add_action( 'init', 'dfads_enable_shortcodes_in_text_widget' );

/**
 * Add _dfads_impression_count custom field to new post.
 */
function dfads_save_post( $post_id ) {
    $slug = 'dfads';
    $_POST += array("{$slug}_edit_nonce" => '');
    if ( isset( $_POST['post_type'] ) && $slug != $_POST['post_type'] ) { return; }
    if ( !current_user_can( 'edit_post', $post_id ) ) { return; }
    if ( !wp_verify_nonce( $_POST["{$slug}_edit_nonce"], plugin_basename( __FILE__ ) ) ) { return; }
    if ( isset( $post_id ) ) {
        add_post_meta($post_id, DFADS_METABOX_PREFIX.'impression_count', 0);
    }
}
add_action( 'save_post', 'dfads_save_post' );

/**
 * Add shortcode functionality.
 */
function dfads_shortcode( $atts ) {
	return dfads( $atts['params'] );
}
add_shortcode( 'dfads', 'dfads_shortcode' );

/**
 * Add settings link on plugin page.
 */
function dfads_settings_link( $links ) { 
	$settings_link = '<a href="options-general.php?page=dfads-settings">Settings</a>'; 
	array_unshift( $links, $settings_link) ; 
	return $links; 
}
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'dfads_settings_link' );

/**
 * Add plugin meta links on plugin page.
 */
function dfads_plugin_row_meta( $links, $file ) {
	$plugin = plugin_basename( __FILE__ );
 	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="'.DFADS_DOCS_URL.'">Documentation</a>' )
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'dfads_plugin_row_meta', 10, 2 );

/**
 * Add help drop down menu to plugin's settings page.
 */
function dfads_help( $contextual_help, $screen_id, $screen ) {
	global $dfads_plugin_hook;
	if ($screen_id == $dfads_plugin_hook) {
		$contextual_help = '<p><a href="'.DFADS_DOCS_URL.'">Documentation</a></p>';
	}
	return $contextual_help;
}
add_filter( 'contextual_help', 'dfads_help', 10, 3 );

/**
 * Remove "Visual" editor from "Add Ad" page.
 */
function dfads_user_can_richedit( $default ) {
	global $post;
	if ( DFADS_CONTEXT == get_post_type( $post ) ) {
		return false;
	}
	return $default;
}
add_filter( 'user_can_richedit', 'dfads_user_can_richedit' );

/**
 * Hide some meta boxes from "Add Ad" page.
 */
function dfads_default_hidden_meta_boxes( $hidden, $screen ) {
    if ( DFADS_CONTEXT == $screen->id ) {
		$hidden[] = 'postcustom'; 	 //hide custom fields.
		$hidden[] = 'pageparentdiv'; //hide page attributes fields.
		$hidden[] = 'postimagediv';  // hide featured image.
		$hidden[] = 'revisionsdiv';  // hide revisions.
    }
    return $hidden;
}
add_filter( 'default_hidden_meta_boxes', 'dfads_default_hidden_meta_boxes', 10, 2 );


/**
 * Delete transient data related to DFADS
 * https://github.com/Seebz/Snippets/tree/master/Wordpress/plugins/purge-transients
 */
function dfads_purge_transients() {
	
	global $wpdb;
	$older_than='10 seconds';
	$older_than_time = strtotime( '-' . $older_than );
		
	if ( $older_than_time > time() || $older_than_time < 1 ) {
		return false;
	}

	$transients = $wpdb->get_col(
		$wpdb->prepare( "
			SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name 
			FROM {$wpdb->options} 
			WHERE option_name LIKE '\_transient\_timeout\_dfad\__%%'
				AND option_value < %s
		", $older_than_time )
	);

	foreach($transients as $transient) {
		get_transient( $transient );
	}
	
	update_option('dfads_transient_data_deleted_time', time());
}
add_action( 'shutdown', 'dfads_purge_transients'  );


