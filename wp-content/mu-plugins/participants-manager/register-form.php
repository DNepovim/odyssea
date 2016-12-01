<?php

//[register-form year=$year]
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

	echo '<form class="form" method="post" action="' . htmlspecialchars( plugin_dir_url( __FILE__ ) . 'save-participant.php') . '">';
	echo '<input type="hidden" name="year" value="' . $a['year'] . '">';
	foreach ( $fields as $partition ) {
		if ( ! isset( $partition['frontend'] ) ) {
			echo '<fieldset class="form-fieldset">';
			echo '<legend class="form-legend">' . $partition['title'] . '</legend>';
			foreach ( $partition['fields'] as $field ) {
				ptcm_render_field( $field );
				if ($_GET[$field['id']]) {
					echo '<span class="form-error">Vyplň prosím toto pole.</span>';
				}
			}
			echo '</fieldset>';
		}
	}
	echo '<fieldset class="form-fieldset">';
	echo '<input id="submitted" type="hidden" name="submitted" value="true">';
	wp_nonce_field( 'post_nonce', 'post_nonce_field' );
	echo '<button class="button form-button" type="submit">Přihlašuji se na plavbu!</button>';
	echo '</fieldset>';
	echo '</form>';

}

function ptcm_render_field( $args ) {
	echo '<label class="form-label" for="' . $args['id'] . '">' . $args['name'] . ':</label>';
	switch ( $args['type'] ) {
		case 'text':
		case 'date':
		case 'email':
		case 'checkbox':
			echo '<input class="form-input form-' . $args['type'] . '" type="' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" required></input>';
			break;
		case 'textarea':
			echo '<textarea class="form-input form-' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" ></textarea>';
			break;
		case 'select':
			echo '<select class="form-input form-' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '" >';
			foreach ( $args['options'] as $value => $label ) {
				echo '<option value="' . $value . '">' . $label . '</option>';

			}
			echo '</select>';
			break;
		case 'radio':
			foreach ( $args['options'] as $value => $label ) {
				echo '<label class="form-radio-label" for="' . $args['id'] . '_' . $value . '">';
				echo '<input class="form-radio-input" type="' . $args['type'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '_' . $value . '" ></input>';
				echo  $label;
				echo '</label>';
			}
			break;
	}
}
