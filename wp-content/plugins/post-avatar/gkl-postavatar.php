<?php
/*
	Plugin Name: Post Avatar
	Plugin URI: http://www.garinungkadol.com/plugins/post-avatar/
	Description: Attach a picture to posts easily by selecting from a list of uploaded images. Similar to Livejournal Userpics. 
	Version: 1.6.0
	Author: Vicky Arulsingam
	Author URI: http://garinungkadol.com
	License: GPL2
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: gklpa
	Domain Path: /languages
*/

/*  Copyright 2006 - 2015 Vicky Arulsingam  (email : vix@garinungkadol.com)

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

// Avoid calling page directly
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );

	exit( 'Uh oh! Accessing this file outside of WordPress is not allowed' );	
}

	require plugin_dir_path( __FILE__ ) . 'deprecated.php';
	require plugin_dir_path( __FILE__ ) . 'settings.php';
/**
 * OPTIONS
 */
$gklpa_plugin_settings = gklpa_get_settings();
$gklpa_db_version = 1;

/**
 * Display post avatar within The Loop
 *
 * @since Post Avatar 1.0
 *
 * @param string $class CSS class to be placed in the <img> tag
 * @param string $before HTML displayed before image
 * @param string $after HTML displayed after image
 * @param string $do_what Display or return the image HTML
 * @return string Image HTML
 */
function gkl_postavatar($class='', $before='', $after='', $do_what= 'echo') {
	global $post, $allowedposttags, $gklpa_plugin_settings;
	
	$post_avatar_text = '';
	if (empty($class)) $class  = $gklpa_plugin_settings['css_class'];
	if( empty( $before ) ) $before =  $gklpa_plugin_settings['html_before'];
	if( empty( $after ) ) $after = $gklpa_plugin_settings['html_after'];

	// Validate and sanitize function options
	$possible_values = array( 'echo', 'return' );
	if ( !in_array( $do_what, $possible_values ) )
		wp_die( 'Invalid value in gkl_postavatar template tag', 'gklpa');
	
	$class = sanitize_html_class( $class );
	$before = wp_kses( $before, $allowedposttags );
	$after = wp_kses( $after, $allowedposttags );
	if (!empty($class)) $class = ' class="' . $class . '"';
	
	$post_avatar = gkl_get_postavatar($post);
	$avatar_dim = '';

	if( !is_null( $post_avatar ) ) {
		// Show image dimensions
		if ($post_avatar['show_image_dim']) {
			$avatar_dim = 'width="' . intval( $post_avatar['image_width'] ) .'" height="'. intval( $post_avatar['image_height'] ) .'"';
		}
		// Put the post avatar HTML together	
		$post_avatar_text = $before .'<img' .$class . ' src="'.  esc_url( str_replace( ' ', '%20', $post_avatar['avatar_url'] ) ) .'" '. $avatar_dim . ' alt="'. esc_attr( $post_avatar['post_title'] ). '" />'. $after ."\n";
	} 
	$post_avatar_text = apply_filters( 'gklpa_the_postavatar', $post_avatar_text, $post_avatar ); // You can override the default output
	
	// Show post avatar	
	if( $do_what === 'echo' ) echo $post_avatar_text;
	elseif( $do_what === 'return' ) return $post_avatar_text;
	
}


/**
 * Prepare post avatar data from the current post
 *
 * @since Post Avatar 1.2.5
 *
 * @param object $post Current post object
 * @return mixed $post_avatar Array of post avatar data or null if empty or file does not exist
 */
