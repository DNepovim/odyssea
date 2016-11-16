<?php


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
			'menu_icon'   => 'dashicons-admin-users'
		)
	);
}
