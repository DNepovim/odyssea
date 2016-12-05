<?php

include '../../../wp-load.php';


if ( isset( $_POST['submitted'] )
     && isset( $_POST['post_nonce_field'] )
     && wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' )
) {

	$prefix = 'ptcm_';

	$fields = ptcm_add_default_questions();

	foreach ( $fields as $partition ) {
		foreach ( $partition['fields'] as $field ) {
			$value = trim( $_POST[ $field['id'] ] );
			if ( ! isset( $value ) ) {
				$err[ $field['id'] ] = true;
				$hasError            = true;
			}
		}
	}


	$vintage       = get_page_by_title( $_POST['year'], 'OBJECT', $prefix . 'vintage' );
	$custom_fields = get_post_meta( $vintage->ID, $prefix . 'field', true );
	$i             = 0;

	foreach ( $custom_fields as $field ) {

		$id    = $prefix . 'custom_meta_' . sprintf( "%02d", $i ++ );
		$value = trim( $_POST[ $id ] );
		if ( ! isset( $value ) ) {
			$err[ $id ] = true;
			$hasError   = true;
		}
	}

	if ( $hasError ) {
		$query = '?';
		foreach ( $err as $key => $value ) {
			$query .= $key . '=' . $value . '&';
		}
		wp_redirect( wp_get_referer() . $query  );
		exit;
	} else {
		$query = '?success=true';
	}

	$post_information = array(
		'post_title'  => $_POST[ $prefix . 'firstname' ] . ' ' . $_POST[ $prefix . 'surname' ],
		'post_type'   => $prefix . $_POST['year'],
		'post_status' => 'private'
	);

	$post_id = wp_insert_post( $post_information );

	foreach ( $fields as $partition ) {
		foreach ( $partition['fields'] as $field ) {
			$value = trim( $_POST[ $field['id'] ] );
			add_post_meta( $post_id, $field['id'], $value );
		}
	}

	$i = 0;
	foreach ( $custom_fields as $field ) {

		$id    = $prefix . 'custom_meta_' . sprintf( "%02d", $i ++ );
		$value = trim( $_POST[ $id ] );
		add_post_meta( $post_id, $id, $value );
	}


	$vintage = get_page_by_title( $_POST['year'], 'OBJECT', $prefix . 'vintage' );

	$headers .= 'From:Odysseus Ithacky <odysseus.ithacky@gmail.com>' . "\r\n";
	$headers .= 'Content-type: text/html; UTF-8' . "\r\n";

	if (wp_mail(
		$_POST[ $prefix . 'email' ],
		get_post_meta( $vintage->ID, $prefix . 'mail_subject', true ),
		get_post_meta( $vintage->ID, $prefix . 'mail_body', true ),
		$headers
	)) {
		add_post_meta( $post_id, 'ptcm_mail_state', 'successfully send' );
	} else {
		add_post_meta( $post_id, 'ptcm_mail_state', 'there is some error' );
	}

	if ( $post_id ) {
		wp_redirect( wp_get_referer() . $query  );
	}
}
