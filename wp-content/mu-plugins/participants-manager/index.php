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

function ptcm_add_posttype_note() {
	global $pagenow;
	if ( ! empty( $_GET['post_type'] ) ) {
		if ( $year = $_GET['post_type'] ) {
			if ( $pagenow == 'edit.php' && substr( $year, 0, 5 ) == 'ptcm_' ) {
				echo '<h2>Shortcodes</h2>';
				echo '<p>Show register form: [register-form year=' . substr( $year, 5 ) . ']</p>';
				echo '<p>Show participants list: [participants-list year=' . substr( $year, 5 ) . ']</p>';
			}
		}
	}

}

add_action( 'all_admin_notices', 'ptcm_add_posttype_note' );
