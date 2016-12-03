<?php


/**
 * Get list of vintages
 *
 * @return array of vintages
 */

function ptcm_get_vintages() {
	$args = array(
		'post_type' => 'ptcm_vintage',
		'offset'    => 0,
		'order'     => 'ASC',
	);

	return get_posts( $args );
}


/**
 * Register post type for every vintage
 */

add_action( 'init', 'ptcm_create_vintage' );
function ptcm_create_vintage() {

	$prefix = 'ptcm_';

	$vintages = ptcm_get_vintages();

	add_post_type_support( 'page', 'excerpt' );


	foreach ( $vintages as $item ) {
		register_post_type( $prefix . $item->post_title,
			array(
				'labels'        => array(
					'name' => $item->post_title,
				),
				'public'        => true,
				'has_archive'   => true,
				'supports'      => array(
					'title',
					'thumbnail'
				),
				'menu_icon'     => 'dashicons-admin-users',
				'menu_position' => 1,
				'show_in_menu'  => 'edit.php?post_type=ptcm_vintage'
			)
		);
	}
}


/**
 * Create list of all meta fields
 *
 * @return array of meta fields
 */

add_filter( 'rwmb_meta_boxes', 'ptcm_vintages_meta' );
function ptcm_vintages_meta($meta_boxes) {

	$prefix = 'ptcm_';

	$vintages = ptcm_get_vintages();

	foreach ( $vintages as $item ) {

		$next_question[]   = ptcm_add_next_questions( $item );
		$vintages_titles[] = $prefix . $item->post_title;

	};

	$default_questions = ptcm_add_default_questions( $vintages_titles );

	$meta_boxes = array_merge( $default_questions, $next_question );

	return $meta_boxes;
}


/**
 * Create list with defaults questions
 *
 * @param  array of vintages titles
 *
 * @return array of meta fields
 */

function ptcm_add_default_questions( $vintages_titles = '') {

	$prefix = 'ptcm_';

	$meta_boxes[] = array(
		'title'      => __( 'Osobní údaje', 'textdomain' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'   => $prefix . 'gender',
				'name' => __( 'Pohlaví', 'textdomain' ),
				'type' => 'radio',
				'options' => array(
					'male' => 'skaut',
					'female' => 'skautka'
				)
			),
			array(
				'id'   => $prefix . 'nickname',
				'name' => __( 'Přezdívka', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'firstname',
				'name' => __( 'Jméno', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'surname',
				'name' => __( 'Příjmení', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'birthdate',
				'name' => __( 'Datum narození', 'textdomain' ),
				'type' => 'date'
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Kontakty', 'textdomain' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'   => $prefix . 'email',
				'name' => __( 'E-mail', 'textdomain' ),
				'type' => 'email'
			),
			array(
				'id'   => $prefix . 'phone',
				'name' => __( 'Telefonní číslo', 'textdomain' ),
				'type' => 'text'
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Bydliště', 'textdomain' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'   => $prefix . 'address_street',
				'name' => __( 'Ulice', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'address_number',
				'name' => __( 'Číslo popisné', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'address_city',
				'name' => __( 'Město', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'address_post_code',
				'name' => __( 'PSČ', 'textdomain' ),
				'type' => 'text'
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Oddíl', 'textdomain' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'   => $prefix . 'section',
				'name' => __( 'Název', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'unit',
				'name' => __( 'Středisko', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'chief',
				'name' => __( 'Jméno vedoucího oddílu', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'chief_contact',
				'name' => __( 'Kontakt na vedoucího oddílu', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'co-chief',
				'name' => __( 'Jméno zástupce vedoucího oddílu', 'textdomain' ),
				'type' => 'text'
			),
			array(
				'id'   => $prefix . 'co-chief_contact',
				'name' => __( 'Kontakt na zástupce vedoucího oddílu', 'textdomain' ),
				'type' => 'text'
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'SMSka ' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'   => $prefix . 'sms',
				'name' => __( 'pro insktruktory a účastníky – představ se – kdo jsi, co rád děláš…', 'textdomain', 'textdomain' ),
				'type' => 'textarea'
			)
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Přijetí', 'textdomain' ),
		'post_types' => $vintages_titles,
		'context' => 'side',
		'frontend' => false,
		'fields'     => array(
			array(
				'id'      => $prefix . 'record',
				'name'    => __( 'Rekord', 'textdomain' ),
				'type'    => 'select',
				'options' => array(
					false => 'neodevzdal',
					true  => 'odevzdal'
				)
			),
			array(
				'id'      => $prefix . 'record',
				'name'    => __( 'Přijat', 'textdomain' ),
				'type'    => 'checkbox'
			),
		)
	);

	return $meta_boxes;
}


/**
 * Create list with next questions
 *
 * @param  WP_Post $item vintage post object
 *
 * @return array of meta fields
 */

function ptcm_add_next_questions( $item ) {

	$prefix = 'ptcm_';

	$fields_values = get_post_meta( $item->ID, $prefix . 'field', true );
	$i             = 0;

	foreach ( $fields_values as $field ) {


		$field_settings = array(
			'id'   => $prefix . 'custom_meta_' . sprintf( "%02d", $i++ ),
			'name' => __( $field[ $prefix . 'name' ], 'textdomain' ),
			'type' => $field[ $prefix . 'type' ]
		);

		if ( array_key_exists( $prefix . 'options', $field ) ) {
			$field_settings['options'] = $field[ $prefix . 'options' ];
		}

		$fields[] = $field_settings;
	}

	$meta_boxes = array(
		'title'      => __( 'Další otázky', 'textdomain' ),
		'post_types' => $prefix . $item->post_title,
		'fields'     => $fields
	);

	return $meta_boxes;
}