function gkl_get_postavatar($post) {
	global $gklpa_plugin_settings;

	// Defaults
	$post_avatar = array();
	$post_id = 0;
	$curr_avatar = '';
	
	$post_id = $post->ID;
	$curr_avatar = get_post_meta($post_id,'postuserpic', true);
	$curr_avatar_path = wp_normalize_path(  realpath( $gklpa_plugin_settings['path_to_avatars'] . $curr_avatar ) );

	// Verify that image exists
	if ( !empty($curr_avatar) && file_exists($curr_avatar_path) ) {
		// Prepare post avatar values
		$post_title = esc_attr(strip_tags($post->post_title) );
		$curr_avatar_url =  trailingslashit ($gklpa_plugin_settings['avatars_url'] ) . ltrim( $curr_avatar, '/' )  ;
		
		if ( $curr_avatar_url != $gklpa_plugin_settings['avatars_url'] ) {
			$dim = ( $gklpa_plugin_settings['get_imagesize'] ) ? @getimagesize( $curr_avatar_path ) : array( null, null );
			
			// create array of post avatar values			
			$post_avatar = array(
				'avatar_url' => $curr_avatar_url, 
				'show_image_dim' => $gklpa_plugin_settings['get_imagesize'], 
				'image_height'=> $dim[1], 
				'image_width' => $dim[0], 
				'post_id' => $post_id, 
				'post_title' => $post_title, 
				'image_name' => ltrim($curr_avatar,'/')
			);
		}
	} else {
			$post_avatar = null;
	}

	return $post_avatar;
}


/**
 * Get list of directory contents
 *
 * List of files found in the defined directory. Filtered to include images only.
 * TODO: Look into using `scandir()` since minimum required PHP version is now 5.2.4
 * TODO: Look into using RecursiveDirectoryIterator once minimu required PHP version is more than 5.3
 * @since Post Avatar 1.0
 *
 * @param string $dir Directory to scan
 * @param boolean $recursive Scan sub-directories
 * @return array $array_items Final list of images
 */
function gklpa_readdir($dir, $recursive = true) {
	global $gklpa_plugin_settings;
	
	
	// Cut of the myAvatarDir from the output
	$dir2 = trailingslashit ( $gklpa_plugin_settings['path_to_avatars'] );

	// Init
	$array_items = array();

	$handle = @opendir($dir);

	while (false !== ($file = @readdir($handle))) {
		// Bad for recursive to scan the current folder again and again and again...
		// ...also bad to scan the parent folder
		if ( $file != '.' && $file != ".." ) {
			// if is_file
			if (!is_dir($dir .'/'. $file)) {
				$file = $dir .'/'. $file;
				// remove image path from the output
				$array_items[] = str_replace($dir2, '', $file);
			} else {
				// if (is_dir && recursive scan) scan dir
				if ($recursive ) {
					$array_items = array_merge($array_items, gklpa_readdir($dir .'/'. $file, $recursive));
				}
				$file = $dir .'/'. $file;

				// remove image path from the output
				$array_items[] = str_replace($dir2, '', $file);
			}
		}
	}
	@closedir($handle);

	// Limit list to only images
	$array_items = preg_grep('/.jpg$|.jpeg$|.gif$|.png$/', $array_items);
	asort($array_items);
	return $array_items;
}

/*
 * Filter to include post avatar in the_content() or the_excerpt()
 *
 * @since Post Avatar 1.0
 *
 * @param text $content Content to be filtered
 * @return text $content Final output
 */
function gkl_postavatar_filter( $content ) {
	global $post, $gklpa_plugin_settings, $wp_query;
	$post_avatar = '';

	if ($gklpa_plugin_settings['use_content_filter'] == 1 && in_array( $post->post_type, $gklpa_plugin_settings['allowed_post_types'] ) ){
		// Make sure that we're in the main WP query
		// in cases where `the_content` filter runs outside the loop 
		// e.g. wp-admin, sidebars, custom queries
		if ( ( !is_main_query() && !in_the_loop() )	|| is_feed() ){ 
			return $content; 
		} else {
			$post_avatar = gkl_postavatar('', '', '', 'return');
		}
	}
	return $post_avatar . $content;	
}


/*
 * Filter to include post avatar in feeds
 *
 * @since Post Avatar 1.2.4
 *
 * @param text $content
 * @return text $content
 */
