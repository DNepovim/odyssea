<?php

/**
 * Print register form with shortcode [register-form year=$year]
 *
 * @param array $atts shortcode atributes
 *
 * @return string with HTML form
 */

add_shortcode( 'register-form', 'ptcm_show_register_form' );
function ptcm_show_register_form( $atts ) {

	$a = shortcode_atts( array( 'year' => 'missing year' ), $atts );

	$prefix = 'ptcm_';

	$args = array(
		'post_type' => $prefix . $a['year'],
		'offset'    => 0,
		'order'     => 'ASC',
	);

	$participants = get_posts( $args );


	$fields = ptcm_add_default_questions();
	$output = '';

	if (!empty($_GET['success'])) {
		if ($_GET['success']) {
			$output .= '<div id="message" class="message message-success"><strong>Výborně!</strong><br>Přihlásil jsem tě mezi uchazeče a poslal ti e-mail na adresu <strong>' . $_GET['recipient'] . '</strong></div>';
		}
	}
	$output .= '<form class="form" method="post" action="' . htmlspecialchars( plugin_dir_url( __FILE__ ) . 'save-participant.php') . '">';
	$output .= '<input type="hidden" name="year" value="' . $a['year'] . '">';
	foreach ( $fields as $partition ) {
		if ( ! isset( $partition['frontend'] ) ) {
			$output .= '<fieldset class="form-fieldset">';
			$output .= '<legend class="form-legend">' . $partition['title'] . '</legend>';
			foreach ( $partition['fields'] as $field ) {
				$output .= ptcm_render_field( $field );
				if (!empty($_GET[$field['id']])) {
					if ($_GET[$field['id']]) {
						$output .= '<span class="form-error">Vyplň prosím toto pole.</span>';
					}
				}
			}
			$output .= '</fieldset>';
		}
	}

	$vintage = get_page_by_title($a['year'], 'OBJECT','ptcm_vintage');
	$custom_fields = get_post_meta($vintage->ID, 'ptcm_field', true);
	$i = 0;

	$output .= '<fieldset class="form-fieldset">';
	$output .= '<legend class="form-legend">Další informace</legend>';

	foreach ( $custom_fields as $field ) {

		$field_settings = array(
			'id'   => $prefix . 'custom_meta_' . sprintf( "%02d", $i++ ),
			'name' => $field[ $prefix . 'name' ],
			'type' => $field[ $prefix . 'type' ]
		);

		if ( array_key_exists( $prefix . 'options', $field ) ) {
			$field_settings['options'] = $field[ $prefix . 'options' ];
		}

		$output .= ptcm_render_field($field_settings);

	}

	$output .= '</fieldset>';

	$output .= '<fieldset class="form-fieldset">';
	$output .= wp_nonce_field( 'post_nonce', 'post_nonce_field' );
	$output .= '<input id="submitted" type="hidden" name="submitted" value="true">';
	$output .= '<button class="button form-button" type="submit">Přihlašuji se na plavbu!</button>';
	$output .= '</fieldset>';
	$output .= '</form>';

	return $output;

}

/**
 * Print register form with shortcode [register-form year=$year]
 *
 * @param array $args List of input args
 *
 * @return string with HTML input
 */

function ptcm_render_field( $args ) {
	$output = '<label class="form-label" for="' . $args['id'] . '">' . $args['name'] . ':</label>';
	switch ( $args['type'] ) {
		case 'text':
		case 'date':
		case 'email':
		case 'checkbox':
			$output .= '<input class="form-input form-' . $args['type'] . '" type="' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" required></input>';
			break;
		case 'textarea':
			$output .= '<textarea class="form-input form-' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" required></textarea>';
			break;
		case 'select':
			$output .= '<select class="form-input form-' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" required>';
			foreach ( $args['options'] as $value => $label ) {
				$output .= '<option value="' . $value . '">' . $label . '</option>';

			}
			$output .= '</select>';
			break;
		case 'radio':
			foreach ( $args['options'] as $value => $label ) {
				$output .= '<label class="form-radio-label" for="' . $args['id'] . '_' . $value . '">';
				$output .= '<input class="form-radio-input" value="' . $value . '" type="' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '_' . $value . '" required></input>';
				$output .=  $label;
				$output .= '</label>';
			}
			break;
	}
	return $output;
}
