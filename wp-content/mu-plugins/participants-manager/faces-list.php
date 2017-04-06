<?php

/**
 * Accepted participants list with shortcode [faces-list year=$year]
 *
 * @param array $atts shortcode atributes
 *
 * @return string with participants table
 */

add_shortcode( 'faces-list', 'ptcm_show_faces_list' );
function ptcm_show_faces_list( $atts ) {

	$a = shortcode_atts( array( 'year' => 'missing year' ), $atts );

	$prefix = 'ptcm_';

	$args = array(
		'post_type'   => $prefix . $a['year'],
		'numberposts' => - 1,
		'order'       => 'ASC',
		'post_status' => 'private',
		'meta_key'    => $prefix . 'accepted',
		'meta_value'  => 2
	);


	$output = '';

	if ( $participants = get_posts( $args ) ) {
		foreach ( $participants as $person ) {
			bdump(get_post_meta($person->ID, $prefix . 'accepted', true));
			$output .= '<article class="face-box">';
			$thumbnail = get_the_post_thumbnail_url($person, 'ptcm_face' );
			$output .= '<figure class="face-box-thumb" style="background-image: url(' . $thumbnail . ')"></figure>';
			if ($title = get_post_meta($person->ID, $prefix . 'nickname', true)) {
				$output .= '<h3 class="face-box-title">' . $title . '</h3>';
			}
			if ( $sms = get_post_meta( $person->ID, $prefix . 'sms', true ) ) {
				$output .= '<p class="face-box-sms"/>' . $sms . '</p>';
			}
			$output .= '</article>';
		}
	} else {
		$output .= '<tr><td class="table-empty" colspan="5">Zatím nejsou žádní přijatí účastníci.</td></tr>';
	}

	return $output;
}