function gkl_postavatar_feed_filter($content) {
	global $post, $gklpa_plugin_settings, $wp_query;
	$post_avatar = '';
	
	if( $gklpa_plugin_settings['use_feed_filter'] == 1 && is_feed() )  
		$post_avatar = gkl_postavatar('', '', '', 'return');

	return $post_avatar . $content;
}

# ----------------------------------------------------------------------------------- #
#                                 POST META BOX                                       #
# ----------------------------------------------------------------------------------- #

/*
 * Display custom meta box in post screen
 *
 * @since Post Avatar 1.2.5
 *
 */
function gkl_postavatar_metabox_admin() {
	global $gklpa_plugin_settings;
	
	if ( !current_user_can('post_avatars') ) 
		return;
		
	if( !is_dir( $gklpa_plugin_settings['path_to_avatars'] ) ) {
		printf( __( '<p>Whoops! The folder "<strong>%1$s</strong>" does not exist. Please go to Settings >> Post Avatar to set the correct location.</p>', 'gklpa' ), $gklpa_plugin_settings['avatars_url'] );
		return;
	}
		
	$post_id = 0;	
	// Get current post's avatar
	if( isset( $_GET['post'] ) ) $post_id = intval($_GET['post'] );
	$curr_avatar = esc_attr( get_post_meta( $post_id, 'postuserpic', true ) );
	$selected = ltrim( $curr_avatar, '/' );
	
	//! Get AvatarList
	$recursive =  $gklpa_plugin_settings['scan_recursive'] ;  
	$AvatarList = gklpa_readdir($gklpa_plugin_settings['path_to_avatars'], $recursive);
	$AvatarList = apply_filters( 'gklpa_list_images', $AvatarList );
	?>
		<fieldset id="postavatarfield">
			<?php  gklpa_avatar_html($AvatarList, $curr_avatar, $selected); ?>		
		</fieldset>
	<?php
}

/** 
 * Generate html for post avatar display
 *
 * @since Post Avatar 1.2.4
 *
 * @param array $AvatarList List of images to be displayed
 * @param string $curr_avatar Image saved in post meta
 * @param string $selected Currently chosen image
 */
function gklpa_avatar_html($AvatarList, $curr_avatar, $selected) {
	global $gklpa_plugin_settings;
?>
	<table cellspacing="3" cellpadding="3" width="100%" align="left">
		<tr valign="top">
			<th width="20%"><?php _e('Select an avatar', 'gklpa'); ?></th>
			<td align="center">
			
				<a href="#prev" onclick="prevPostAvatar();return false" class="pa"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/prev.png'; ?>" alt="prev" title="" /></a>

				<select name="postuserpic" id="postuserpic" onchange="chPostAvatar(this)">
					<option value="no_avatar.png" onclick="chPostAvatar(this)"><?php _e('No Avatar selected', 'gklpa'); ?></option>
			<?php
				foreach ($AvatarList as $file) {
					if ($file == 'no_avatar.png')
						continue;
	
					$oncklick = ' onclick="chPostAvatar(this)"';
					echo '<option value="/'. esc_attr( $file ) .'"'. selected( $selected, $file, false ) . $oncklick .'>'. esc_attr( $file ) .'</option>'."\n";
				}
	?>
				</select>
	
				<a href="#next" onclick="nextPostAvatar();return false" class="pa"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/next.png'; ?>" alt="next" title="" /></a>

			</td>
		</tr>
	<?php
		// Display current avatar
		
	?>
			<tr>
				<th width="20%" align="center"><?php _e('Preview', 'gklpa'); ?></th>
				<td align="center">
				<?php
					if ( !empty($curr_avatar) ) {
						if ( file_exists($gklpa_plugin_settings['path_to_avatars'] . $curr_avatar) ) {
							$url =  esc_url( str_replace( ' ', '%20', $gklpa_plugin_settings['avatars_url'] . $curr_avatar ) ) ;
							$alt_text = __( 'Avatar' , 'gklpa' );
						} else {
							$url = 	plugin_dir_url( __FILE__ ) . 'images/missing_avatar.png';
							$alt_text =  __('Avatar Does Not Exist', 'gklpa') ;
						}
					} else {
						$url = plugin_dir_url( __FILE__ ) . 'images/no_avatar.png';
						$alt_text =  __('No Avatar selected', 'gklpa') ;
					}
					echo '<img id="postavatar" src="'. $url .'" alt="' . $alt_text . '" />';
				?></td>
			</tr>
	</table>
	<?php wp_nonce_field( plugin_basename( __FILE__ ), 'postuserpic-key' ); 
}

