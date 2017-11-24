<?php
global $photonic_setup_options, $photonic_generic_options, $photonic_flickr_options, $photonic_picasa_options, $photonic_smugmug_options, $photonic_500px_options, $photonic_instagram_options, $photonic_zenfolio_options;

$photonic_setup_options = array();

require_once(plugin_dir_path(__FILE__) . "/generic-options.php");
foreach ($photonic_generic_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/flickr-options.php");
foreach ($photonic_flickr_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/picasa-options.php");
foreach ($photonic_picasa_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/500px-options.php");
foreach ($photonic_500px_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/smugmug-options.php");
foreach ($photonic_smugmug_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/instagram-options.php");
foreach ($photonic_instagram_options as $option) {
	$photonic_setup_options[] = $option;
}

require_once(plugin_dir_path(__FILE__) . "/zenfolio-options.php");
foreach ($photonic_zenfolio_options as $option) {
	$photonic_setup_options[] = $option;
}

function photonic_title_styles() {
	$ret = array(
		"regular" => "<img src='".trailingslashit(PHOTONIC_URL).'include/images/title-regular.png'."' />Normal title display using the HTML \"title\" attribute",
		"below" => "<img src='".trailingslashit(PHOTONIC_URL).'include/images/title-below.png'."' />Below the thumbnail (Doesn't work for Random Justified Gallery and Mosaic Layout)",
		"tooltip" => "<img src='".trailingslashit(PHOTONIC_URL).'include/images/title-jq-tooltip.png'."' />Using the JQuery Tooltip plugin",
		"hover-slideup-show" => "<img src='".trailingslashit(PHOTONIC_URL).'include/images/title-slideup.png'."' />Slide up from bottom upon hover",
	);
	return $ret;
}

function photonic_default_border() {
	$ret = array(
		'top' => array('colortype' => 'transparent', 'color' => '#c0c0c0', 'style' => 'none', 'border-width' => 0, 'border-width-type' => 'px'),
		'right' => array('colortype' => 'transparent', 'color' => '#c0c0c0', 'style' => 'none', 'border-width' => 0, 'border-width-type' => 'px'),
		'bottom' => array('colortype' => 'custom', 'color' => '#c0c0c0', 'style' => 'none', 'border-width' => 0, 'border-width-type' => 'px'),
		'left' => array('colortype' => 'transparent', 'color' => '#c0c0c0', 'style' => 'none', 'border-width' => 0, 'border-width-type' => 'px'),
	);
	return $ret;
}

function photonic_default_padding() {
	$ret = array(
		'top' => array('padding' => 0, 'padding-type' => 'px'),
		'right' => array('padding' => 0, 'padding-type' => 'px'),
		'bottom' => array('padding' => 0, 'padding-type' => 'px'),
		'left' => array('padding' => 0, 'padding-type' => 'px'),
	);
	return $ret;
}

function photonic_selection_range($min, $max) {
	$ret = array();
	for ($i = $min; $i <= $max; $i++) {
		$ret[$i] = $i;
	}
	return $ret;
}
