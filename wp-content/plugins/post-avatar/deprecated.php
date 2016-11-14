<?php
/**
 * Create global variables used prior to Post Avatar 1.6.
 * To be deprecated in future versions
 * Please make use of `$gklpa_plugin_settings` array 
 * to access options
 *
 * @since Post Avatar 1.6
 */ 
function gklpa_set_globals(){
	global $gklpa_plugin_settings, $gklpa_siteurl, $gkl_myAvatarDir, $gkl_ScanRecursive, $gkl_ShowInContent, $gkl_getsize;
	
	$gklpa_siteurl = site_url();
	$gkl_myAvatarDir = str_replace( '/', DIRECTORY_SEPARATOR, $gklpa_plugin_settings['base_dir'] . $gklpa_plugin_settings['image_dir'] ); // Updated absolute path to images folder (takes into account Win servers)
	$gkl_AvatarURL = trailingslashit(site_url()) . $gklpa_plugin_settings['image_dir']; // URL to images folder
	$gkl_ScanRecursive = $gklpa_plugin_settings['scan_recursive']; // Recursive scan of the images?
	$gkl_ShowInContent = $gklpa_plugin_settings['use_content_filter']; // Show avatar automatically in content?
	$gkl_getsize = $gklpa_plugin_settings['get_imagesize']; // Use getimagesize?
	$gkl_dev_override = false;
}

# ----------------------------------------------------------------------------------- #
#                               DEPRECATED FUNCTIONS                                  #
# ----------------------------------------------------------------------------------- #
/**
 * Override plugin setting to automatically display post avatar in content
 *
 * @since Post Avatar 1.2.5
 *
 * @deprecated Post Avatar 1.4
 * @deprecated Use remove_filter('the_content', 'gkl_postavatar_filter');
 *
 * @param string $value
 * @return $value
 */
function gkl_dev_override($deprecated_override = false) {
	global $gkl_dev_override;
	_deprecated_argument( __FUNCTION__, 'Post Avatar 1.4', __('Use <code>remove_filter</code> to override the automatic theme display', 'gklpa') );
	$gkl_dev_override = $deprecated_override;
}

/**
 * Display html characters
 *
 * @since Post Avatar 1.2.4
 *
 * @deprecated Post Avatar 1.4
 * @deprecated Use esc_html() instead
 *
 * @param string $value
 * @return $value
 */
function gkl_unescape_html($value) {
	_deprecated_argument( __FUNCTION__, 'Post Avatar 1.4', __('Using <code>esc_html()</code> to convert html for display in input boxes/text area', 'gklpa') );
	return str_replace(
		array("&lt;", "&gt;", "&quot;", "&amp;"),
		array("<", ">", "\"", "&"),
		$value);
}

/**
 * Checks, whether one of two strings are substrings of PHP_SELF
 * Used in case WordPress is installed in a subdomain (?)
 *
 * @since Post Avatar 1.2.2
 *
 * @deprecated Post Avatar 1.6
 * @deprecated Use `load-{file-name.php}` hook instead
 *
 * @return boolean
 */
function gkl_check_phpself() {
	_deprecated_argument( __FUNCTION__, 'Post Avatar 1.6', __('Use load-{file-name.php} to control where metabox is displayed', 'gklpa') );

	if (substr_count($_SERVER['PHP_SELF'], '/wp-admin/post.php') == 1 
		|| substr_count($_SERVER['PHP_SELF'], '/wp-admin/page.php') == 1 
		|| substr_count($_SERVER['PHP_SELF'], '/wp-admin/page-new.php') == 1 || substr_count($_SERVER['PHP_SELF'], '/wp-admin/post-new.php') == 1 
		|| substr_count($_SERVER['PHP_SELF'], '/wp-admin/edit.php') == 1)
		return true;
	else
		return false;
}

/**
 * Validate checked options
 *
 * @since Post Avatar 1.2.3
 *
 * @deprecated Post Avatar 1.6
 *
 * @param string $option
 * @return $value
 */
function gkl_validate_checked($option) {
	_deprecated_argument( __FUNCTION__, 'Post Avatar 1.6', __('Use absint() instead', 'gklpa') );

	$value = intval($option);
	if (!empty($value)) 
		$value = 1;
	
	return $value;
}

?>