/**
 * Update post avatar
 *
 * @since Post Avatar 1.0
 *
 * @param integer $postid
 */
function gklpa_avatar_edit( $post_id ) {
	global $gklpa_plugin_settings, $post;

	if( !isset( $post_id) )
		$post_id = ((int) $_POST['post_ID']);
	
	// Checks save status
	$is_autosave = is_int( wp_is_post_autosave( $post_id ) ); // verify if this is an auto save routine. 
	$is_revision = is_int( wp_is_post_revision( $post_id ) ); // verify if this is the post revision routine

	// verify if nonce key is valid	
	$is_valid_nonce = ( isset( $_POST['postuserpic-key'] ) && wp_verify_nonce( $_POST['postuserpic-key'], plugin_basename( __FILE__ ) ) ) ? 'true' : 'false';  
 
	// Check permission for each post type
	$post_type = get_post_type_object( $post->post_type );
	$is_authorized_to_edit = ( current_user_can( $post_type->cap->edit_post, $post_id ) ) ? 'true' : 'false';
	
 	// Exits script depending on save status and permission status
	if ( $is_autosave || $is_revision || !$is_valid_nonce || !$is_authorized_to_edit ) {
		return;
	}
	
	$meta_value =  esc_attr($_POST['postuserpic']) ;
	$CheckAvatar = $gklpa_plugin_settings['path_to_avatars'] . $meta_value;

	// Verify avatar exists
	if ( !empty($meta_value) && !file_exists($CheckAvatar) ) unset($meta_value);

	if( isset($meta_value) && !empty($meta_value) && $meta_value != 'no_avatar.png' ) {
		update_post_meta($post_id, 'postuserpic', $meta_value);
	} else {
		delete_post_meta($post_id, 'postuserpic');
	}
}

/**
 * Upgrade plugin settings/features
 *
 * @since Post Avatar 1.6
 *
 * @param integer $postid
 */

function gklpa_upgrade( $current_db_version){
	global $gklpa_plugin_settings;

	
	if( $current_db_version < 1 ) {
		// Remove option to show/hide image preview in Pages screen
		// Used prior to Post Avatar 1.5
		if( get_option( 'gklpa_showinwritepage' ) )
			delete_option( 'gklpa_showinwritepage' );
	
		// change from many options to a single option array
		// since Post Avatar 1.6
		$options = array(
			'image_dir' => get_option( 'gklpa_mydir' ),
			'html_before' => get_option( 'gklpa_before' ),
			'html_after' => get_option( 'gklpa_after' ),
			'css_class' => get_option( 'gklpa_class' ),
			'get_imagesize' => get_option( 'gklpa_getsize' ),
			'scan_recursive' => get_option( 'gklpa_scanrecursive' ),
			'use_content_filter' => get_option( 'gklpa_showincontent' ),
			'use_feed_filter' => get_option( 'gklpa_showinfeeds' )
		);	
		update_option( 'gkl_postavatar', $options );
		
		// delete old options
		delete_option( 'gklpa_mydir' );
		delete_option( 'gklpa_before' );
		delete_option( 'gklpa_after' );
		delete_option( 'gklpa_class' );
		delete_option( 'gklpa_getsize' );
		delete_option( 'gklpa_scanrecursive' );
		delete_option( 'gklpa_showincontent' );
		delete_option( 'gklpa_showinfeeds' );

		
	}
	

}
/**
 * Installation function
 *
 * Checks version of WordPress against minimum version supported by plugin.
 * Creates `post_avatar` capability and set up default options
 * 
 * @since Post Avatar 1.4	
 */
