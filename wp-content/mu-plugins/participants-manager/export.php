<?php

require_once __DIR__ . '/../../../wp-load.php';

$prefix = 'ptcm_';

// get participants older then limit
$args = array(
	'post_type'   => $prefix . $_GET['year'],
	'numberposts' => -1,
	'order'       => 'ASC',
	'post_status' => 'private',
	'meta_key'		=> $prefix . 'accepted',
	'meta_value'	=> true,

);
$participants = get_posts($args);

echo '<table border="2px"><thead><tr><td><strong>Přesdívka</strong></td><td><strong>Jméno</strong></td><td><strong>Město</strong></td><td><strong>Telefon</strong></td></tr></thead><tbody>';


foreach ($participants as $item) {
	echo '<tr>';
	echo '<td>' . get_post_meta($item->ID, $prefix . 'nickname', true) . '</td>';
	echo '<td>' . $item->post_title . '</td>';
	echo '<td>' . get_post_meta($item->ID, $prefix . 'address_street', true) . '</td>';
	echo '<td>' . get_post_meta($item->ID, $prefix . 'address_number', true) . '</td>';
	echo '<td>' . get_post_meta($item->ID, $prefix . 'address_city', true) . '</td>';
	echo '<td>' . get_post_meta($item->ID, $prefix . 'address_post_code', true) . '</td>';
	echo '</tr>';
}

echo '</tbody></table>';
