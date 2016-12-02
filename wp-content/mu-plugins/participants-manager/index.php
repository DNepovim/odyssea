<?php

/**
 * Plugin Name: Participants Manager
 * Plugin URI:
 * Version: 0.1
 * Author: Dominik Blaha
 * Author URI: http://www.dombl.cz
 * License: MIT
 * Requirements: https://metabox.io/plugins/meta-box-group/
 */

require_once ABSPATH . '/wp-content/mu-plugins/wp-tracy/index.php';
use Tracy\Debugger;
Debugger::enable();


require_once 'vintage_register.php';
require_once 'vintage_settings.php';
require_once 'participants-list.php';
require_once 'register-form.php';

! defined( 'ABSPATH' ) AND exit;
/** Plugin Name: (#64933) »kaiser« Add post/page note */

function wpse64933_add_posttype_note()
{
	global $post, $pagenow;

	if ($pagenow == 'edit.php' && substr($post->post_type, 0, 5) == 'ptcm_') {
		echo '<h2>Shortcodes</h2>';
		echo '<p>Show register form: [register-form year=' . substr($post->post_type, 5) . ']</p>';
		echo '<p>Show participants list: [participants-list year=' . substr($post->post_type, 5) . ']</p>';
	}
}
add_action( 'all_admin_notices', 'wpse64933_add_posttype_note' );