function gklpa_install(){
	global $wp_version; 
	if ( version_compare( $wp_version , "3.9", "<" ) ) { 
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
		wp_die( __('This plugin requires WordPress version 3.9 or higher.', 'gklpa' ) );
	}
	
	// Set default options
	$options = array(
		'image_dir' => 'wp-content/uploads/',  // Default path is in relation to the current WordPress installation
		'html_before' => '<div class="postavatar">', // HTML to display before image
		'html_after' => '</div>', // HTML to display after image
		'css_class' => '', // Image class
		'get_imagesize' => 1,  // Display image dimensions
		'scan_recursive' => 1, // Include sub-directories in image folder
		'use_content_filter' => 1,  // Automatically filter the_content
		'use_feed_filter' => 0,  // Content filter for feeds is turned off by default 
		'db_version' => 1 // Current database version
	);
	add_option( 'gkl_postavatar', $options );
	
	// Create capability
	$role_list_arr = array( 'administrator', 'editor', 'author' ); // list of allowed roles
	foreach ( $role_list_arr as $role_value ) {
		$role = get_role( $role_value );
		if(!$role->has_cap('post_avatars'))
			$role->add_cap('post_avatars');
	}
}


/**
 * Actions to run inside admin
 *
 * @since Post Avatar 1.4
 */
function gklpa_admin_init(){
	global $gklpa_db_version, $gklpa_plugin_settings;
	
	$plugin_settings = get_option( 'gkl_postavatar' );
	// Upgrade plugin settings
	if ( false === $plugin_settings || ! isset( $plugin_settings['db_version'] ) || $plugin_settings['db_version'] < $gklpa_db_version ) {
			if ( ! is_array( $plugin_settings ) )
				$plugin_settings = array();
			
			$current_db_version = isset( $plugin_settings['db_version'] ) ? $plugin_settings['db_version'] : 0;
			gklpa_upgrade( $current_db_version );	
			$plugin_settings['db_version'] = $gklpa_db_version;
			$plugin_settings = array_merge( $plugin_settings, get_option( 'gkl_postavatar' ) );
			update_option( 'gkl_postavatar', $plugin_settings ); 
			
	}
	
	// Post meta box actions
	if( is_array( $gklpa_plugin_settings['allowed_post_types'] ) ){
		foreach ( $gklpa_plugin_settings['allowed_post_types'] as $post_type ){
			add_meta_box('postavatardiv', __('Post Avatar', 'gklpa'), 'gkl_postavatar_metabox_admin', $post_type); // Add meta box in the Post screen
			add_action("save_post_{$post_type}", 'gklpa_avatar_edit'); // Update post avatar when post is saved		
			// Control the admin pages the stylesheet and javascript will be shown. 
		}
	}
	add_action( 'admin_enqueue_scripts', 'gklpa_display_css' );
	add_action( 'admin_enqueue_scripts', 'gklpa_display_script' );
	
	
}

/**
 * Prepare global variable of all plugin settings
 *
 * Includes the database option 'gkl_postavatar' as well as helper variables
 *
 * @since Post Avatar 1.6
 * 
 * @return array $full_settings Complete array of plugin settings
 */
function gklpa_get_settings(){
	global $gklpa_plugin_settings; 

	$full_settings = get_option( 'gkl_postavatar' );
	
	$full_settings['site_url'] = apply_filters( 'gklpa_image_url', site_url() );  
	$full_settings['base_dir'] = apply_filters( 'gklpa_image_dir', ABSPATH );  
	$full_dir = trailingslashit(  $full_settings['base_dir'] . '/' .  $gklpa_plugin_settings['image_dir']  ) ; // Updated absolute path to images folder (takes into account Win servers) 
	$full_settings['path_to_avatars'] =  wp_normalize_path(  realpath( $full_dir )  ); 
	$full_settings['avatars_url'] = esc_url_raw( trailingslashit( $full_settings['site_url']) . $gklpa_plugin_settings['image_dir'] ); // URL to images folder
 	$full_settings['allowed_post_types'] = apply_filters( 'gklpa_allowed_post_types', array( 'post' ) );
 
	return $full_settings; 

}


