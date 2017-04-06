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

$vintages = ptcm_get_vintages();

foreach ( $vintages as $vintage ) {
	$hook = 'manage_edit-ptcm_' . $vintage->post_title . '_columns';
	add_filter( $hook, 'add_new_participants_columns' );

	$hook = 'manage_ptcm_' . $vintage->post_title . '_posts_custom_column';
	add_action( $hook, 'manage_participants_columns', 10, 2 );

}

// Add to admin_init function

function add_new_participants_columns( $gallery_columns ) {
	$new_columns['cb'] = '<input type="checkbox" />';

	$new_columns['title']    = 'Jméno';
	$new_columns['nickname'] = 'Přezdívka';
	$new_columns['record']   = 'Rekord';
	$new_columns['accepted'] = 'Přijat';

	return $new_columns;
}

// Add to admin_init function

function manage_participants_columns( $column_name, $id ) {
	global $wpdb;
	switch ( $column_name ) {
		case 'nickname':
			echo get_post_meta( $id, 'ptcm_nickname', true );
			break;
		case 'record':
			$record = get_post_meta( $id, 'ptcm_record', true );
			if ( $record ) {
				echo 'odevzdal';
			} else {

				echo 'neodevzdal';
			}
			break;
		case 'accepted':
			$accepted = get_post_meta( $id, 'ptcm_accepted', true );
			if ( $accepted == 1 ) {
				echo 'nepřijat';
			} else if ( $accepted == 2 ) {
				echo 'přijat';
			};
			break;
		default:
			break;
	}
}

// Add to our admin_init function
add_action( 'quick_edit_custom_box', 'ptcm_add_quick_edit', 10, 2 );

function ptcm_add_quick_edit( $column_name, $post_type ) {
	$prefix = 'ptcm_';
	if ( $column_name != 'record' ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<span class="title">Rekord</span>
			<input type="hidden" name="ptcm_record_noncename" id="ptcm_record_noncename" value=""/>
			<select name='post_record' id='post_record'>
				<option class='option' value='1'>Odevzdal</option>
				<option class='option' value='0'>Neodevzdal</option>
				?>
			</select>
		</div>
	</fieldset>
	<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<span class="title">Přijat</span>
			<input type="hidden" name="ptcm_accepted_noncename" id="ptcm_accepted_noncename" value=""/>
			<select name='post_accepted' id='post_accepted'>
				<option class='option' value='0'>Nerozhodnuto</option>
				<option class='option' value='1'>Nepřijat</option>
				<option class='option' value='2'>Přijat</option>
				?>
			</select>
		</div>
	</fieldset>
	<?php
}

// Add to our admin_init function
add_action( 'save_post', 'ptcm_save_quick_edit_data' );
function ptcm_save_quick_edit_data( $post_id ) {
	$prefix = 'ptcm_';
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}
	$post = get_post( $post_id );
	if ( isset( $_POST['post_record'] ) && ( $post->post_type != 'revision' ) ) {
		$ptcm_set_id = esc_attr( $_POST['post_record'] );
		if ( $ptcm_set_id ) {
			update_post_meta( $post_id, $prefix . 'record', $ptcm_set_id );
		} else {
			delete_post_meta( $post_id, $prefix . 'record' );
		}
	}
	if ( isset( $_POST['post_accepted'] ) && ( $post->post_type != 'revision' ) ) {
		$ptcm_set_id = esc_attr( $_POST['post_accepted'] );
		if ( $ptcm_set_id ) {
			update_post_meta( $post_id, $prefix . 'accepted', $ptcm_set_id );
		} else {
			delete_post_meta( $post_id, $prefix . 'accepted' );
		}
	}

	return $ptcm_set_id;
}

/**
 * Join posts and postmeta tables
 *
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 */
function cf_search_where( $where ) {
    global $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );

/**
 * Create list of all meta fields
 *
 * @return array of meta fields
 */

add_filter( 'rwmb_meta_boxes', 'ptcm_vintages_meta' );
function ptcm_vintages_meta( $meta_boxes ) {

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

function ptcm_add_default_questions( $vintages_titles = '' ) {

	$prefix = 'ptcm_';

	$meta_boxes[] = array(
		'title'      => __( 'Přijetí', 'textdomain' ),
		'post_types' => $vintages_titles,
		'context'    => 'side',
		'frontend'   => false,
		'fields'     => array(
			array(
				'id'   => $prefix . 'record',
				'name' => __( 'Rekord', 'textdomain' ),
				'type' => 'checkbox',
			),
			array(
				'id'      => $prefix . 'accepted',
				'name'    => __( 'Přijat', 'textdomain' ),
				'type'    => 'select',
				'options' => array(
					0 => 'nerozhodnuto',
					1 => 'nepřijat',
					2 => 'přijat'
				)
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Mail', 'textdomain' ),
		'post_types' => $vintages_titles,
		'context'    => 'side',
		'frontend'   => false,
		'fields'     => array(
			array(
				'id'   => $prefix . 'mail_state',
				'name' => __( 'Status', 'textdomain' ),
				'type' => 'text'
			),
		)
	);

	$meta_boxes[] = array(
		'title'      => __( 'Osobní údaje', 'textdomain' ),
		'post_types' => $vintages_titles,
		'fields'     => array(
			array(
				'id'      => $prefix . 'gender',
				'name'    => __( 'Pohlaví', 'textdomain' ),
				'type'    => 'radio',
				'options' => array(
					'male'   => 'skaut',
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

		if ( ! empty( $field['ptcm_name'] ) && ! empty( $field['ptcm_type'] ) ) {
			$field_settings = array(
				'id'   => $prefix . 'custom_meta_' . sprintf( "%02d", $i ++ ),
				'name' => __( $field[ $prefix . 'name' ], 'textdomain' ),
				'type' => $field[ $prefix . 'type' ]
			);

			if ( array_key_exists( $prefix . 'options', $field ) ) {
				$field_settings['options'] = $field[ $prefix . 'options' ];
			}

			$fields[] = $field_settings;
		}
	}

	if ( ! empty( $fields ) ) {
		$meta_boxes = array(
			'title'      => __( 'Další otázky', 'textdomain' ),
			'post_types' => $prefix . $item->post_title,
			'fields'     => $fields
		);

		return $meta_boxes;
	}

	return false;

}
