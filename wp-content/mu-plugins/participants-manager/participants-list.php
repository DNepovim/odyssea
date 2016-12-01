<?php

//[participants-list year=$year]
add_shortcode( 'participants-list', 'ptcm_show_participants_list' );
function ptcm_show_participants_list( $atts ) {

	$a = shortcode_atts( array( 'year' => 'missing year' ), $atts );

	$prefix = 'ptcm_';

	$args = array(
		'post_type' => $prefix . $a['year'],
		'offset'    => 0,
		'order'     => 'ASC',
		'post_status'  => 'private'
	);

	$participants = get_posts( $args );

	echo '<table>';

	foreach ($participants as $person) {
		echo '<tr>';
		echo '<td class="' . get_post_meta($person->ID, $prefix . 'gender', true) . '">'. get_post_meta($person->ID, $prefix . 'nickname', true) . '</td>';
		echo '<td>'. get_post_meta($person->ID, $prefix . 'firstname', true) . ' '. get_post_meta($person->ID, $prefix . 'surname', true) . '</td>';
		echo '<td>'. get_post_meta($person->ID, $prefix . 'address_city', true) . '</td>';
		echo '<td>';
		if ($sms = get_post_meta($person->ID, $prefix . 'sms', true)) {
			echo '<div>' . $sms . '</div>';
		}
		echo '</td>';
		echo '<td>';
		if (get_post_meta($person->ID, $prefix . 'record', true)) {
			echo '<span></span>';
		}
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}

