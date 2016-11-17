<?php


/**
 * Register post type
 */

add_action( 'init', 'ptcm_create_post_type' );
function ptcm_create_post_type() {
	add_post_type_support( 'page', 'excerpt' );
	register_post_type( 'ptcm_vintage',
		array(
			'labels'      => array(
				'name' => __( 'Ročník' ),
			),
			'public'      => true,
			'has_archive' => true,
			'supports'    => array(
				'title'
			),
			'menu_icon'   => 'dashicons-admin-users',
			'menu_position' => 5
		)
	);
}


/**
 * Register meta boxes
 *
 * @return array of meta fields
 */

add_filter( 'rwmb_meta_boxes', 'ptcm_settings_meta' );
function ptcm_settings_meta( $meta_boxes ) {

	$prefix = 'ptcm_';

	$meta_boxes[] = array(
		'title'      => __( 'Data', 'textdomain' ),
		'post_types' => $prefix . 'vintage',
		'fields'     => array(
			array(
				'id'   => $prefix . 'field',
				'name' => __( 'pole', 'textdomain' ),
				'type' => 'group',
				'clone' => true,
				'sort_clone' => true,
				'fields' => array(
					array(
						'id'   => $prefix . 'name',
						'name' => __( 'Popisek', 'textdomain' ),
						'type' => 'text'
					),
					array(
						'id'      => $prefix . 'type',
						'name'    => __( 'Typ', 'textdomain' ),
						'type'    => 'select',
						'options' => array(
							'text'     => 'Text',
							'textarea' => 'Textová oblast',
							'date'     => 'Datum',
							'email'    => 'E-mail',
							'select'   => 'Rozevírací seznam',
							'checkbox' => 'Checkbox',
							'radio'    => 'Radiobox',
						)
					),
					array(
						'id'    => $prefix . 'options',
						'name'  => __( 'Možnosti', 'textdomain' ),
						'desc'    => __( 'Pouze u rozevíracího seznamu a radioboxu', 'textdomain' ),
						'type'  => 'text',
						'clone' => true,
						'sort_clone' => true
					),

				)
			),
		),
	);

	return $meta_boxes;
}