/**
 * Actions to run throughout the site
 *
 * @since Post Avatar 1.4
 * 
 */
function gklpa_init(){
	global $gklpa_plugin_settings;
	
	$gklpa_plugin_settings = gklpa_get_settings(); 
	gklpa_set_globals();
	
	// Register script and stylesheet
	wp_register_script( 'gkl_postavatar_js', plugins_url('head/gkl-postavatar.js',  __FILE__ ), array(), NULL );
	wp_register_style('gkl_postavatar_css', plugins_url('head/gkl-postavatar.css', __FILE__), array(), NULL );

	// Load text domain for translation
	load_plugin_textdomain( 'gklpa', false, plugin_dir_path( __FILE__ ) . 'languages' );
}

/**
 * Enqueue the javascript file in the Post Screen
 *
 * @since Post Avatar 1.4
 * 
 * @param $string $hook Current screen e.g. post.php, post-new.php
 */
function gklpa_display_script( $hook ){
		global $gklpa_plugin_settings, $typenow;
		
	// Use filter `gklpa_allowed_post_types` to control where post avatar metabox can be used
	if( in_array( $typenow, $gklpa_plugin_settings['allowed_post_types'] ) && ( $hook == 'post-new.php' || $hook == 'post.php' ) ) {
		//if( in_array( $hook, $gklpa_plugin_settings['show_where_admin'] ) ) {
		wp_enqueue_script( 'gkl_postavatar_js');
		
		// Adding script variables
		wp_localize_script( 'gkl_postavatar_js', 'gkl_postavatar_text', array(
		'noavatar_msg' => __( 'No Avatar selected', 'gklpa' ),
		'site_url' => $gklpa_plugin_settings['site_url'],
		'avatar_url' => $gklpa_plugin_settings['avatars_url'],
		'avatar_img' => plugin_dir_url( __FILE__ ) . 'images'
		) );
	}
}

/**
 * Enqueue plugin stylesheet
 *
 * @since Post Avatar 1.4
 * 
 * @param $string $hook Current screen e.g. post.php, post-new.php
 */
function gklpa_display_css( $hook ){
	global $gklpa_plugin_settings, $typenow;
	// Show stylesheet in backend and front end
	if( ( is_admin() &&  ( in_array( $typenow, $gklpa_plugin_settings['allowed_post_types'] ) && ($hook == 'post.php' || $hook == 'post-new.php' ) ) ) || 
		( !is_admin() && $gklpa_plugin_settings['use_content_filter'] == 1) ) {
		wp_enqueue_style('gkl_postavatar_css');
	} 
}

# ----------------------------------------------------------------------------------- #
#                               HOOKS AND FILTERS                                     #
# ----------------------------------------------------------------------------------- #

register_activation_hook( __FILE__, 'gklpa_install' );	// Installation
add_action( 'admin_init', 'gklpa_admin_init' );			// Run in Admin
add_action( 'admin_init', 'gklpa_register_settings' ); 
add_action( 'init', 'gklpa_init' );						// Run throughout site
add_action( 'admin_menu', 'gklpa_settings_menu');			// Settings

// Displays the CSS and JavaScript files in the appropriate places
add_action('wp_enqueue_scripts', 'gklpa_display_css' );

// Filters
// Automatic Display in Feeds
add_filter('the_content_feed', 'gkl_postavatar_feed_filter');			
// Automatic Display in Content/Excerpts
add_filter('the_content', 'gkl_postavatar_filter', 99);			
add_filter('the_excerpt', 'gkl_postavatar_filter', 99);

?>