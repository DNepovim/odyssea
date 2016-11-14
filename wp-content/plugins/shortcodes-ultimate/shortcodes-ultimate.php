<?php
/*
  Plugin Name: Shortcodes Ultimate
  Plugin URI: http://gndev.info/shortcodes-ultimate/
  Version: 4.9.9
  Author: Vladimir Anokhin
  Author URI: http://gndev.info/
  Description: Supercharge your WordPress theme with mega pack of shortcodes
  Text Domain: shortcodes-ultimate
  Domain Path: /languages
  License: GPL
 */

// Define plugin constants
define( 'SU_PLUGIN_FILE', __FILE__ );
define( 'SU_PLUGIN_VERSION', '4.9.9' );
define( 'SU_ENABLE_CACHE', true );

// Includes
require_once 'inc/vendor/sunrise.php';
require_once 'inc/core/admin-views.php';
require_once 'inc/core/requirements.php';
require_once 'inc/core/load.php';
require_once 'inc/core/assets.php';
require_once 'inc/core/shortcodes.php';
require_once 'inc/core/tools.php';
require_once 'inc/core/data.php';
require_once 'inc/core/generator-views.php';
require_once 'inc/core/generator.php';
require_once 'inc/core/widget.php';
require_once 'inc/core/vote.php';
require_once 'inc/core/counters.php';
