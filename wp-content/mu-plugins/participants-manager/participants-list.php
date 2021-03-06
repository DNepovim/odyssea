<?php

/**
 * Print participants list with shortcode [participants-list year=$year]
 *
 * @param array $atts shortcode atributes
 *
 * @return string with participants table
 */

add_shortcode( 'participants-list', 'ptcm_show_participants_list' );
function ptcm_show_participants_list( $atts ) {

	$a = shortcode_atts( array( 'year' => 'missing year' ), $atts );

	$prefix = 'ptcm_';

	$args = array(
		'post_type'   => $prefix . $a['year'],
		'numberposts' => - 1,
		'order'       => 'ASC',
		'post_status' => 'private'
	);


	$output = '<table class="table">';
	$output .= '<thead>';
	$output .= '<tr>';
	$output .= '<th class="table-head"></th>';
	$output .= '<th class="table-head">Přezdívka</th>';
	$output .= '<th class="table-head">Jméno a příjmení</th>';
	$output .= '<th class="table-head">Město</th>';
	$output .= '<th class="table-head">SMS</th>';
	$output .= '<th class="table-head">Úkol</th>';
	$output .= '</tr>';
	$output .= '</thead>';

	if ( $participants = get_posts( $args ) ) {
		$i = 1;
		foreach ( $participants as $person ) {
			$output .= '<tr class="table-row">';
			$output .= '<td class="tablle-cell table-number">' . $i ++ . '. </td>';
			$output .= '<td class="table-cell table-nickname ' . get_post_meta( $person->ID, $prefix . 'gender', true ) . '">' . get_post_meta( $person->ID, $prefix . 'nickname', true ) . '</td>';
			$output .= '<td class="table-cell table-name">' . get_post_meta( $person->ID, $prefix . 'firstname', true ) . ' ' . get_post_meta( $person->ID, $prefix . 'surname', true ) . '</td>';
			$output .= '<td class="table-cell table-city">' . get_post_meta( $person->ID, $prefix . 'address_city', true ) . '</td>';
			$output .= '<td class="table-cell table-message">';
			if ( $sms = get_post_meta( $person->ID, $prefix . 'sms', true ) ) {
				$output .= '<svg class="table-icon" width="1692" height="1658" viewBox="0 0 1692 1658" xmlns="http://www.w3.org/2000/svg"><path d="M475.402 1657.973c15.035 0 29.926-4.822 41.984-14.746l439.527-361.254h576.755c86.817 0 158.332-72.961 158.332-161.899V160.168C1692 71.627 1620.485.973 1533.668.973H158.832C71.089.973 0 71.628 0 160.168v959.909c0 88.938 71.089 161.896 158.832 161.896H410v309.93c0 25.561 14.415 48.826 37.528 59.744 8.993 4.245 18.299 6.326 27.874 6.326zm1058.266-1525c13.953 0 25.332 11.52 25.332 27.195v959.906c0 15.805-11.615 29.898-25.332 29.898H933.23c-15.311 0-29.89 4.95-41.715 14.674L542 1451.998v-236.699c0-36.49-30.096-65.326-66.586-65.326H158.832c-14.123 0-26.832-14.639-26.832-29.896V160.168c0-15.146 12.457-27.195 26.832-27.195h1374.836z"/></svg>';
				$output .= '<div class="hidden">' . $sms . '</div>';
			}
			$output .= '</td>';
			$output .= '<td class="table-cell table-record">';
			if ( get_post_meta( $person->ID, $prefix . 'record', true ) ) {
				$output .= '<svg class="table-check" width="67" height="49" viewBox="0 0 67 49" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 21.3L24.6 44 64.5 3" /></svg>';
			}
			$output .= '</td>';
			$output .= '</tr>';
		}
	} else {
		$output .= '<tr><td class="table-empty" colspan="5">Zatím nejsou žádní přihlášení.</td></tr>';
	}

	$output .= '</table>';

	return $output;
}

