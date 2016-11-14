<?php

/*
  Plugin Name: WP Tracy
  Plugin URI: https://github.com/ktstudio/wp-tracy/
  Description: (Nette) Tracy connector for WordPress
  Version: 1.0.5
  Author: KTStudio.cz
  Author URI: http://www.ktstudio.cz/
  License: GPLv2
 */

if (!function_exists("add_action")) { // Make sure we don't expose any info if called directly
    wp_die("Hi there!  I\'m just a plugin, not much I can do when called directly.");
}

if (!empty($GLOBALS["pagenow"]) && "plugins.php" === $GLOBALS["pagenow"]) {
    add_action("admin_notices", "wp_tracy_check_admin_notices", 0); // check current and required PHP & WordPress versions
}

function wp_tracy_check_admin_notices() {
    $phpMinVersion = "5.4";
    $phpCurrentVersion = phpversion();
    if (version_compare($phpMinVersion, $phpCurrentVersion, ">")) {
        $message = sprintf(__("Your server is running on PHP %s, but this plugin requires at least PHP %s. Please do the upgrade.", "WP_TRACY"), $phpCurrentVersion, $phpMinVersion);
        echo "<div id=\"message\" class=\"error\"><p>$message</p></div>";
        return;
    }
    $wpMinVersion = "4.0";
    global $wp_version;
    if (version_compare($wpMinVersion, $wp_version, ">")) {
        $message = sprintf(__("Your WordPress is in version %s, but this plugin requires at least version %s. Please start the upgrade.", "WP_TRACY"), $wp_version, $wpMinVersion);
        echo "<div id=\"message\" class=\"error\"><p>$message</p></div>";
        return;
    }
}

$pluginPath = __DIR__;
$vendorAutoloadPath = "$pluginPath/vendor/autoload.php";
if (!file_exists($vendorAutoloadPath)) { // composer check
    $message = __("First, install WP Tracy using Composer, run the command:", "WP_TRACY");
    $command = "composer require ktstudio/wp-tracy";
    echo "<div id=\"message\" class=\"error\"><p>$message</p><p>$pluginPath><b>$command</b></></p></div>";
    return;
}

$loader = require $vendorAutoloadPath; // apply by composer
